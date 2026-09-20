<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/profile_helpers.php';
require_once __DIR__ . '/../includes/photo_upload.php';
require_once __DIR__ . '/../includes/admin_helpers.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
ini_set('memory_limit', '512M');

function uploadResponse(array $data, int $status = 200): never
{
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') uploadResponse(['error' => 'POSTで送信してください。'], 405);
$userId = currentUserId();
if ($userId === null) uploadResponse(['error' => 'ログインが必要です。別のタブでログインしてから再試行してください。'], 401);
if (currentUserType() !== 'photographer') uploadResponse(['error' => '写真投稿ユーザーのみ利用できます。'], 403);
if (!profileCsrfValid()) uploadResponse(['error' => '送信情報を確認できません。ページを再読み込みしてください。'], 403);
$projectId = (int)($_POST['project_id'] ?? 0);
$stmt = db()->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$projectId, $userId]);
if (!$stmt->fetch()) uploadResponse(['error' => 'プロジェクトが見つかりません。'], 404);

// 24時間以内のバッチを保持し、送信結果が届かなかった場合の重複登録を防ぎます。
$_SESSION['photo_batches'] ??= [];
foreach ($_SESSION['photo_batches'] as $key => $batch) {
  if ($batch['created'] < time() - 86400) unset($_SESSION['photo_batches'][$key]);
}
$action = $_POST['action'] ?? '';
if ($action === 'begin') {
  $count = (int)($_POST['file_count'] ?? 0);
  if ($count < 1 || $count > MAX_UPLOAD_BATCH) uploadResponse(['error' => '1回に選べる写真は1〜50枚です。'], 422);
  $remaining = MAX_PHOTOS_PER_PROJECT - projectPhotoCount($projectId);
  if ($count > $remaining) uploadResponse(['error' => "このプロジェクトにはあと{$remaining}枚まで登録できます。"], 422);
  if (count($_SESSION['photo_batches']) >= 100) uploadResponse(['error' => '送信回数が多すぎます。時間をおいて再度お試しください。'], 429);
  try {
    $pdo = db();
    $pdo->beginTransaction();
    $tagIds = array_values(array_unique(array_map('intval', (array)($_POST['tag_ids'] ?? []))));
    foreach (parseTagNames(is_string($_POST['new_tag_name'] ?? null) ? $_POST['new_tag_name'] : '') as $name) $tagIds[] = createOwnedTag($userId, $name);
    $tagIds = validatePhotoTags($userId, $tagIds);
    $pdo->commit();
  } catch (Throwable $e) {
    if (db()->inTransaction()) db()->rollBack();
    uploadResponse(['error' => $e instanceof PDOException ? 'タグを保存できませんでした。' : $e->getMessage()], 422);
  }
  $id = bin2hex(random_bytes(24));
  $_SESSION['photo_batches'][$id] = ['created' => time(), 'project_id' => $projectId, 'count' => $count,
    'tags' => $tagIds, 'results' => []];
  uploadResponse(['batch_id' => $id]);
}
if ($action !== 'upload') uploadResponse(['error' => '操作が正しくありません。'], 400);
$batchId = is_string($_POST['batch_id'] ?? null) ? $_POST['batch_id'] : '';
$batch = $_SESSION['photo_batches'][$batchId] ?? null;
$index = filter_var($_POST['item_index'] ?? null, FILTER_VALIDATE_INT);
if (!$batch || $batch['project_id'] !== $projectId || $index === false || $index < 0 || $index >= $batch['count']) {
  uploadResponse(['error' => '送信情報の有効期限が切れたか、写真の件数が不正です。ページを再読み込みしてください。'], 422);
}
if (isset($batch['results'][$index])) uploadResponse($batch['results'][$index]);
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
try {
  if (!isset($_FILES['photo']) || is_array($_FILES['photo']['name'])) throw new RuntimeException('写真を1枚ずつ送信してください。');
  $result = saveUploadedPhoto($_FILES['photo'], $user, $projectId, $batch['tags']);
  $_SESSION['photo_batches'][$batchId]['results'][$index] = $result;
  uploadResponse($result);
} catch (RuntimeException $error) {
  if ($error instanceof PDOException) {
    error_log($error->getMessage());
    uploadResponse(['error' => '写真を保存できませんでした。再試行してください。'], 500);
  }
  uploadResponse(['error' => $error->getMessage()], 422);
} catch (Throwable $error) {
  error_log($error->getMessage());
  uploadResponse(['error' => '写真を処理できませんでした。再試行してください。'], 500);
}
