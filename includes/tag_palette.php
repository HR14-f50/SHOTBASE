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
  $colors = $palette[$tag['tag_type'] ?? 'other'] ?? $palette['other'];
  $result = '';
  foreach (['bg', 'fg', 'border', 'selected', 'selected_text'] as $name) {
    $value = $colors[$name] ?? $palette['other'][$name];
    if (!preg_match('/^#[0-9a-f]{6}$/i', $value)) $value = $palette['other'][$name];
    if ($name === 'bg') $value = 'var(--profile-soft, ' . $value . ')';
    if ($name === 'fg' || $name === 'selected') $value = 'var(--profile-accent, ' . $value . ')';
    $result .= '--tag-' . str_replace('_', '-', $name) . ':' . $value . ';';
  }
  return $result;
}
