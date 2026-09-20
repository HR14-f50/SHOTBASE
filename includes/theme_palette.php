<?php
declare(strict_types=1);

/**
 * テーマカラーの変更はこの一覧に集約します。
 * `white` が初期テーマです。既存のキーは保存済みユーザーとの互換性のため残します。
 */
function profileThemes(): array
{
  return [
    'white' => ['name' => 'ホワイト', 'accent' => '#6f7c87', 'soft' => '#f7f9fa'],
    'blue' => ['name' => 'ブルー', 'accent' => '#6f91b0', 'soft' => '#eef5fa'],
    'navy' => ['name' => 'ネイビー', 'accent' => '#7180a0', 'soft' => '#f0f3fa'],
    'sky' => ['name' => 'スカイ', 'accent' => '#6da9bd', 'soft' => '#edf8fb'],
    'teal' => ['name' => 'ティール', 'accent' => '#6aa7a1', 'soft' => '#eef9f7'],
    'sage' => ['name' => 'セージ', 'accent' => '#86a58a', 'soft' => '#f2f8f2'],
    'lavender' => ['name' => 'ラベンダー', 'accent' => '#9b8fc0', 'soft' => '#f5f2fb'],
    'rose' => ['name' => 'ローズ', 'accent' => '#bd879b', 'soft' => '#fcf3f6'],
    'amber' => ['name' => 'アンバー', 'accent' => '#bd9b5f', 'soft' => '#fcf8ed'],
    'slate' => ['name' => 'スレート', 'accent' => '#536b7c', 'soft' => '#e8eef2'],
    'mono' => ['name' => 'モノトーン', 'accent' => '#252a30', 'soft' => '#eceff1'],
  ];
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
