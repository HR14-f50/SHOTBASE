<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/watermark.php';

function allowedImageTypes(): array
{
  return [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_WEBP => 'webp',
  ];
}

function imageInfo(string $tmpPath): array
{
  $info = getimagesize($tmpPath);

  if ($info === false) {
    throw new RuntimeException('画像として読み込めません。');
  }

  $types = allowedImageTypes();

  if (!isset($types[$info[2]])) {
    throw new RuntimeException('JPEG、PNG、WebPのみ対応しています。');
  }

  return [
    'width' => (int)$info[0],
    'height' => (int)$info[1],
    'type' => (int)$info[2],
    'extension' => $types[$info[2]],
    'mime_type' => $info['mime'] ?? 'image/jpeg',
  ];
}

function createSourceImage(
  string $sourcePath,
  int $type
): GdImage {

  switch ($type) {
    case IMAGETYPE_JPEG:
      $src = imagecreatefromjpeg($sourcePath);
      break;

    case IMAGETYPE_PNG:
      $src = imagecreatefrompng($sourcePath);
      break;

    case IMAGETYPE_WEBP:
      $src = imagecreatefromwebp($sourcePath);
      break;

    default:
      throw new RuntimeException('対応していない画像形式です。');
  }

  if ($src === false) {
    throw new RuntimeException('画像の読み込みに失敗しました。');
  }

  return $src;
}

function watermarkPosition(
  string $position,
  int $imageWidth,
  int $imageHeight,
  int $textWidth,
  int $textHeight,
  int $padding
): array {

  switch ($position) {

    case 'top-left':
      $x = $padding;
      $y = $padding;
      break;

    case 'top-center':
      $x = (int)(($imageWidth - $textWidth) / 2);
      $y = $padding;
      break;

    case 'top-right':
      $x = $imageWidth - $textWidth - $padding;
      $y = $padding;
      break;

    case 'middle-left':
      $x = $padding;
      $y = (int)(($imageHeight - $textHeight) / 2);
      break;

    case 'center':
      $x = (int)(($imageWidth - $textWidth) / 2);
      $y = (int)(($imageHeight - $textHeight) / 2);
      break;

    case 'middle-right':
      $x = $imageWidth - $textWidth - $padding;
      $y = (int)(($imageHeight - $textHeight) / 2);
      break;

    case 'bottom-left':
      $x = $padding;
      $y = $imageHeight - $textHeight - $padding;
      break;

    case 'bottom-center':
      $x = (int)(($imageWidth - $textWidth) / 2);
      $y = $imageHeight - $textHeight - $padding;
      break;

    case 'bottom-right':
    default:
      $x = $imageWidth - $textWidth - $padding;
      $y = $imageHeight - $textHeight - $padding;
      break;
  }

  return [
    'x' => max(0, $x),
    'y' => max(0, $y),
  ];
}

/** JPEGへ再エンコードし、向き・長辺・保存容量をそろえます。 */
function createResizedImage(
  string $sourcePath,
  string $destinationPath,
  array $info,
  string $watermarkText = '',
  string $watermarkPosition = 'bottom-right',
  array $watermarkStyle = []
): array {
  if (!gdAvailable()) {
    throw new RuntimeException('画像の圧縮機能が利用できません。管理者にお問い合わせください。');
  }
  $pixels = (float)$info['width'] * (float)$info['height'];
  $memory = ini_get('memory_limit');
  $memoryBytes = function_exists('ini_parse_quantity') ? ini_parse_quantity($memory) : 256 * 1024 * 1024;
  if ($pixels <= 0 || $pixels > 80000000 || ($memoryBytes > 0 && $pixels * 6 + memory_get_usage(true) + 32 * 1024 * 1024 > $memoryBytes)) {
    throw new RuntimeException('画像の解像度が高すぎます。長辺を小さくしてから再度選択してください。');
  }
  $src = createSourceImage($sourcePath, $info['type']);
  $canvas = null;
  try {
    if ($info['type'] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
      $exif = @exif_read_data($sourcePath);
      $orientation = (int)($exif['Orientation'] ?? 1);
      $angle = [3 => 180, 5 => -90, 6 => -90, 7 => 90, 8 => 90][$orientation] ?? 0;
      if ($angle !== 0) {
        $rotated = imagerotate($src, $angle, 0);
        if ($rotated === false) {
          throw new RuntimeException('画像の向きを補正できませんでした。');
        }
        imagedestroy($src);
        $src = $rotated;
      }
      if (in_array($orientation, [2, 5, 7], true)) imageflip($src, IMG_FLIP_HORIZONTAL);
      if ($orientation === 4) imageflip($src, IMG_FLIP_VERTICAL);
    }
    $scale = min(1, MAX_LONG_SIDE / max(imagesx($src), imagesy($src)));
    $width = max(1, (int)round(imagesx($src) * $scale));
    $height = max(1, (int)round(imagesy($src) * $scale));
    for ($attempt = 0; $attempt < 10; $attempt++) {
      $canvas = imagecreatetruecolor($width, $height);
      imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
      imagecopyresampled($canvas, $src, 0, 0, 0, 0, $width, $height, imagesx($src), imagesy($src));
      if ($watermarkText !== '') {
        drawWatermark($canvas, $watermarkText, $watermarkPosition, $watermarkStyle);
      }
      foreach ([88, 80, 72, 64, 56] as $quality) {
        if (!imagejpeg($canvas, $destinationPath, $quality)) {
          throw new RuntimeException('画像を保存できませんでした。');
        }
        clearstatcache(true, $destinationPath);
        $size = filesize($destinationPath);
        if ($size !== false && $size <= MAX_STORED_BYTES) {
          return ['width' => $width, 'height' => $height, 'file_size' => $size, 'mime_type' => 'image/jpeg'];
        }
      }
      imagedestroy($canvas);
      $canvas = null;
      $width = max(1, (int)floor($width * 0.8));
      $height = max(1, (int)floor($height * 0.8));
    }
    throw new RuntimeException('2MB以内に圧縮できませんでした。別の画像でお試しください。');
  } finally {
    imagedestroy($src);
    if ($canvas !== null) imagedestroy($canvas);
  }
}

function gdAvailable(): bool
{
  return function_exists('imagecreatetruecolor')
    && function_exists('imagecreatefromjpeg')
    && function_exists('imagejpeg');
}
