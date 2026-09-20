<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
require_once __DIR__ . '/../includes/image.php';
$userId = requirePhotographer();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = db()->prepare('SELECT ph.*, p.title AS project_title FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.id = ? AND ph.user_id = ? AND p.user_id = ?');
$stmt->execute([$id, $userId, $userId]);
$photo = $stmt->fetch();
if (!$photo) { http_response_code(404); exit('写真が見つかりません。'); }
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$from = (string)($_POST['from'] ?? $_GET['from'] ?? 'photo');
if (!in_array($from, ['photo', 'photos', 'project'], true)) $from = 'photo';
$returnPath = match ($from) {
  'photos' => 'admin/project.php?id=' . (int)$photo['project_id'],
  'project' => 'admin/project.php?id=' . (int)$photo['project_id'],
  default => 'admin/photo.php?id=' . $id,
};
$tags = availableTags($userId);
$stmt = db()->prepare('SELECT tag_id FROM photo_tags WHERE photo_id = ?');
$stmt->execute([$id]);
$selected = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
foreach ($_SESSION['tag_edit_sessions'] ?? [] as $key => $context) {
  if ($context['expires'] < time()) unset($_SESSION['tag_edit_sessions'][$key]);
}
$editToken = is_string($_POST['edit_token'] ?? null) ? $_POST['edit_token'] : '';
$editContext = tagEditContext($editToken, $userId);
if (!$editContext || $editContext['photo_id'] !== $id) {
  $editToken = bin2hex(random_bytes(24));
  $_SESSION['tag_edit_sessions'][$editToken] = ['user_id' => $userId, 'photo_id' => $id, 'expires' => time() + 7200, 'created' => []];
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) { http_response_code(403); exit('ページを再読み込みしてください。'); }
  $pdo = db();
  if (($_POST['action'] ?? '') === 'delete') {
    try {
      $pdo->prepare('DELETE FROM photos WHERE id = ? AND user_id = ?')->execute([$id, $userId]);
      foreach (['file_path', 'original_path'] as $field) {
        $path = realpath(__DIR__ . '/../' . $photo[$field]);
        $root = realpath(__DIR__ . '/../uploads');
        if ($path && $root && str_starts_with($path, $root . '/') && is_file($path)) unlink($path);
      }
      db()->prepare('UPDATE projects SET updated_at = NOW() WHERE id = ? AND user_id = ?')->execute([$photo['project_id'], $userId]);
      redirect('admin/project.php?id=' . (int)$photo['project_id']);
    } catch (Throwable $e) { $errors[] = '写真を削除できませんでした。'; }
  } else {
    $photo['caption'] = trim((string)($_POST['caption'] ?? ''));
    $photo['visibility'] = (string)($_POST['visibility'] ?? 'public');
    $position = isset($_POST['use_default_position']) ? 'default' : (string)($_POST['watermark_position'] ?? 'default');
    $photo['watermark_position'] = $position === 'default' ? null : $position;
    $photo['watermark_size'] = ($_POST['watermark_size'] ?? '') === '' ? null : (string)$_POST['watermark_size'];
    $photo['watermark_opacity'] = ($_POST['watermark_opacity'] ?? '') === '' ? null : (int)$_POST['watermark_opacity'];
    if ($photo['watermark_size'] !== null && !in_array($photo['watermark_size'], ['small', 'medium', 'large'], true)) $errors[] = '文字サイズを選択してください。';
    if ($photo['watermark_opacity'] !== null && !in_array($photo['watermark_opacity'], [100, 50, 20, 10], true)) $errors[] = '文字の濃さを選択してください。';
    $selected = array_values(array_unique(array_map('intval', (array)($_POST['tag_ids'] ?? []))));
    $newTag = trim((string)($_POST['new_tag_name'] ?? ''));
    if (mb_strlen($photo['caption']) > 1000) $errors[] = 'キャプションは1000文字以内にしてください。';
    if (!in_array($photo['visibility'], ['public', 'private', 'draft'], true)) $errors[] = '公開状態を選択してください。';
    if ($position !== 'default' && !isset(watermarkPositions()[$position])) $errors[] = 'ウォーターマークの位置を選択してください。';
    if (array_diff($selected, array_map('intval', array_column($tags, 'id')))) $errors[] = '選択できないタグが含まれています。';
    $newNames = [];
    try { $newNames = parseTagNames($newTag); } catch (RuntimeException $e) { $errors[] = $e->getMessage(); }
    if (!$errors) {
      $staged = [];
      try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id FROM photos WHERE id = ? AND user_id = ? FOR UPDATE');
        $stmt->execute([$id, $userId]);
        if (!$stmt->fetch()) throw new RuntimeException('写真が見つかりません。');
        foreach ($newNames as $name) $selected[] = createOwnedTag($userId, $name);
        $selected = validatePhotoTags($userId, $selected);
        $staged[] = stagePhotoWatermark($photo, $user);
        $pdo->prepare('UPDATE photos SET caption = ?, visibility = ?, watermark_position = ?, watermark_size = ?, watermark_opacity = ? WHERE id = ? AND user_id = ?')->execute([$photo['caption'], $photo['visibility'], $photo['watermark_position'], $photo['watermark_size'], $photo['watermark_opacity'], $id, $userId]);
        $pdo->prepare('DELETE FROM photo_tags WHERE photo_id = ?')->execute([$id]);
        $stmt = $pdo->prepare('INSERT INTO photo_tags (photo_id, tag_id) VALUES (?, ?)');
        foreach (array_unique($selected) as $tagId) $stmt->execute([$id, $tagId]);
        commitWatermarkRows($pdo, $staged, $userId);
        $pdo->commit();
        discardWatermarkFiles($staged, true);
        unset($_SESSION['tag_edit_sessions'][$editToken]);
        redirect($returnPath);
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        discardWatermarkFiles($staged);
        error_log($e->getMessage());
        $errors[] = $e instanceof RuntimeException && !$e instanceof PDOException ? $e->getMessage() : '保存できませんでした。元画像と入力内容を確認してください。';
      }
    }
  }
}
$pageTitle = '写真を編集';
$pageClass = 'accountPage adminPage';
$themeUserId = $userId;
require __DIR__ . '/../includes/header.php';
?>
<section class="settingsShell">
  <p class="eyebrow">EDIT YOUR MOMENT</p>
  <h1>写真を編集</h1>
  <p class="accountHelp"><?= h($photo['project_title']) ?></p>
  <p><a class="textLink" href="<?= BASE_URL ?>/<?= h($returnPath) ?>">← 元の画面へ戻る</a></p>
  <?php if ($errors): ?><div class="notice error" role="alert"><?php foreach ($errors as $error): ?><p><?= h($error) ?></p><?php endforeach; ?></div><?php endif; ?>
  <figure class="fullPhoto editPhotoPreview"><img src="<?= h(publicPhotoPath($photo['file_path'])) ?>" alt="編集中の写真"></figure>
  <form method="post" class="formStack">
    <input type="hidden" name="edit_token" value="<?= h($editToken) ?>">
    <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="from" value="<?= h($from) ?>">
    <section class="settingsCard formStack">
      <h2>写真の情報</h2>
      <label>キャプション<textarea name="caption" rows="5" maxlength="1000"><?= h($photo['caption']) ?></textarea></label>
      <label>公開状態<select name="visibility"><?php foreach (['public', 'private', 'draft'] as $visibility): ?><option value="<?= h($visibility) ?>" <?= $photo['visibility'] === $visibility ? 'selected' : '' ?>><?= h(visibilityName($visibility)) ?></option><?php endforeach; ?></select></label>
      <p class="accountHelp">写真が公開でも、プロジェクトが下書き・非公開の場合は外部に表示されません。</p>
    </section>
    <section class="settingsCard formStack">
      <h2>タグ</h2>
      <p class="accountHelp">タグは最大10件、各12文字以内です。選択を外すとこの写真から解除されます。今回の編集で追加した未使用タグだけ×で削除できます。</p>
      <?php require __DIR__ . '/../includes/tag_search_ui.php'; ?>
      <div class="tagList" data-tag-list data-tag-delete="<?= BASE_URL ?>/admin/tag_delete.php"><?php foreach ($tags as $tag): ?><span class="tagManageItem"><label class="tagCheckbox" style="<?= h(profileTagStyle($tag)) ?>"><input type="checkbox" name="tag_ids[]" value="<?= (int)$tag['id'] ?>" <?= in_array((int)$tag['id'], $selected, true) ? 'checked' : '' ?>><span class="tag" style="<?= h(profileTagStyle($tag)) ?>">#<?= h($tag['name']) ?></span></label><?php if (in_array((int)$tag['id'], $_SESSION['tag_edit_sessions'][$editToken]['created'], true)): ?><button type="button" class="tagDeleteButton" data-delete-tag="<?= (int)$tag['id'] ?>" data-tag-name="<?= h($tag['name']) ?>" aria-label="<?= h($tag['name']) ?>を登録タグから削除">×</button><?php endif; ?></span><?php endforeach; ?></div>
      <?php require __DIR__ . '/../includes/tag_create_ui.php'; ?>
    </section>
    <section class="settingsCard formStack">
      <h2>ウォーターマーク</h2>
      <div class="settingsColumns">
        <label>文字サイズ<select name="watermark_size"><?php foreach (['' => 'プロフィール設定に合わせる', 'small' => '小', 'medium' => '中', 'large' => '大'] as $key => $label): ?><option value="<?= h($key) ?>" <?= ($photo['watermark_size'] ?? '') === $key ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
        <label>文字の濃さ<select name="watermark_opacity"><?php foreach (['' => 'プロフィール設定に合わせる', 100 => '100%', 50 => '50%', 20 => '20%', 10 => '10%'] as $key => $label): ?><option value="<?= h((string)$key) ?>" <?= (string)($photo['watermark_opacity'] ?? '') === (string)$key ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
      </div>
      <label class="checkLine"><input type="checkbox" name="use_default_position" value="1" data-default-position="<?= h($user['watermark_position']) ?>" <?= !$photo['watermark_position'] ? 'checked' : '' ?>>プロフィールの位置設定に合わせる</label>
      <div class="watermarkLayout">
        <div class="positionGrid"><?php foreach (watermarkPositions() as $key => $label): ?><label><input type="radio" name="watermark_position" value="<?= h($key) ?>" <?= ($photo['watermark_position'] ?: $user['watermark_position']) === $key ? 'checked' : '' ?>><span><?= h($label) ?></span></label><?php endforeach; ?></div>
        <figure class="watermarkLivePreview" data-watermark-preview="<?= BASE_URL ?>/admin/watermark_preview.php" data-photo-id="<?= $id ?>" data-enabled="<?= (int)$user['watermark_enabled'] ?>">
          <figcaption>この写真でプレビュー</figcaption><img alt="現在の位置設定を反映した写真" hidden><p class="accountHelp" role="status" aria-live="polite">読み込み中です。</p>
        </figure>
      </div>
      <p class="accountHelp">この写真だけ位置・文字サイズ・文字の濃さを変更できます。文字の内容と色はプロフィール設定を使います。<?= !$user['watermark_enabled'] ? '現在はウォーターマークがオフになっています。' : '' ?></p>
    </section>
    <div class="settingsActions"><button class="button primary" type="submit" name="action" value="save">編集完了</button><a class="button" href="<?= BASE_URL ?>/admin/photo_edit.php?id=<?= $id ?>&amp;from=<?= h($from) ?>">元に戻す</a></div>
  </form>
  <details class="settingsDanger"><summary>この写真を削除</summary><form method="post" data-confirm="この写真を削除しますか？元には戻せません。"><input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><button class="button danger" name="action" value="delete">写真を削除</button></form></details>
</section>
<script src="<?= BASE_URL ?>/assets/js/tag-create.js" defer></script>
<script src="<?= BASE_URL ?>/assets/js/watermark-preview.js" defer></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
