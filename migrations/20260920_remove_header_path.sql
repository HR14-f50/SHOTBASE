-- ヘッダー画像機能の廃止に伴い、旧ユーザー列を削除します。
ALTER TABLE users
  DROP COLUMN header_path;
