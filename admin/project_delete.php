<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = requirePhotographer();
require_once __DIR__ . '/../includes/profile_helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !profileCsrfValid()) {
  http_response_code(403);
  exit('ページを再読み込みしてください。');
}

// -------------------------------------
// POST以外は禁止
// -------------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

  header(
    'Location: ' .
    BASE_URL .
    '/' . accountHome()
  );

  exit;
}

// -------------------------------------
// プロジェクトID取得
// -------------------------------------

$projectId = (int)($_POST['project_id'] ?? 0);

if ($projectId <= 0) {

  header(
    'Location: ' .
    BASE_URL .
    '/' . accountHome()
  );

  exit;
}

// -------------------------------------
// 本人のプロジェクトか確認
// -------------------------------------

$stmt = db()->prepare('
  SELECT
    id,
    title
  FROM projects
  WHERE id = ?
    AND user_id = ?
');

$stmt->execute([
  $projectId,
  $userId
]);

$project = $stmt->fetch();

if (!$project) {

  http_response_code(404);

  require_once __DIR__ . '/../includes/header.php';

  echo '<h1>プロジェクトが見つかりません。</h1>';

  require_once __DIR__ . '/../includes/footer.php';

  exit;
}

// -------------------------------------
// プロジェクト内の写真を取得
// ファイル削除用
// -------------------------------------

$stmt = db()->prepare('
  SELECT
    id,
    file_path,
    original_path
  FROM photos
  WHERE project_id = ?
    AND user_id = ?
');

$stmt->execute([
  $projectId,
  $userId
]);

$photos = $stmt->fetchAll();

// -------------------------------------
// DB削除
// -------------------------------------

$pdo = db();

try {

  $pdo->beginTransaction();

  // ---------------------------------
  // 写真とタグの関連削除
  // ---------------------------------

  $stmt = $pdo->prepare('
    DELETE pt
    FROM photo_tags pt

    INNER JOIN photos ph
      ON ph.id = pt.photo_id

    WHERE ph.project_id = ?
      AND ph.user_id = ?
  ');

  $stmt->execute([
    $projectId,
    $userId
  ]);

  // ---------------------------------
  // 写真削除
  // ---------------------------------

  $stmt = $pdo->prepare('
    DELETE FROM photos
    WHERE project_id = ?
      AND user_id = ?
  ');

  $stmt->execute([
    $projectId,
    $userId
  ]);

  // ---------------------------------
  // プロジェクト削除
  // ---------------------------------

  $stmt = $pdo->prepare('
    DELETE FROM projects
    WHERE id = ?
      AND user_id = ?
  ');

  $stmt->execute([
    $projectId,
    $userId
  ]);

  $pdo->commit();

} catch (Throwable $e) {

  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  http_response_code(500);

  require_once __DIR__ . '/../includes/header.php';

  echo '
    <section class="emptyState">
      <h1>プロジェクトを削除できませんでした。</h1>
      <p>データベース処理中にエラーが発生しました。</p>
    </section>
  ';

  require_once __DIR__ . '/../includes/footer.php';

  exit;
}

// -------------------------------------
// DB削除成功後に画像ファイルを削除
// -------------------------------------

foreach ($photos as $photo) {

  // 表示用画像
  if (!empty($photo['file_path'])) {

    $displayFile =
      dirname(__DIR__) .
      '/' .
      ltrim($photo['file_path'], '/');

    if (is_file($displayFile)) {
      @unlink($displayFile);
    }
  }

  // 元画像
  if (!empty($photo['original_path'])) {

    $originalFile =
      dirname(__DIR__) .
      '/' .
      ltrim($photo['original_path'], '/');

    if (is_file($originalFile)) {
      @unlink($originalFile);
    }
  }
}

// -------------------------------------
// 管理画面へ戻る
// -------------------------------------

header(
  'Location: ' .
  BASE_URL .
  '/' . accountHome()
);

exit;
