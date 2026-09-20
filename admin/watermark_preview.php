<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/profile_helpers.php';
require_once __DIR__ . '/../includes/image.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !currentUserId() || currentUserType() !== 'photographer' || !profileCsrfValid()) {
  http_response_code(403);
  exit;
}
$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([currentUserId()]);
$user = $stmt->fetch();
$photoPreview = (int)($_POST['photo_id'] ?? 0);
if ($photoPreview) {
  $stmt = db()->prepare('SELECT ph.* FROM photos ph JOIN projects p ON p.id = ph.project_id WHERE ph.id = ? AND ph.user_id = ? AND p.user_id = ?');
  $stmt->execute([$photoPreview, currentUserId(), currentUserId()]);
  $photo = $stmt->fetch();
  if (!$photo) { http_response_code(404); exit; }
}
$defaultSize = $user['watermark_size'];
$defaultOpacity = (int)$user['watermark_opacity'];
foreach ($photoPreview ? ['watermark_position'] : ['watermark_source', 'watermark_text', 'watermark_color', 'watermark_size', 'watermark_position', 'nickname'] as $field) {
  if (is_string($_POST[$field] ?? null)) $user[$field] = mb_substr(trim($_POST[$field]), 0, 20);
}
if ($photoPreview) {
  $size = $_POST['watermark_size'] ?? $photo['watermark_size'] ?? '';
  $opacity = $_POST['watermark_opacity'] ?? $photo['watermark_opacity'] ?? '';
  $user['watermark_size'] = $size === '' ? $defaultSize : $size;
  $user['watermark_opacity'] = $opacity === '' ? $defaultOpacity : (int)$opacity;
} else {
  $user['watermark_opacity'] = (int)($_POST['watermark_opacity'] ?? 50);
}
if (!isset(watermarkPositions()[$user['watermark_position']])
  || !in_array($user['watermark_source'], ['username', 'nickname', 'custom'], true)
  || !in_array($user['watermark_size'], ['small', 'medium', 'large'], true)
  || !in_array($user['watermark_color'], ['black', 'white'], true)
  || !in_array($user['watermark_opacity'], [100, 50, 20, 10], true)) { http_response_code(422); exit; }
if ($photoPreview) {
  $source = realpath(__DIR__ . '/../' . $photo['original_path']);
  $root = realpath(PHOTO_ORIGINAL_DIR);
  if (!$source || !$root || !str_starts_with($source, $root . '/')) { http_response_code(422); exit; }
  $info = imageInfo($source);
  if ($info['width'] * $info['height'] > 12000000) { http_response_code(422); exit; }
  $src = createSourceImage($source, $info['type']);
  $scale = min(1, 900 / max(imagesx($src), imagesy($src)));
  $im = imagecreatetruecolor(max(1, (int)(imagesx($src) * $scale)), max(1, (int)(imagesy($src) * $scale)));
  imagecopyresampled($im, $src, 0, 0, 0, 0, imagesx($im), imagesy($im), imagesx($src), imagesy($src));
  imagedestroy($src);
} else {
$im = imagecreatetruecolor(720, 480);
for ($y = 0; $y < 480; $y++) {
  $color = $y < 210 ? imagecolorallocate($im, 135 + (int)($y / 8), 168 + (int)($y / 10), 195 + (int)($y / 10)) : imagecolorallocate($im, 80, 112 + (int)(($y - 210) / 10), 91);
  imageline($im, 0, $y, 719, $y, $color);
}
imagefilledrectangle($im, 0, 195, 719, 220, imagecolorallocate($im, 67, 82, 98));
imagefilledellipse($im, 360, 430, 480, 300, imagecolorallocate($im, 170, 144, 112));
imagefilledpolygon($im, [360, 275, 515, 370, 360, 458, 205, 370], imagecolorallocate($im, 113, 142, 107));
imagesetthickness($im, 2);
imageline($im, 360, 458, 85, 285, imagecolorallocate($im, 225, 222, 209));
imageline($im, 360, 458, 635, 285, imagecolorallocate($im, 225, 222, 209));
imagefilledellipse($im, 360, 365, 20, 10, imagecolorallocate($im, 187, 164, 132));
}
if ($photoPreview ? (int)$user['watermark_enabled'] === 1 : ($_POST['watermark_enabled'] ?? '') === '1') {
  drawWatermark($im, watermarkLine($user), $user['watermark_position'], $user);
}
header('Content-Type: image/jpeg');
header('Cache-Control: no-store');
imagejpeg($im, null, 90);
imagedestroy($im);
