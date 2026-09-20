<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
header('Content-Type: application/json; charset=utf-8');
$userId = currentUserId();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$userId || currentUserType() !== 'photographer' || !profileCsrfValid()) {
  http_response_code(403);
  echo json_encode(['error' => 'この操作は許可されていません。'], JSON_UNESCAPED_UNICODE);
  exit;
}
$editToken = is_string($_POST['edit_token'] ?? null) ? $_POST['edit_token'] : '';
$context = tagEditContext($editToken, $userId);
$tagId = (int)($_POST['id'] ?? 0);
if (!$context || !in_array($tagId, $context['created'], true)) {
  http_response_code(403);
  echo json_encode(['error' => '今回の編集中に追加したタグだけ削除できます。'], JSON_UNESCAPED_UNICODE);
  exit;
}
try {
  $stmt = db()->prepare('DELETE FROM tags WHERE id = ? AND user_id = ? AND NOT EXISTS (SELECT 1 FROM photo_tags WHERE tag_id = tags.id) AND NOT EXISTS (SELECT 1 FROM user_profile_tags WHERE tag_id = tags.id) AND NOT EXISTS (SELECT 1 FROM project_default_tags WHERE tag_id = tags.id)');
  $stmt->execute([$tagId, $userId]);
  if (!$stmt->rowCount()) {
    http_response_code(404);
    echo json_encode(['error' => 'このタグはすでに使われているため削除できません。'], JSON_UNESCAPED_UNICODE);
    exit;
  }
  echo json_encode(['deleted' => true]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => '削除できませんでした。再度お試しください。'], JSON_UNESCAPED_UNICODE);
}
