<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
if (currentUserId() === null) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) $error = 'ページを再読み込みしてから、もう一度お試しください。';
  else { logoutUser(); redirect('index.php'); }
}
$pageTitle = 'ログアウトの確認';
$pageClass = 'accountPage';
require __DIR__ . '/includes/header.php';
?>
<section class="signupShell">
  <p class="eyebrow">SEE YOU NEXT GAME</p>
  <h1>ログアウトしますか？</h1>
  <div class="signupCard formStack">
    <p>また写真を追加・編集するときは、ログインしてください。</p>
    <?php if ($error): ?><p class="notice error" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="post" class="actionRow">
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <button class="button primary" type="submit">ログアウトする</button>
      <a class="button" href="<?= BASE_URL ?>/<?= h(accountHome()) ?>">キャンセル</a>
    </form>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
