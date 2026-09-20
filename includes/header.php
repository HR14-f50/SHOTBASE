<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

require_once __DIR__ . '/theme_palette.php';
$personalTheme = null;
$personalThemeKey = null;
if (isset($themeUserId)) {
  $themeStmt = db()->prepare('SELECT theme_key FROM users WHERE id = ?');
  $themeStmt->execute([$themeUserId]);
  $personalThemeKey = normalizeProfileThemeKey($themeStmt->fetchColumn());
  $personalTheme = profileThemes()[$personalThemeKey];
}
$pageTitle = $pageTitle ?? SERVICE_NAME;
?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($pageTitle) ?> | <?= h(SERVICE_NAME) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/profile.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/account.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/account.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile-settings.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/profile-settings.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tags.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/tags.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/gallery.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/gallery.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sidebar-refresh.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/sidebar-refresh.css') ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/project-refresh.css?v=<?= (int) @filemtime(__DIR__ . '/../assets/css/project-refresh.css') ?>">
</head>
<body class="<?= h($pageClass ?? '') ?> <?= $personalTheme ? 'personalTheme theme-' . h($personalThemeKey) : '' ?>" <?= $personalTheme ? 'style="--profile-accent:' . h($personalTheme['accent']) . ';--profile-soft:' . h($personalTheme['soft']) . ';--profile-secondary:' . h($personalTheme['secondary']) . ';"' : '' ?>>
<a class="skipLink" href="#mainContent">本文へスキップ</a>
<div class="headerReveal">
<header class="siteHeader">
  <a class="logo" href="<?= BASE_URL ?>/index.php"><?= h(SERVICE_NAME) ?></a>
  <nav>
    <?php if (currentUserId() !== null && empty($guestPreview)): ?>
      <a href="<?= BASE_URL ?>/<?= h(accountHome()) ?>"><?= currentUserType() === 'viewer' ? 'アカウント' : 'マイプロフィール' ?></a>
      <a href="<?= BASE_URL ?>/logout.php">ログアウト</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/login.php">ログイン</a>
    <?php endif; ?>
  </nav>
</header>
</div>

<main class="<?= !empty($fullWidth) ? 'pageFullWidth' : 'container' ?>" id="mainContent">

<?php if (!empty($guestPreview)): ?>
  <aside class="guestPreviewNotice">ログアウト時の表示をプレビューしています。ログイン状態は維持されています。<a href="<?= h($previewBack ?? (BASE_URL . '/admin/' . (isset($photo) ? 'photo' : 'project') . '.php?id=' . (int)($id ?? 0))) ?>">編集画面へ戻る</a></aside>
<?php endif; ?>
