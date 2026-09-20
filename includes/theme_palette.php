<?php
declare(strict_types=1);

/**
 * テーマカラーの変更はこの一覧に集約します。
 * 選択肢はホワイト、プロ野球12球団、パープル・ピンク・ダークモードの16種類です。
 */
function profileThemes(): array
{
  return [
    'white' => ['name' => 'ホワイト', 'accent' => '#6f7c87', 'soft' => '#f7f9fa'],
    'fighters' => ['name' => 'ファイターズ', 'accent' => '#1e3a8a', 'soft' => '#e8efff'],
    'eagles' => ['name' => 'イーグルス', 'accent' => '#8b1e3f', 'soft' => '#f9e9ef'],
    'marines' => ['name' => 'マリーンズ', 'accent' => '#1f4d8f', 'soft' => '#e8f0fc'],
    'lions' => ['name' => 'ライオンズ', 'accent' => '#0b5ba7', 'soft' => '#e6f1ff'],
    'buffaloes' => ['name' => 'バファローズ', 'accent' => '#8c6a16', 'soft' => '#fbf5df'],
    'hawks' => ['name' => 'ホークス', 'accent' => '#9a7800', 'soft' => '#fff8d9'],
    'swallows' => ['name' => 'スワローズ', 'accent' => '#2386a8', 'soft' => '#e5f6fb'],
    'giants' => ['name' => 'ジャイアンツ', 'accent' => '#d86616', 'soft' => '#fff0e5'],
    'baystars' => ['name' => 'ベイスターズ', 'accent' => '#005baa', 'soft' => '#e5f1ff'],
    'dragons' => ['name' => 'ドラゴンズ', 'accent' => '#003d7c', 'soft' => '#e6eef9'],
    'tigers' => ['name' => 'タイガース', 'accent' => '#8b7100', 'soft' => '#fff8d8'],
    'carp' => ['name' => 'カープ', 'accent' => '#c9152b', 'soft' => '#ffe9ed'],
    'purple' => ['name' => 'パープル', 'accent' => '#7852a9', 'soft' => '#f2ebfb'],
    'pink' => ['name' => 'ピンク', 'accent' => '#c45d83', 'soft' => '#fcebf2'],
    'dark' => ['name' => 'ダークモード', 'accent' => '#d7e2ee', 'soft' => '#1a222b'],
  ];
}

/** 旧テーマキーを、新しい16種類のいずれかへ読み替えます。 */
function profileThemeAliases(): array
{
  return [
    'blue' => 'white',
    'navy' => 'white',
    'sky' => 'white',
    'teal' => 'white',
    'sage' => 'white',
    'lavender' => 'white',
    'rose' => 'white',
    'amber' => 'white',
    'slate' => 'white',
    'mono' => 'white',
  ];
}

function normalizeProfileThemeKey(?string $key): string
{
  $key = (string)$key;
  if (isset(profileThemes()[$key])) return $key;
  return profileThemeAliases()[$key] ?? 'white';
}

function profileTheme(?string $key): array
{
  return profileThemes()[normalizeProfileThemeKey($key)];
}

function presetIcons(): array
{
  return ['ball' => '野球ボール', 'camera' => 'カメラ', 'star' => 'スター',
    'diamond' => 'ダイヤモンド', 'cap' => 'キャップ', 'flag' => 'フラッグ'];
}

function presetIconPath(string $key): string
{
  return 'assets/icons/' . (isset(presetIcons()[$key]) ? $key : 'ball') . '.svg';
}
