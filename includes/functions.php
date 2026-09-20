<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tag_palette.php';

function h(?string $value): string
{
  return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
  header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
  exit;
}

function flash(string $key, ?string $value = null): ?string
{
  if ($value !== null) {
    $_SESSION['_flash'][$key] = $value;
    return null;
  }

  $message = $_SESSION['_flash'][$key] ?? null;
  unset($_SESSION['_flash'][$key]);
  return $message;
}

function projectPhotoCount(int $projectId): int
{
  $stmt = db()->prepare('SELECT COUNT(*) FROM photos WHERE project_id = ?');
  $stmt->execute([$projectId]);
  return (int)$stmt->fetchColumn();
}

function generateStoredFilename(string $extension): string
{
  return bin2hex(random_bytes(16)) . '.' . strtolower($extension);
}

function publicPhotoPath(string $relativePath): string
{
  return BASE_URL . '/' . ltrim($relativePath, '/');
}
