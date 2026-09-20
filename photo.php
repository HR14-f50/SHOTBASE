<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/public_sidebar.php';
$pageClass = 'publicDetailPage';
$fullWidth = true;

$id = (int)($_GET['id'] ?? 0);
require_once __DIR__ . '/includes/auth.php';
$guestPreview = false;
if (($_GET['preview'] ?? '') === 'guest' && currentUserId() !== null) {
  $previewCheck = db()->prepare('SELECT id FROM photos WHERE id = ? AND user_id = ?');
  $previewCheck->execute([$id, currentUserId()]);
  $guestPreview = (bool)$previewCheck->fetch();
}

// -------------------------------------
// 写真取得
// -------------------------------------

$stmt = db()->prepare('
  SELECT
    ph.*,
    pr.title AS project_title,
    pr.id AS project_id,
    u.username,
    u.nickname
  FROM photos ph
  INNER JOIN projects pr
    ON pr.id = ph.project_id
  INNER JOIN users u
    ON u.id = ph.user_id
  WHERE ph.id = ?
    AND ph.visibility = "public"
    AND pr.visibility = "public"
');

$stmt->execute([$id]);

$photo = $stmt->fetch();

// 写真が存在しない・非公開の場合
if (!$photo) {

  http_response_code(404);

  require_once __DIR__ . '/includes/header.php';

  echo '
    <section class="emptyState">
      <h1>写真が見つかりません。</h1>
      <p>
        この写真は非公開になっているか、
        削除された可能性があります。
      </p>
    </section>
  ';

  require_once __DIR__ . '/includes/footer.php';

  exit;
}

$stmt = db()->prepare('SELECT ph.id FROM photos ph JOIN projects pr ON pr.id = ph.project_id WHERE ph.project_id = ? AND ph.user_id = ? AND ph.visibility = "public" AND pr.visibility = "public" ORDER BY ph.created_at DESC, ph.id DESC');
$stmt->execute([(int)$photo['project_id'], (int)$photo['user_id']]);
$photoIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
$photoIndex = array_search($id, $photoIds, true);
$previousPhotoId = $photoIndex === false ? null : ($photoIds[$photoIndex - 1] ?? null);
$nextPhotoId = $photoIndex === false ? null : ($photoIds[$photoIndex + 1] ?? null);

// -------------------------------------
// 写真についているタグ取得
// -------------------------------------

$stmt = db()->prepare('
  SELECT
    t.id,
    t.name,
    t.tag_type,
    t.division_id,
    t.team_id,
    t.border_color,
    t.background_color,
    tm.border_color AS team_border_color,
    tm.background_color AS team_background_color
  FROM photo_tags pt
  INNER JOIN tags t
    ON t.id = pt.tag_id
  LEFT JOIN teams tm
    ON tm.id = t.team_id
  WHERE pt.photo_id = ?
  ORDER BY t.name ASC
');

$stmt->execute([$id]);

$tags = $stmt->fetchAll();

$themeUserId = (int)$photo['user_id'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="profileLayout publicDetailLayout">
  <?php renderPublicSidebar((int)$photo['user_id'], $guestPreview, null, [], 0, false, true); ?>
  <div class="profileAreaC">
<section class="photoDetail">

  <!-- 撮影者 -->
  <p class="muted">

    <?php if (!empty($photo['nickname'])): ?>

      <?= h($photo['nickname']) ?>

    <?php else: ?>

      @<?= h($photo['username']) ?>

    <?php endif; ?>

  </p>

  <!-- 写真 -->
  <div class="photoDetailImage">

    <img
      src="<?= h(publicPhotoPath($photo['file_path'])) ?>"
      alt="<?= h($photo['original_filename'] ?? '写真') ?>"
      draggable="false"
    >

  </div>

  <nav class="photoPager" aria-label="プロジェクト内の写真">
    <?php if ($previousPhotoId): ?><a class="button" rel="prev" href="<?= BASE_URL ?>/photo.php?id=<?= $previousPhotoId ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>">← 前の写真</a><?php else: ?><span class="button isDisabled" aria-disabled="true">← 前の写真</span><?php endif; ?>
    <span><?= $photoIndex === false ? '' : ((string)($photoIndex + 1) . ' / ' . count($photoIds)) ?></span>
    <?php if ($nextPhotoId): ?><a class="button" rel="next" href="<?= BASE_URL ?>/photo.php?id=<?= $nextPhotoId ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>">次の写真 →</a><?php else: ?><span class="button isDisabled" aria-disabled="true">次の写真 →</span><?php endif; ?>
  </nav>

  <!-- キャプション -->
  <?php if (!empty($photo['caption'])): ?>

    <div class="photoCaption">

      <p>
        <?= nl2br(h($photo['caption'])) ?>
      </p>

    </div>

  <?php endif; ?>

  <!-- タグ -->
  <?php if ($tags): ?>

    <div class="photoTags">

      <?php foreach ($tags as $tag): ?>

        <?php

        $borderColor =
          !empty($tag['border_color'])
            ? $tag['border_color']
            : '#cccccc';

        $backgroundColor =
          !empty($tag['background_color'])
            ? $tag['background_color']
            : '#ffffff';

        ?>

        <a
          class="photoTag"
          href="<?= BASE_URL ?>/tag.php?tag=<?= (int)$tag['id'] ?>"
          style="<?= h(profileTagStyle($tag)) ?>"
        >
          #<?= h($tag['name']) ?>
        </a>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

  <!-- プロジェクトへ戻る -->
  <div class="actionRow">

    <a
      class="button"
      href="<?= BASE_URL ?>/project.php?id=<?= (int)$photo['project_id'] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"
    >
      プロジェクトへ戻る
    </a>

  </div>

</section>

</div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
