# SHOTBASE

野球写真専用の個人向け写真アーカイブの初期PHP版。

## 開発環境
- PHP 8.x
- MariaDB / MySQL
- XAMPP
- HTML / CSS / JavaScript

## 1. DB
`shotbase_database.sql` を phpMyAdmin からインポートしたあと、`migrations/` 内のSQLをファイル名順に適用してください。各マイグレーションは一度だけ実行します。

## 2. DB接続
`includes/config.php` を開き、XAMPPのMariaDB設定に合わせて変更します。

## 3. 初期ユーザー
`register.php` から最初のユーザーを登録できます。
会員登録ページは本番公開時には制限・改善してください。

## 4. 画像仕様
- 入力は1枚20MB未満、1回最大50枚。保存時に1画像2MB以内へ自動圧縮
- 長辺2000px超はアップロード時に自動縮小
- プロジェクト最大200枚
- MIMEタイプを確認
- 保存時はランダムファイル名を使用

## 5. ウォーターマーク
`includes/watermark.php` で画像加工します。文字サイズと太さは `includes/watermark_style.php` で設定します。
1行目にユーザー名・ニックネーム・自由入力のいずれか、2行目にサイト名を描画します。

タグ配色と今回の画面調整は [調整ガイド](docs/gallery-tags-watermark.md) を参照してください。

注意：GD拡張が有効である必要があります。

## 6. 今回の骨組みに含まれるもの
公開:
- index.php
- profile.php
- project.php
- photo.php
- tag.php

ログイン:
- login.php
- logout.php
- register.php

管理:
- admin/index.php（旧URLからの転送用）
- admin/project.php
- admin/project_new.php
- admin/project_edit.php
- admin/upload.php
- admin/photo.php
- admin/photo_edit.php
- admin/profile_edit.php
- admin/withdraw.php

## 7. 試作版で見送ったもの
- メール認証（関係者限定利用のため見送り）
- 複数ユーザー向けの権限管理
- 本番公開向けの監視・レート制限・詳細なセキュリティ監査

## 2026-09-17: トップ・公開プロフィール
- `index.php`: サービス案内とログイン・新規登録への入口。
- `profile.php?username=ユーザー名`: 全幅プロフィール、タグ・月別アーカイブ、ピックアップ、プロジェクト一覧。
- `admin/profile_edit.php`: 自己紹介（200文字）、ピックアップ写真（12枚）の設定。
- 最新順・古い順はプロジェクト作成日時が基準。同日時はIDで順序を固定。
- アーカイブは撮影日が基準。期間指定は開始月、未指定は「日付未指定」。
- 公開プロフィールには公開プロジェクトと公開写真だけを表示。写真数・タグ集計も同様。
- ピックアップは縦横比を保持した隙間のない行配置。画面幅に合わせて再配置。
- ログインしたユーザーはプロジェクトにいいね・取り消しが可能。
- スタイルは `assets/css/profile.css`、ギャラリーは `assets/js/profile-gallery.js`。
- タグの基本CSSは従来どおり `assets/css/style.css` の `.tag` を共有。

### 別環境へ移す場合
`shotbase_database.sql` を復元後、`migrations/` 内のSQLをファイル名順に適用してください。ローカル環境で作成した `shotbase.sql` はユーザーデータを含むため、リポジトリには含めていません。
このMAMP環境には適用済みです。追加テーブルはピックアップ写真といいねです。プロフィール専用タグは廃止し、写真・プロジェクトのタグだけを使用します。
プロフィールはアイコンを表示します。ヘッダー画像は2026-09-18のデザイン変更で非表示になりました。
画像未設定・公開写真未登録の場合は、それぞれ代替背景・空の状態を表示します。

## 写真登録・ログイン時の転送
詳細は `docs/photo-upload.md` を参照してください。
ログイン済みのトップページアクセスは、投稿ユーザーなら自分のプロフィール、閲覧ユーザーならアカウント画面へ転送します。

## 4ステップ登録・15種類のテーマカラー
別環境では追加で `migrations/20260918_registration.sql` を一度適用してください。このMAMP環境には適用済みです。
メール認証は実装していません。現時点の新規登録は確認後すぐに利用可能になります。

## プロフィール中心の管理・アイコン・ウォーターマーク
詳細は `docs/profile-controls.md` を参照してください。
独立した管理画面は廃止し、旧URLは自分のプロフィールへ転送します。
`migrations/20260918_profile_controls.sql` はこのMAMP環境に適用済みです。
テーマカラーはホワイト、プロ野球12球団、パープル、ピンクの15種類です。既存ユーザーは `migrations/20260920_reset_theme_colors.sql` でホワイトへ移行します。
ヘッダー画像は廃止し、`migrations/20260920_remove_header_path.sql` で旧DB列も削除します。

## 管理UI更新（2026-09-19）
ログアウト確認、マイページのプロジェクト管理統合、写真の公開初期値、写真登録・編集のタグ追加、ゲストプレビュー、ウォーターマークの見本表示を追加しました。
詳細は `docs/admin-ui.md`、DB初期値の変更は `migrations/20260919_public_photos.sql` を参照してください（このMAMP環境に適用済み）。

最新のサイドバー・タグ・アイコン操作は [画面更新ガイド](docs/sidebar-refresh.md) を参照してください。

最新のプロジェクト管理・タグ制限・テーマ仕様は [管理・検索更新ガイド](docs/project-refresh.md) を参照してください。

## 運営タグとタグ検索（2026-09-20）
チーム・区分・大会・ホーム／ビジター・ファイターズ選手の初期タグは `migrations/20260920_operator_tags.sql`、選手のふりがなは `migrations/20260920_fighters_readings.sql`、所属チームと守備位置カテゴリは `migrations/20260920_fighters_categories.sql`、ファイターズの背番号は `migrations/20260920_fighters_uniform_numbers.sql`、マリーンズ選手は `migrations/20260920_marines_players.sql`、ライオンズ選手は `migrations/20260920_lions_players.sql`、球場タグは `migrations/20260920_stadium_tags.sql` にまとめています。適用方法とチームカラー・リーグカラーの変更箇所は [運営タグガイド](docs/operator-tags.md) を参照してください。
写真アップロード、写真編集、プロジェクト作成、一括タグ付与にはタグ検索欄があります。
プロフィール設定の「自分で作ったタグを管理」から、写真・プロジェクトで使うユーザー作成タグの作成・修正・削除ができます。プロフィール専用タグは使用しません。選手検索は全角数字を半角として扱い、該当する守備位置カテゴリだけを表示します。球場タグは本拠地・ファーム・地方に分類しています。
