<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
$userId = requirePhotographer();
$projectId = (int)($_GET['project_id'] ?? 0);
$stmt = db()->prepare('SELECT id, title FROM projects WHERE id = ? AND user_id = ?');
$stmt->execute([$projectId, $userId]);
$project = $stmt->fetch();
if (!$project) {
  http_response_code(404);
  exit('プロジェクトが見つかりません。');
}
$tags = availableTags($userId);
$stmt = db()->prepare('SELECT tag_id FROM project_default_tags WHERE project_id = ?');
$stmt->execute([$projectId]);
$defaultTags = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
$remaining = max(0, MAX_PHOTOS_PER_PROJECT - projectPhotoCount($projectId));
$pageTitle = '写真を追加';
$themeUserId = $userId;
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/photo-upload.css">
<section class="photoUploadPage">
  <p class="eyebrow">ADD YOUR MOMENTS</p>
  <h1>写真を追加</h1>
  <p class="uploadProjectName"><?= h($project['title']) ?></p>
  <div class="uploadRules">
    <p>1回50枚まで · 1枚20MB未満 · JPEG / PNG / WebP</p>
    <p>長辺2000px以下・2MB以内に自動圧縮して保存します。</p>
    <p>このプロジェクトにはあと<strong><?= $remaining ?>枚</strong>追加できます（全体で200枚まで）。</p>
  </div>
  <noscript><p class="notice error">写真の選択と送信にはJavaScriptを有効にしてください。</p></noscript>
  <form id="photoUploadForm"
    data-endpoint="<?= BASE_URL ?>/admin/photo_upload_api.php"
    data-max-files="<?= min(MAX_UPLOAD_BATCH, $remaining) ?>"
    data-max-bytes="<?= MAX_UPLOAD_BYTES ?>">
    <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">
    <input type="hidden" name="project_id" value="<?= $projectId ?>">
    <div class="uploadPicker" id="uploadDropArea">
      <span class="uploadPickerIcon" aria-hidden="true">＋</span>
      <h2>撮影した写真を選びましょう</h2>
      <p>ここにドラッグ＆ドロップ、またはボタンから選択</p>
      <label class="button primary" for="photoFiles">写真を追加する</label>
      <input class="uploadFileInput" type="file" id="photoFiles" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" multiple <?= $remaining === 0 ? 'disabled' : '' ?>>
      <p class="muted">追加で選んでも、すでに選んだ写真は残ります。</p>
    </div>
    <div id="uploadErrors" class="notice error" role="alert" hidden></div>
    <div class="uploadQueueHeader">
      <h2>選択した写真 <span id="uploadCount">0 / <?= min(MAX_UPLOAD_BATCH, $remaining) ?></span></h2>
      <button type="button" id="clearPhotos" class="button" disabled>選択をすべて解除</button>
    </div>
    <p id="emptyPhotoQueue" class="uploadQueueEmpty">選択した写真のプレビューがここに並びます。</p>
    <ul id="photoQueue" class="photoQueue" aria-label="アップロード予定の写真"></ul>
    <fieldset class="uploadTags" id="uploadTags">
      <legend>今回の写真すべてに付けるタグ</legend>
      <?php require __DIR__ . '/../includes/tag_search_ui.php'; ?>
      <?php
        $tagPickerTags = $tags;
        $tagPickerSelected = $defaultTags;
        $tagPickerInputName = 'tag_ids[]';
        $tagPickerLabelClass = 'uploadTagChoice';
        require __DIR__ . '/../includes/tag_picker.php';
      ?>
      <?php if (!$tags): ?><p class="muted">タグは写真の編集画面でも追加できます。</p><?php endif; ?>
      <?php require __DIR__ . '/../includes/tag_create_ui.php'; ?>
    </fieldset>
    <div class="uploadProgressArea" id="uploadProgressArea" hidden>
      <label for="uploadProgress">送信状況</label>
      <progress id="uploadProgress" max="100" value="0"></progress>
      <p id="uploadStatus" role="status" aria-live="polite"></p>
    </div>
    <div class="uploadActions">
      <button class="button primary" type="submit" id="submitPhotos" disabled>写真をアップロード</button>
      <a class="button" href="<?= BASE_URL ?>/admin/project.php?id=<?= $projectId ?>">登録済みの写真を見る</a>
    </div>
    <p class="uploadSaveNote">写真は「公開」で保存されます。プロジェクトが非公開の場合、写真も外部には表示されません。</p>
    <p class="uploadSaveNote">圧縮前の元ファイルは保存されません。お手元の写真はそのまま保管してください。</p>
  </form>
</section>
<script src="<?= BASE_URL ?>/assets/js/photo-upload.js" defer></script>
<script src="<?= BASE_URL ?>/assets/js/tag-create.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
