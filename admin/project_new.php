<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_helpers.php';

$userId = requirePhotographer();

$errors = [];

// -------------------------------------
// タグ取得
// -------------------------------------

$stmt = db()->prepare('
  SELECT
    id,
    name,
    tag_type,
    border_color,
    background_color
  FROM tags
  WHERE user_id = ?
    OR user_id IS NULL
  ORDER BY name ASC
');

$stmt->execute([$userId]);

$tags = $stmt->fetchAll();

// -------------------------------------
// 初期値
// -------------------------------------

$title = '';
$dateMode = 'single';

$shootingDate = date('Y-m-d');
$shootingDateStart = date('Y-m-d');
$shootingDateEnd = date('Y-m-d');

$description = '';
$visibility = 'public';

$selectedDefaultTagIds = [];

foreach ($_SESSION['tag_edit_sessions'] ?? [] as $key => $context) {
  if (($context['expires'] ?? 0) < time()) unset($_SESSION['tag_edit_sessions'][$key]);
}
$editToken = is_string($_POST['edit_token'] ?? null) ? $_POST['edit_token'] : '';
$editContext = tagEditContext($editToken, $userId);
if (!$editContext) {
  $editToken = bin2hex(random_bytes(24));
  $_SESSION['tag_edit_sessions'][$editToken] = [
    'user_id' => $userId,
    'expires' => time() + 7200,
    'created' => [],
  ];
}

// -------------------------------------
// POST
// -------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (!profileCsrfValid()) {
    $errors[] = 'ページの有効期限が切れています。ページを再読み込みして、もう一度お試しください。';
  }

  $title = trim($_POST['title'] ?? '');

  $dateMode =
    $_POST['date_mode'] ?? 'single';

  $shootingDate =
    trim($_POST['shooting_date'] ?? '');

  $shootingDateStart =
    trim($_POST['shooting_date_start'] ?? '');

  $shootingDateEnd =
    trim($_POST['shooting_date_end'] ?? '');

  $description =
    trim($_POST['description'] ?? '');

  $visibility =
    $_POST['visibility'] ?? 'draft';

  $selectedDefaultTagIds =
    $_POST['default_tag_ids'] ?? [];

  // ---------------------------------
  // 日付形式
  // ---------------------------------

  $allowedDateModes = [
    'single',
    'range',
    'none'
  ];

  if (!in_array(
    $dateMode,
    $allowedDateModes,
    true
  )) {
    $dateMode = 'none';
  }

  // ---------------------------------
  // 単日
  // ---------------------------------

  if ($dateMode === 'single') {

    if ($shootingDate === '') {

      $errors[] =
        '撮影日を選択してください。';

    } else {

      $date =
        DateTime::createFromFormat(
          'Y-m-d',
          $shootingDate
        );

      if (
        !$date ||
        $date->format('Y-m-d')
          !== $shootingDate
      ) {
        $errors[] =
          '撮影日が正しくありません。';
      }
    }
  }

  // ---------------------------------
  // 期間
  // ---------------------------------

  if ($dateMode === 'range') {

    if (
      $shootingDateStart === ''
      || $shootingDateEnd === ''
    ) {

      $errors[] =
        '撮影期間の開始日と終了日を選択してください。';

    } else {

      $start =
        DateTime::createFromFormat(
          'Y-m-d',
          $shootingDateStart
        );

      $end =
        DateTime::createFromFormat(
          'Y-m-d',
          $shootingDateEnd
        );

      if (
        !$start ||
        !$end ||
        $start->format('Y-m-d')
          !== $shootingDateStart ||
        $end->format('Y-m-d')
          !== $shootingDateEnd
      ) {

        $errors[] =
          '撮影期間が正しくありません。';

      } elseif ($end < $start) {

        $errors[] =
          '終了日は開始日以降を選択してください。';
      }
    }
  }

  // ---------------------------------
  // キャプション
  // ---------------------------------

  if (mb_strlen($description) > 2000) {

    $errors[] =
      'プロジェクトのキャプションは2000文字以内で入力してください。';
  }

  // ---------------------------------
  // 公開設定
  // ---------------------------------

  $allowedVisibility = [
    'draft',
    'private',
    'public'
  ];

  if (!in_array(
    $visibility,
    $allowedVisibility,
    true
  )) {

    $visibility = 'draft';
  }

  // ---------------------------------
  // デフォルトタグ
  // ---------------------------------

  $selectedDefaultTagIds =
    array_values(
      array_unique(
        array_map(
          'intval',
          $selectedDefaultTagIds
        )
      )
    );

  $validDefaultTagIds = [];

  if ($selectedDefaultTagIds) {

    $placeholders =
      implode(
        ',',
        array_fill(
          0,
          count($selectedDefaultTagIds),
          '?'
        )
      );

    $params = array_merge(
      [$userId],
      $selectedDefaultTagIds
    );

    $stmt = db()->prepare("
      SELECT id
      FROM tags
      WHERE
        (
          user_id = ?
          OR user_id IS NULL
        )
        AND id IN ($placeholders)
    ");

    $stmt->execute($params);

    $validDefaultTagIds =
      array_map(
        'intval',
        array_column(
          $stmt->fetchAll(),
          'id'
        )
      );
  }

  // ---------------------------------
  // タイトル自動生成
  // ---------------------------------

  if ($title === '') {

    if ($dateMode === 'single') {

      $formattedDate =
        (new DateTime($shootingDate))
          ->format('Y/m/d');

      $title =
        $formattedDate .
        'のプロジェクト';

    } elseif ($dateMode === 'range') {

      $start =
        new DateTime($shootingDateStart);

      $end =
        new DateTime($shootingDateEnd);

      $days =
        $start->diff($end)->days + 1;

      $title =
        $start->format('Y/m/d')
        . 'から'
        . $days
        . '日間のプロジェクト';

    } else {

      $title =
        date('Y/m/d')
        . 'のプロジェクト';
    }
  }

  if (mb_strlen($title) > 200) {

    $errors[] =
      'プロジェクト名は200文字以内で入力してください。';
  }

  // ---------------------------------
  // DB保存
  // ---------------------------------

  try { validatePhotoTags($userId, (array)($_POST['default_tag_ids'] ?? [])); } catch (RuntimeException $e) { $errors[] = $e->getMessage(); }
  if (!$errors) {

    if ($dateMode === 'single') {

      $dbStartDate =
        $shootingDate;

      $dbEndDate =
        null;

    } elseif ($dateMode === 'range') {

      $dbStartDate =
        $shootingDateStart;

      $dbEndDate =
        $shootingDateEnd;

    } else {

      $dbStartDate = null;
      $dbEndDate = null;
    }

    $pdo = db();

    try {

      $pdo->beginTransaction();

      $stmt = $pdo->prepare('
        INSERT INTO projects (
          user_id,
          title,
          description,
          date_mode,
          shooting_date,
          shooting_date_end,
          visibility
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
      ');

      $stmt->execute([
        $userId,
        $title,
        $description !== ''
          ? $description
          : null,
        $dateMode,
        $dbStartDate,
        $dbEndDate,
        $visibility
      ]);

      $projectId =
        (int)$pdo->lastInsertId();

      // -------------------------
      // デフォルトタグ保存
      // -------------------------

      if ($validDefaultTagIds) {

        $tagStmt =
          $pdo->prepare('
            INSERT INTO project_default_tags
            (
              project_id,
              tag_id
            )
            VALUES (?, ?)
          ');

        foreach (
          $validDefaultTagIds
          as $tagId
        ) {

          $tagStmt->execute([
            $projectId,
            $tagId
          ]);
        }
      }

      $pdo->commit();
      unset($_SESSION['tag_edit_sessions'][$editToken]);

      header(
        'Location: '
        . BASE_URL
        . '/admin/project.php?id='
        . $projectId
      );

      exit;

    } catch (Throwable $e) {

      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }

      $errors[] =
        'プロジェクトの作成に失敗しました。';
    }
  }
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="formPage">

  <p class="eyebrow">
    NEW PROJECT
  </p>

  <h1>
    プロジェクト作成
  </h1>

  <?php if ($errors): ?>

    <div class="errorBox">

      <?php foreach ($errors as $error): ?>

        <p>
          <?= h($error) ?>
        </p>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

  <form method="post" class="projectForm">
    <input type="hidden" name="edit_token" value="<?= h($editToken) ?>">
    <input type="hidden" name="csrf" value="<?= h(profileCsrfToken()) ?>">

    <!-- =====================================
      プロジェクト名
    ====================================== -->

    <div class="formGroup">

      <label for="title">
        プロジェクト名
      </label>

      <input
        type="text"
        id="title"
        name="title"
        maxlength="200"
        value="<?= h($title) ?>"
        placeholder="未入力の場合は日付から自動生成されます"
        data-counter-target="titleCounter"
      >

      <div class="formMeta">

        <p class="formHelp">
          空欄の場合は撮影日から自動で名前を付けます。
        </p>

        <p class="textCounter">
          <span id="titleCounter">0</span>
          / 200
        </p>

      </div>

    </div>

    <!-- =====================================
      撮影日
    ====================================== -->

    <fieldset class="formGroup">

      <legend>
        撮影日
      </legend>

      <div class="dateModeOptions">

        <label>

          <input
            type="radio"
            name="date_mode"
            value="single"
            <?= $dateMode === 'single'
              ? 'checked'
              : '' ?>
          >

          日付を選択

        </label>

        <label>

          <input
            type="radio"
            name="date_mode"
            value="range"
            <?= $dateMode === 'range'
              ? 'checked'
              : '' ?>
          >

          期間を選択

        </label>

        <label>

          <input
            type="radio"
            name="date_mode"
            value="none"
            <?= $dateMode === 'none'
              ? 'checked'
              : '' ?>
          >

          日付未選択

        </label>

      </div>

      <!-- 単日 -->

      <div
        id="singleDateArea"
        class="dateInputArea"
      >

        <label for="shooting_date">
          撮影日
        </label>

        <input
          type="date"
          id="shooting_date"
          name="shooting_date"
          value="<?= h($shootingDate) ?>"
        >

      </div>

      <!-- 期間 -->

      <div
        id="rangeDateArea"
        class="dateInputArea"
      >

        <div>

          <label for="shooting_date_start">
            開始日
          </label>

          <input
            type="date"
            id="shooting_date_start"
            name="shooting_date_start"
            value="<?= h($shootingDateStart) ?>"
          >

        </div>

        <span class="dateRangeSeparator">
          〜
        </span>

        <div>

          <label for="shooting_date_end">
            終了日
          </label>

          <input
            type="date"
            id="shooting_date_end"
            name="shooting_date_end"
            value="<?= h($shootingDateEnd) ?>"
          >

        </div>

      </div>

    </fieldset>

    <!-- =====================================
      キャプション
    ====================================== -->

    <div class="formGroup">

      <label for="description">
        プロジェクトのキャプション
      </label>

      <textarea
        id="description"
        name="description"
        rows="10"
        maxlength="2000"
        placeholder="試合や撮影についての文章を書くことができます。"
        data-counter-target="descriptionCounter"
      ><?= h($description) ?></textarea>

      <div class="formMeta">

        <p class="formHelp">
          試合の記録、撮影時の出来事、写真についての文章などを書けます。
        </p>

        <p class="textCounter">
          <span id="descriptionCounter">0</span>
          / 2000
        </p>

      </div>

    </div>

    <!-- =====================================
      デフォルトタグ
    ====================================== -->

    <fieldset class="formGroup">

      <legend>
        デフォルト付与タグ
      </legend>

      <p class="formHelp">
        このプロジェクトに追加する写真へ
        自動的に付けるタグです。
      </p>

      <?php if ($tags): ?>

        <div class="tagCheckboxList" data-tag-list data-tag-delete="<?= BASE_URL ?>/admin/tag_delete.php" data-tag-input-name="default_tag_ids[]">

          <?php foreach ($tags as $tag): ?>

            <span class="tagManageItem">
              <label
                class="tagCheckboxItem"
                style="<?= h(profileTagStyle($tag)) ?>"
              >

              <input
                type="checkbox"
                name="default_tag_ids[]"
                value="<?= (int)$tag['id'] ?>"
                <?= in_array(
                  (int)$tag['id'],
                  $selectedDefaultTagIds,
                  true
                )
                  ? 'checked'
                  : '' ?>
              >

              #<?= h($tag['name']) ?>

              </label>
              <?php if (in_array((int)$tag['id'], $_SESSION['tag_edit_sessions'][$editToken]['created'], true)): ?><button type="button" class="tagDeleteButton" data-delete-tag="<?= (int)$tag['id'] ?>" data-tag-name="<?= h($tag['name']) ?>" aria-label="<?= h($tag['name']) ?>を登録タグから削除">×</button><?php endif; ?>
            </span>

          <?php endforeach; ?>

        </div>

      <?php else: ?>

        <p class="notice">
          登録されているタグはありません。
        </p>
        <div class="tagCheckboxList" data-tag-list data-tag-delete="<?= BASE_URL ?>/admin/tag_delete.php" data-tag-input-name="default_tag_ids[]"></div>

      <?php endif; ?>
      <?php $tagInputName = 'default_tag_ids[]'; require __DIR__ . '/../includes/tag_create_ui.php'; ?>

    </fieldset>

    <!-- =====================================
      公開設定
    ====================================== -->

    <fieldset class="formGroup">

      <legend>
        公開設定
      </legend>

      <label>

        <input
          type="radio"
          name="visibility"
          value="draft"
          <?= $visibility === 'draft'
            ? 'checked'
            : '' ?>
        >

        下書き

      </label>

      <label>

        <input
          type="radio"
          name="visibility"
          value="private"
          <?= $visibility === 'private'
            ? 'checked'
            : '' ?>
        >

        非公開

      </label>

      <label>

        <input
          type="radio"
          name="visibility"
          value="public"
          <?= $visibility === 'public'
            ? 'checked'
            : '' ?>
        >

        公開

      </label>

    </fieldset>

    <div class="actionRow">

      <button
        class="button primary"
        type="submit"
      >
        プロジェクトを作成
      </button>

      <a
        class="button"
        href="<?= BASE_URL ?>/<?= h(accountHome()) ?>"
      >
        キャンセル
      </a>

    </div>

  </form>

</section>

<script>
document.addEventListener(
  'DOMContentLoaded',
  function () {

    // -------------------------------------
    // 撮影日の表示切り替え
    // -------------------------------------

    const radios =
      document.querySelectorAll(
        'input[name="date_mode"]'
      );

    const singleArea =
      document.getElementById(
        'singleDateArea'
      );

    const rangeArea =
      document.getElementById(
        'rangeDateArea'
      );

    function updateDateFields() {

      const checked =
        document.querySelector(
          'input[name="date_mode"]:checked'
        );

      if (!checked) {
        return;
      }

      if (checked.value === 'single') {

        singleArea.hidden = false;
        rangeArea.hidden = true;

      } else if (
        checked.value === 'range'
      ) {

        singleArea.hidden = true;
        rangeArea.hidden = false;

      } else {

        singleArea.hidden = true;
        rangeArea.hidden = true;
      }
    }

    radios.forEach(function (radio) {

      radio.addEventListener(
        'change',
        updateDateFields
      );
    });

    updateDateFields();

  }
);
</script>

<script src="<?= BASE_URL ?>/assets/js/tag-create.js?v=<?= (int) @filemtime(__DIR__ . '/../assets/js/tag-create.js') ?>" defer></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
