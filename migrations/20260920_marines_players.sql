-- 2026年シーズンの千葉ロッテマリーンズ選手タグ
-- 公式選手名鑑の守備位置、背番号、ふりがなを登録します。
START TRANSACTION;

INSERT INTO tags (user_id, name, tag_type, category, reading, uniform_number, team_id)
SELECT NULL, v.name, 'player', v.category, v.reading, v.uniform_number, t.id
FROM teams t
JOIN (
  SELECT 'ホセ・カスティーヨ' AS name, 'pitcher' AS category, 'ほせ・かすてぃーよ' AS reading, '11' AS uniform_number UNION ALL
  SELECT '毛利 海大', 'pitcher', 'もうり かいと', '13' UNION ALL
  SELECT '小島 和哉', 'pitcher', 'おじま かずや', '14' UNION ALL
  SELECT '横山 陸人', 'pitcher', 'よこやま りくと', '15' UNION ALL
  SELECT '種市 篤暉', 'pitcher', 'たねいち あつき', '16' UNION ALL
  SELECT '石垣 元気', 'pitcher', 'いしがき げんき', '18' UNION ALL
  SELECT '唐川 侑己', 'pitcher', 'からかわ ゆうき', '19' UNION ALL
  SELECT '石川 柊太', 'pitcher', 'いしかわ しゅうた', '21' UNION ALL
  SELECT '東妻 勇輔', 'pitcher', 'あづま ゆうすけ', '24' UNION ALL
  SELECT '菊地 吏玖', 'pitcher', 'きくち りく', '28' UNION ALL
  SELECT '西野 勇士', 'pitcher', 'にしの ゆうじ', '29' UNION ALL
  SELECT '廣畑 敦也', 'pitcher', 'ひろはた あつや', '30' UNION ALL
  SELECT '大谷 輝龍', 'pitcher', 'おおたに ひかる', '31' UNION ALL
  SELECT '八木 彬', 'pitcher', 'やぎ あきら', '33' UNION ALL
  SELECT '高野 脩汰', 'pitcher', 'たかの しゅうた', '34' UNION ALL
  SELECT '田中 晴也', 'pitcher', 'たなか はるや', '35' UNION ALL
  SELECT '坂本 光士郎', 'pitcher', 'さかもと こうしろう', '36' UNION ALL
  SELECT '小野 郁', 'pitcher', 'おの ふみや', '37' UNION ALL
  SELECT '奥村 頼人', 'pitcher', 'おくむら らいと', '40' UNION ALL
  SELECT '一條 力真', 'pitcher', 'いちじょう りきま', '41' UNION ALL
  SELECT 'アンドレ・ジャクソン', 'pitcher', 'あんどれ・じゃくそん', '42' UNION ALL
  SELECT '冨士 隼斗', 'pitcher', 'ふじ はやと', '46' UNION ALL
  SELECT '鈴木 昭汰', 'pitcher', 'すずき しょうた', '47' UNION ALL
  SELECT 'ジョーイ・ルケーシー', 'pitcher', 'じょーい・るけーしー', '48' UNION ALL
  SELECT '益田 直也', 'pitcher', 'ますだ なおや', '52' UNION ALL
  SELECT '木村 優人', 'pitcher', 'きむら ゆうと', '53' UNION ALL
  SELECT '澤田 圭佑', 'pitcher', 'さわだ けいすけ', '54' UNION ALL
  SELECT '中森 俊介', 'pitcher', 'なかもり しゅんすけ', '56' UNION ALL
  SELECT '河村 説人', 'pitcher', 'かわむら ときと', '58' UNION ALL
  SELECT '早坂 響', 'pitcher', 'はやさか おと', '59' UNION ALL
  SELECT '大聖', 'pitcher', 'やまと', '60' UNION ALL
  SELECT '坂井 遼', 'pitcher', 'さかい はる', '62' UNION ALL
  SELECT '廣池 康志郎', 'pitcher', 'ひろいけ こうしろう', '64' UNION ALL
  SELECT '宮﨑 颯', 'pitcher', 'みやざき はやと', '66' UNION ALL
  SELECT 'サム・ロング', 'pitcher', 'さむ・ろんぐ', '73' UNION ALL
  SELECT '吉川 悠斗', 'pitcher', 'よしかわ ゆうと', '91' UNION ALL
  SELECT '森 遼大朗', 'pitcher', 'もり りょうたろう', '92' UNION ALL
  SELECT '髙橋 快秀', 'pitcher', 'たかはし かいしゅう', '93' UNION ALL
  SELECT '本前 郁也', 'pitcher', 'もとまえ ふみや', '121' UNION ALL
  SELECT '秋山 正雲', 'pitcher', 'あきやま せいうん', '123' UNION ALL
  SELECT '中村 亮太', 'pitcher', 'なかむら りょうた', '124' UNION ALL
  SELECT '永島田 輝斗', 'pitcher', 'ながしまだ きらと', '125' UNION ALL
  SELECT '中山 優人', 'pitcher', 'なかやま ゆうと', '126' UNION ALL
  SELECT '茨木 佑太', 'pitcher', 'いばらぎ ゆうた', '131' UNION ALL
  SELECT '長島 幸佑', 'pitcher', 'ながしま こうすけ', '132' UNION ALL
  SELECT '武内 涼太', 'pitcher', 'たけうち りょうた', '133' UNION ALL
  SELECT 'チャリエル・ラドニー', 'pitcher', 'ちゃりえる・らどにー', '139' UNION ALL
  SELECT '松川 虎生', 'catcher', 'まつかわ こう', '2' UNION ALL
  SELECT '田村 龍弘', 'catcher', 'たむら たつひろ', '27' UNION ALL
  SELECT '佐藤 都志也', 'catcher', 'さとう としや', '32' UNION ALL
  SELECT '植田 将太', 'catcher', 'うえだ しょうた', '45' UNION ALL
  SELECT '寺地 隆成', 'catcher', 'てらち りゅうせい', '65' UNION ALL
  SELECT '岡村 了樹', 'catcher', 'おかむら りょうじゅ', '69' UNION ALL
  SELECT '富山 紘之進', 'catcher', 'とみやま こうのしん', '137' UNION ALL
  SELECT '池田 来翔', 'infielder', 'いけだ らいと', '00' UNION ALL
  SELECT '友杉 篤輝', 'infielder', 'ともすぎ あつき', '4' UNION ALL
  SELECT '安田 尚憲', 'infielder', 'やすだ ひさのり', '5' UNION ALL
  SELECT '藤岡 裕大', 'infielder', 'ふじおか ゆうだい', '7' UNION ALL
  SELECT '中村 奨吾', 'infielder', 'なかむら しょうご', '8' UNION ALL
  SELECT '上田 希由翔', 'infielder', 'うえだ きゅうと', '10' UNION ALL
  SELECT '山﨑 剛', 'infielder', 'やまさき つよし', '38' UNION ALL
  SELECT '石垣 勝海', 'infielder', 'いしがき まさみ', '43' UNION ALL
  SELECT '宮崎 竜成', 'infielder', 'みやざき りゅうせい', '44' UNION ALL
  SELECT '立松 由宇', 'infielder', 'たてまつ ゆう', '49' UNION ALL
  SELECT '櫻井 ユウヤ', 'infielder', 'さくらい ゆうや', '55' UNION ALL
  SELECT '小川 龍成', 'infielder', 'おがわ りゅうせい', '57' UNION ALL
  SELECT '茶谷 健太', 'infielder', 'ちゃたに けんた', '67' UNION ALL
  SELECT 'ネフタリ・ソト', 'infielder', 'ねふたり・そと', '99' UNION ALL
  SELECT '金田 優太', 'infielder', 'かねだ ゆうた', '120' UNION ALL
  SELECT '勝又 琉偉', 'infielder', 'かつまた るい', '129' UNION ALL
  SELECT '谷村 剛', 'infielder', 'たにむら つよし', '130' UNION ALL
  SELECT '松石 信八', 'infielder', 'まついし しんや', '134' UNION ALL
  SELECT '髙部 瑛斗', 'outfielder', 'たかべ あきと', '0' UNION ALL
  SELECT '藤原 恭大', 'outfielder', 'ふじわら きょうた', '1' UNION ALL
  SELECT '角中 勝也', 'outfielder', 'かくなか かつや', '3' UNION ALL
  SELECT '西川 史礁', 'outfielder', 'にしかわ みしょう', '6' UNION ALL
  SELECT 'グレゴリー・ポランコ', 'outfielder', 'ぐれごりー・ぽらんこ', '22' UNION ALL
  SELECT '石川 慎吾', 'outfielder', 'いしかわ しんご', '23' UNION ALL
  SELECT '岡 大海', 'outfielder', 'おか ひろみ', '25' UNION ALL
  SELECT '井上 広大', 'outfielder', 'いのうえ こうた', '39' UNION ALL
  SELECT '愛斗', 'outfielder', 'あいと', '50' UNION ALL
  SELECT '山口 航輝', 'outfielder', 'やまぐち こうき', '51' UNION ALL
  SELECT '山本 大斗', 'outfielder', 'やまもと だいと', '61' UNION ALL
  SELECT '和田 康士朗', 'outfielder', 'わだ こうしろう', '63' UNION ALL
  SELECT 'HARUTO', 'outfielder', 'はると', '100' UNION ALL
  SELECT '杉山 諒', 'outfielder', 'すぎやま りょう', '128' UNION ALL
  SELECT '髙野 光海', 'outfielder', 'こうの ひかる', '135' UNION ALL
  SELECT '藤田 和樹', 'outfielder', 'ふじた かずき', '136' UNION ALL
  SELECT 'スティベン・アセベド', 'outfielder', 'すてぃべん・あせべど', '140' UNION ALL
  SELECT 'サブロー', 'coach', 'さぶろー', '86' UNION ALL
  SELECT '光山 英和', 'coach', 'みつやま ひでかず', '90' UNION ALL
  SELECT '西岡 剛', 'coach', 'にしおか つよし', '77' UNION ALL
  SELECT '松山 秀明', 'coach', 'まつやま ひであき', '80' UNION ALL
  SELECT '黒木 知宏', 'coach', 'くろき ともひろ', '84' UNION ALL
  SELECT '小林 宏之', 'coach', 'こばやし ひろゆき', '71' UNION ALL
  SELECT '栗原 健太', 'coach', 'くりはら けんた', '88' UNION ALL
  SELECT '江村 直也', 'coach', 'えむら なおや', '76' UNION ALL
  SELECT '根元 俊一', 'coach', 'ねもと しゅんいち', '87' UNION ALL
  SELECT '伊志嶺 翔大', 'coach', 'いしみね しょうた', '74' UNION ALL
  SELECT '福浦 和也', 'coach', 'ふくうら かずや', '70' UNION ALL
  SELECT '大隣 憲司', 'coach', 'おおとなり けんじ', '78' UNION ALL
  SELECT '松永 昂大', 'coach', 'まつなが たかひろ', '79' UNION ALL
  SELECT '美馬 学', 'coach', 'みま まなぶ', '81' UNION ALL
  SELECT '南 昌輝', 'coach', 'みなみ まさき', '85' UNION ALL
  SELECT '堀 幸一', 'coach', 'ほり こういち', '75' UNION ALL
  SELECT '細谷 圭', 'coach', 'ほそや けい', '82' UNION ALL
  SELECT '金澤 岳', 'coach', 'かなざわ たけし', '89' UNION ALL
  SELECT '三木 亮', 'coach', 'みき りょう', '72' UNION ALL
  SELECT '諸積 兼司', 'coach', 'もろづみ けんじ', '83'
) v
WHERE t.slug = 'marines'
  AND NOT EXISTS (
    SELECT 1 FROM tags existing
    WHERE existing.user_id IS NULL AND existing.name = v.name
  );

COMMIT;
