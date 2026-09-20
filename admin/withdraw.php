<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/profile_helpers.php';
$userId = requireLogin();
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) $error = 'ページを再読み込みしてから、もう一度お試しください。';
  elseif (($_POST['confirm_delete'] ?? '') !== 'yes') $error = '削除内容を確認し、確認欄にチェックしてください。';
  elseif (!password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) $error = 'パスワードが正しくありません。';
  else {
    $moved = [];
    $staging = null;
    try {
      $pdo->beginTransaction();
      $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
      $stmt->execute([$userId]);
      $stmt = $pdo->prepare('SELECT file_path, original_path FROM photos WHERE user_id = ? FOR UPDATE');
      $stmt->execute([$userId]);
      $photos = $stmt->fetchAll();
      $paths = array_merge(array_column($photos, 'file_path'), array_column($photos, 'original_path'), [$user['icon_path']]);
      // 共通アイコンと他ユーザーが参照する共有ファイルは削除しません。
      $root = realpath(__DIR__ . '/../uploads');
      $staging = sys_get_temp_dir() . '/shotbase-withdraw-' . bin2hex(random_bytes(16));
      if (!mkdir($staging, 0700)) throw new RuntimeException('削除用の一時領域を作成できません。');
      foreach (array_unique(array_filter($paths)) as $relative) {
        $absolute = realpath(__DIR__ . '/../' . $relative);
        if (!$absolute || !$root || !str_starts_with($absolute, $root . '/') || !is_file($absolute)) continue;
        $check = $pdo->prepare('SELECT id FROM photos WHERE user_id <> ? AND (file_path = ? OR original_path = ?) LIMIT 1');
        $check->execute([$userId, $relative, $relative]);
        if ($check->fetch()) continue;
        $check = $pdo->prepare('SELECT id FROM users WHERE id <> ? AND icon_path = ? LIMIT 1');
        $check->execute([$userId, $relative]);
        if ($check->fetch()) continue;
        $temporary = $staging . '/' . count($moved);
        if (!rename($absolute, $temporary)) throw new RuntimeException('写真を削除できませんでした。');
        $moved[$absolute] = $temporary;
      }
      // 関連する写真・プロジェクト・タグ・いいね等は外部キーのCASCADEで削除。
      $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
      $pdo->commit();
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      foreach ($moved as $absolute => $temporary) if (is_file($temporary)) rename($temporary, $absolute);
      if ($staging && is_dir($staging)) rmdir($staging);
      error_log($e->getMessage());
      $error = '退会処理を完了できませんでした。登録情報は変更していません。';
    }
    if (!$error) {
      foreach ($moved as $temporary) if (!unlink($temporary)) error_log('Withdrawal cleanup failed: ' . $temporary);
      if ($staging && is_dir($staging)) rmdir($staging);
      logoutUser();
      redirect('index.php?withdrawn=1');
    }
  }
}
$pageTitle = '退会の確認';
$pageClass = 'accountPage';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="signupShell">
  <p class="eyebrow">CLOSE YOUR ACCOUNT</p>
  <h1>退会の確認</h1>
  <div class="signupCard formStack">
    <p><strong>退会すると登録した写真やプロジェクトも全て削除されます。</strong></p>
    <p>プロフィール、タグ、いいねなどのアカウント情報も削除されます。この操作は元に戻せません。</p>
    <p>@<?= h($user['username']) ?>（<?= h($user['nickname']) ?>）</p>
    <?php if ($error): ?><p class="notice error" role="alert"><?= h($error) ?></p><?php endif; ?>
    <form method="post" class="formStack" data-confirm="退会すると登録した写真やプロジェクトも全て削除されます。本当に退会しますか？">
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <label>本人確認のパスワード<input type="password" name="password" autocomplete="current-password" required></label>
      <label class="checkLine"><input type="checkbox" name="confirm_delete" value="yes" required>全ての写真・プロジェクトが削除されることを確認しました</label>
      <button class="button danger" type="submit">全て削除して退会する</button>
      <a class="button" href="<?= BASE_URL ?>/admin/profile_edit.php">退会せずに戻る</a>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
