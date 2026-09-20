-- 運営タグ：試合の開催区分
INSERT INTO tags (user_id, name, tag_type)
SELECT NULL, src.name, 'custom'
FROM (
  SELECT 'ホーム' AS name
  UNION ALL SELECT 'ビジター'
) src
WHERE NOT EXISTS (
  SELECT 1 FROM tags existing
  WHERE existing.user_id IS NULL AND existing.name = src.name
);
