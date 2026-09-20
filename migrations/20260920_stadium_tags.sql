-- 球場タグとマリーンズの一般参加者タグ整理
START TRANSACTION;

ALTER TABLE tags
  MODIFY tag_type ENUM('custom', 'division', 'team', 'player', 'event', 'stadium') NOT NULL DEFAULT 'custom';

DELETE FROM tags
WHERE user_id IS NULL
  AND tag_type = 'player'
  AND name = 'HARUTO'
  AND team_id = (SELECT id FROM teams WHERE slug = 'marines' LIMIT 1)
  AND NOT EXISTS (SELECT 1 FROM photo_tags WHERE tag_id = tags.id)
  AND NOT EXISTS (SELECT 1 FROM user_profile_tags WHERE tag_id = tags.id)
  AND NOT EXISTS (SELECT 1 FROM project_default_tags WHERE tag_id = tags.id);

UPDATE tags
SET category = CASE name
  WHEN 'エスコンF' THEN 'home'
  WHEN '楽天モバイル' THEN 'home'
  WHEN 'ZOZOマリン' THEN 'home'
  WHEN 'ベルーナドーム' THEN 'home'
  WHEN '京セラD大阪' THEN 'home'
  WHEN 'みずほPayPay' THEN 'home'
  WHEN '東京ドーム' THEN 'home'
  WHEN 'バンテリンドーム' THEN 'home'
  WHEN '甲子園' THEN 'home'
  WHEN 'マツダスタジアム' THEN 'home'
  WHEN '神宮球場' THEN 'home'
  WHEN '横浜スタジアム' THEN 'home'
  WHEN '森林どり泉' THEN 'farm'
  WHEN '鎌スタ' THEN 'farm'
  WHEN 'ナゴヤ球場' THEN 'farm'
  WHEN 'Gタウン' THEN 'farm'
  WHEN '杉本商事BS' THEN 'farm'
  WHEN 'ハードオフ新潟' THEN 'farm'
  WHEN 'ちゅ〜る' THEN 'farm'
  WHEN 'SGL' THEN 'farm'
  WHEN 'カーミニーク' THEN 'farm'
  WHEN '戸田' THEN 'farm'
  WHEN 'タマスタ筑後' THEN 'farm'
  WHEN '横須賀' THEN 'farm'
  WHEN 'G球場' THEN 'farm'
  WHEN 'ロッテ浦和' THEN 'farm'
  ELSE 'regional'
END
WHERE user_id IS NULL AND tag_type = 'stadium';

INSERT INTO tags (user_id, name, tag_type, category, team_id)
SELECT NULL, v.name, 'stadium', v.category, tm.id
FROM (
  SELECT 'エスコンF' AS name, 'fighters' AS team_slug, 'home' AS category
  UNION ALL SELECT '楽天モバイル', 'eagles', 'home'
  UNION ALL SELECT 'ZOZOマリン', 'marines', 'home'
  UNION ALL SELECT 'ベルーナドーム', 'lions', 'home'
  UNION ALL SELECT '京セラD大阪', 'buffaloes', 'home'
  UNION ALL SELECT 'みずほPayPay', 'hawks', 'home'
  UNION ALL SELECT '東京ドーム', 'giants', 'home'
  UNION ALL SELECT 'バンテリンドーム', 'dragons', 'home'
  UNION ALL SELECT '甲子園', 'tigers', 'home'
  UNION ALL SELECT 'マツダスタジアム', 'carp', 'home'
  UNION ALL SELECT '神宮球場', 'swallows', 'home'
  UNION ALL SELECT '横浜スタジアム', 'baystars', 'home'
  UNION ALL SELECT '森林どり泉', 'eagles', 'farm'
  UNION ALL SELECT '鎌スタ', 'fighters', 'farm'
  UNION ALL SELECT 'ナゴヤ球場', 'dragons', 'farm'
  UNION ALL SELECT 'Gタウン', 'giants', 'farm'
  UNION ALL SELECT '杉本商事BS', 'buffaloes', 'farm'
  UNION ALL SELECT 'ハードオフ新潟', 'albirex', 'farm'
  UNION ALL SELECT 'ちゅ〜る', 'hayate', 'farm'
  UNION ALL SELECT 'SGL', 'tigers', 'farm'
  UNION ALL SELECT 'カーミニーク', 'lions', 'farm'
  UNION ALL SELECT '戸田', 'swallows', 'farm'
  UNION ALL SELECT 'タマスタ筑後', 'hawks', 'farm'
  UNION ALL SELECT '横須賀', 'baystars', 'farm'
  UNION ALL SELECT 'G球場', 'giants', 'farm'
  UNION ALL SELECT 'ロッテ浦和', 'marines', 'farm'
  UNION ALL SELECT 'ほっともっと', NULL, 'regional'
  UNION ALL SELECT '平塚', NULL, 'regional'
) v
LEFT JOIN teams tm ON tm.slug = v.team_slug
WHERE NOT EXISTS (
  SELECT 1 FROM tags existing
  WHERE existing.user_id IS NULL AND existing.name = v.name
);

COMMIT;
