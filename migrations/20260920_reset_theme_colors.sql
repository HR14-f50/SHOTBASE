START TRANSACTION;

-- テーマ刷新時の初期状態。既存ユーザーは全員ホワイトへ戻します。
UPDATE users
SET theme_key = 'white'
WHERE theme_key IS NULL OR theme_key <> 'white';

COMMIT;
