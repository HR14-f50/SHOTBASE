-- 運営タグ（チーム・区分・ファイターズ選手）
-- user_id=NULL のタグは全ユーザーが利用でき、ユーザー側では削除できません。

START TRANSACTION;

ALTER TABLE tags
  MODIFY tag_type ENUM('custom', 'division', 'team', 'player', 'event') NOT NULL DEFAULT 'custom';

INSERT INTO baseball_divisions (parent_id, name, slug, sort_order)
SELECT p.id, 'ファーム', 'farm', 2
FROM baseball_divisions p
WHERE p.name = 'プロ'
  AND NOT EXISTS (SELECT 1 FROM baseball_divisions WHERE name = 'ファーム');

INSERT INTO teams (division_id, name, slug, border_color, background_color, is_active)
SELECT d.id, src.name, src.slug, src.border_color, src.background_color, 1
FROM baseball_divisions d
JOIN (
  SELECT 'ファイターズ' AS name, 'fighters' AS slug, '#1b4f9c' AS border_color, '#e8eef8' AS background_color, 'プロ' AS division_name
  UNION ALL SELECT 'イーグルス', 'eagles', '#870e2c', '#f8e9ee', 'プロ'
  UNION ALL SELECT 'マリーンズ', 'marines', '#111111', '#eeeeee', 'プロ'
  UNION ALL SELECT 'ライオンズ', 'lions', '#0b3d91', '#e8eef8', 'プロ'
  UNION ALL SELECT 'バファローズ', 'buffaloes', '#0d2b45', '#e9eef2', 'プロ'
  UNION ALL SELECT 'ホークス', 'hawks', '#f5b400', '#fff6d6', 'プロ'
  UNION ALL SELECT 'スワローズ', 'swallows', '#1e5799', '#e7f0fa', 'プロ'
  UNION ALL SELECT 'ジャイアンツ', 'giants', '#f05a28', '#fff0e8', 'プロ'
  UNION ALL SELECT 'ベイスターズ', 'baystars', '#005bac', '#e7f2fb', 'プロ'
  UNION ALL SELECT 'ドラゴンズ', 'dragons', '#0b2a6f', '#e8edf8', 'プロ'
  UNION ALL SELECT 'タイガース', 'tigers', '#d69e00', '#fff7d7', 'プロ'
  UNION ALL SELECT 'カープ', 'carp', '#bc002d', '#f8e8ec', 'プロ'
  UNION ALL SELECT 'アルビレックス', 'albirex', '#e85d04', '#fff0e4', 'ファーム'
  UNION ALL SELECT 'ハヤテ', 'hayate', '#d71920', '#fdebed', 'ファーム'
) src ON src.division_name = d.name
WHERE NOT EXISTS (SELECT 1 FROM teams t WHERE t.slug = src.slug);

INSERT INTO tags (user_id, division_id, team_id, name, tag_type, border_color, background_color)
SELECT NULL, t.division_id, t.id, t.name, 'team', t.border_color, t.background_color
FROM teams t
WHERE t.slug IN ('fighters', 'eagles', 'marines', 'lions', 'buffaloes', 'hawks', 'swallows', 'giants', 'baystars', 'dragons', 'tigers', 'carp', 'albirex', 'hayate')
  AND NOT EXISTS (SELECT 1 FROM tags existing WHERE existing.user_id IS NULL AND existing.name = t.name);

INSERT INTO tags (user_id, name, tag_type)
SELECT NULL, src.name, src.tag_type
FROM (
  SELECT '一軍' AS name, 'division' AS tag_type
  UNION ALL SELECT 'ファーム', 'division'
  UNION ALL SELECT 'セリーグ', 'division'
  UNION ALL SELECT 'パリーグ', 'division'
  UNION ALL SELECT '交流戦', 'event'
  UNION ALL SELECT 'オープン戦', 'event'
  UNION ALL SELECT 'キャンプ', 'event'
  UNION ALL SELECT '自主トレ', 'event'
  UNION ALL SELECT 'みやざきフェニックスリーグ', 'event'
  UNION ALL SELECT 'ホーム', 'custom'
  UNION ALL SELECT 'ビジター', 'custom'
) src
WHERE NOT EXISTS (SELECT 1 FROM tags existing WHERE existing.user_id IS NULL AND existing.name = src.name);

