-- Fighters player positions used by the tag picker.
-- Run after 20260920_fighters_readings.sql.
ALTER TABLE tags
  ADD COLUMN category VARCHAR(30) DEFAULT NULL AFTER tag_type;

UPDATE tags
SET team_id = (SELECT id FROM teams WHERE slug = 'fighters' LIMIT 1), category = 'pitcher'
WHERE user_id IS NULL AND tag_type = 'player' AND name IN (
  '矢澤 宏太','生田目 翼','加藤 貴之','北山 亘基','達 孝太','伊藤 大海','山﨑 福也','玉井 大翔','上原 健太','金村 尚真','宮西 尚生','田中 正義','大川 慈英','河野 竜生','細野 晴希','サウリン・ラオ','柴田 獅子','藤田 琉生','堀 瑞輝','浅利 太門','古林 睿煬','島本 浩也','福谷 浩司','福島 蓮','畔柳 亨丞','柳川 大晟','齋藤 友貴哉','池田 隆英','菊地 大稀','清水 大暉','山城 航太郎','山本 拓実','有原 航平','松岡 洸希','清宮 虎多朗','孫 易磊','加藤 大和','松本 遼大','川勝 空人','澁谷 純希','横山 永遠','福田 俊','安西 叶翔','根本 悠楓'
);

UPDATE tags
SET team_id = (SELECT id FROM teams WHERE slug = 'fighters' LIMIT 1), category = 'catcher'
WHERE user_id IS NULL AND tag_type = 'player' AND name IN (
  'アリエル・マルティネス','郡司 裕也','清水 優心','進藤 勇也','ライル・リン','藤森 海斗','吉田 賢吾','田宮 裕涼','梅林 優貴','日渡 騰輝'
);

UPDATE tags
SET team_id = (SELECT id FROM teams WHERE slug = 'fighters' LIMIT 1), category = 'infielder'
WHERE user_id IS NULL AND tag_type = 'player' AND name IN (
  '上川畑 大悟','野村 佑希','ロドルフォ・カストロ','中島 卓也','清宮 幸太郎','有薗 直輝','水野 達稀','阪口 樂','大塚 瑠晏','半田 南十','山縣 秀','細川 凌平','奈良間 大己','明瀬 諒介','常谷 拓輝','濵田 泰希'
);

UPDATE tags
SET team_id = (SELECT id FROM teams WHERE slug = 'fighters' LIMIT 1), category = 'outfielder'
WHERE user_id IS NULL AND tag_type = 'player' AND name IN (
  '西川 遥輝','淺間 大基','宮崎 一樹','エドポロ ケイン','五十幡 亮汰','水谷 瞬','今川 優馬','万波 中正','フランミル・レイエス','藤田 大清','山口 アタル','星野 ひので'
);

INSERT INTO tags (user_id, name, tag_type, category, reading, team_id)
SELECT NULL, v.name, 'player', 'coach', v.reading, t.id
FROM teams t
JOIN (
  SELECT '新庄 剛志' AS name, 'しんじょう つよし' AS reading UNION ALL
  SELECT '的場 直樹', 'まとば なおき' UNION ALL
  SELECT '山田 勝彦', 'やまだ かつひこ' UNION ALL
  SELECT '武田 久', 'たけだ ひさし' UNION ALL
  SELECT '小田 智之', 'おだ ともゆき' UNION ALL
  SELECT '紺田 敏正', 'こんた としまさ' UNION ALL
  SELECT '林 孝哉', 'はやし たかや' UNION ALL
  SELECT '清水 雅治', 'しみず まさじ' UNION ALL
  SELECT '松本 哲也', 'まつもと てつや' UNION ALL
  SELECT '森本 稀哲', 'もりもと ひちょり' UNION ALL
  SELECT '横尾 俊建', 'よこお としたけ' UNION ALL
  SELECT '加藤 武治', 'かとう たけはる' UNION ALL
  SELECT '浦野 博司', 'うらの ひろし' UNION ALL
  SELECT '岩舘 学', 'いわだて まなぶ' UNION ALL
  SELECT '谷内 亮太', 'やち りょうた' UNION ALL
  SELECT '佐藤 友亮', 'さとう ともあき' UNION ALL
  SELECT '稲葉 篤紀', 'いなば あつのり' UNION ALL
  SELECT '金子 千尋', 'かねこ ちひろ' UNION ALL
  SELECT '江口 孝義', 'えぐち たかよし'
) v
WHERE t.slug = 'fighters'
  AND NOT EXISTS (
    SELECT 1 FROM tags existing
    WHERE existing.user_id IS NULL AND existing.name = v.name
  );

UPDATE tags
SET team_id = (SELECT id FROM teams WHERE slug = 'fighters' LIMIT 1), category = 'coach'
WHERE user_id IS NULL AND tag_type = 'player' AND name IN (
  '新庄 剛志','的場 直樹','山田 勝彦','武田 久','小田 智之','紺田 敏正','林 孝哉','清水 雅治','松本 哲也','森本 稀哲','横尾 俊建','加藤 武治','浦野 博司','岩舘 学','谷内 亮太','佐藤 友亮','稲葉 篤紀','金子 千尋','江口 孝義'
);
