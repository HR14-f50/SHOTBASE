<?php
declare(strict_types=1);
require_once __DIR__ . '/profile_helpers.php';
require_once __DIR__ . '/theme_palette.php';
require_once __DIR__ . '/photo_upload.php';

function removeSignupIcon(array $draft): void
{
  $path = $draft['icon_path'] ?? '';
  if (preg_match('~^uploads/avatars/signup_[a-f0-9]{32}\.jpg$~', $path)) {
    $absolute = __DIR__ . '/../' . $path;
    if (is_file($absolute)) unlink($absolute);
  }
}

function storeSignupIcon(array $file, float $cropX = 50, float $cropY = 50, float $zoom = 100): string
{
  if ((int)($file['size'] ?? 0) >= MAX_UPLOAD_BYTES) {
    throw new RuntimeException('アイコンは20MB未満の画像を選択してください。');
  }
  $info = validatePhotoUpload($file);
  $folder = __DIR__ . '/../uploads/avatars/';
  if (!is_dir($folder) && !mkdir($folder, 0755, true) && !is_dir($folder)) {
    throw new RuntimeException('アイコンを保存できませんでした。');
  }
  $filename = 'signup_' . bin2hex(random_bytes(16)) . '.jpg';
  $path = $folder . $filename;
  try {
    createResizedImage($file['tmp_name'], $path, $info);
    $src = imagecreatefromjpeg($path);
    $canvas = imagecreatetruecolor(256, 256);
    $side = min(imagesx($src), imagesy($src));
    $zoom = max(100, min(300, $zoom));
    $cropSide = max(1, $side / ($zoom / 100));
    imagecopyresampled($canvas, $src, 0, 0, (int)round((imagesx($src) - $cropSide) * max(0, min(100, $cropX)) / 100),
      (int)round((imagesy($src) - $cropSide) * max(0, min(100, $cropY)) / 100), 256, 256, (int)round($cropSide), (int)round($cropSide));
    $saved = imagejpeg($canvas, $path, 88);
    imagedestroy($src);
    imagedestroy($canvas);
    if (!$saved) throw new RuntimeException('アイコンを保存できませんでした。');
    return 'uploads/avatars/' . $filename;
  } catch (Throwable $error) {
    if (is_file($path)) unlink($path);
    throw $error;
  }
}
