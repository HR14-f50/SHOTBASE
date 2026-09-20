<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
$userId = requireLogin();
$stmt = db()->prepare('SELECT username, nickname, user_type, icon_path FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user) { logoutUser(); redirect('login.php'); }
$stmt = db()->query('SELECT u.username, u.nickname FROM users u WHERE u.user_type = "photographer" AND EXISTS (SELECT 1 FROM projects p WHERE p.user_id = u.id AND p.visibility = "public") ORDER BY u.id DESC LIMIT 12');
$creators = $stmt->fetchAll();
$stmt = db()->prepare('SELECT t.*, tm.name AS team_name, tm.border_color AS team_border_color, tm.background_color AS team_background_color FROM user_profile_tags upt JOIN tags t ON t.id = upt.tag_id LEFT JOIN teams tm ON tm.id = t.team_id WHERE upt.user_id = ? ORDER BY upt.sort_order');
$stmt->execute([$userId]);
$accountTags = $stmt->fetchAll();
$pageTitle = 'アカウント';
$pageClass = 'accountPage';
require_once __DIR__ . '/includes/header.php';
?>
<section class="signupShell">
  <p class="eyebrow">YOUR ACCOUNT</p>
  <h1><?= h($user['nickname']) ?>さん</h1>
  <div class="signupCard">
    <?php if ($user['icon_path']): ?><img class="accountAvatar" src="<?= h(publicPhotoPath($user['icon_path'])) ?>" alt="アカウントのアイコン"><?php endif; ?>
    <p>@<?= h($user['username']) ?></p>
    <div class="tagList"><?php foreach ($accountTags as $tag): ?><span class="tag" style="<?= h(profileTagStyle($tag)) ?>">#<?= h($tag['name']) ?></span><?php endforeach; ?></div>
    <?php if ($user['user_type'] === 'viewer'): ?>
      <p>閲覧専用アカウントです。公開された写真を楽しんで、いいねで応援できます。</p>
      <p class="accountHelp">あなたの公開プロフィールは作成されません。</p>
    <?php else: ?>
      <a class="button primary" href="<?= BASE_URL ?>/<?= h(accountHome()) ?>">写真を管理する</a>
    <?php endif; ?>
    <a class="button" href="<?= BASE_URL ?>/admin/profile_edit.php">プロフィールを編集</a>
  </div>
  <h2 class="accountBrowseTitle">公開されている写真を見に行く</h2>
  <div class="cardGrid">
    <?php foreach ($creators as $creator): ?>
      <a class="card" href="<?= h(profileUrl($creator['username'])) ?>"><strong><?= h($creator['nickname']) ?></strong><p>@<?= h($creator['username']) ?></p></a>
    <?php endforeach; ?>
  </div>
  <?php if (!$creators): ?><p class="accountHelp">公開された写真はまだありません。</p><?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
