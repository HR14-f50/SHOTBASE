<?php
declare(strict_types=1);
require_once __DIR__ . '/profile_helpers.php';

/** 公開詳細ページでは、所有者であっても公開データだけを集計します。 */
function renderPublicSidebar(int $userId, bool $guestPreview, ?array $projectTags = null, array $selected = [], int $projectId = 0, bool $admin = false): void
{
  $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
  $stmt->execute([$userId]);
  $user = $stmt->fetch();
  if (!$user) return;
  $username = $user['username'];
  $isOwner = false;
  if ($projectTags !== null) {
    $sidebarProjectTags = $projectTags;
    $sidebarSelectedTags = $selected;
    $sidebarAction = BASE_URL . ($admin ? '/admin/project.php' : '/project.php');
    $sidebarHidden = ['id' => $projectId] + ($guestPreview ? ['preview' => 'guest'] : []);
  }
  $tagId = 0;
  $filters = $guestPreview ? ['preview' => 'guest'] : [];
  $stmt = db()->prepare('SELECT t.id, t.name, t.tag_type, COUNT(DISTINCT ph.id) AS use_count FROM tags t JOIN photo_tags pt ON pt.tag_id = t.id JOIN photos ph ON ph.id = pt.photo_id JOIN projects p ON p.id = ph.project_id WHERE p.user_id = ? AND ph.user_id = p.user_id AND p.visibility = "public" AND ph.visibility = "public" GROUP BY t.id, t.name, t.tag_type ORDER BY use_count DESC, t.name LIMIT 20');
  $stmt->execute([$userId]);
  $popularTags = $stmt->fetchAll();
  $stmt = db()->prepare('SELECT COUNT(*) FROM projects WHERE user_id = ? AND visibility = "public"');
  $stmt->execute([$userId]);
  $totalProjects = (int)$stmt->fetchColumn();
  $stmt = db()->prepare('SELECT COUNT(*) FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.user_id = ? AND p.user_id = ph.user_id AND ph.visibility = "public" AND p.visibility = "public"');
  $stmt->execute([$userId]);
  $totalPhotos = (int)$stmt->fetchColumn();
  echo '<aside class="profileAreaB" aria-label="撮影者のプロフィール">';
  require __DIR__ . '/profile_sidebar_content.php';
  echo '</aside>';
}
