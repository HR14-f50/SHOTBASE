<?php
declare(strict_types=1);

/**
 * タグ種類ごとの基準色はここで設定します。
 * プロフィールなどテーマが適用される画面では、通常色と選択色のアクセントをテーマ色へ置き換えます。
 */
function tagPalette(): array
{
  $base = ['bg' => '#f2f4f6', 'fg' => '#687582', 'border' => '#d6dce2', 'selected' => '#687582', 'selected_text' => '#ffffff'];
  return [
    'team' => $base,
    'division' => $base,
    'player' => $base,
    'stadium' => $base,
    'event' => $base,
    'custom' => $base,
    'other' => $base,
  ];
}

function profileTagStyle(array $tag): string
{
  $palette = tagPalette();
  $type = $tag['tag_type'] ?? 'other';
  $colors = $palette[$type] ?? $palette['other'];
  $useTeamColors = $type === 'team'
    && preg_match('/^#[0-9a-f]{6}$/i', (string)($tag['background_color'] ?? ''))
    && preg_match('/^#[0-9a-f]{6}$/i', (string)($tag['border_color'] ?? ''));
  if ($useTeamColors) {
    $colors = [
      'bg' => strtolower((string)$tag['background_color']),
      'fg' => tagContrastText((string)$tag['background_color']),
      'border' => strtolower((string)$tag['border_color']),
      'selected' => strtolower((string)$tag['border_color']),
      'selected_text' => tagContrastText((string)$tag['border_color']),
    ];
  }
  $result = '';
  foreach (['bg', 'fg', 'border', 'selected', 'selected_text'] as $name) {
    $value = $colors[$name] ?? $palette['other'][$name];
    if (!preg_match('/^#[0-9a-f]{6}$/i', $value)) $value = $palette['other'][$name];
    if (!$useTeamColors && $name === 'bg') $value = 'var(--profile-soft, ' . $value . ')';
    if (!$useTeamColors && ($name === 'fg' || $name === 'selected')) $value = 'var(--profile-accent, ' . $value . ')';
    $result .= '--tag-' . str_replace('_', '-', $name) . ':' . $value . ';';
  }
  return $result;
}

function tagContrastText(string $hex): string
{
  $rgb = sscanf(ltrim($hex, '#'), '%2x%2x%2x');
  if (!$rgb || count($rgb) !== 3) return '#ffffff';
  $luminance = (0.299 * $rgb[0]) + (0.587 * $rgb[1]) + (0.114 * $rgb[2]);
  return $luminance > 165 ? '#17212b' : '#ffffff';
}
