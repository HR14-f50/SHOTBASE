<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
require_once __DIR__ . '/includes/theme_palette.php';

$username = is_string($_GET['username'] ?? null) ? $_GET['username'] : '';
$stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND user_type = "photographer"');
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) {
  http_response_code(404);
  $pageTitle = 'ユーザーが見つかりません';
  require_once __DIR__ . '/includes/header.php';
  echo '<section class="emptyState"><h1>ユーザーが見つかりません。</h1></section>';
  require_once __DIR__ . '/includes/footer.php';
  exit();
}

$userId = (int) $user['id'];
$guestPreview = currentUserId() === $userId && ($_GET['preview'] ?? '') === 'guest';
$previewBack = profileUrl($username);
$isOwner = currentUserId() === $userId && !$guestPreview;
$pickupError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_pickup') {
  if (!$isOwner || !profileCsrfValid()) { http_response_code(403); exit('この操作は許可されていません。'); }
  $ids = array_values(array_unique(array_map('intval', (array)($_POST['featured_photos'] ?? []))));
  $stmt = db()->prepare('SELECT ph.id FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.user_id = ? AND p.user_id = ? AND ph.visibility = "public" AND p.visibility = "public"');
  $stmt->execute([$userId, $userId]);
  if (count($ids) > 12 || array_diff($ids, array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)))) {
    $pickupError = '自分の公開写真から12枚以内で選んでください。';
  } else {
    $pdo = db();
    try {
      $pdo->beginTransaction();
      $pdo->prepare('DELETE FROM user_featured_photos WHERE user_id = ?')->execute([$userId]);
      $stmt = $pdo->prepare('INSERT INTO user_featured_photos (user_id, photo_id, sort_order) VALUES (?, ?, ?)');
      foreach ($ids as $order => $id) $stmt->execute([$userId, $id, $order]);
      $pdo->commit();
      header('Location: ' . profileUrl($username) . '#pickupTitle', true, 303);
      exit;
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $pickupError = 'ピックアップを保存できませんでした。もう一度お試しください。';
    }
  }
}
$pickupChoices = [];
if ($isOwner) {
  $stmt = db()->prepare('SELECT ph.*, p.title AS project_title FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.user_id = ? AND p.user_id = ? AND ph.visibility = "public" AND p.visibility = "public" ORDER BY ph.id DESC');
  $stmt->execute([$userId, $userId]);
  $pickupChoices = $stmt->fetchAll();
}
$sorts = [
  'newest' => ['最新順', 'p.created_at DESC, p.id DESC'],
  'oldest' => ['古い順', 'p.created_at ASC, p.id ASC'],
  'likes' => ['いいね順', 'like_count DESC, p.created_at DESC, p.id DESC'],
  'photos' => ['写真が多い順', 'photo_count DESC, p.created_at DESC, p.id DESC'],
];
$sort = is_string($_GET['sort'] ?? null) && isset($sorts[$_GET['sort']]) ? $_GET['sort'] : 'newest';
$month = is_string($_GET['month'] ?? null) ? $_GET['month'] : '';
if ($month !== 'undated' && !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
  $month = '';
}
$tagIds = selectedTagIds();
$tagId = $tagIds[0] ?? 0;
$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = array_filter(['preview' => $guestPreview ? 'guest' : '', 'month' => $month, 'tags' => $tagIds, 'sort' => $sort]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'save_pickup') {
  if ($guestPreview || currentUserId() === null || !profileCsrfValid()) {
    http_response_code(403);
    exit('ログイン状態を確認し、ページを再読み込みしてください。');
  }
  $projectId = (int) ($_POST['project_id'] ?? 0);
  $stmt = db()->prepare(
    'SELECT id FROM projects WHERE id = ? AND user_id = ? AND visibility = "public"',
  );
  $stmt->execute([$projectId, $userId]);
  if (!$stmt->fetch()) {
    http_response_code(404);
    exit('プロジェクトが見つかりません。');
  }
  if (($_POST['like_action'] ?? '') === 'add') {
    $stmt = db()->prepare('INSERT IGNORE INTO project_likes (user_id, project_id) VALUES (?, ?)');
  } elseif (($_POST['like_action'] ?? '') === 'remove') {
    $stmt = db()->prepare('DELETE FROM project_likes WHERE user_id = ? AND project_id = ?');
  } else {
    http_response_code(400);
    exit('操作が正しくありません。');
  }
  $stmt->execute([currentUserId(), $projectId]);
  header(
    'Location: ' . profileUrl($username, $filters + ['page' => $page]) . '#projects',
    true,
    303,
  );
  exit();
}

