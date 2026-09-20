<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/profile_helpers.php';
if (currentUserId() !== null) redirect(accountHome());
$error = null;
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
  $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
  if (!profileCsrfValid()) {
    $error = 'ページを再読み込みしてからログインしてください。';
  } else {
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
      loginUser((int)$user['id']);
      redirect(accountHome());
    }
    $error = 'ユーザー名またはパスワードが正しくありません。';
  }
}
$pageTitle = 'ログイン';
$pageClass = 'accountPage';
require_once __DIR__ . '/includes/header.php';
?>
<section class="loginShell">
  <p class="eyebrow">WELCOME BACK</p>
  <h1>おかえりなさい。</h1>
  <p class="accountHelp">あなたの野球の記録、その続きを。</p>
  <div class="signupCard">
    <?php if ($error): ?><div class="notice error" role="alert"><?= h($error) ?></div><?php endif; ?>
    <form method="post" class="formStack">
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <label for="loginUsername">ユーザー名</label>
      <input id="loginUsername" type="text" name="username" value="<?= h($username) ?>" autocomplete="username" required>
      <label for="loginPassword">パスワード</label>
      <div class="passwordField">
        <input id="loginPassword" type="password" name="password" autocomplete="current-password" required>
        <button type="button" data-password-toggle="loginPassword" aria-pressed="false" aria-label="パスワードを表示">表示</button>
      </div>
      <button class="button primary" type="submit">ログイン</button>
    </form>
  </div>
  <p class="accountBottomLink">はじめての方は <a href="<?= BASE_URL ?>/register.php">新規登録</a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
