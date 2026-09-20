<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

function loginUser(int $userId): void
{
  session_regenerate_id(true);
  $_SESSION['user_id'] = $userId;
}

function logoutUser(): void
{
  $_SESSION = [];

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
      session_name(),
      '',
      time() - 42000,
      $params['path'],
      $params['domain'],
      (bool)$params['secure'],
      (bool)$params['httponly']
    );
  }

  session_destroy();
}

function currentUserId(): ?int
{
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function requireLogin(): int
{
  $id = currentUserId();

  require_once __DIR__ . '/db.php';
  $exists = db()->prepare('SELECT id FROM users WHERE id = ?');
  $exists->execute([$id]);
  if ($id === null || !$exists->fetchColumn()) {
    unset($_SESSION['user_id']);
    require_once __DIR__ . '/config.php';
    header('Location: ' . BASE_URL . '/login.php');
    exit;
  }

  return $id;
}

function currentUserType(): string
{
  require_once __DIR__ . '/db.php';
  $stmt = db()->prepare('SELECT user_type FROM users WHERE id = ?');
  $stmt->execute([currentUserId()]);
  return $stmt->fetchColumn() ?: 'viewer';
}

function accountHome(): string
{
  require_once __DIR__ . '/db.php';
  $stmt = db()->prepare('SELECT username, user_type FROM users WHERE id = ?');
  $stmt->execute([currentUserId()]);
  $user = $stmt->fetch();
  return $user && $user['user_type'] === 'photographer'
    ? 'profile.php?username=' . rawurlencode($user['username']) : 'account.php';
}

function requirePhotographer(): int
{
  $id = requireLogin();
  if (currentUserType() !== 'photographer') {
    require_once __DIR__ . '/config.php';
    header('Location: ' . BASE_URL . '/account.php');
    exit;
  }
  return $id;
}
