<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin_helpers.php';
header('Content-Type: application/json; charset=utf-8');
function tagResponse(array $body, int $status = 200): never
{
  http_response_code($status);
  echo json_encode($body, JSON_UNESCAPED_UNICODE);
  exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') tagResponse(['error' => 'POSTで送信してください。'], 405);
$userId = currentUserId();
if (!$userId || !profileCsrfValid()) tagResponse(['error' => 'ログイン状態を確認して、ページを再読み込みしてください。'], 403);
$name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
try {
  $names = parseTagNames($name);
  if (!$names) throw new RuntimeException('タグ名を入力してください。');
  $pdo = db();
  $pdo->beginTransaction();
  $tags = [];
  $created = [];
  $editToken = is_string($_POST['edit_token'] ?? null) ? $_POST['edit_token'] : '';
  $context = tagEditContext($editToken, $userId);
  foreach ($names as $name) {
    $lookup = $pdo->prepare('SELECT id FROM tags WHERE name = ? AND (user_id = ? OR user_id IS NULL)');
    $lookup->execute([$name, $userId]);
    $isNew = !$lookup->fetchColumn();
    $id = createOwnedTag($userId, $name);
    $stmt = $pdo->prepare('SELECT * FROM tags WHERE id = ?');
    $stmt->execute([$id]);
    $tag = $stmt->fetch();
    if ($isNew) $created[] = $id;
    $tags[$id] = ['id' => $id, 'name' => $tag['name'], 'style' => profileTagStyle($tag), 'owned' => (int)$tag['user_id'] === $userId, 'deletable' => $context && $isNew];
  }
  $pdo->commit();
  if ($context) $_SESSION['tag_edit_sessions'][$editToken]['created'] = array_values(array_unique([...$context['created'], ...$created]));
  $tags = array_values($tags);
  tagResponse(['tags' => $tags] + $tags[0]);
} catch (Throwable $e) {
  if (db()->inTransaction()) db()->rollBack();
  tagResponse(['error' => $e instanceof PDOException ? 'タグを保存できませんでした。' : $e->getMessage()], 422);
}
