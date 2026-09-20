<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/registration.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$userId = currentUserId();
$key = is_string($_POST['theme_key'] ?? null) ? $_POST['theme_key'] : '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$userId || currentUserType() !== 'photographer' || !profileCsrfValid()) {
  http_response_code(403);
  echo json_encode(['error' => 'ログイン状態を確認してください。'], JSON_UNESCAPED_UNICODE);
  exit;
}
if (!isset(profileThemes()[$key])) {
  http_response_code(422);
  echo json_encode(['error' => 'テーマカラーを選択してください。'], JSON_UNESCAPED_UNICODE);
  exit;
}
try {
  db()->prepare('UPDATE users SET theme_key = ? WHERE id = ?')->execute([$key, $userId]);
  echo json_encode(['saved' => true, 'theme_key' => $key]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'テーマを保存できませんでした。'], JSON_UNESCAPED_UNICODE);
}
