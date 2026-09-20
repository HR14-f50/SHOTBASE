<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = requirePhotographer();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// -------------------------------------
// プロジェクト取得
// -------------------------------------

$stmt = db()->prepare('
  SELECT *
  FROM projects
  WHERE id = ?
    AND user_id = ?
');

$stmt->execute([
  $id,
  $userId
]);

$project = $stmt->fetch();

// プロジェクトが存在しない場合
if (!$project) {

  http_response_code(404);

  require_once __DIR__ . '/../includes/header.php';

  echo '
    <section class="emptyState">
      <h1>プロジェクトが見つかりません。</h1>
    </section>
  ';

  require_once __DIR__ . '/../includes/footer.php';

  exit;
}

// -------------------------------------
// 初期値
// -------------------------------------

$title = $project['title'];
$shootingDate = $project['shooting_date'] ?? '';
$description = $project['description'] ?? '';
$visibility = $project['visibility'];

$errors = [];

// -------------------------------------
// 更新処理
// -------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $title = trim($_POST['title'] ?? '');
  $shootingDate = trim($_POST['shooting_date'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $visibility = $_POST['visibility'] ?? 'private';

  // ---------------------------------
  // バリデーション
  // ---------------------------------

  if ($title === '') {

    $errors[] = 'プロジェクト名を入力してください.';

  } elseif (mb_strlen($title) > 100) {

    $errors[] = 'プロジェクト名は100文字以内で入力してください。';
  }

  if (mb_strlen($description) > 500) {

    $errors[] = '説明は500文字以内で入力してください。';
  }

  $allowedVisibility = [
    'private',
    'public'
  ];

  if (!in_array($visibility, $allowedVisibility, true)) {

    $errors[] = '公開設定が正しくありません。';
  }

  // 撮影日は空欄OK
  if ($shootingDate !== '') {

    $date = DateTime::createFromFormat(
      'Y-m-d',
      $shootingDate
    );

    if (
      !$date ||
      $date->format('Y-m-d') !== $shootingDate
    ) {
      $errors[] = '撮影日の形式が正しくありません。';
    }
  }

  // ---------------------------------
  // DB更新
  // ---------------------------------

  if (!$errors) {

    $stmt = db()->prepare('
      UPDATE projects
      SET
        title = ?,
        shooting_date = ?,
        description = ?,
        visibility = ?,
        updated_at = NOW()
      WHERE id = ?
        AND user_id = ?
    ');

    $stmt->execute([
      $title,
      $shootingDate !== ''
        ? $shootingDate
        : null,
      $description !== ''
        ? $description
        : null,
      $visibility,
      $id,
      $userId
    ]);

    header(
      'Location: ' .
      BASE_URL .
      '/admin/project.php?id=' .
      $id .
      '&updated=1'
    );

    exit;
  }
}

$themeUserId = $userId;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="formPage">

  <p class="eyebrow">
    EDIT PROJECT
  </p>

  <h1>
    プロジェクトを編集
  </h1>

  <!-- エラー -->
  <?php if ($errors): ?>

    <div class="errorBox">

      <?php foreach ($errors as $error): ?>

        <p>
          <?= h($error) ?>
        </p>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

  <form
    method="post"
    action=""
    class="projectForm"
  >

    <input
      type="hidden"
      name="id"
      value="<?= $id ?>"
    >

    <!-- プロジェクト名 -->
    <div class="formGroup">

      <label for="title">
        プロジェクト名
      </label>

      <input
        type="text"
        id="title"
        name="title"
        value="<?= h($title) ?>"
        maxlength="100"
        required
      >

    </div>

    <!-- 撮影日 -->
    <div class="formGroup">

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

    <!-- 説明 -->
    <div class="formGroup">

      <label for="description">
        説明
      </label>

      <textarea
        id="description"
        name="description"
        rows="8"
        maxlength="500"
      ><?= h($description) ?></textarea>

    </div>

    <!-- 公開設定 -->
    <fieldset class="formGroup">

      <legend>
        公開設定
      </legend>

      <label>

        <input
          type="radio"
          name="visibility"
          value="private"
          <?= $visibility === 'private' ? 'checked' : '' ?>
        >

        非公開

      </label>

      <label>

        <input
          type="radio"
          name="visibility"
          value="public"
          <?= $visibility === 'public' ? 'checked' : '' ?>
        >

        公開

      </label>

    </fieldset>

    <!-- ボタン -->
    <div class="actionRow">

      <button
        class="button primary"
        type="submit"
      >
        変更を保存
      </button>

      <a
        class="button"
        href="<?= BASE_URL ?>/admin/project.php?id=<?= $id ?>"
      >
        キャンセル
      </a>

    </div>

  </form>

</section>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