$stmt = db()->prepare('
  SELECT t.id, t.name, t.tag_type, t.border_color, t.background_color, COUNT(DISTINCT ph.id) AS use_count
  FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id
  JOIN photos ph ON ph.id = pt.photo_id
  JOIN projects p ON p.id = ph.project_id
  WHERE p.user_id = ? AND ph.user_id = p.user_id
    AND (' . ($isOwner ? '1 = 1' : 'p.visibility = "public" AND ph.visibility = "public"') . ')
  GROUP BY t.id, t.name, t.tag_type, t.border_color, t.background_color
  ORDER BY use_count DESC, t.name ASC LIMIT 20
');
$stmt->execute([$userId]);
$popularTags = $stmt->fetchAll();

$stmt = db()->prepare('
  SELECT CASE WHEN date_mode = "none" OR shooting_date IS NULL THEN "undated"
    ELSE DATE_FORMAT(shooting_date, "%Y-%m") END AS archive_month, COUNT(*) AS total
  FROM projects WHERE user_id = ? AND (' . ($isOwner ? '1 = 1' : 'visibility = "public"') . ')
  GROUP BY archive_month ORDER BY archive_month = "undated", archive_month DESC
');
$stmt->execute([$userId]);
$archives = $stmt->fetchAll();
$archiveYears = [];
$undatedCount = 0;
foreach ($archives as $archive) {
  if ($archive['archive_month'] === 'undated') {
    $undatedCount = (int) $archive['total'];
  } else {
    $archiveYears[substr($archive['archive_month'], 0, 4)][] = $archive;
  }
}
$totalProjects = array_sum(array_column($archives, 'total'));
$stmt = db()->prepare('
  SELECT COUNT(*) FROM photos ph JOIN projects p ON p.id = ph.project_id
  WHERE p.user_id = ? AND ph.user_id = p.user_id AND (' . ($isOwner ? '1 = 1' : 'p.visibility = "public" AND ph.visibility = "public"') . ')
');
$stmt->execute([$userId]);
$totalPhotos = (int) $stmt->fetchColumn();

$stmt = db()->prepare('
  SELECT ph.* FROM user_featured_photos f JOIN photos ph ON ph.id = f.photo_id
  JOIN projects p ON p.id = ph.project_id
  WHERE f.user_id = ? AND ph.user_id = f.user_id AND p.user_id = f.user_id
    AND ph.visibility = "public" AND p.visibility = "public"
  ORDER BY f.sort_order, ph.id LIMIT 12
');
$stmt->execute([$userId]);
$featuredPhotos = $stmt->fetchAll();

$where = 'p.user_id = ?' . ($isOwner ? '' : ' AND p.visibility = "public"');
$params = [$userId];
if ($month === 'undated') {
  $where .= ' AND (p.date_mode = "none" OR p.shooting_date IS NULL)';
} elseif ($month !== '') {
  $where .= ' AND p.date_mode <> "none" AND p.shooting_date >= ? AND p.shooting_date < ?';
  $params[] = $month . '-01';
  $params[] = (new DateTimeImmutable($month . '-01'))->modify('+1 month')->format('Y-m-d');
}
if ($tagIds) {
  $where .= ' AND EXISTS (SELECT 1 FROM photos tagged WHERE tagged.project_id = p.id AND tagged.user_id = p.user_id AND (' . ($isOwner ? '1 = 1' : 'tagged.visibility = "public"') . ')';
  foreach ($tagIds as $filterTag) {
    $where .= ' AND EXISTS (SELECT 1 FROM photo_tags pt WHERE pt.photo_id = tagged.id AND pt.tag_id = ?)';
    $params[] = $filterTag;
  }
  $where .= ')';
}
$stmt = db()->prepare('SELECT COUNT(*) FROM projects p WHERE ' . $where);
$stmt->execute($params);
$filteredCount = (int) $stmt->fetchColumn();
$projectsPerPage = 20;
$pageCount = max(1, (int) ceil($filteredCount / $projectsPerPage));
$page = min($page, $pageCount);
$offset = ($page - 1) * $projectsPerPage;
$stmt = db()->prepare(
  '
  SELECT p.*,
    (SELECT COUNT(*) FROM photos ph WHERE ph.project_id = p.id AND ph.user_id = p.user_id AND (' . ($isOwner ? '1 = 1' : 'ph.visibility = "public"') . ')) AS photo_count,
    (SELECT ph.file_path FROM photos ph WHERE ph.project_id = p.id AND ph.user_id = p.user_id AND (' . ($isOwner ? '1 = 1' : 'ph.visibility = "public"') . ') ORDER BY (ph.id = p.cover_photo_id) DESC, ph.id LIMIT 1) AS cover_path,
    (SELECT COUNT(*) FROM project_likes pl WHERE pl.project_id = p.id) AS like_count,
    EXISTS (SELECT 1 FROM project_likes mine WHERE mine.project_id = p.id AND mine.user_id = ?) AS liked
  FROM projects p WHERE ' .
    $where .
    ' ORDER BY ' .
    $sorts[$sort][1] .
    ' LIMIT ' . $projectsPerPage . ' OFFSET ' .
    $offset,
);
$stmt->execute(array_merge([currentUserId() ?? 0], $params));
$projects = $stmt->fetchAll();
$pageTitle = $user['nickname'] . 'の写真';
$pageClass = 'publicProfilePage';
$profileTheme = profileThemes()[$user['theme_key']] ?? profileThemes()['white'];
$fullWidth = true;
$themeUserId = $userId;
require_once __DIR__ . '/includes/header.php';
?>

<div class="profileTheme" style="--profile-accent: <?= h($profileTheme['accent']) ?>; --profile-soft: <?= h($profileTheme['soft']) ?>">
<div class="profileLayout">
  <aside
    class="profileAreaB"
    aria-label="写真の絞り込み"
  >
    <?php require __DIR__ . '/includes/profile_sidebar_content.php'; ?>
    <nav
      class="sidebarSection archiveNav"
      aria-label="撮影年月のアーカイブ"
    >
      <p class="eyebrow">ARCHIVE</p>
      <h2>撮影の記録</h2>
      <details class="archiveDisclosure" open><summary>撮影月から探す</summary>
      <a
        class="archiveLink <?= $month === '' ? 'isActive' : '' ?>"
        href="<?= h(profileUrl($username, array_merge($filters, ['month' => '']))) ?>#projects"
      >
        すべての月
        <span><?= $totalProjects ?></span>
      </a>
      <?php foreach ($archiveYears as $year => $months): ?>
        <details open>
          <summary>
            <?= (int) $year ?>年
            <span><?= array_sum(array_column($months, 'total')) ?></span>
          </summary>
          <ul>
            <?php foreach ($months as $archive): ?>
              <li>
                <a
                  class="archiveLink <?= $month === $archive['archive_month'] ? 'isActive' : '' ?>"
                  href="<?= h(profileUrl($username, array_merge($filters, ['month' => $archive['archive_month']]))) ?>#projects"
                  <?= $month === $archive['archive_month'] ? 'aria-current="true"' : '' ?>
                >
                  <?= (int) substr($archive['archive_month'], 5) ?>月
                  <span><?= (int) $archive[ 'total' ] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </details>
      <?php endforeach; ?>
      <a
        class="archiveLink <?= $month === 'undated' ? 'isActive' : '' ?>"
        href="<?= h(profileUrl($username, array_merge($filters, ['month' => 'undated']))) ?>#projects"
        <?= $month === 'undated' ? 'aria-current="true"' : '' ?>
      >
        日付未指定
        <span><?= $undatedCount ?></span>
      </a>
      </details>
    </nav>
  </aside>
  <div class="profileAreaC">
    <section
      class="pickupSection"
      aria-labelledby="pickupTitle"
    >
      <div class="profileSectionHeading">
        <div>
          <p class="eyebrow">SELECTED MOMENTS</p>
          <h2 id="pickupTitle">ピックアップ</h2>
        </div>
        <span>お気に入りの瞬間を、ここに。</span>
      </div>
      <?php if ($isOwner): ?>
        <details class="pickupEditor settingsCard" <?= $pickupError ? 'open' : '' ?>>
          <summary>ピックアップ写真を変更</summary>
          <?php if ($pickupError): ?><p class="notice error" role="alert"><?= h($pickupError) ?></p><?php endif; ?>
          <p class="accountHelp">自分の公開写真から最大12枚選べます。</p>
          <form method="post" action="<?= h(profileUrl($username)) ?>#pickupTitle">
            <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
            <input type="hidden" name="action" value="save_pickup">
            <div class="featuredPhotoChoices">
              <?php foreach ($pickupChoices as $choice): ?>
                <label><input type="checkbox" name="featured_photos[]" value="<?= (int)$choice['id'] ?>" <?= in_array((int)$choice['id'], array_map('intval', array_column($featuredPhotos, 'id')), true) ? 'checked' : '' ?>><img src="<?= h(publicPhotoPath($choice['file_path'])) ?>" loading="lazy" alt="<?= h($choice['original_filename']) ?>"><span><?= h($choice['project_title']) ?></span></label>
              <?php endforeach; ?>
            </div>
            <?php if (!$pickupChoices): ?><p>選べる公開写真はまだありません。</p><?php endif; ?>
            <button class="button" type="submit">ピックアップを保存</button>
          </form>
        </details>
      <?php endif; ?>
      <?php if ($featuredPhotos): ?>
        <div class="pickupSlider"><div class="sliderControls"><button type="button" data-gallery-direction="-1" aria-label="前のピックアップ写真">←</button><button type="button" data-gallery-direction="1" aria-label="次のピックアップ写真">→</button></div>
        <div
          class="pickupGallery"
          data-pickup-gallery tabindex="0" aria-label="ピックアップ写真。左右にスクロールできます"
        >
          <?php foreach ($featuredPhotos as $photo): ?>
            <a
              href="<?= BASE_URL ?>/photo.php?id=<?= (int) $photo['id'] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"
              data-ratio="<?= max(1, (int) $photo['width']) / max(1, (int) $photo['height']) ?>"
            >
              <img
                src="<?= h(publicPhotoPath($photo['file_path'])) ?>"
                alt="<?= h($photo['original_filename']) ?>"
                width="<?= (int) $photo[ 'width' ] ?>"
                height="<?= (int) $photo['height'] ?>"
                loading="lazy"
              />
            </a>
          <?php endforeach; ?>
        </div>
        </div>
      <?php else: ?>
      <div class="profileEmpty pickupEmpty">
        <span
          class="emptyFrame"
          aria-hidden="true"
        >
          ＋
        </span>
        <h3>とっておきの一枚から。</h3>
        <p>ピックアップ写真はまだありません。</p>
        <?php if ($isOwner): ?>
          <a
            class="textLink"
            href="#pickupTitle"
          >
            上の「ピックアップ写真を変更」から選ぶ →
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </section>
    <section
      class="profileProjects"
      id="projects"
      aria-labelledby="projectsTitle"
    >
      <div class="profileSectionHeading">
        <div>
          <p class="eyebrow">PHOTO JOURNAL</p>
          <h2 id="projectsTitle">
            プロジェクト
            <small><?= $filteredCount ?></small>
          </h2>
        </div>
        <form
          class="projectSort"
          method="get"
          action="<?= BASE_URL ?>/profile.php#projects"
        >
          <?php if ($guestPreview): ?><input type="hidden" name="preview" value="guest"><?php endif; ?>
          <input
            type="hidden"
            name="username"
            value="<?= h($username) ?>"
          />
          <input
            type="hidden"
            name="month"
            value="<?= h($month) ?>"
          />
          <?php foreach ($tagIds as $filterTag): ?><input type="hidden" name="tags[]" value="<?= $filterTag ?>"><?php endforeach; ?>
          <label for="projectSort">並び順</label>
          <select
            name="sort"
            id="projectSort"
          >
            <?php foreach ($sorts as $key => $option): ?>
              <option
                value="<?= h($key) ?>"
                <?= $sort === $key ? 'selected' : '' ?>
              >
                <?= h($option[0]) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit">適用</button>
        </form>
      </div>
      <?php if ($month !== '' || $tagIds): ?>
        <div class="profileFilterStatus">
          <span><?= $month === 'undated' ? '日付未指定' : ($month !== '' ? h($month) : 'すべての月') ?><?= $tagIds ? ' · ' . count($tagIds) . 'タグで絞り込み中' : '' ?></span>
          <a href="<?= h(profileUrl($username, ['sort' => $sort] + ($guestPreview ? ['preview' => 'guest'] : []))) ?>#projects">絞り込みを解除 ×</a>
        </div>
      <?php endif; ?>
      <?php if ($isOwner): ?>
        <p class="accountHelp">下書き・非公開は自分だけに表示されます。各プロジェクトから写真を追加・編集できます。</p>
        <a class="profileProjectRow projectCreateRow" href="<?= BASE_URL ?>/admin/project_new.php">
          <span class="projectCoverLink projectCreateThumb" aria-hidden="true">＋</span>
          <span class="projectSummary"><strong>新しいプロジェクトを作成</strong><span class="accountHelp">撮影の記録を、新しいアルバムに。</span></span>
          <span class="projectArrow" aria-hidden="true">→</span>
        </a>
      <?php endif; ?>
      <?php if (!$projects): ?>
        <div class="profileEmpty">
          <h3><?= $month !== '' || $tagIds ? '該当するプロジェクトはありません。' : '撮影の記録を、ここから。' ?></h3>
          <p><?= $month !== '' || $tagIds ? '別の月やタグを選んでみてください。' : '公開したプロジェクトがここに並びます。' ?></p>

        </div>
      <?php else: ?>
      <div class="profileProjectList">
        <?php foreach ($projects as $project): ?>
          <article class="profileProjectRow">
            <a
              class="projectCoverLink"
              href="<?= BASE_URL ?>/<?= $isOwner ? 'admin/' : '' ?>project.php?id=<?= (int) $project[ 'id' ] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"
              aria-label="<?= h($project['title']) ?>を開く"
            >
              <?php if ($project['cover_path']): ?>
                <img
                  src="<?= h(publicPhotoPath($project['cover_path'])) ?>"
                  alt=""
                  loading="lazy"
                />
              <?php else: ?>
              <span
                class="projectCoverPlaceholder"
                aria-hidden="true"
              >
                SHOTBASE
                <br />
                <small>PHOTO JOURNAL</small>
              </span>
            <?php endif; ?>
          </a>
          <div class="projectSummary">
            <p class="projectUpdated">更新 <?= h(date('Y.m.d', strtotime($project['updated_at']))) ?></p>
            <p class="projectDate"><?= h(projectDateLabel($project)) ?></p>
            <h3><a href="<?= BASE_URL ?>/<?= $isOwner ? 'admin/' : '' ?>project.php?id=<?= (int) $project[ 'id' ] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"><?= h($project['title']) ?></a></h3>
            <?php if ($project['description']): ?>
              <p class="projectExcerpt"><?= h($project['description']) ?></p>
            <?php endif; ?>
            <div class="projectRowMeta">
              <span><?= (int) $project[ 'photo_count' ] ?> 写真</span>
              <?php if ($isOwner): ?><span class="visibilityPill"><?= ['public' => '公開', 'private' => '非公開', 'draft' => '下書き'][$project['visibility']] ?></span><a class="textLink" href="<?= BASE_URL ?>/admin/project.php?id=<?= (int)$project['id'] ?>">編集・写真を追加</a><?php endif; ?>
              <?php if (!$guestPreview && currentUserId() !== null && $project['visibility'] === 'public'): ?>
                <form
                  method="post"
                  action="<?= h(profileUrl($username, $filters + ['page' => $page])) ?>#projects"
                >
                  <input
                    type="hidden"
                    name="csrf"
                    value="<?= h(profileCsrfToken()) ?>"
                  />
                  <input
                    type="hidden"
                    name="project_id"
                    value="<?= (int) $project[ 'id' ] ?>"
                  />
                  <input
                    type="hidden"
                    name="like_action"
                    value="<?= $project['liked'] ? 'remove' : 'add' ?>"
                  />
                  <button
                    class="projectLike"
                    type="submit"
                    aria-pressed="<?= $project['liked'] ? 'true' : 'false' ?>"
                    aria-label="<?= h($project['title']) ?>のいいね<?= $project['liked'] ? 'を取り消す' : '' ?>"
                  >
                    <span aria-hidden="true"><?= $project['liked'] ? '♥' : '♡' ?></span>
                    <?= (int) $project[ 'like_count' ] ?>
                  </button>
                </form>
              <?php elseif ($guestPreview || currentUserId() === null): ?>
              <a
                class="projectLike"
                href="<?= BASE_URL ?>/login.php"
                aria-label="ログインしていいねする"
              >
                ♡ <?= (int) $project[ 'like_count' ] ?>
              </a>
              <?php endif; ?>
            </div>
          </div>
          <a
            class="projectArrow"
            aria-label="<?= h($project['title']) ?>を見る"
            href="<?= BASE_URL ?>/<?= $isOwner ? 'admin/' : '' ?>project.php?id=<?= (int) $project['id'] ?><?= $guestPreview ? '&amp;preview=guest' : '' ?>"
          >
            ↗
          </a>
        </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if ($pageCount > 1): ?>
        <nav
          class="profilePagination"
          aria-label="プロジェクトのページ"
        >
          <?php if ($page > 1): ?>
            <a href="<?= h(profileUrl($username, $filters + ['page' => $page - 1])) ?>#projects">← 前へ</a>
          <?php endif; ?>
          <span><?= $page ?> / <?= $pageCount ?></span>
          <?php if ($page < $pageCount): ?>
            <a href="<?= h(profileUrl($username, $filters + ['page' => $page + 1])) ?>#projects">次へ →</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </section>
  </div>
</div>
</div>
<script
  src="<?= BASE_URL ?>/assets/js/profile-gallery.js"
  defer
></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
