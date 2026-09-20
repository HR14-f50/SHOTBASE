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
    if (($_POST['action'] ?? '') !== 'bulk_tags') throw new RuntimeException('操作が正しくありません。');
    $tagIds = validatePhotoTags($userId, (array)($_POST['bulk_tags'] ?? []));
    $photoIds = array_values(array_unique(array_map('intval', (array)($_POST['photos'] ?? []))));
    if (!$tagIds || !$photoIds || count($photoIds) > 200) throw new RuntimeException('タグと写真を選択してください（写真は最大200枚）。');
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM projects WHERE id = ? AND user_id = ? FOR UPDATE');
    $stmt->execute([$id, $userId]);
    if (!$stmt->fetch()) throw new RuntimeException('プロジェクトが見つかりません。');
    $stmt = $pdo->prepare('SELECT id FROM photos WHERE project_id = ? AND user_id = ? AND id IN (' . implode(',', array_fill(0, count($photoIds), '?')) . ') ORDER BY id FOR UPDATE');
    $stmt->execute([$id, $userId, ...$photoIds]);
    if (count($stmt->fetchAll()) !== count($photoIds)) throw new RuntimeException('このプロジェクトの写真だけ選択できます。');
    foreach ($photoIds as $photoId) {
      $stmt = $pdo->prepare('SELECT tag_id FROM photo_tags WHERE photo_id = ?');
      $stmt->execute([$photoId]);
      validatePhotoTags($userId, [...$stmt->fetchAll(PDO::FETCH_COLUMN), ...$tagIds]);
    }
    $stmt = $pdo->prepare('INSERT IGNORE INTO photo_tags (photo_id, tag_id) VALUES (?, ?)');
    foreach ($photoIds as $photoId) foreach ($tagIds as $tagId) $stmt->execute([$photoId, $tagId]);
    $pdo->prepare('UPDATE projects SET updated_at = NOW() WHERE id = ?')->execute([$id]);
    $pdo->commit();
    flash('bulk_tags', count($photoIds) . '枚の写真にタグを追加しました。');
    redirect('admin/project.php?id=' . $id);
  } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $errors[] = $e instanceof PDOException ? 'タグを保存できませんでした。' : $e->getMessage();
  }
}
$tagIds = selectedTagIds();
$stmt = db()->prepare('SELECT t.*, COUNT(DISTINCT ph.id) AS use_count FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id JOIN photos ph ON ph.id = pt.photo_id WHERE ph.project_id = ? AND ph.user_id = ? GROUP BY t.id ORDER BY use_count DESC, t.name');
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
      <input type="hidden" name="action" value="bulk_tags">
      <details class="settingsCard bulkTagPanel"><summary>写真にタグを一括追加</summary>
        <p class="accountHelp">タグを選んでから、下の写真を選択してください。既存タグを残して追加します。各写真の合計は最大10件です。1枚でも上限を超える場合は全件保存しません。</p>
        <?php require __DIR__ . '/../includes/tag_search_ui.php'; ?>
        <?php
          $tagPickerTags = $tags;
          $tagPickerSelected = [];
          $tagPickerInputName = 'bulk_tags[]';
          $tagPickerLabelClass = 'tagChoice';
          require __DIR__ . '/../includes/tag_picker.php';
        ?>
        <?php $tagInputName = 'bulk_tags[]'; require __DIR__ . '/../includes/tag_create_ui.php'; ?>
        <div class="actionRow"><button class="button" type="button" data-select-photos>表示中の写真をすべて選択</button><button class="button primary" type="submit">選択した写真にタグを追加</button><span data-photo-selection role="status">0枚選択中</span></div>
      </details>
      <div class="managePhotoGrid">
        <a class="photoAddCard" href="<?= BASE_URL ?>/admin/upload.php?project_id=<?= $id ?>"><span>写真を追加</span><span class="addPlus" aria-hidden="true">＋</span></a>
        <?php foreach ($photos as $photo): ?>
          <article class="managePhotoCard">
            <label class="bulkPhotoChoice"><input type="checkbox" name="photos[]" value="<?= (int)$photo['id'] ?>">タグ一括追加の対象にする</label>
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
