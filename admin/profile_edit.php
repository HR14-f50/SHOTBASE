<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/registration.php';
require_once __DIR__ . '/../includes/icon_catalog.php';
$userId = requireLogin();
$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$saved = $stmt->fetch();
$saved['theme_key'] = normalizeProfileThemeKey($saved['theme_key'] ?? 'white');
$user = $saved;
$photographer = $saved['user_type'] === 'photographer';
$tags = availableTags($userId);
$stmt = $pdo->prepare('SELECT tag_id FROM user_profile_tags WHERE user_id = ? ORDER BY sort_order');
$stmt->execute([$userId]);
$selectedTags = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
$source = $photographer ? 'keep' : 'custom';
$cropX = 50;
$cropY = 50;
$newTags = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!profileCsrfValid()) {
    $errors[] = 'ページを再読み込みしてから、もう一度保存してください。';
  } elseif (($_POST['action'] ?? '') === 'tag_create_manage') {
    $name = trim((string)($_POST['tag_name'] ?? ''));
    if ($name === '' || mb_strlen($name) > 12 || preg_match('/[\x00-\x1F]/u', $name)) {
      $errors[] = 'タグ名は制御文字を含まない1〜12文字で入力してください。';
    } else {
      $stmt = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND user_id = ? LIMIT 1');
      $stmt->execute([$name, $userId]);
      $alreadyOwned = $stmt->fetchColumn();
      $stmt = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND user_id IS NULL LIMIT 1');
      $stmt->execute([$name]);
      $alreadyOperator = $stmt->fetchColumn();
      if ($alreadyOwned || $alreadyOperator) {
        $errors[] = '同じ名前のタグがすでにあります。';
      } else {
        $pdo->prepare('INSERT INTO tags (user_id, name, tag_type) VALUES (?, ?, "custom")')->execute([$userId, $name]);
        flash('tag_management', 'タグを作成しました。');
        redirect('admin/profile_edit.php#tag-management');
      }
    }
  } elseif (($_POST['action'] ?? '') === 'tag_update_manage') {
    $tagId = (int)($_POST['tag_id'] ?? 0);
    $name = trim((string)($_POST['tag_name'] ?? ''));
    if ($tagId <= 0 || $name === '' || mb_strlen($name) > 12 || preg_match('/[\x00-\x1F]/u', $name)) {
      $errors[] = 'タグ名は制御文字を含まない1〜12文字で入力してください。';
    } else {
      $stmt = $pdo->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ? AND tag_type = "custom"');
      $stmt->execute([$tagId, $userId]);
      if (!$stmt->fetchColumn()) {
        $errors[] = '編集できるタグが見つかりません。';
      } else {
        $stmt = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND id <> ? AND (user_id = ? OR user_id IS NULL) LIMIT 1');
        $stmt->execute([$name, $tagId, $userId]);
        if ($stmt->fetchColumn()) {
          $errors[] = '同じ名前のタグがすでにあります。';
        } else {
          $pdo->prepare('UPDATE tags SET name = ? WHERE id = ? AND user_id = ? AND tag_type = "custom"')->execute([$name, $tagId, $userId]);
          flash('tag_management', 'タグ名を変更しました。');
          redirect('admin/profile_edit.php#tag-management');
        }
      }
    }
  } elseif (($_POST['action'] ?? '') === 'tag_delete_manage') {
    $tagId = (int)($_POST['tag_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT id FROM tags WHERE id = ? AND user_id = ? AND tag_type = "custom"');
    $stmt->execute([$tagId, $userId]);
    if (!$stmt->fetchColumn()) {
      $errors[] = '削除できるタグが見つかりません。';
    } else {
      // タグを削除すると、写真・プロジェクト・プロフィールに付けた同タグも一緒に外れます。
      $pdo->prepare('DELETE FROM tags WHERE id = ? AND user_id = ? AND tag_type = "custom"')->execute([$tagId, $userId]);
      flash('tag_management', 'タグと、そのタグの付与先を削除しました。');
      redirect('admin/profile_edit.php#tag-management');
    }
  } elseif (($_POST['action'] ?? '') === 'upgrade') {
    $pdo->prepare('UPDATE users SET user_type = "photographer" WHERE id = ? AND user_type = "viewer"')->execute([$userId]);
    flash('profile_saved', '写真投稿会員に変更しました。公開プロフィールが利用できます。');
    redirect('admin/profile_edit.php');
  } else {
    $user['nickname'] = trim((string)($_POST['nickname'] ?? ''));
    $source = $photographer ? (string)($_POST['icon_source'] ?? 'keep') : 'custom';
    $user['icon_motif'] = (string)($_POST['icon_motif'] ?? $saved['icon_motif']);
    $user['icon_color'] = (string)($_POST['icon_color'] ?? $saved['icon_color']);
    $selectedTags = array_values(array_unique(array_map('intval', (array)($_POST['profile_tags'] ?? []))));
    $newTags = trim((string)($_POST['new_tags'] ?? ''));
    $newNames = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,、\r\n]+/u', $newTags)))));
    if ($user['nickname'] === '' || mb_strlen($user['nickname']) > 20) $errors[] = 'ニックネームは1〜20文字で入力してください。';
    if (!in_array($source, ['keep', 'custom', 'upload'], true)) $errors[] = 'アイコンの設定方法を選択してください。';
    if ($source === 'custom' && !isset(iconMotifs()[$user['icon_motif']], iconColors()[$user['icon_color']])) $errors[] = 'アイコンの素材と背景色を選択してください。';
    if (count($selectedTags) + count($newNames) > 10) $errors[] = 'タグは合計10個まで選べます。';
    if (array_diff($selectedTags, array_map('intval', array_column($tags, 'id')))) $errors[] = '選択できないタグが含まれています。';
    foreach ($newNames as $name) {
      if (mb_strlen($name) > 12) $errors[] = 'タグ名は12文字以内で入力してください。';
    }
    if ($photographer) {
      $user['bio'] = trim((string)($_POST['bio'] ?? ''));
      $user['theme_key'] = (string)($_POST['theme_key'] ?? $saved['theme_key']);
      $user['watermark_enabled'] = isset($_POST['watermark_enabled']) ? 1 : 0;
      foreach (['source', 'text', 'size', 'color', 'position'] as $field) {
        $user['watermark_' . $field] = trim((string)($_POST['watermark_' . $field] ?? $saved['watermark_' . $field]));
      }
      $user['watermark_opacity'] = (int)($_POST['watermark_opacity'] ?? 50);
      if (mb_strlen($user['bio']) > 200) $errors[] = '自己紹介は200文字以内で入力してください。';
      if (!isset(profileThemes()[$user['theme_key']])) $errors[] = 'テーマカラーを選択してください。';
      if (!in_array($user['watermark_source'], ['username', 'nickname', 'custom'], true)) $errors[] = 'ウォーターマークの内容を選択してください。';
      if (mb_strlen($user['watermark_text']) > 20 || preg_match('/[\r\n\x00-\x1F]/', $user['watermark_text']) || ($user['watermark_source'] === 'custom' && $user['watermark_text'] === '')) $errors[] = '自由入力は改行を含まない1〜20文字にしてください。';
      if (!in_array($user['watermark_size'], ['large', 'medium', 'small'], true)
        || !in_array($user['watermark_color'], ['black', 'white'], true)
        || !in_array($user['watermark_opacity'], [100, 50, 20, 10], true)
        || !isset(watermarkPositions()[$user['watermark_position']])) $errors[] = 'ウォーターマークの設定を選択してください。';
    }
    $cropX = filter_var($_POST['icon_crop_x'] ?? 50, FILTER_VALIDATE_FLOAT);
    $cropY = filter_var($_POST['icon_crop_y'] ?? 50, FILTER_VALIDATE_FLOAT);
    if ($cropX === false || $cropY === false || $cropX < 0 || $cropX > 100 || $cropY < 0 || $cropY > 100) $errors[] = '画像の位置は0〜100の範囲で指定してください。';
    if (!$errors) {
      $newIcon = null;
      $staged = [];
      try {
        if ($source === 'custom') $user['icon_path'] = customIconPath($user['icon_motif'], $user['icon_color']);
        if ($source === 'upload') {
          $newIcon = storeSignupIcon($_FILES['icon'] ?? [], (float)$cropX, (float)$cropY);
          $user['icon_path'] = $newIcon;
        }
        $changed = false;
        foreach (['enabled', 'source', 'text', 'size', 'color', 'opacity', 'position'] as $field) {
          if ((string)$user['watermark_' . $field] !== (string)$saved['watermark_' . $field]) $changed = true;
        }
        if ($user['watermark_source'] === 'nickname' && $user['nickname'] !== $saved['nickname']) $changed = true;
        $pdo->beginTransaction();
        // 同時編集で元画像と設定が食い違わないよう、対象ユーザー・写真をロックします。
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
        $stmt->execute([$userId]);
        if ($photographer && $changed) {
          set_time_limit(0);
          $stmt = $pdo->prepare('SELECT * FROM photos WHERE user_id = ? FOR UPDATE');
          $stmt->execute([$userId]);
          foreach ($stmt->fetchAll() as $photo) $staged[] = stagePhotoWatermark($photo, $user);
        }
        $fields = ['nickname', 'icon_path', 'icon_motif', 'icon_color'];
        if ($photographer) $fields = array_merge($fields, ['bio', 'theme_key', 'watermark_enabled', 'watermark_source', 'watermark_text', 'watermark_size', 'watermark_color', 'watermark_opacity', 'watermark_position']);
        $stmt = $pdo->prepare('UPDATE users SET ' . implode(', ', array_map(fn($field) => "$field = ?", $fields)) . ' WHERE id = ?');
        $stmt->execute(array_merge(array_map(fn($field) => $user[$field], $fields), [$userId]));
        foreach ($newNames as $name) {
          $stmt = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND user_id = ? LIMIT 1');
          $stmt->execute([$name, $userId]);
          $tag = $stmt->fetchColumn();
          if (!$tag) {
            $pdo->prepare('INSERT INTO tags (user_id, name, tag_type) VALUES (?, ?, "custom")')->execute([$userId, $name]);
            $tag = $pdo->lastInsertId();
          }
          $selectedTags[] = (int)$tag;
        }
        $pdo->prepare('DELETE FROM user_profile_tags WHERE user_id = ?')->execute([$userId]);
        $stmt = $pdo->prepare('INSERT INTO user_profile_tags (user_id, tag_id, sort_order) VALUES (?, ?, ?)');
        foreach (array_values(array_unique($selectedTags)) as $order => $tag) $stmt->execute([$userId, $tag, $order]);
        commitWatermarkRows($pdo, $staged, $userId);
        $pdo->commit();
        discardWatermarkFiles($staged, true);
        if ($saved['icon_path'] !== $user['icon_path']) removeSignupIcon($saved);
        flash('profile_saved', $changed && $photographer ? 'プロフィールと写真のウォーターマークを保存しました。' : 'プロフィールを保存しました。');
        redirect('admin/profile_edit.php');
      } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        discardWatermarkFiles($staged);
        if ($newIcon) removeSignupIcon(['icon_path' => $newIcon]);
        error_log($e->getMessage());
        $errors[] = $e instanceof RuntimeException ? $e->getMessage() : '保存できませんでした。もう一度お試しください。';
      }
    }
  }
}
$pageTitle = 'プロフィール編集';
$pageClass = 'accountPage';
$themeUserId = $userId;
require_once __DIR__ . '/../includes/header.php';
$message = flash('profile_saved');
$tagMessage = flash('tag_management');
$stmt = $pdo->prepare('SELECT t.id, t.name, t.created_at,
  (SELECT COUNT(*) FROM photo_tags pt JOIN photos ph ON ph.id = pt.photo_id WHERE pt.tag_id = t.id AND ph.user_id = ?) AS photo_use_count,
  (SELECT COUNT(*) FROM project_default_tags pdt JOIN projects p ON p.id = pdt.project_id WHERE pdt.tag_id = t.id AND p.user_id = ?) AS project_use_count,
  (SELECT COUNT(*) FROM user_profile_tags upt WHERE upt.tag_id = t.id AND upt.user_id = ?) AS profile_use_count
  FROM tags t WHERE t.user_id = ? AND t.tag_type = "custom" ORDER BY t.name');
$stmt->execute([$userId, $userId, $userId, $userId]);
$ownedTags = $stmt->fetchAll();
?>
<section class="settingsShell">
  <p class="eyebrow">YOUR PROFILE</p>
  <h1>あなたらしい、ひとつの場所。</h1>
  <p class="accountHelp">プロフィール編集 · <?= $photographer ? '写真投稿会員' : '閲覧限定会員' ?></p>
  <p><a class="textLink" href="<?= BASE_URL ?>/<?= h(accountHome()) ?>">← <?= $photographer ? 'マイプロフィール' : 'アカウント' ?>へ戻る</a></p>
  <?php if ($message): ?><p class="notice success" role="status"><?= h($message) ?></p><?php endif; ?>
  <?php if ($tagMessage): ?><p class="notice success" role="status"><?= h($tagMessage) ?></p><?php endif; ?>
  <?php if ($errors): ?><div class="notice error" role="alert"><?php foreach ($errors as $error): ?><p><?= h($error) ?></p><?php endforeach; ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="formStack" data-settings-form>
    <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
    <section class="settingsCard">
      <h2>アイコン</h2>
      <?php if ($photographer): ?>
        <?php if ($saved['icon_path']): ?><img class="accountAvatar" src="<?= h(publicPhotoPath($saved['icon_path'])) ?>" alt="現在のアイコン"><?php endif; ?>
        <div class="iconSourceChoices">
          <?php foreach (['keep' => '現在のアイコン', 'custom' => '素材と色で作る', 'upload' => '画像をアップロード'] as $key => $label): ?>
            <label><input type="radio" name="icon_source" value="<?= h($key) ?>" <?= $source === $key ? 'checked' : '' ?>><?= h($label) ?></label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div <?= $photographer ? 'data-icon-source-panel="custom"' : '' ?>>
        <?php $iconMotif = $user['icon_motif']; $iconColor = $user['icon_color']; require __DIR__ . '/../includes/icon_picker.php'; ?>
      </div>
      <?php if ($photographer): ?>
        <div data-icon-source-panel="upload">
          <label>画像ファイル<input type="file" name="icon" accept="image/jpeg,image/png,image/webp" data-avatar-upload></label>
          <p class="accountHelp">JPEG・PNG・WebP、20MB未満。保存時に自動で縮小・圧縮し、2MB以内の正方形アイコンにします。</p>
          <img class="accountAvatar" data-upload-preview src="<?= h(publicPhotoPath($saved['icon_path'] ?: customIconPath('ball', 'blue'))) ?>" alt="アップロードする画像のプレビュー">
          <label>横の位置（左 ↔ 右）<input type="range" name="icon_crop_x" min="0" max="100" value="<?= (float)$cropX ?>" data-avatar-x></label>
          <label>縦の位置（上 ↔ 下）<input type="range" name="icon_crop_y" min="0" max="100" value="<?= (float)$cropY ?>" data-avatar-y></label>
          <p class="accountHelp" data-avatar-status role="status">画像を選ぶと、切り抜く範囲を確認できます。</p>
        </div>
      <?php endif; ?>
    </section>
    <section class="settingsCard formStack">
      <h2>基本情報</h2>
      <div class="fixedField" aria-describedby="usernameFixed">
        <span class="fixedFieldLabel">ユーザー名（変更不可）</span>
        <span class="fixedFieldValue"><?= h($user['username']) ?></span>
      </div>
      <p class="accountHelp" id="usernameFixed">ユーザー名は登録後に変更できません。</p>
      <label for="editNickname">ニックネーム</label>
      <input id="editNickname" name="nickname" maxlength="20" required value="<?= h($user['nickname']) ?>" data-counter-target="editNicknameCount">
      <p class="textCounter"><span id="editNicknameCount">0</span> / 20文字</p>
      <?php if ($photographer): ?>
        <label for="editBio">自己紹介</label>
        <textarea id="editBio" name="bio" maxlength="200" rows="4" data-counter-target="editBioCount"><?= h($user['bio']) ?></textarea>
        <p class="textCounter"><span id="editBioCount">0</span> / 200文字</p>
        <fieldset class="signupFieldset themePicker" data-theme-picker="<?= BASE_URL ?>/admin/theme_update.php" data-saved-theme="<?= h($saved['theme_key']) ?>"><legend>テーマカラー（選ぶとすぐ保存されます）</legend>
          <div class="themeSwatches"><?php foreach (profileThemes() as $key => $theme): ?><label title="<?= h($theme['name']) ?>"><input type="radio" name="theme_key" value="<?= h($key) ?>" data-accent="<?= h($theme['accent']) ?>" data-soft="<?= h($theme['soft']) ?>" <?= $user['theme_key'] === $key ? 'checked' : '' ?>><span style="background:<?= h($theme['accent']) ?>"></span><small><?= h($theme['name']) ?></small></label><?php endforeach; ?></div>
          <p class="accountHelp" data-theme-status role="status">プロフィール・プロジェクト・写真詳細にも反映します。</p>
        </fieldset>
      <?php endif; ?>
    </section>
    <section class="settingsCard formStack">
      <h2>プロフィールのタグ</h2>
      <p class="accountHelp">合計10個まで選べます。<?= !$photographer ? '閲覧限定会員のタグはアカウント画面に表示され、公開プロフィールは作成されません。' : '' ?></p>
      <?php require __DIR__ . '/../includes/tag_search_ui.php'; ?>
      <?php
        $tagPickerTags = $tags;
        $tagPickerSelected = $selectedTags;
        $tagPickerInputName = 'profile_tags[]';
        $tagPickerLabelClass = 'profileTagChoice';
        require __DIR__ . '/../includes/tag_picker.php';
      ?>
      <?php $tagInputName = 'profile_tags[]'; require __DIR__ . '/../includes/tag_create_ui.php'; ?>
    </section>
    <?php if ($photographer): ?>
      <section class="settingsCard formStack">
        <h2>ウォーターマーク</h2>
        <label class="checkLine"><input type="checkbox" name="watermark_enabled" <?= (int)$user['watermark_enabled'] ? 'checked' : '' ?>>写真にウォーターマークを入れる</label>
        <label>1行目<select name="watermark_source"><?php foreach (['username' => 'ユーザー名', 'nickname' => 'ニックネーム', 'custom' => '自由入力テキスト'] as $key => $label): ?><option value="<?= h($key) ?>" <?= $user['watermark_source'] === $key ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></label>
        <label for="watermarkText">自由入力テキスト</label>
        <input id="watermarkText" name="watermark_text" maxlength="20" value="<?= h($user['watermark_text']) ?>" data-counter-target="watermarkCount">
        <p class="textCounter"><span id="watermarkCount">0</span> / 20文字</p>
        <p class="accountHelp">2行目にはサイト名「SHOTBASE」が必ず入ります。</p>
        <div class="settingsColumns">
          <?php foreach (['size' => ['文字サイズ', ['large' => '大', 'medium' => '中', 'small' => '小']], 'color' => ['文字色', ['black' => '黒', 'white' => '白']], 'opacity' => ['不透明度', [100 => '100%', 50 => '50%', 20 => '20%', 10 => '10%']]] as $field => [$label, $choices]): ?>
            <label><?= h($label) ?><select name="watermark_<?= h($field) ?>"><?php foreach ($choices as $key => $caption): ?><option value="<?= h((string)$key) ?>" <?= (string)$user['watermark_' . $field] === (string)$key ? 'selected' : '' ?>><?= h($caption) ?></option><?php endforeach; ?></select></label>
          <?php endforeach; ?>
        </div>
        <div class="watermarkLayout">
        <fieldset class="signupFieldset"><legend>位置を一括指定</legend>
          <div class="positionGrid"><?php foreach (watermarkPositions() as $key => $label): ?><label><input type="radio" name="watermark_position" value="<?= h($key) ?>" <?= $user['watermark_position'] === $key ? 'checked' : '' ?>><span><?= h($label) ?></span></label><?php endforeach; ?></div>
        </fieldset>
        <figure class="watermarkLivePreview" data-watermark-preview="<?= BASE_URL ?>/admin/watermark_preview.php">
          <figcaption>プレビュー（見本）</figcaption>
          <img alt="ウォーターマークを反映した球場の見本" hidden>
          <p class="accountHelp" role="status" aria-live="polite">見本を読み込み中です。</p>
        </figure>
        </div>
        <p class="accountHelp">一括指定しても、写真ごとに個別で位置変更が可能です。</p>
        <p class="accountHelp">保存すると既存の写真にも反映します。写真ごとに指定した位置は優先されます。写真が多い場合は保存に時間がかかります。</p>
      </section>
    <?php endif; ?>
    <div class="settingsActions"><button class="button primary" type="submit" name="action" value="save">プロフィールを保存</button><a class="button" href="<?= BASE_URL ?>/admin/profile_edit.php">やり直す（元に戻す）</a></div>
  </form>
  <section class="settingsCard tagManagementCard" id="tag-management">
    <h2>自分で作ったタグを管理</h2>
    <p class="accountHelp">ここで作ったタグは、あなたの写真・プロジェクト・プロフィールで使えます。名前を変更すると、すでに付けた場所にも反映されます。</p>
    <form method="post" class="tagManagementCreate formStack">
      <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
      <input type="hidden" name="action" value="tag_create_manage">
      <label for="managedTagName">新しいタグ名</label>
      <div class="inlineTagControls"><input id="managedTagName" name="tag_name" maxlength="12" required placeholder="例：球場グルメ"><button class="button" type="submit">タグを作成</button></div>
      <p class="accountHelp">1〜12文字。運営タグと同じ名前は作成できません。</p>
    </form>
    <?php if (!$ownedTags): ?>
      <p class="accountHelp">まだ作成したタグはありません。</p>
    <?php else: ?>
      <div class="tagManagementList">
        <?php foreach ($ownedTags as $tag): ?>
          <?php $useCount = (int)$tag['photo_use_count'] + (int)$tag['project_use_count'] + (int)$tag['profile_use_count']; ?>
          <article class="tagManagementRow">
            <form method="post" class="tagManagementEdit">
              <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
              <input type="hidden" name="action" value="tag_update_manage">
              <input type="hidden" name="tag_id" value="<?= (int)$tag['id'] ?>">
              <label><span class="srOnly">タグ名</span><input name="tag_name" maxlength="12" required value="<?= h($tag['name']) ?>"></label>
              <button class="button" type="submit">修正</button>
            </form>
            <div class="tagManagementMeta"><span><?= $useCount ? '使用中：' . $useCount . 'か所' : '未使用' ?></span><form method="post" data-confirm="「<?= h($tag['name']) ?>」と、そのタグを付けた写真・プロジェクト・プロフィール情報を削除しますか？この操作は取り消せません。"><input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>"><input type="hidden" name="action" value="tag_delete_manage"><input type="hidden" name="tag_id" value="<?= (int)$tag['id'] ?>"><button class="button danger" type="submit">削除</button></form></div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
  <?php if (!$photographer): ?>
    <section class="settingsCard"><h2>写真を投稿してみませんか？</h2><p>変更すると、誰でもアクセスできる公開プロフィールが作成されます。</p><form method="post"><input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>"><button class="button" name="action" value="upgrade">写真投稿会員に変更する</button></form></section>
  <?php endif; ?>
  <section class="settingsDanger"><p>アカウントを終了する場合</p><a class="button danger" href="<?= BASE_URL ?>/admin/withdraw.php">退会</a></section>
</section>
<script src="<?= BASE_URL ?>/assets/js/profile-settings.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/profile-settings.js') ?>" defer></script>
<script src="<?= BASE_URL ?>/assets/js/watermark-preview.js" defer></script>
<script src="<?= BASE_URL ?>/assets/js/tag-create.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/tag-create.js') ?>" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