INSERT INTO tags (user_id, name, tag_type)
SELECT NULL, src.name, 'player'
FROM (
  SELECT '矢澤 宏太' AS name UNION ALL SELECT '生田目 翼' UNION ALL SELECT '加藤 貴之' UNION ALL SELECT '北山 亘基'
  UNION ALL SELECT '達 孝太' UNION ALL SELECT '伊藤 大海' UNION ALL SELECT '山﨑 福也' UNION ALL SELECT '玉井 大翔'
  UNION ALL SELECT '上原 健太' UNION ALL SELECT '金村 尚真' UNION ALL SELECT '宮西 尚生' UNION ALL SELECT '田中 正義'
  UNION ALL SELECT '大川 慈英' UNION ALL SELECT '河野 竜生' UNION ALL SELECT '細野 晴希' UNION ALL SELECT 'サウリン・ラオ'
  UNION ALL SELECT '柴田 獅子' UNION ALL SELECT '藤田 琉生' UNION ALL SELECT '堀 瑞輝' UNION ALL SELECT '浅利 太門'
  UNION ALL SELECT '古林 睿煬' UNION ALL SELECT '島本 浩也' UNION ALL SELECT '福谷 浩司' UNION ALL SELECT '福島 蓮'
  UNION ALL SELECT '畔柳 亨丞' UNION ALL SELECT '柳川 大晟' UNION ALL SELECT '齋藤 友貴哉' UNION ALL SELECT '池田 隆英'
  UNION ALL SELECT '菊地 大稀' UNION ALL SELECT '清水 大暉' UNION ALL SELECT '山城 航太郎' UNION ALL SELECT '山本 拓実'
  UNION ALL SELECT '有原 航平' UNION ALL SELECT '松岡 洸希' UNION ALL SELECT '清宮 虎多朗' UNION ALL SELECT '孫 易磊'
  UNION ALL SELECT '加藤 大和' UNION ALL SELECT '松本 遼大' UNION ALL SELECT '川勝 空人' UNION ALL SELECT '澁谷 純希'
  UNION ALL SELECT '横山 永遠' UNION ALL SELECT '福田 俊' UNION ALL SELECT '安西 叶翔' UNION ALL SELECT '根本 悠楓'
  UNION ALL SELECT 'アリエル・マルティネス' UNION ALL SELECT '郡司 裕也' UNION ALL SELECT '清水 優心' UNION ALL SELECT '進藤 勇也'
  UNION ALL SELECT 'ライル・リン' UNION ALL SELECT '藤森 海斗' UNION ALL SELECT '吉田 賢吾' UNION ALL SELECT '田宮 裕涼'
  UNION ALL SELECT '梅林 優貴' UNION ALL SELECT '日渡 騰輝' UNION ALL SELECT '上川畑 大悟' UNION ALL SELECT '野村 佑希'
  UNION ALL SELECT 'ロドルフォ・カストロ' UNION ALL SELECT '中島 卓也' UNION ALL SELECT '清宮 幸太郎' UNION ALL SELECT '有薗 直輝'
  UNION ALL SELECT '水野 達稀' UNION ALL SELECT '阪口 樂' UNION ALL SELECT '大塚 瑠晏' UNION ALL SELECT '半田 南十'
  UNION ALL SELECT '山縣 秀' UNION ALL SELECT '細川 凌平' UNION ALL SELECT '奈良間 大己' UNION ALL SELECT '明瀬 諒介'
  UNION ALL SELECT '常谷 拓輝' UNION ALL SELECT '濵田 泰希' UNION ALL SELECT '西川 遥輝' UNION ALL SELECT '淺間 大基'
  UNION ALL SELECT '宮崎 一樹' UNION ALL SELECT 'エドポロ ケイン' UNION ALL SELECT '五十幡 亮汰' UNION ALL SELECT '水谷 瞬'
  UNION ALL SELECT '今川 優馬' UNION ALL SELECT '万波 中正' UNION ALL SELECT 'フランミル・レイエス' UNION ALL SELECT '藤田 大清'
  UNION ALL SELECT '山口 アタル' UNION ALL SELECT '星野 ひので'
) src
WHERE NOT EXISTS (SELECT 1 FROM tags existing WHERE existing.user_id IS NULL AND existing.name = src.name);

COMMIT;
