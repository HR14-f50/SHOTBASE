<?php
declare(strict_types=1);
require_once __DIR__ . '/profile_helpers.php';

function visibilityName(string $value): string
{
  return ['public' => '公開', 'private' => '非公開', 'draft' => '下書き'][$value] ?? '下書き';
}

function ownedProject(int $id, int $userId): array
{
  $stmt = db()->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');
  $stmt->execute([$id, $userId]);
  $project = $stmt->fetch();
  if (!$project) { http_response_code(404); exit('プロジェクトが見つかりません。'); }
  return $project;
}

function availableTags(int $userId): array
{
  $stmt = db()->prepare('SELECT * FROM tags WHERE user_id = ? OR user_id IS NULL ORDER BY name');
  $stmt->execute([$userId]);
  return $stmt->fetchAll();
}

function createOwnedTag(int $userId, string $name): int
{
  $name = trim($name);
  if ($name === '' || mb_strlen($name) > 12) throw new RuntimeException('タグ名は1〜12文字で入力してください。');
  $stmt = db()->prepare('SELECT id FROM tags WHERE name = ? AND (user_id = ? OR user_id IS NULL) LIMIT 1');
  $stmt->execute([$name, $userId]);
  $existing = $stmt->fetchColumn();
  if ($existing) return (int)$existing;
  db()->prepare('INSERT INTO tags (user_id, name, tag_type) VALUES (?, ?, "custom")')->execute([$userId, $name]);
  return (int)db()->lastInsertId();
}

/** 区切り入力を検証してから保存し、途中だけ登録されることを防ぎます。 */
function parseTagNames(string $input): array
{
  if (mb_strlen($input) > 260) throw new RuntimeException('一度に追加できるタグは20個まで、各12文字以内です。');
  $names = array_values(array_unique(array_filter(array_map('trim', preg_split('/[,、\r\n]+/u', $input)), fn($name) => $name !== '')));
  if (count($names) > 20) throw new RuntimeException('一度に追加できるタグは20個までです。');
  foreach ($names as $name) {
    if (mb_strlen($name) > 12 || preg_match('/[\x00-\x1F]/u', $name)) throw new RuntimeException('タグ名は制御文字を含まない12文字以内で入力してください。');
  }
  return $names;
}

function validatePhotoTags(int $userId, array $ids): array
{
  $ids = array_values(array_unique(array_map('intval', $ids)));
  if (count($ids) > 10) throw new RuntimeException('1枚の写真に付けられるタグは10件までです。');
  if (!$ids) return [];
  $stmt = db()->prepare('SELECT id, name, user_id, tag_type FROM tags WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') AND (user_id = ? OR user_id IS NULL)');
  $stmt->execute([...$ids, $userId]);
  $rows = $stmt->fetchAll();
  if (count($rows) !== count($ids)) throw new RuntimeException('選択できないタグが含まれています。');
  foreach ($rows as $row) {
    // 運営タグ（user_id=NULL）は、公式大会名など12文字を超える名称を許可します。
    if ($row['user_id'] !== null && mb_strlen($row['name']) > 12) throw new RuntimeException('タグは12文字以内のものを選んでください。');
  }
  return $ids;
}

function tagEditContext(string $token, int $userId): ?array
{
  $context = $_SESSION['tag_edit_sessions'][$token] ?? null;
  return $context && $context['user_id'] === $userId && $context['expires'] > time() ? $context : null;
}
