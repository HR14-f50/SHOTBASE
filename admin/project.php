<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
require_once __DIR__ . '/../includes/public_sidebar.php';
$userId = requirePhotographer();
$id = (int)($_GET['id'] ?? 0);
$project = ownedProject($id, $userId);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) { http_response_code(403); exit('ページを再読み込みしてください。'); }
  $pdo = db();
  try {
    $action = (string)($_POST['action'] ?? '');
    if (!in_array($action, ['bulk_tags', 'bulk_visibility', 'bulk_delete'], true)) throw new RuntimeException('操作が正しくありません。');
    $photoIds = array_values(array_unique(array_map('intval', (array)($_POST['photos'] ?? []))));
    if (!$photoIds || count($photoIds) > 200) throw new RuntimeException('写真を選択してください（最大200枚）。');
    $bulkTagIds = $action === 'bulk_tags' ? validatePhotoTags($userId, (array)($_POST['bulk_tags'] ?? [])) : [];
    $visibility = (string)($_POST['visibility'] ?? '');
    if ($action === 'bulk_tags' && !$bulkTagIds) throw new RuntimeException('追加するタグを選択してください。');
    if ($action === 'bulk_visibility' && !in_array($visibility, ['public', 'private'], true)) throw new RuntimeException('公開範囲を選択してください。');
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ? FOR UPDATE');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) throw new RuntimeException('プロジェクトが見つかりません。');
    $stmt = $pdo->prepare('SELECT id, file_path, original_path FROM photos WHERE project_id = ? AND user_id = ? AND id IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ') ORDER BY id FOR UPDATE');
    $stmt->execute([$id, $userId, ...$photoIds]);
    $selectedPhotos = $stmt->fetchAll();
    if (count($selectedPhotos) !== count($photoIds)) throw new RuntimeException('このプロジェクトの写真だけ選択できます。');
    if ($action === 'bulk_tags') {
      foreach ($photoIds as $photoId) {
        $stmt = $pdo->prepare('SELECT tag_id FROM photo_tags WHERE photo_id = ?');
        $stmt->execute([$photoId]);
        validatePhotoTags($userId, [...$stmt->fetchAll(PDO::FETCH_COLUMN), ...$bulkTagIds]);
      }
      $stmt = $pdo->prepare('INSERT IGNORE INTO photo_tags (photo_id, tag_id) VALUES (?, ?)');
      foreach ($photoIds as $photoId) foreach ($bulkTagIds as $tagId) $stmt->execute([$photoId, $tagId]);
    } elseif ($action === 'bulk_visibility') {
      $stmt = $pdo->prepare('UPDATE photos SET visibility = ?, updated_at = NOW() WHERE project_id = ? AND user_id = ? AND id IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ')');
      $stmt->execute([$visibility, $id, $userId, ...$photoIds]);
    } else {
      $stmt = $pdo->prepare('DELETE FROM photos WHERE project_id = ? AND user_id = ? AND id IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ')');
      $stmt->execute([$id, $userId, ...$photoIds]);
    }
    $pdo->prepare('UPDATE projects SET updated_at = NOW() WHERE id = ?')->execute([$id]);
    $pdo->commit();
    if ($action === 'bulk_delete') {
      $root = realpath(__DIR__ . '/../uploads');
      foreach ($selectedPhotos as $selectedPhoto) {
        foreach (['file_path', 'original_path'] as $field) {
          $path = realpath(__DIR__ . '/../' . ltrim((string)($selectedPhoto[$field] ?? ''), '/'));
          if ($path && $root && str_starts_with($path, $root . '/') && is_file($path)) @unlink($path);
        }
      }
      flash('bulk_tags', count($photoIds) . '枚の写真を削除しました。');
    } elseif ($action === 'bulk_visibility') {
      flash('bulk_tags', count($photoIds) . '枚の写真を「' . visibilityName($visibility) . '」に変更しました。');
    } else {
      flash('bulk_tags', count($photoIds) . '枚の写真にタグを追加しました。');
    }
    redirect('admin/project.php?id=' . $id);
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $errors[] = $e instanceof PDOException ? 'タグを保存できませんでした。' : $e->getMessage();
  }
}
$tagIds = selectedTagIds();
$stmt = db()->prepare('SELECT t.*, tm.name AS team_name, tm.border_color AS team_border_color, tm.background_color AS team_background_color, COUNT(DISTINCT ph.id) AS use_count FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id JOIN photos ph ON ph.id = pt.photo_id LEFT JOIN teams tm ON tm.id = t.team_id WHERE ph.project_id = ? AND ph.user_id = ? GROUP BY t.id ORDER BY use_count DESC, t.name');
$stmt->execute([$id, $userId]);
$projectTags = $stmt->fetchAll();
$sql = 'SELECT ph.* FROM photos ph WHERE ph.project_id = ? AND ph.user_id = ?';
$params = [$id, $userId];
foreach ($tagIds as $tagId) { $sql .= ' AND EXISTS (SELECT 1 FROM photo_tags pt WHERE pt.photo_id = ph.id AND pt.tag_id = ?)'; $params[] = $tagId; }
$stmt = db()->prepare($sql . ' ORDER BY ph.created_at DESC, ph.id DESC');
$stmt->execute($params);
$photos = $stmt->fetchAll();
$tags = availableTags($userId);
$pageTitle = $project['title'];
$pageClass = 'accountPage adminPage';
$fullWidth = true;
$themeUserId = $userId;
require __DIR__ . '/../includes/header.php';
?>
<div class="profileLayout publicDetailLayout">
  <?php renderPublicSidebar($userId, false, $projectTags, $tagIds, $id, true); ?>
  <div class="profileAreaC">
    <p><a class="textLink" href="<?= BASE_URL ?>/<?= h(accountHome()) ?>#projects">← マイプロフィールへ戻る</a></p>
    <?php if ($message = flash('bulk_tags')): ?><p class="notice success" role="status"><?= h($message) ?></p><?php endif; ?>
    <?php foreach ($errors as $error): ?><p class="notice error" role="alert"><?= h($error) ?></p><?php endforeach; ?>
    <?php if (isset($_GET['updated'])): ?><p class="notice success">プロジェクトを更新しました。</p><?php endif; ?>
    <section class="projectHeader">
      <h1><?= h($project['title']) ?></h1>
      <p class="accountHelp"><?= h(projectDateLabel($project)) ?> · <?= h(visibilityName($project['visibility'])) ?> · <?= count($photos) ?>枚</p>
      <p class="projectUpdated">更新 <?= h(date('Y.m.d H:i', strtotime($project['updated_at']))) ?></p>
      <?php if ($project['description']): ?><p><?= nl2br(h($project['description'])) ?></p><?php endif; ?>
      <div class="actionRow"><a class="button primary" href="<?= BASE_URL ?>/admin/project_edit.php?id=<?= $id ?>">プロジェクトを編集</a><a class="button" href="<?= BASE_URL ?>/project.php?id=<?= $id ?>&amp;preview=guest" target="_blank" rel="noopener">訪問者表示をプレビュー ↗</a></div>
    </section>
    <form method="post" data-bulk-tags>
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <details class="settingsCard bulkTagPanel"><summary>写真にタグを一括追加</summary>
        <p class="accountHelp">タグを選んでから、下の写真を選択してください。既存タグを残して追加します。各写真の合計は最大10件です。写真の選択状態は、公開範囲の一括変更・一括削除にも使えます。</p>
        <?php require __DIR__ . '/../includes/tag_search_ui.php'; ?>
        <?php
          $tagPickerTags = $tags;
          $tagPickerSelected = [];
          $tagPickerInputName = 'bulk_tags[]';
          $tagPickerLabelClass = 'tagChoice';
          require __DIR__ . '/../includes/tag_picker.php';
        ?>
        <?php $tagInputName = 'bulk_tags[]'; require __DIR__ . '/../includes/tag_create_ui.php'; ?>
        <div class="actionRow bulkPhotoActions">
          <button class="button" type="button" data-select-photos>表示中の写真をすべて選択</button>
          <button class="button primary" type="submit" name="action" value="bulk_tags">選択した写真にタグを追加</button>
          <label>公開範囲<select name="visibility"><option value="public">公開</option><option value="private">非公開</option></select></label>
          <button class="button" type="submit" name="action" value="bulk_visibility">公開範囲を一括設定</button>
          <button class="button danger" type="submit" name="action" value="bulk_delete" data-confirm="選択した写真を削除しますか？この操作は取り消せません。">選択した写真を削除</button>
          <span data-photo-selection role="status">0枚選択中</span>
        </div>
      </details>
      <div class="managePhotoGrid">
        <a class="photoAddCard" href="<?= BASE_URL ?>/admin/upload.php?project_id=<?= $id ?>"><span>写真を追加</span><span class="addPlus" aria-hidden="true">＋</span></a>
        <?php foreach ($photos as $photo): ?>
          <article class="managePhotoCard">
            <label class="bulkPhotoChoice"><input type="checkbox" name="photos[]" value="<?= (int)$photo['id'] ?>">一括操作の対象にする</label>
            <a class="managePhotoImage" href="<?= BASE_URL ?>/admin/photo.php?id=<?= (int)$photo['id'] ?>"><img src="<?= h(publicPhotoPath($photo['file_path'])) ?>" alt="<?= h($photo['original_filename']) ?>" loading="lazy"></a>
            <div class="managePhotoMeta"><span><?= h(visibilityName($photo['visibility'])) ?></span><a href="<?= BASE_URL ?>/admin/photo_edit.php?id=<?= (int)$photo['id'] ?>&amp;from=project">編集 →</a></div>
          </article>
        <?php endforeach; ?>
      </div>
    </form>
    <details class="settingsDanger"><summary>プロジェクトを削除</summary><p>写真もすべて削除されます。この操作は取り消せません。</p><form method="post" action="<?= BASE_URL ?>/admin/project_delete.php" data-confirm="このプロジェクトと写真をすべて削除しますか？"><input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>"><input type="hidden" name="project_id" value="<?= $id ?>"><button class="button danger">プロジェクトを削除</button></form></details>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
