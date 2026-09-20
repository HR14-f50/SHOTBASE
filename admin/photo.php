<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
$userId = requirePhotographer();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT ph.*, p.title AS project_title, p.cover_photo_id FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.id = ? AND ph.user_id = ? AND p.user_id = ?');
$stmt->execute([$id, $userId, $userId]);
$photo = $stmt->fetch();
if (!$photo) { http_response_code(404); exit('写真が見つかりません。'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) { http_response_code(403); exit('ページを再読み込みしてください。'); }
  if (($_POST['action'] ?? '') === 'set_cover') {
    db()->prepare('UPDATE projects SET cover_photo_id = ? WHERE id = ? AND user_id = ?')->execute([$id, $photo['project_id'], $userId]);
  } elseif (($_POST['action'] ?? '') === 'clear_cover') {
    db()->prepare('UPDATE projects SET cover_photo_id = NULL WHERE id = ? AND user_id = ?')->execute([$photo['project_id'], $userId]);
  } else { http_response_code(400); exit('操作が正しくありません。'); }
  redirect('admin/photo.php?id=' . $id . '&cover_saved=1');
}
$stmt = db()->prepare('SELECT t.*, tm.name AS team_name, tm.border_color AS team_border_color, tm.background_color AS team_background_color FROM photo_tags pt JOIN tags t ON t.id = pt.tag_id LEFT JOIN teams tm ON tm.id = t.team_id WHERE pt.photo_id = ? ORDER BY t.name');
$stmt->execute([$id]);
$tags = $stmt->fetchAll();
$stmt = db()->prepare('SELECT id FROM photos WHERE project_id = ? AND user_id = ? ORDER BY created_at DESC, id DESC');
$stmt->execute([$photo['project_id'], $userId]);
$ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
$index = array_search($id, $ids, true);
$previous = $ids[$index - 1] ?? null;
$next = $ids[$index + 1] ?? null;
$pageTitle = '写真を見る';
$pageClass = 'accountPage adminPage';
$themeUserId = $userId;
require __DIR__ . '/../includes/header.php';
?>
<section class="adminPhotoDetail" aria-label="写真の詳細">
  <div class="photoDetailToolbar">
    <a class="textLink" href="<?= BASE_URL ?>/admin/project.php?id=<?= (int)$photo['project_id'] ?>">← <?= h($photo['project_title']) ?>の写真一覧</a>
    <a class="button" href="<?= BASE_URL ?>/admin/photo_edit.php?id=<?= $id ?>&amp;from=photo">この写真を編集</a>
  </div>
  <?php if (isset($_GET['cover_saved'])): ?><p class="notice success" role="status">プロジェクトのカバー設定を保存しました。</p><?php endif; ?>
  <form method="post" class="coverActions">
    <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
    <?php if ((int)$photo['cover_photo_id'] === $id): ?>
      <span class="accountHelp">この写真はプロジェクトのカバーに設定されています。</span>
      <button class="button" name="action" value="clear_cover">カバーを自動選択に戻す</button>
    <?php else: ?><button class="button" name="action" value="set_cover">この写真をプロジェクトのカバーにする</button><?php endif; ?>
    <?php if ($photo['visibility'] !== 'public'): ?><p class="accountHelp">この写真が非公開の間、訪問者には別の公開写真をカバーとして表示します。</p><?php endif; ?>
  </form>
  <figure class="fullPhoto"><img src="<?= h(publicPhotoPath($photo['file_path'])) ?>" alt="<?= h($photo['original_filename']) ?>"></figure>
  <nav class="photoPager" aria-label="プロジェクト内の写真">
    <?php if ($previous): ?><a class="button" rel="prev" href="<?= BASE_URL ?>/admin/photo.php?id=<?= $previous ?>">← 前の写真</a><?php else: ?><span class="button isDisabled" aria-disabled="true">← 前の写真</span><?php endif; ?>
    <span><?= $index + 1 ?> / <?= count($ids) ?></span>
    <?php if ($next): ?><a class="button" rel="next" href="<?= BASE_URL ?>/admin/photo.php?id=<?= $next ?>">次の写真 →</a><?php else: ?><span class="button isDisabled" aria-disabled="true">次の写真 →</span><?php endif; ?>
  </nav>
  <div class="photoBelow">
    <?php if ($photo['caption']): ?><p class="photoCaption"><?= nl2br(h($photo['caption'])) ?></p><?php endif; ?>
    <div class="tagList"><?php foreach ($tags as $tag): ?><span class="tag" style="<?= h(profileTagStyle($tag)) ?>">#<?= h($tag['name']) ?></span><?php endforeach; ?></div>
    <p class="accountHelp"><?= h(visibilityName($photo['visibility'])) ?></p>
  </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
