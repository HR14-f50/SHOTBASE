<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
requireLogin();
if (empty($_SESSION['just_registered'])) redirect(accountHome());
unset($_SESSION['just_registered']);
$pageTitle = '登録完了';
$pageClass = 'accountPage';
require_once __DIR__ . '/includes/header.php';
?>
<section class="loginShell signupSuccess">
  <p class="eyebrow">YOU'RE PART OF THE GAME</p>
  <div class="signupCard">
    <span class="successMark" aria-hidden="true">✓</span>
    <h1>登録が完了しました。</h1>
    <p><?= currentUserType() === 'photographer' ? 'あなたの写真で、最初の一冊を。' : 'お気に入りの野球写真を見つけましょう。' ?></p>
    <a class="button primary" href="<?= BASE_URL ?>/<?= h(accountHome()) ?>"><?= currentUserType() === 'photographer' ? 'マイプロフィールへ' : 'アカウント画面へ' ?></a>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
