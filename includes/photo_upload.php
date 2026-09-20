<?php
declare(strict_types=1);
require_once __DIR__ . '/image.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/admin_helpers.php';

/** 容量・実体・拡張形式をサーバー側でも検証します。 */
function validatePhotoUpload(array $file): array
{
  $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
  if ($error !== UPLOAD_ERR_OK) {
    throw new RuntimeException(match ($error) {
      UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'ファイルがサーバーの容量制限を超えています。20MB未満の写真を選択してください。',
      UPLOAD_ERR_PARTIAL => '送信が途中で切れました。この写真を再試行してください。',
      UPLOAD_ERR_NO_FILE => '写真が選択されていません。',
      default => '写真を受け取れませんでした。もう一度お試しください。',
    });
  }
  $path = (string)($file['tmp_name'] ?? '');
  if (!is_uploaded_file($path)) throw new RuntimeException('アップロードされた写真を確認できません。');
  $size = filesize($path);
  if ($size === false || $size === 0 || $size >= MAX_UPLOAD_BYTES) {
    throw new RuntimeException('1枚20MB以上のファイルは登録できません。20MB未満の写真を選択してください。');
  }
  $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
  if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    throw new RuntimeException('JPEG・PNG・WebP形式の写真を選択してください。');
  }
  return imageInfo($path);
}

/** 画像の保存とDB登録。失敗した場合は作成したファイルだけを削除します。 */
function saveUploadedPhoto(array $file, array $user, int $projectId, array $tagIds): array
{
  $tagIds = validatePhotoTags((int)$user['id'], $tagIds);
  $info = validatePhotoUpload($file);
  foreach ([PHOTO_UPLOAD_DIR, PHOTO_ORIGINAL_DIR] as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
      throw new RuntimeException('写真の保存フォルダーを作成できません。');
    }
  }
  $filename = bin2hex(random_bytes(16)) . '.jpg';
  $original = PHOTO_ORIGINAL_DIR . $filename;
  $display = PHOTO_UPLOAD_DIR . $filename;
  $pdo = db();
  try {
    createResizedImage($file['tmp_name'], $original, $info);
    $text = (int)$user['watermark_enabled'] === 1 ? watermarkLine($user) : '';
    if ($text === '') {
      if (!copy($original, $display)) throw new RuntimeException('表示用画像を保存できませんでした。');
      $image = imageInfo($display) + ['file_size' => filesize($display)];
    } else {
      $image = createResizedImage($original, $display, imageInfo($original), $text, $user['watermark_position'] ?: 'bottom-right', $user);
    }
    $pdo->beginTransaction();
    // 別タブから同時登録しても、プロジェクト上限を超えないようロックします。
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ? FOR UPDATE');
    $stmt->execute([$projectId, $user['id']]);
    if (!$stmt->fetch()) throw new RuntimeException('プロジェクトが見つかりません。');
    if (projectPhotoCount($projectId) >= MAX_PHOTOS_PER_PROJECT) {
      throw new RuntimeException('プロジェクトの写真上限に達しています。');
    }
    $name = mb_substr(basename(str_replace('\\', '/', (string)$file['name'])), 0, 255);
    $stmt = $pdo->prepare('
      INSERT INTO photos (project_id, user_id, filename, original_filename, original_path, file_path,
        mime_type, width, height, file_size, visibility)
      VALUES (?, ?, ?, ?, ?, ?, "image/jpeg", ?, ?, ?, "public")
    ');
    $stmt->execute([$projectId, $user['id'], $filename, $name, 'uploads/originals/' . $filename,
      'uploads/photos/' . $filename, $image['width'], $image['height'], $image['file_size']]);
    $id = (int)$pdo->lastInsertId();
    $stmt = $pdo->prepare('INSERT IGNORE INTO photo_tags (photo_id, tag_id) SELECT ?, id FROM tags WHERE id = ? AND (user_id = ? OR user_id IS NULL)');
    foreach ($tagIds as $tagId) $stmt->execute([$id, $tagId, $user['id']]);
    $pdo->prepare('UPDATE projects SET updated_at = NOW() WHERE id = ?')->execute([$projectId]);
    $pdo->commit();
    return ['photo_id' => $id, 'bytes' => (int)$image['file_size']];
  } catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    foreach ([$original, $display] as $path) if (is_file($path)) unlink($path);
    throw $error;
  }
}
