<?php
declare(strict_types=1);
require_once __DIR__ . '/tag_filters.php';

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

function profileCsrfToken(): string
{
  if (!isset($_SESSION['profile_csrf'])) {
    $_SESSION['profile_csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['profile_csrf'];
}

function profileCsrfValid(): bool
{
  return isset($_SESSION['profile_csrf']) &&
    is_string($_POST['csrf'] ?? null) &&
    hash_equals($_SESSION['profile_csrf'], $_POST['csrf']);
}

function profileUrl(string $username, array $filters = []): string
{
  return BASE_URL .
    '/profile.php?' .
    http_build_query(array_merge(['username' => $username], $filters));
}

function projectDateLabel(array $project): string
{
  if ($project['date_mode'] === 'none' || !$project['shooting_date']) {
    return '日付未指定';
  }
  $start = str_replace('-', '.', $project['shooting_date']);
  if ($project['date_mode'] === 'range' && $project['shooting_date_end']) {
    return $start . ' – ' . str_replace('-', '.', $project['shooting_date_end']);
  }
  return $start;
}
