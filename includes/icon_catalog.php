<?php
declare(strict_types=1);

function iconMotifs(): array
{
  return [
    'none' => '無地（白線画なし）',
    'ball' => '野球ボール',
    'bat' => 'バット',
    'glove' => 'グラブ',
    'cap' => '帽子',
    'helmet' => 'ヘルメット',
    'mask' => 'キャッチャーマスク',
    'spike' => 'スパイク',
    'megaphone' => '応援メガホン',
    'sticks' => 'ツインスティック',
    'flag' => 'フラッグ',
    'umbrella' => '傘',
    'star' => '星',
    'heart' => 'ハート',
    'rabbit' => '兎',
    'tiger' => '虎',
    'hamster' => 'ハムスター',
    'swallow' => '燕',
    'koala' => 'コアラ',
    'dragon' => '龍',
    'carp' => '鯉',
    'hawk' => '鷹',
    'lion' => 'ライオン',
    'fox' => '狐',
    'squirrel' => 'リス',
    'buffalo' => '水牛',
    'gull' => '鴎',
    'eagle' => '犬鷲',
  ];
}

function iconColors(): array
{
  return [
    'red' => ['name' => '赤', 'hex' => '#df6f7f'],
    'orange' => ['name' => 'オレンジ', 'hex' => '#eea065'],
    'yellow' => ['name' => '黄色', 'hex' => '#e6cf6e'],
    'green' => ['name' => '緑', 'hex' => '#79b998'],
    'sky' => ['name' => '水色', 'hex' => '#80c8df'],
    'blue' => ['name' => '青', 'hex' => '#7e9ed9'],
    'navy' => ['name' => '紺色', 'hex' => '#6e7da5'],
    'black' => ['name' => '黒', 'hex' => '#4e535b'],
    'maroon' => ['name' => 'えんじ色', 'hex' => '#b77d8f'],
    'pink' => ['name' => 'ピンク', 'hex' => '#dda5bb'],
    'purple' => ['name' => '紫', 'hex' => '#a68dc6'],
    'gray' => ['name' => '濃いグレー', 'hex' => '#7d858f'],
  ];
}

function customIconPath(string $motif, string $color): string
{
  if (!isset(iconMotifs()[$motif], iconColors()[$color])) {
    throw new InvalidArgumentException('アイコンの素材と背景色を選択してください。');
  }
  return 'avatar.php?' . http_build_query(['motif' => $motif, 'color' => $color]);
}
