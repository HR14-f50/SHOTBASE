# 運営タグ

運営側で用意するタグは `migrations/20260920_operator_tags.sql` にまとめています。MAMPの `shotbase` データベースへ一度適用すると、チーム・区分・大会・ホーム／ビジター・ファイターズ選手のタグが登録されます。既存DBへホーム／ビジターだけを追加する場合は `20260920_home_visitor_tags.sql` を使います。選手のふりがなは `20260920_fighters_readings.sql`、所属チームと守備位置カテゴリ（投手・捕手・内野手・外野手・監督コーチ）は `20260920_fighters_categories.sql`、ファイターズの背番号は `20260920_fighters_uniform_numbers.sql`、マリーンズの選手・背番号・ふりがなは `20260920_marines_players.sql`、球場タグは `20260920_stadium_tags.sql` で追加します。

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
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_marines_players.sql
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --protocol=SOCKET \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -u"$SHOTBASE_DB_USER" -p"$SHOTBASE_DB_PASS" shotbase \
  < /Applications/MAMP/htdocs/shotbase/migrations/20260920_stadium_tags.sql
```

運営タグは `user_id = NULL` で登録されるため、すべての投稿ユーザーが使えます。ユーザーが作成したタグとは異なり、編集画面の「×」では削除できません。

## チーム色の変更

チームタグの色は `teams.border_color` と `teams.background_color` に保存しています。色を変える場合は対象チームの2つのカラーコードを変更し、タグの表示を再読み込みしてください。`includes/tag_palette.php` の `profileTagStyle()` がこの2色をチームタグと選手タグの通常色・選択色へ反映します。セリーグは緑、パリーグは水色、その他の区分は黄系、大会タグは紫系で表示します。リーグ・大会の色を変える場合も同じ関数の色コードを変更してください。

## 選手名とカテゴリの更新

ファイターズとマリーンズの選手名は `tag_type = 'player'` の登録部分にあります。公式名鑑の更新に合わせて、追加・削除・表記変更を行ってください。カテゴリは `tags.category`、所属チームは `tags.team_id`、背番号は `tags.uniform_number` に保存しています。タグ付与画面ではチームを選ぶと、そのチームの選手だけが守備位置別に表示されます。選手一覧は背番号順で、3桁の番号を2桁以下の後ろに配置します。検索欄では名前・ふりがな・背番号を検索できます。背番号はタグ選択・検索欄だけに表示し、通常の公開タグには表示しません。検索語を入力した場合はチーム未選択でも全チームを横断して一致する選手を表示し、選手のアコーディオンを自動で開きます。
ふりがなは `migrations/20260920_fighters_readings.sql` の `reading` 列へ登録しています。タグ検索では選手名だけでなく、このふりがなでも検索できます。

## 球場タグ

本拠地・ファーム・地方球場は `migrations/20260920_stadium_tags.sql` に登録しています。分類は `tags.category` の `home`（本拠地）、`farm`（ファーム）、`regional`（地方）です。チームに所属する球場には `tags.team_id` を設定しているため、対応チームのカラーが表示されます。`ほっともっと` と `平塚` は中立球場として運営タグの共通色で表示します。球場を追加・名称変更する場合は、このSQLの一覧を編集してください。

## タグ検索

タグを付与する画面には共通の検索欄を表示しています。対象は写真アップロード、写真編集、プロジェクト作成、プロジェクト内の一括タグ付与です。検索文字を入力すると、その場でタグを絞り込みます。選手検索はチームを選択していなくても全チームを対象にします。

## ユーザー作成タグの管理

プロフィール設定の「自分で作ったタグを管理」から、写真・プロジェクトで使うユーザー自身のカスタムタグを作成・修正・削除できます。タグ名は1〜12文字で、運営タグと同じ名前は登録できません。削除すると、そのタグを付けた写真・プロジェクトからも同時に外れます。
