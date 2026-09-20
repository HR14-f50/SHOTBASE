-- 初期テーマを白〜薄いグレーのテーマへ変更します。
-- 以前の初期値 blue のユーザーも、明示的なテーマ選択前の状態として white に移行します。
ALTER TABLE users
  MODIFY theme_key VARCHAR(30) NOT NULL DEFAULT 'white';

UPDATE users
SET theme_key = 'white'
WHERE theme_key = 'blue';
