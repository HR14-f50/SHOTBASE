-- 2026年シーズンの埼玉西武ライオンズ選手タグ
-- 公式選手名鑑の守備位置、背番号、ふりがなを登録します。
-- 既存タグとチームIDを確認するため、同じSQLを再実行しても重複しません。

START TRANSACTION;

INSERT INTO tags (user_id, name, tag_type, category, reading, uniform_number, team_id)
SELECT NULL, v.name, 'player', v.category, v.reading, v.uniform_number, t.id
FROM teams t
JOIN (
  SELECT '上田 大河' AS name, 'pitcher' AS category, 'うえだ たいが' AS reading, '11' AS uniform_number UNION ALL
  SELECT '渡邉 勇太朗', 'pitcher', 'わたなべ ゆうたろう', '12' UNION ALL
  SELECT '高橋 光成', 'pitcher', 'たかはし こうな', '13' UNION ALL
  SELECT '與座 海人', 'pitcher', 'よざ かいと', '15' UNION ALL
  SELECT '隅田 知一郎', 'pitcher', 'すみだ ちひろ', '16' UNION ALL
  SELECT '松本 航', 'pitcher', 'まつもと わたる', '17' UNION ALL
  SELECT '佐藤 隼輔', 'pitcher', 'さとう しゅんすけ', '19' UNION ALL
  SELECT '岩城 颯空', 'pitcher', 'いわき はく', '20' UNION ALL
  SELECT '武内 夏暉', 'pitcher', 'たけうち なつき', '21' UNION ALL
  SELECT '糸川 亮太', 'pitcher', 'いとがわ りょうた', '23' UNION ALL
  SELECT '森脇 亮介', 'pitcher', 'もりわき りょうすけ', '28' UNION ALL
  SELECT '青山 美夏人', 'pitcher', 'あおやま みなと', '29' UNION ALL
  SELECT 'アラン・ワイナンス', 'pitcher', 'あらん・わいなんす', '30' UNION ALL
  SELECT '甲斐野 央', 'pitcher', 'かいの ひろし', '34' UNION ALL
  SELECT '山田 陽翔', 'pitcher', 'やまだ はると', '36' UNION ALL
  SELECT '堀越 啓太', 'pitcher', 'ほりこし けいた', '40' UNION ALL
  SELECT '成田 晴風', 'pitcher', 'なりた はるせ', '41' UNION ALL
  SELECT '羽田 慎之介', 'pitcher', 'はだ しんのすけ', '43' UNION ALL
  SELECT 'トレイ・ウィンゲンター', 'pitcher', 'とれい・うぃんげんたー', '45' UNION ALL
  SELECT '狩生 聖真', 'pitcher', 'かりう しょうま', '46' UNION ALL
  SELECT '杉山 遙希', 'pitcher', 'すぎやま はるき', '47' UNION ALL
  SELECT '篠原 響', 'pitcher', 'しのはら ひびき', '52' UNION ALL
  SELECT '黒木 優太', 'pitcher', 'くろき ゆうた', '54' UNION ALL
  SELECT 'エマニュエル・ラミレス', 'pitcher', 'えまにゅえる・らみれす', '56' UNION ALL
  SELECT '黒田 将矢', 'pitcher', 'くろだ まさや', '57' UNION ALL
  SELECT '中村 祐太', 'pitcher', 'なかむら ゆうた', '58' UNION ALL
  SELECT '平良 海馬', 'pitcher', 'たいら かいま', '61' UNION ALL
  SELECT '冨士 大和', 'pitcher', 'ふじ やまと', '67' UNION ALL
  SELECT '豆田 泰志', 'pitcher', 'まめだ たいし', '70' UNION ALL
  SELECT '菅井 信也', 'pitcher', 'すがい しんや', '71' UNION ALL
  SELECT '佐藤 爽', 'pitcher', 'さとう そう', '75' UNION ALL
  SELECT '浜屋 将太', 'pitcher', 'はまや しょうた', '90' UNION ALL
  SELECT '上間 永遠', 'pitcher', 'うえま とわ', '114' UNION ALL
  SELECT '佐々木 健', 'pitcher', 'ささき たける', '115' UNION ALL
  SELECT '宮澤 太成', 'pitcher', 'みやざわ たいせい', '117' UNION ALL
  SELECT '斎藤 佳紳', 'pitcher', 'さいとう けいしん', '118' UNION ALL
  SELECT '三浦 大輝', 'pitcher', 'みうら だいき', '121' UNION ALL
  SELECT '濱岡 蒼太', 'pitcher', 'はまおか そうた', '124' UNION ALL
  SELECT 'シンクレア', 'pitcher', 'しんくれあ', '125' UNION ALL
  SELECT '平口 寛人', 'pitcher', 'ひらぐち ひろと', '128' UNION ALL
  SELECT '川下 将勲', 'pitcher', 'かわしも まさひろ', '129' UNION ALL
  SELECT '木瀬 翔太', 'pitcher', 'きせ しょうた', '131' UNION ALL
  SELECT '正木 悠馬', 'pitcher', 'まさき ゆうま', '134' UNION ALL
  SELECT '高橋 礼', 'pitcher', 'たかはし れい', '136' UNION ALL
  SELECT 'イサビレ・ムサ・アゼッド', 'pitcher', 'いさびれ・むさ・あぜっど', '141' UNION ALL
  SELECT 'チャッゼ・フレッド', 'pitcher', 'ちゃっぜ・ふれっど', '142' UNION ALL
  SELECT 'ホルヘ・ゴンザレス', 'pitcher', 'ほるへ・ごんざれす', '144' UNION ALL
  SELECT 'ロニー・オリバー', 'pitcher', 'ろにー・おりばー', '145' UNION ALL
  SELECT 'アクシャイ・モーレ', 'pitcher', 'あくしゃい・もーれ', '146' UNION ALL
  SELECT '小島 大河', 'catcher', 'こじま たいが', '10' UNION ALL
  SELECT '古賀 悠斗', 'catcher', 'こが ゆうと', '22' UNION ALL
  SELECT '炭谷 銀仁朗', 'catcher', 'すみたに ぎんじろう', '27' UNION ALL
  SELECT '柘植 世那', 'catcher', 'つげ せな', '37' UNION ALL
  SELECT '牧野 翔矢', 'catcher', 'まきの しょうや', '53' UNION ALL
  SELECT '龍山 暖', 'catcher', 'たつやま はるき', '64' UNION ALL
  SELECT '是澤 涼輔', 'catcher', 'これさわ りょうすけ', '65' UNION ALL
  SELECT '野田 海人', 'catcher', 'のだ かいと', '113' UNION ALL
  SELECT '野村 大樹', 'catcher', 'のむら だいじゅ', '120' UNION ALL
  SELECT '児玉 亮涼', 'infielder', 'こだま りょうすけ', '0' UNION ALL
  SELECT '仲田 慶介', 'infielder', 'なかた けいすけ', '00' UNION ALL
  SELECT '齋藤 大翔', 'infielder', 'さいとう ひろと', '2' UNION ALL
  SELECT '石井 一成', 'infielder', 'いしい かずなり', '4' UNION ALL
  SELECT '外崎 修汰', 'infielder', 'とのさき しゅうた', '5' UNION ALL
  SELECT '源田 壮亮', 'infielder', 'げんだ そうすけ', '6' UNION ALL
  SELECT 'タイラー・ネビン', 'infielder', 'たいらー・ねびん', '26' UNION ALL
  SELECT '山村 崇嘉', 'infielder', 'やまむら たかよし', '32' UNION ALL
  SELECT '佐藤 太陽', 'infielder', 'さとう たいよう', '38' UNION ALL
  SELECT '平沢 大河', 'infielder', 'ひらさわ たいが', '39' UNION ALL
  SELECT '高松 渡', 'infielder', 'たかまつ わたる', '50' UNION ALL
  SELECT '横田 蒼和', 'infielder', 'よこた そうわ', '59' UNION ALL
  SELECT '中村 剛也', 'infielder', 'なかむら たけや', '60' UNION ALL
  SELECT '滝澤 夏央', 'infielder', 'たきざわ なつお', '62' UNION ALL
  SELECT '村田 怜音', 'infielder', 'むらた れおん', '99' UNION ALL
  SELECT '新井 唯斗', 'infielder', 'あらい ゆいと', '111' UNION ALL
  SELECT '今岡 拓夢', 'infielder', 'いまおか たくむ', '112' UNION ALL
  SELECT '古賀 輝希', 'infielder', 'こが てるき', '119' UNION ALL
  SELECT '谷口 朝陽', 'infielder', 'たにぐち あさひ', '126' UNION ALL
  SELECT '金子 功児', 'infielder', 'かねこ こうじ', '130' UNION ALL
  SELECT '福尾 遥真', 'infielder', 'ふくお はるま', '138' UNION ALL
  SELECT 'フアン・コルニエル', 'infielder', 'ふあん・こるにえる', '140' UNION ALL
  SELECT 'フランシスコ・アポンテ', 'infielder', 'ふらんしすこ・あぽんて', '147' UNION ALL
  SELECT '栗山 巧', 'outfielder', 'くりやま たくみ', '1' UNION ALL
  SELECT '桑原 将志', 'outfielder', 'くわはら まさゆき', '7' UNION ALL
  SELECT '渡部 聖弥', 'outfielder', 'わたなべ せいや', '8' UNION ALL
  SELECT '蛭間 拓哉', 'outfielder', 'ひるま たくや', '9' UNION ALL
  SELECT 'アレクサンダー・カナリオ', 'outfielder', 'あれくさんだー・かなりお', '25' UNION ALL
  SELECT '茶野 篤政', 'outfielder', 'ちゃの とくまさ', '31' UNION ALL
  SELECT '古川 雄大', 'outfielder', 'ふるかわ ゆうだい', '33' UNION ALL
  SELECT '秋山 俊', 'outfielder', 'あきやま しゅん', '35' UNION ALL
  SELECT '林 冠臣', 'outfielder', 'りん くぁんちぇん', '44' UNION ALL
  SELECT '若林 楽人', 'outfielder', 'わかばやし がくと', '49' UNION ALL
  SELECT '西川 愛也', 'outfielder', 'にしかわ まなや', '51' UNION ALL
  SELECT '仲三 優太', 'outfielder', 'なかみ ゆうた', '55' UNION ALL
  SELECT '長谷川 信哉', 'outfielder', 'はせがわ しんや', '63' UNION ALL
  SELECT '川田 悠慎', 'outfielder', 'かわだ ゆうしん', '66' UNION ALL
  SELECT '岸 潤一郎', 'outfielder', 'きし じゅんいちろう', '68' UNION ALL
  SELECT '林 安可', 'outfielder', 'りん・あんこー', '73' UNION ALL
  SELECT '奥村 光一', 'outfielder', 'おくむら こういち', '116' UNION ALL
  SELECT 'ラマル', 'outfielder', 'らまる', '132' UNION ALL
  SELECT '安藤 銀杜', 'outfielder', 'あんどう ぎんと', '135' UNION ALL
  SELECT '澤田 遥斗', 'outfielder', 'さわだ はると', '137' UNION ALL
  SELECT 'オケム', 'outfielder', 'おけむ', '139' UNION ALL
  SELECT 'カルロス・トーバー', 'outfielder', 'かるろす・とーばー', '143' UNION ALL
  SELECT '西口 文也', 'coach', 'にしぐち ふみや', '74' UNION ALL
  SELECT '鳥越 裕介', 'coach', 'とりごえ ゆうすけ', '91' UNION ALL
  SELECT '豊田 清', 'coach', 'とよだ きよし', '81' UNION ALL
  SELECT '大石 達也', 'coach', 'おおいし たつや', '95' UNION ALL
  SELECT '中田 祥多', 'coach', 'なかた しょうた', '96' UNION ALL
  SELECT '仁志 敏久', 'coach', 'にし としひさ', '78' UNION ALL
  SELECT '立花 義家', 'coach', 'たちばな よしいえ', '83' UNION ALL
  SELECT '黒田 哲史', 'coach', 'くろだ さとし', '87' UNION ALL
  SELECT '熊代 聖人', 'coach', 'くましろ まさと', '84' UNION ALL
  SELECT '小関 竜也', 'coach', 'おぜき たつや', '79' UNION ALL
  SELECT '土肥 義弘', 'coach', 'どい よしひろ', '72' UNION ALL
  SELECT '渡辺 智男', 'coach', 'わたなべ とみお', '98' UNION ALL
  SELECT '青木 勇人', 'coach', 'あおき はやと', '93' UNION ALL
  SELECT '榎田 大樹', 'coach', 'えのきだ だいき', '85' UNION ALL
  SELECT '辻 竜太郎', 'coach', 'つじ りゅうたろう', '77' UNION ALL
  SELECT '野田 浩輔', 'coach', 'のだ こうすけ', '82' UNION ALL
  SELECT '赤田 将吾', 'coach', 'あかだ しょうご', '86' UNION ALL
  SELECT '大島 裕行', 'coach', 'おおしま ひろゆき', '88' UNION ALL
  SELECT '大引 啓次', 'coach', 'おおびき けいじ', '80' UNION ALL
  SELECT '青木 智史', 'coach', 'あおき ともし', '97' UNION ALL
  SELECT '田邊 徳雄', 'coach', 'たなべ のりお', '76' UNION ALL
  SELECT '鬼﨑 裕司', 'coach', 'おにざき ゆうじ', '94' UNION ALL
  SELECT '木村 文紀', 'coach', 'きむら ふみかず', '89' UNION ALL
  SELECT '岡田 雅利', 'coach', 'おかだ まさとし', '92'
) v ON 1 = 1
WHERE t.slug = 'lions'
  AND NOT EXISTS (
    SELECT 1
    FROM tags existing
    WHERE existing.user_id IS NULL
      AND existing.team_id = t.id
      AND existing.name = v.name
  );

COMMIT;
