<?php
declare(strict_types=1);
require_once __DIR__ . '/watermark_style.php';

function watermarkPositions(): array
{
  return ['top-left' => '左上', 'top-center' => '中央上', 'top-right' => '右上',
    'middle-left' => '左中央', 'center' => '中央', 'middle-right' => '右中央',
    'bottom-left' => '左下', 'bottom-center' => '中央下', 'bottom-right' => '右下'];
}

function watermarkLine(array $user): string
{
  return match ($user['watermark_source'] ?? 'username') {
    'nickname' => $user['nickname'],
    'custom' => $user['watermark_text'],
    default => '@' . $user['username'],
  };
}

function watermarkFont(): string
{
  $configured = getenv('SHOTBASE_WATERMARK_FONT');
  if ($configured && is_readable($configured)) return $configured;
  $config = watermarkStyleConfig();
  $local = $config['fonts'][$config['font_weight']] ?? $config['fonts'][400];
  if (is_readable($local)) return $local;
  throw new RuntimeException('日本語ウォーターマーク用フォントが見つかりません。');
}

function drawWatermark(GdImage $canvas, string $text, string $position, array $style): void
{
  $font = watermarkFont();
  $width = imagesx($canvas);
  $height = imagesy($canvas);
  $config = watermarkStyleConfig();
  $factor = $config['size_factors'][$style['watermark_size'] ?? 'medium'] ?? $config['size_factors']['medium'];
  $size = max(5.0, min($width, $height) * $factor);
  $padding = max(2, (int)round(min($width, $height) * $config['padding_factor']));
  $lines = [$text, SERVICE_NAME];
  do {
    $boxes = array_map(fn($line) => imagettfbbox($size, 0, $font, $line), $lines);
    $textWidth = max(array_map(fn($b) => $b[2] - $b[0], $boxes));
    $lineHeight = max(1, (int)ceil($size * $config['line_height']));
    $textHeight = $lineHeight * 2;
    if ($textWidth <= $width - 2 * $padding && $textHeight <= $height - 2 * $padding) break;
    $size *= 0.85;
  } while ($size > 1);
  $point = watermarkPosition($position, $width, $height, $textWidth, $textHeight, $padding);
  $rgb = ($style['watermark_color'] ?? 'white') === 'black' ? 0 : 255;
  $opacity = (int)($style['watermark_opacity'] ?? 50);
  $color = imagecolorallocatealpha($canvas, $rgb, $rgb, $rgb, (int)round(127 * (1 - $opacity / 100)));
  foreach ($lines as $i => $line) {
    $lineWidth = $boxes[$i][2] - $boxes[$i][0];
    $x = $point['x'] + (int)(($textWidth - $lineWidth) / 2) - $boxes[$i][0];
    $y = $point['y'] + $i * $lineHeight - $boxes[$i][7];
    imagettftext($canvas, $size, 0, $x, $y, $color, $font, $line);
  }
}

/** 自分の原本から別ファイルに再生成し、DBの切り替え後に旧表示画像を片付けます。 */
function stagePhotoWatermark(array $photo, array $user): array
{
  foreach (['watermark_size', 'watermark_opacity'] as $key) {
    if (($photo[$key] ?? null) !== null) $user[$key] = $photo[$key];
  }
  $root = dirname(__DIR__);
  $source = realpath($root . '/' . ($photo['original_path'] ?? ''));
  $originalRoot = realpath(PHOTO_ORIGINAL_DIR);
  if (!$source || !$originalRoot || !str_starts_with($source, $originalRoot . '/') || !is_file($source)) {
    throw new RuntimeException('元画像が見つからない写真があるため、保存できませんでした。');
  }
  $filename = bin2hex(random_bytes(16)) . '.jpg';
  $path = 'uploads/photos/' . $filename;
  try {
    $image = createResizedImage($source, $root . '/' . $path, imageInfo($source),
      (int)$user['watermark_enabled'] === 1 ? watermarkLine($user) : '',
      $photo['watermark_position'] ?: $user['watermark_position'], $user);
    return ['id' => $photo['id'], 'file_path' => $path, 'old_path' => $photo['file_path'], 'image' => $image];
  } catch (Throwable $e) {
    if (is_file($root . '/' . $path)) unlink($root . '/' . $path);
    throw $e;
  }
}

function discardWatermarkFiles(array $staged, bool $old = false): void
{
  foreach ($staged as $item) {
    $relative = $item[$old ? 'old_path' : 'file_path'];
    if (preg_match('~^uploads/photos/[A-Za-z0-9_.-]+\.(jpe?g|png|webp)$~i', $relative)) {
      $path = dirname(__DIR__) . '/' . $relative;
      if (is_file($path) && !unlink($path)) error_log('Could not remove replaced photo: ' . $relative);
    }
  }
}

function commitWatermarkRows(PDO $pdo, array $staged, int $userId): void
{
  $stmt = $pdo->prepare('UPDATE photos SET file_path = ?, width = ?, height = ?, file_size = ?, mime_type = "image/jpeg" WHERE id = ? AND user_id = ?');
  foreach ($staged as $item) {
    $im = $item['image'];
    $stmt->execute([$item['file_path'], $im['width'], $im['height'], $im['file_size'], $item['id'], $userId]);
    $pdo->prepare('UPDATE projects SET updated_at = NOW() WHERE id = (SELECT project_id FROM photos WHERE id = ?) AND user_id = ?')->execute([$item['id'], $userId]);
  }
}
