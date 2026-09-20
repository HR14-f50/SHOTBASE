<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/public_sidebar.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT p.*, u.username FROM projects p JOIN users u ON u.id = p.user_id WHERE p.id = ? AND p.visibility = "public"');
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) {
  http_response_code(404);
  require __DIR__ . '/includes/header.php';
  echo '<p>プロジェクトが見つかりません。</p>';
  require __DIR__ . '/includes/footer.php';
  exit;
}
$guestPreview = currentUserId() === (int)$project['user_id'] && ($_GET['preview'] ?? '') === 'guest';
$tagIds = selectedTagIds();
$stmt = db()->prepare('SELECT t.*, tm.name AS team_name, tm.border_color AS team_border_color, tm.background_color AS team_background_color, COUNT(DISTINCT ph.id) AS use_count FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id JOIN photos ph ON ph.id = pt.photo_id LEFT JOIN teams tm ON tm.id = t.team_id WHERE ph.project_id = ? AND ph.user_id = ? AND ph.visibility = "public" GROUP BY t.id ORDER BY use_count DESC, t.name');
$stmt->execute([$id, $project['user_id']]);
$projectTags = $stmt->fetchAll();
$sql = 'SELECT ph.* FROM photos ph WHERE ph.project_id = ? AND ph.user_id = ? AND ph.visibility = "public"';
$params = [$id, $project['user_id']];
foreach ($tagIds as $tagId) { $sql .= ' AND EXISTS (SELECT 1 FROM photo_tags pt WHERE pt.photo_id = ph.id AND pt.tag_id = ?)'; $params[] = $tagId; }
$stmt = db()->prepare($sql . ' ORDER BY ph.created_at DESC, ph.id DESC');
$stmt->execute($params);
$photos = $stmt->fetchAll();
$pageTitle = $project['title'];
$pageClass = 'publicDetailPage projectPublicPage';
$fullWidth = true;
$themeUserId = (int)$project['user_id'];
require __DIR__ . '/includes/header.php';
?>
<div class="profileLayout publicDetailLayout">
  <?php renderPublicSidebar((int)$project['user_id'], $guestPreview, $projectTags, $tagIds, $id); ?>
  <div class="profileAreaC">
    <section class="projectHeader">
      <h1><?= h($project['title']) ?></h1>
      <p class="accountHelp"><?= h(projectDateLabel($project)) ?> · <?= count($photos) ?>枚</p>
      <p class="projectUpdated">更新 <?= h(date('Y.m.d H:i', strtotime($project['updated_at']))) ?></p>
      <?php if ($project['description']): ?><p><?= nl2br(h($project['description'])) ?></p><?php endif; ?>
    </section>
    <?php if (!$photos): ?><p class="profileEmpty">該当する写真はありません。</p><?php endif; ?>
    <section class="photoGrid">
      <?php foreach ($photos as $photo): ?><a class="photoCard" href="<?= BASE_URL ?>/photo.php?id=<?= (int)$photo['id'] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"><img src="<?= h(publicPhotoPath($photo['file_path'])) ?>" alt="<?= h($photo['original_filename']) ?>" loading="lazy"></a><?php endforeach; ?>
    </section>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
