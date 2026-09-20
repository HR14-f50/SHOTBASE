# SHOTBASE：初期配色・タグ・プロフィール設定

2026年9月20日

## 初期テーマ

`includes/theme_palette.php` の `profileThemes()` がテーマ一覧です。現在はホワイト、12球団をイメージした配色、パープル、ピンクの15種類です。画面上の表示名は球団名ではなく、配色を表す一般的な色名にしています。`white` が新規ユーザーの初期テーマで、既存ユーザーも `migrations/20260920_reset_theme_colors.sql` でホワイトへ移行します。テーマを追加する場合は、配列にキー・表示名・アクセント色・淡い背景色・サブカラーを追加します。

```php
'mint' => [
  'name' => 'ミント',
  'accent' => '#70a99f',
  'soft' => '#eef9f6',
  'secondary' => '#4d8178',
  'swatch' => '#70a99f', // 丸い色見本のメイン色。省略時は accent を使用
],
```

カラーコードは6桁の16進数で指定します。`accent` はリンク・ボタン・タグの通常色、`soft` は背景色、`secondary` は枠線などのサブカラーです。管理画面の丸い色見本は `swatch` と `secondary` の2色で表示します。`swatch` を省略した場合は `accent` が使われます。

## 初期タグの追加・配色

タグの種類ごとの基準色は `includes/tag_palette.php` の `tagPalette()` にまとめています。現在の初期タグはDBの `tags` テーブルに保存され、タグの表示色はこの配列を通ります。

```php
$base = [
  'bg' => '#f2f4f6',       // 通常の背景
  'fg' => '#687582',       // 通常の文字
  'border' => '#d6dce2',   // 枠線
  'selected' => '#687582', // 選択時の背景
  'selected_text' => '#ffffff',
];

return [
  'team' => $base,
  'player' => $base,
  'stadium' => $base,
  'event' => $base,
  'custom' => $base,
  'other' => $base,
];
```

チームタグだけ色を変える場合は、たとえば次のように個別配列へ置き換えます。

```php
'team' => [
  'bg' => '#f1f7f3',
  'fg' => '#5f9277',
  'border' => '#c9e2d1',
  'selected' => '#6ca989',
  'selected_text' => '#ffffff',
],
```

テーマが設定されているページでは、`profileTagStyle()` が `--profile-accent` と `--profile-soft` を優先するため、ユーザーのテーマ色がタグにも反映されます。種類別の固定色を優先したい場合は、同関数内の `bg`・`fg`・`selected` の `var(--profile-...)` 置換部分を削除します。

## アイコン背景色

`includes/icon_catalog.php` の `iconColors()` が12色のプリセットです。各 `hex` を変更すると、白線画アイコンの背景とプロフィール編集画面の色見本が同時に変わります。現在は少し明るく透過感のある色に調整しています。

## プロフィール編集のアイコン画像

- 保存処理と自動縮小・圧縮：`includes/registration.php` の `storeSignupIcon()`
- 上限値：`includes/config.php` の `MAX_UPLOAD_BYTES`（20MB）と `MAX_STORED_BYTES`（2MB）
- 編集画面：`admin/profile_edit.php`
- 選択時プレビュー・位置調整：`assets/js/profile-settings.js`

プロフィール編集のユーザー名は入力欄ではなく、変更不可の表示ブロックとして出力しています。変更する場合は `admin/profile_edit.php` の `.fixedField` 周辺を編集します。

登録画面のアイコン画像も同じ20MB制限と自動圧縮を使います。画面上の説明と選択時プレビューは `register.php` と `assets/js/registration.js` にあります。

プロフィール専用タグは廃止しています。プロフィールへ移動する場合は、サイドバーのアイコンをクリックします。
