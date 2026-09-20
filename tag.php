<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
$tagIds = selectedTagIds();
$stmt = db()->query('SELECT t.*, COUNT(DISTINCT ph.id) AS use_count FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id JOIN photos ph ON ph.id = pt.photo_id JOIN projects p ON p.id = ph.project_id WHERE ph.visibility = "public" AND p.visibility = "public" AND p.user_id = ph.user_id GROUP BY t.id ORDER BY use_count DESC, t.name');
$tags = $stmt->fetchAll();
$photos = [];
if ($tagIds) {
  $sql = 'SELECT ph.* FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.visibility = "public" AND p.visibility = "public" AND p.user_id = ph.user_id';
  foreach ($tagIds as $tagId) $sql .= ' AND EXISTS (SELECT 1 FROM photo_tags pt WHERE pt.photo_id = ph.id AND pt.tag_id = ?)';
  $stmt = db()->prepare($sql . ' ORDER BY ph.created_at DESC, ph.id DESC');
  $stmt->execute($tagIds);
  $photos = $stmt->fetchAll();
}
$pageTitle = 'タグで写真を探す';
require __DIR__ . '/includes/header.php';
?>
<section class="tagHeader">
  <h1>タグで写真を探す</h1>
  <?php renderTagFilter($tags, $tagIds, BASE_URL . '/tag.php'); ?>
  <p><?= $tagIds ? count($photos) . '枚の写真' : 'タグを選んで絞り込んでください。' ?></p>
</section>
<?php if ($tagIds && !$photos): ?><p>選んだタグをすべて含む公開写真はありません。</p><?php endif; ?>
<section class="photoGrid">
  <?php foreach ($photos as $photo): ?><a class="photoCard" href="<?= BASE_URL ?>/photo.php?id=<?= (int)$photo['id'] ?>"><img src="<?= h(publicPhotoPath($photo['file_path'])) ?>" alt="<?= h($photo['original_filename']) ?>" loading="lazy"></a><?php endforeach; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
