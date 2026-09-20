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

INSERT INTO tags (user_id, name, tag_type, team_id)
SELECT NULL, v.name, 'stadium', tm.id
FROM (
  SELECT 'エスコンF' AS name, 'fighters' AS team_slug
  UNION ALL SELECT '楽天モバイル', 'eagles'
  UNION ALL SELECT 'ZOZOマリン', 'marines'
  UNION ALL SELECT 'ベルーナドーム', 'lions'
  UNION ALL SELECT '京セラD大阪', 'buffaloes'
  UNION ALL SELECT 'みずほPayPay', 'hawks'
  UNION ALL SELECT '東京ドーム', 'giants'
  UNION ALL SELECT 'バンテリンドーム', 'dragons'
  UNION ALL SELECT '甲子園', 'tigers'
  UNION ALL SELECT 'マツダスタジアム', 'carp'
  UNION ALL SELECT '神宮球場', 'swallows'
  UNION ALL SELECT '横浜スタジアム', 'baystars'
  UNION ALL SELECT '森林どり泉', 'eagles'
  UNION ALL SELECT '鎌スタ', 'fighters'
  UNION ALL SELECT 'ナゴヤ球場', 'dragons'
  UNION ALL SELECT 'Gタウン', 'giants'
  UNION ALL SELECT '杉本商事BS', 'buffaloes'
  UNION ALL SELECT 'ハードオフ新潟', 'albirex'
  UNION ALL SELECT 'ちゅ〜る', 'hayate'
  UNION ALL SELECT 'SGL', 'tigers'
  UNION ALL SELECT 'カーミニーク', 'lions'
  UNION ALL SELECT '戸田', 'swallows'
  UNION ALL SELECT 'タマスタ筑後', 'hawks'
  UNION ALL SELECT '横須賀', 'baystars'
  UNION ALL SELECT 'G球場', 'giants'
  UNION ALL SELECT 'ロッテ浦和', 'marines'
  UNION ALL SELECT 'ほっともっと', NULL
  UNION ALL SELECT '平塚', NULL
) v
LEFT JOIN teams tm ON tm.slug = v.team_slug
WHERE NOT EXISTS (
  SELECT 1 FROM tags existing
  WHERE existing.user_id IS NULL AND existing.name = v.name
);

COMMIT;
