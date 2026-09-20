<?php
declare(strict_types=1);

/**
 * テーマカラーの変更はこの一覧に集約します。
 * 選択肢はホワイト、プロ野球12球団、パープル・ピンクの15種類です。
 */
function profileThemes(): array
{
  return [
    'white' => ['name' => 'ホワイト', 'accent' => '#6f7c87', 'soft' => '#f7f9fa', 'secondary' => '#d6dce2', 'swatch' => '#ffffff'],
    'fighters' => ['name' => 'ブルー・ブラック', 'accent' => '#275b91', 'soft' => '#e9f1fb', 'secondary' => '#111111'],
    'eagles' => ['name' => 'ボルドー・ゴールド', 'accent' => '#7c1718', 'soft' => '#f9e9e9', 'secondary' => '#e9a93e'],
    'marines' => ['name' => 'グレー・ブラック', 'accent' => '#4a4a4a', 'soft' => '#efefef', 'secondary' => '#000000'],
    'lions' => ['name' => 'ネイビー・レッド', 'accent' => '#092048', 'soft' => '#e8edf5', 'secondary' => '#9d1f18'],
    'buffaloes' => ['name' => 'ネイビー・ゴールド', 'accent' => '#00011f', 'soft' => '#e9eaf5', 'secondary' => '#b3ab2d'],
    'hawks' => ['name' => 'ブラック・イエロー', 'accent' => '#171717', 'soft' => '#fff8d9', 'secondary' => '#f3c945'],
    'swallows' => ['name' => 'ネイビー・レッド', 'accent' => '#001444', 'soft' => '#e9edf6', 'secondary' => '#e40028'],
    'giants' => ['name' => 'オレンジ・ブラック', 'accent' => '#f27900', 'soft' => '#fff0e5', 'secondary' => '#000000'],
    'baystars' => ['name' => 'ネイビー・ブルー', 'accent' => '#19418a', 'soft' => '#e8f1fb', 'secondary' => '#3a83c7'],
    'dragons' => ['name' => 'ネイビー・ライトブルー', 'accent' => '#17317d', 'soft' => '#e7eef9', 'secondary' => '#16b3eb'],
    'tigers' => ['name' => 'ブラック・イエロー', 'accent' => '#171717', 'soft' => '#fff8d8', 'secondary' => '#f7e14c'],
    'carp' => ['name' => 'レッド・ネイビー', 'accent' => '#da3630', 'soft' => '#ffe9ed', 'secondary' => '#0e2950'],
    'purple' => ['name' => 'パープル', 'accent' => '#7852a9', 'soft' => '#f2ebfb', 'secondary' => '#c5b2df'],
    'pink' => ['name' => 'ピンク', 'accent' => '#c45d83', 'soft' => '#fcebf2', 'secondary' => '#e8adc3'],
  ];
}

/** 旧テーマキーを、新しい15種類のいずれかへ読み替えます。 */
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
    'dark' => 'white',
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
