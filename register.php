<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/registration.php';
require_once __DIR__ . '/includes/icon_catalog.php';
if (currentUserId() !== null) redirect(accountHome());
$errors = [];
if (isset($_SESSION['signup']) && $_SESSION['signup']['updated'] < time() - 3600) {
  removeSignupIcon($_SESSION['signup']);
  unset($_SESSION['signup']);
}
$_SESSION['signup'] ??= ['step' => 1, 'user_type' => 'photographer', 'username' => '', 'nickname' => '',
  'password_hash' => '', 'bio' => '', 'theme_key' => 'white', 'preset_icon' => 'ball', 'icon_path' => '', 'updated' => time()];
$draft = &$_SESSION['signup'];
$draft['theme_key'] = normalizeProfileThemeKey($draft['theme_key'] ?? 'white');
$draft['icon_motif'] ??= 'ball';
$draft['icon_color'] ??= 'blue';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) {
    $errors[] = 'ページを再読み込みしてから、もう一度お試しください。';
  } else {
    $draft['updated'] = time();
    $action = $_POST['action'] ?? 'next';
    if ($action === 'back') {
      $draft['step'] = max(1, $draft['step'] - 1);
      redirect('register.php');
    } elseif ($action === 'switch' && $draft['step'] === 3) {
      if ($draft['user_type'] === 'photographer') {
        $draft['bio'] = trim(is_string($_POST['bio'] ?? null) ? $_POST['bio'] : $draft['bio']);
        $theme = is_string($_POST['theme_key'] ?? null) ? $_POST['theme_key'] : '';
        if (isset(profileThemes()[$theme])) $draft['theme_key'] = $theme;
      }

      $draft['user_type'] = $draft['user_type'] === 'photographer' ? 'viewer' : 'photographer';
      redirect('register.php');
    } elseif ($action === 'next') {
      if ($draft['step'] === 1) {
        $role = $_POST['user_type'] ?? '';
        if (!in_array($role, ['photographer', 'viewer'], true)) $errors[] = 'ユーザータイプを選んでください。';
        else $draft['user_type'] = $role;
      } elseif ($draft['step'] === 2) {
        $draft['username'] = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
        $draft['nickname'] = trim(is_string($_POST['nickname'] ?? null) ? $_POST['nickname'] : '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if (!preg_match('/\A[A-Za-z0-9_]{1,20}\z/', $draft['username'])) {
          $errors[] = 'ユーザー名は半角英数字・アンダースコアで1〜20文字にしてください。';
        }
        if ($draft['nickname'] === '' || mb_strlen($draft['nickname']) > 20) $errors[] = 'ニックネームは1〜20文字にしてください。';
        if ($password !== '' || $draft['password_hash'] === '') {
          if (!preg_match('/\A[\x21-\x7E]{8,72}\z/', $password) || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'パスワードは8〜72文字の半角英数字・記号で、大文字・小文字・数字をそれぞれ含めてください。';
          } else $draft['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $stmt = db()->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$draft['username']]);
        if ($stmt->fetch()) $errors[] = 'このユーザー名はすでに使われています。';
      } elseif ($draft['step'] === 3) {
        if ($draft['user_type'] === 'photographer') {
          $draft['bio'] = trim(is_string($_POST['bio'] ?? null) ? $_POST['bio'] : '');
          $theme = is_string($_POST['theme_key'] ?? null) ? $_POST['theme_key'] : '';
          if (mb_strlen($draft['bio']) > 200) $errors[] = '自己紹介は200文字以内にしてください。';
          if (!isset(profileThemes()[$theme])) $errors[] = 'テーマカラーを選択してください。';
          else $draft['theme_key'] = $theme;
          if (isset($_FILES['icon']) && (int)$_FILES['icon']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
              $path = storeSignupIcon($_FILES['icon']);
              removeSignupIcon($draft);
              $draft['icon_path'] = $path;
            } catch (Throwable $error) {
              $errors[] = $error instanceof RuntimeException ? $error->getMessage() : 'アイコンを処理できませんでした。';
            }
          }
        } else {
          $motif = is_string($_POST['icon_motif'] ?? null) ? $_POST['icon_motif'] : '';
          $color = is_string($_POST['icon_color'] ?? null) ? $_POST['icon_color'] : '';
          if (!isset(iconMotifs()[$motif], iconColors()[$color])) $errors[] = 'アイコンの素材と背景色を選択してください。';
          else { $draft['icon_motif'] = $motif; $draft['icon_color'] = $color; }
        }
      }
      if (!$errors && $draft['step'] < 4) {
        $draft['step']++;
        redirect('register.php');
      }
    } elseif ($action === 'complete' && $draft['step'] === 4) {
      if (!$draft['username'] || !$draft['nickname'] || !$draft['password_hash']) {
        $draft['step'] = 2;
        $errors[] = '基本情報をもう一度確認してください。';
      } else {
        $icon = $draft['user_type'] === 'photographer' && $draft['icon_path']
          ? $draft['icon_path'] : customIconPath($draft['icon_motif'], $draft['icon_color']);
        try {
          $stmt = db()->prepare('INSERT INTO users (username, nickname, password_hash, user_type, theme_key, preset_icon, icon_path, bio, icon_motif, icon_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
          $stmt->execute([$draft['username'], $draft['nickname'], $draft['password_hash'], $draft['user_type'],
            $draft['user_type'] === 'photographer' ? $draft['theme_key'] : 'white', $draft['preset_icon'], $icon,
            $draft['user_type'] === 'photographer' ? $draft['bio'] : '', $draft['icon_motif'], $draft['icon_color']]);
          $id = (int)db()->lastInsertId();
          if ($draft['user_type'] === 'viewer') removeSignupIcon($draft);
          unset($_SESSION['signup']);
          loginUser($id);
          $_SESSION['just_registered'] = true;
          redirect('welcome.php');
        } catch (PDOException $error) {
          $errors[] = '登録できませんでした。ユーザー名が使われている場合は、戻って変更してください。';
        }
      }
    }
  }
}
$step = $draft['step'];
$photographer = $draft['user_type'] === 'photographer';
$pageTitle = '新規登録';
$pageClass = 'accountPage';
require_once __DIR__ . '/includes/header.php';
?>
<section class="signupShell">
  <p class="eyebrow">WELCOME TO SHOTBASE</p>
  <h1>あなたの野球時間を、ここに。</h1>
  <ol class="signupSteps" aria-label="登録の進行状況">
    <?php foreach (['タイプ選択', '基本情報', '初期設定', '確認'] as $i => $label): ?>
      <li class="<?= $step === $i + 1 ? 'isCurrent' : ($step > $i + 1 ? 'isComplete' : '') ?>" <?= $step === $i + 1 ? 'aria-current="step"' : '' ?>>
        <span><?= $i + 1 ?></span><?= h($label) ?>
      </li>
    <?php endforeach; ?>
  </ol>
  <div class="signupCard">
    <?php if ($errors): ?>
      <div class="notice error" role="alert">
        <?php foreach ($errors as $error): ?><p><?= h($error) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="formStack">
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <?php if ($step === 1): ?>
        <h2>どんなふうに楽しみますか？</h2>
        <p class="accountHelp">使い方に合わせて選んでください。登録途中でも切り替えられます。</p>
        <div class="userTypeChoices">
          <label class="typeChoice">
            <input type="radio" name="user_type" value="photographer" <?= $photographer ? 'checked' : '' ?>>
            <img src="<?= BASE_URL ?>/assets/icons/camera.svg" alt="">
            <strong>写真投稿ユーザー</strong>
            <span>写真を整理して公開。自分だけのプロフィールとアルバムを作れます。</span>
          </label>
          <label class="typeChoice">
            <input type="radio" name="user_type" value="viewer" <?= !$photographer ? 'checked' : '' ?>>
            <img src="<?= BASE_URL ?>/assets/icons/ball.svg" alt="">
            <strong>閲覧のみユーザー</strong>
            <span>公開された写真を楽しんで、いいねで応援。公開プロフィールは作成されません。</span>
          </label>
        </div>
      <?php elseif ($step === 2): ?>
        <h2>基本情報を入力</h2>
        <label for="signupUsername">ユーザー名</label>
        <input id="signupUsername" name="username" value="<?= h($draft['username']) ?>" maxlength="20" pattern="[A-Za-z0-9_]{1,20}" data-counter-target="usernameCounter" autocomplete="username" required aria-describedby="usernameHelp">
        <div class="inputHelpRow" id="usernameHelp"><span>半角英数字・アンダースコア。ログインIDになります。ユーザー名は登録後に変更できません。</span><span><span id="usernameCounter">0</span> / 20</span></div>
        <label for="signupNickname">ニックネーム</label>
        <input id="signupNickname" name="nickname" value="<?= h($draft['nickname']) ?>" maxlength="20" data-counter-target="nicknameCounter" autocomplete="nickname" required>
        <p class="textCounter"><span id="nicknameCounter">0</span> / 20文字</p>
        <label for="signupPassword">パスワード</label>
        <div class="passwordField">
          <input id="signupPassword" type="password" name="password" minlength="8" maxlength="72" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,72}" autocomplete="new-password" <?= !$draft['password_hash'] ? 'required' : '' ?> aria-describedby="passwordHelp">
          <button type="button" data-password-toggle="signupPassword" aria-pressed="false" aria-label="パスワードを表示">表示</button>
        </div>
        <p class="accountHelp" id="passwordHelp">8〜72文字の半角英数字・記号。大文字・小文字・数字を各1文字以上含めてください。<?= $draft['password_hash'] ? '変更しない場合は空欄のままで進めます。' : '' ?></p>
      <?php elseif ($step === 3): ?>
        <h2><?= $photographer ? 'あなたらしいプロフィールに。' : 'お気に入りのアイコンを選択' ?></h2>
        <?php if ($photographer): ?>
          <div class="signupAvatarRow">
            <img id="signupIconPreview" class="accountAvatar" src="<?= h(publicPhotoPath($draft['icon_path'] ?: presetIconPath('ball'))) ?>" alt="アイコンのプレビュー">
            <label>アイコン画像（任意）<input type="file" name="icon" id="signupIcon" accept="image/jpeg,image/png,image/webp"></label>
          </div>
          <p class="accountHelp">JPEG・PNG・WebP、20MB未満。保存時に自動で縮小・圧縮し、2MB以内の正方形アイコンにします。</p>
          <fieldset class="signupFieldset"><legend>テーマカラー</legend>
            <div class="themeChoices">
              <?php foreach (profileThemes() as $key => $theme): ?>
                <label><input type="radio" name="theme_key" value="<?= h($key) ?>" <?= $draft['theme_key'] === $key ? 'checked' : '' ?>><span class="themeSwatch" style="background:<?= h($theme['accent']) ?>"></span><span><?= h($theme['name']) ?></span></label>
              <?php endforeach; ?>
            </div>
          </fieldset>
          <label for="signupBio">自己紹介（任意）</label>
          <textarea id="signupBio" name="bio" rows="4" maxlength="200" data-counter-target="signupBioCounter"><?= h($draft['bio']) ?></textarea>
          <p class="textCounter"><span id="signupBioCounter">0</span> / 200文字</p>
        <?php else: ?>
          <p class="accountHelp">公開プロフィールは作成されません。アイコンはアカウント画面に表示されます。</p>
          <?php $iconMotif = $draft['icon_motif']; $iconColor = $draft['icon_color']; require __DIR__ . '/includes/icon_picker.php'; ?>
        <?php endif; ?>
        <button class="switchUserType" type="submit" name="action" value="switch" formnovalidate><?= $photographer ? '閲覧限定ユーザーとして登録する' : '写真投稿ユーザーとして登録する' ?> →</button>
      <?php else: ?>
        <h2>登録内容を確認</h2>
        <img class="accountAvatar" src="<?= h(publicPhotoPath($photographer && $draft['icon_path'] ? $draft['icon_path'] : customIconPath($draft['icon_motif'], $draft['icon_color']))) ?>" alt="登録するアイコン">
        <dl class="signupSummary">
          <dt>ユーザータイプ</dt><dd><?= $photographer ? '写真投稿ユーザー' : '閲覧のみユーザー' ?></dd>
          <dt>ユーザー名</dt><dd>@<?= h($draft['username']) ?></dd>
          <dt>ニックネーム</dt><dd><?= h($draft['nickname']) ?></dd>
          <dt>パスワード</dt><dd>設定済み（表示しません）</dd>
          <?php if ($photographer): ?>
            <dt>テーマカラー</dt><dd><?= h(profileTheme($draft['theme_key'])['name']) ?></dd>
            <dt>自己紹介</dt><dd><?= $draft['bio'] ? nl2br(h($draft['bio'])) : '未設定' ?></dd>
          <?php else: ?>
            <dt>公開プロフィール</dt><dd>作成しない</dd>
          <?php endif; ?>
        </dl>
      <?php endif; ?>
      <div class="signupActions">
        <?php if ($step > 1): ?><button class="button" type="submit" name="action" value="back" formnovalidate>戻る</button><?php endif; ?>
        <button class="button primary" type="submit" name="action" value="<?= $step === 4 ? 'complete' : 'next' ?>"><?= $step === 4 ? 'この内容で登録する' : '次へ進む' ?></button>
      </div>
    </form>
  </div>
  <p class="accountBottomLink">アカウントをお持ちの方は <a href="<?= BASE_URL ?>/login.php">ログイン</a></p>
</section>
<script src="<?= BASE_URL ?>/assets/js/registration.js?v=<?= (int) @filemtime(__DIR__ . '/assets/js/registration.js') ?>" defer></script>
<script src="<?= BASE_URL ?>/assets/js/profile-settings.js?v=<?= (int) @filemtime(__DIR__ . '/assets/js/profile-settings.js') ?>" defer></script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
