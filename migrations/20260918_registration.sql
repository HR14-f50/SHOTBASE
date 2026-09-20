-- 既存ユーザーは写真投稿ユーザーとして維持します。1回だけ適用してください。
ALTER TABLE users
  ADD COLUMN user_type ENUM('photographer', 'viewer') NOT NULL DEFAULT 'photographer',
  ADD COLUMN theme_key VARCHAR(30) NOT NULL DEFAULT 'blue',
  ADD COLUMN preset_icon VARCHAR(30) NOT NULL DEFAULT 'ball';
