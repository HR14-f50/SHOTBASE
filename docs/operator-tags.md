# 運営タグ

運営側で用意するタグは `migrations/20260920_operator_tags.sql` にまとめています。MAMPの `shotbase` データベースへ一度適用すると、チーム・区分・大会・ファイターズ選手のタグが登録されます。選手のふりがなは `20260920_fighters_readings.sql`、所属チームと守備位置カテゴリ（投手・捕手・内野手・外野手・監督コーチ）は `20260920_fighters_categories.sql`、背番号は `20260920_fighters_uniform_numbers.sql` で追加します。

接続情報は環境に合わせて設定し、パスワードをファイルへ書き込まないでください。接続例は次のように環境変数を使います。

```bash
export SHOTBASE_DB_USER=your_db_user
export SHOTBASE_DB_PASS=your_db_password
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_operator_tags.sql
```

続けて、ふりがなと選手カテゴリを適用します。

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_fighters_readings.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_fighters_categories.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_fighters_uniform_numbers.sql
```

運営タグは `user_id = NULL` で登録されるため、すべての投稿ユーザーが使えます。ユーザーが作成したタグとは異なり、編集画面の「×」では削除できません。

## チーム色の変更

チームタグの色は `teams.border_color` と `teams.background_color` に保存しています。色を変える場合は対象チームの2つのカラーコードを変更し、タグの表示を再読み込みしてください。`includes/tag_palette.php` の `profileTagStyle()` がこの2色をチームタグと選手タグの通常色・選択色へ反映します。セリーグは緑、パリーグは水色、その他の区分は黄系、大会タグは紫系で表示します。リーグ・大会の色を変える場合も同じ関数の色コードを変更してください。

## 選手名とカテゴリの更新

ファイターズの選手名は `tag_type = 'player'` の登録部分にあります。公式名鑑の更新に合わせて、追加・削除・表記変更を行ってください。カテゴリは `tags.category`、所属チームは `tags.team_id`、背番号は `tags.uniform_number` に保存しています。タグ付与画面ではチームを選ぶと、そのチームの選手だけが守備位置別に表示されます。検索欄では名前・ふりがな・背番号を検索できます。
ふりがなは `migrations/20260920_fighters_readings.sql` の `reading` 列へ登録しています。タグ検索では選手名だけでなく、このふりがなでも検索できます。

## タグ検索

タグを付与する画面には共通の検索欄を表示しています。対象は写真アップロード、写真編集、プロジェクト作成、プロジェクト内の一括タグ付与、プロフィール編集です。検索文字を入力すると、その場でタグを絞り込みます。
