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
  $teamBackground = (string)($tag['background_color'] ?? $tag['team_background_color'] ?? '');
  $teamBorder = (string)($tag['border_color'] ?? $tag['team_border_color'] ?? '');
  $useTeamColors = in_array($type, ['team', 'player', 'stadium'], true)
    && preg_match('/^#[0-9a-f]{6}$/i', $teamBackground)
    && preg_match('/^#[0-9a-f]{6}$/i', $teamBorder);
  if ($useTeamColors) {
    $colors = [
      'bg' => strtolower($teamBackground),
      'fg' => tagContrastText($teamBackground),
      'border' => strtolower($teamBorder),
      'selected' => strtolower($teamBorder),
      'selected_text' => tagContrastText($teamBorder),
    ];
  }
  $isOperatorLeagueTag = in_array($type, ['division', 'event'], true) && ($tag['user_id'] ?? null) === null;
  $isNeutralStadiumTag = $type === 'stadium' && ($tag['user_id'] ?? null) === null && empty($tag['team_id']);
  if ($isOperatorLeagueTag || $isNeutralStadiumTag) {
    $name = (string)($tag['name'] ?? '');
    $colors = match (true) {
      $name === 'セリーグ' => ['bg' => '#e5f5ea', 'fg' => '#266f43', 'border' => '#4da66b', 'selected' => '#2f8b55', 'selected_text' => '#ffffff'],
      $name === 'パリーグ' => ['bg' => '#e6f4fb', 'fg' => '#24627d', 'border' => '#55a8c9', 'selected' => '#3287ad', 'selected_text' => '#ffffff'],
      $type === 'division' => ['bg' => '#fff4d9', 'fg' => '#76520e', 'border' => '#d6a83f', 'selected' => '#b98216', 'selected_text' => '#ffffff'],
      $isNeutralStadiumTag => ['bg' => '#f0f2f4', 'fg' => '#59636d', 'border' => '#8a949e', 'selected' => '#68747f', 'selected_text' => '#ffffff'],
      default => ['bg' => '#f0eafb', 'fg' => '#5f438d', 'border' => '#9271c7', 'selected' => '#7654ad', 'selected_text' => '#ffffff'],
    };
  }
  $result = '';
  foreach (['bg', 'fg', 'border', 'selected', 'selected_text'] as $name) {
    $value = $colors[$name] ?? $palette['other'][$name];
    if (!preg_match('/^#[0-9a-f]{6}$/i', $value)) $value = $palette['other'][$name];
    if (!$useTeamColors && !$isOperatorLeagueTag && !$isNeutralStadiumTag && $name === 'bg') $value = 'var(--profile-soft, ' . $value . ')';
    if (!$useTeamColors && !$isOperatorLeagueTag && !$isNeutralStadiumTag && ($name === 'fg' || $name === 'selected')) $value = 'var(--profile-accent, ' . $value . ')';
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
