<?php
declare(strict_types=1);

/** 写真に焼き込む文字のサイズ・太さ。CSSではなく、この設定を変更します。 */
function watermarkStyleConfig(): array
{
  return [
    'size_factors' => ['small' => 0.022, 'medium' => 0.033, 'large' => 0.048],
    'font_weight' => 700, // 400=Regular、700=Bold
    'fonts' => [
      400 => __DIR__ . '/../assets/fonts/NotoSansCJKjp-Regular.otf',
      700 => __DIR__ . '/../assets/fonts/NotoSansCJKjp-Bold.otf',
    ],
    'line_height' => 1.6,
    'padding_factor' => 0.025,
  ];
}
