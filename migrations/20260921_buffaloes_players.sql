-- 2026年シーズンのオリックス・バファローズ選手タグ
-- 公式選手名鑑の守備位置、背番号、ふりがなを登録します。
-- 既存タグとチームIDを確認するため、同じSQLを再実行しても重複しません。

START TRANSACTION;

INSERT INTO tags (user_id, name, tag_type, category, reading, uniform_number, team_id)
SELECT NULL, v.name, 'player', v.category, v.reading, v.uniform_number, t.id
FROM teams t
JOIN (
  SELECT 'アンダーソン・エスピノーザ' AS name, 'pitcher' AS category, 'あんだーそん・えすぴのーざ' AS reading, '00' AS uniform_number UNION ALL
  SELECT '山下 舜平大', 'pitcher', 'やました しゅんぺいた', '11' UNION ALL
  SELECT '東 晃平', 'pitcher', 'あずま こうへい', '12' UNION ALL
  SELECT '寺西 成騎', 'pitcher', 'てらにし なるき', '13' UNION ALL
  SELECT '椋木 蓮', 'pitcher', 'むくのき れん', '15' UNION ALL
  SELECT '平野 佳寿', 'pitcher', 'ひらの よしひさ', '16' UNION ALL
  SELECT '曽谷 龍平', 'pitcher', 'そたに りゅうへい', '17' UNION ALL
  SELECT '宮城 大弥', 'pitcher', 'みやぎ ひろや', '18' UNION ALL
  SELECT '山岡 泰輔', 'pitcher', 'やまおか たいすけ', '19' UNION ALL
  SELECT '阿部 翔太', 'pitcher', 'あべ しょうた', '20' UNION ALL
  SELECT '山崎 颯一郎', 'pitcher', 'やまざき そういちろう', '21' UNION ALL
  SELECT '九里 亜蓮', 'pitcher', 'くり あれん', '22' UNION ALL
  SELECT '吉田 輝星', 'pitcher', 'よしだ こうせい', '23' UNION ALL
  SELECT '齋藤 響介', 'pitcher', 'さいとう きょうすけ', '26' UNION ALL
  SELECT '富山 凌雅', 'pitcher', 'とみやま りょうが', '28' UNION ALL
  SELECT '田嶋 大樹', 'pitcher', 'たじま だいき', '29' UNION ALL
  SELECT '藤川 敦也', 'pitcher', 'ふじかわ あつや', '31' UNION ALL
  SELECT '古田島 成龍', 'pitcher', 'こたじま せいりゅう', '35' UNION ALL
  SELECT '森 陽樹', 'pitcher', 'もり はるき', '36' UNION ALL
  SELECT '岩嵜 翔', 'pitcher', 'いわさき しょう', '40' UNION ALL
  SELECT '佐藤 龍月', 'pitcher', 'さとう りゅうが', '41' UNION ALL
  SELECT 'アンドレス・マチャド', 'pitcher', 'あんどれす・まちゃど', '42' UNION ALL
  SELECT '高谷 舟', 'pitcher', 'たかや しゅう', '43' UNION ALL
  SELECT '本田 仁海', 'pitcher', 'ほんだ ひとみ', '46' UNION ALL
  SELECT '山口 廉王', 'pitcher', 'やまぐち れお', '47' UNION ALL
  SELECT '東松 快征', 'pitcher', 'とうまつ かいせい', '48' UNION ALL
  SELECT '片山 楽生', 'pitcher', 'かたやま らいく', '49' UNION ALL
  SELECT '横山 楓', 'pitcher', 'よこやま かえで', '52' UNION ALL
  SELECT '東山 玲士', 'pitcher', 'ひがしやま れいじ', '54' UNION ALL
  SELECT '山田 修義', 'pitcher', 'やまだ のぶよし', '57' UNION ALL
  SELECT 'ルイス・ペルドモ', 'pitcher', 'るいす・ぺるども', '59' UNION ALL
  SELECT '宮國 凌空', 'pitcher', 'みやぐに りく', '65' UNION ALL
  SELECT '博志', 'pitcher', 'ひろし', '66' UNION ALL
  SELECT '入山 海斗', 'pitcher', 'いりやま かいと', '68' UNION ALL
  SELECT 'ショーン・ジェリー', 'pitcher', 'しょーん・じぇりー', '69' UNION ALL
  SELECT '陳 睦衡', 'pitcher', 'ちぇん むーへん', '92' UNION ALL
  SELECT '佐藤 一磨', 'pitcher', 'さとう かずま', '93' UNION ALL
  SELECT '川瀬 堅斗', 'pitcher', 'かわせ けんと', '94' UNION ALL
  SELECT '才木 海翔', 'pitcher', 'さいき かいと', '95' UNION ALL
  SELECT '高島 泰都', 'pitcher', 'たかしま たいと', '96' UNION ALL
  SELECT '権田 琉成', 'pitcher', 'ごんだ りゅうせい', '98' UNION ALL
  SELECT 'マシュー', 'pitcher', 'ましゅー', '002' UNION ALL
  SELECT '渡邉 一生', 'pitcher', 'わたなべ いっせい', '004' UNION ALL
  SELECT '寿賀 弘都', 'pitcher', 'すが ひろと', '041' UNION ALL
  SELECT '芦田 丈飛', 'pitcher', 'あしだ たけと', '044' UNION ALL
  SELECT '上原 堆我', 'pitcher', 'うえはら たいが', '053' UNION ALL
  SELECT '乾 健斗', 'pitcher', 'いぬい けんと', '056' UNION ALL
  SELECT '小木田 敦也', 'pitcher', 'こぎた あつや', '120' UNION ALL
  SELECT '宇田川 優希', 'pitcher', 'うだがわ ゆうき', '121' UNION ALL
  SELECT '前 佑囲斗', 'pitcher', 'まえ ゆいと', '128' UNION ALL
  SELECT '河内 康介', 'pitcher', 'かわち こうすけ', '135' UNION ALL
  SELECT '若月 健矢', 'catcher', 'わかつき けんや', '2' UNION ALL
  SELECT '森 友哉', 'catcher', 'もり ともや', '4' UNION ALL
  SELECT '福永 奨', 'catcher', 'ふくなが しょう', '32' UNION ALL
  SELECT '石川 亮', 'catcher', 'いしかわ りょう', '37' UNION ALL
  SELECT '頓宮 裕真', 'catcher', 'とんぐう ゆうま', '44' UNION ALL
  SELECT '山中 稜真', 'catcher', 'やまなか りょうま', '50' UNION ALL
  SELECT '野上 士耀', 'catcher', 'のがみ しきら', '60' UNION ALL
  SELECT '堀 柊那', 'catcher', 'ほり しゅうな', '62' UNION ALL
  SELECT '村上 喬一朗', 'catcher', 'むらかみ きょういちろう', '034' UNION ALL
  SELECT '田島 光祐', 'catcher', 'たじま こうすけ', '055' UNION ALL
  SELECT '太田 椋', 'infielder', 'おおた りょう', '1' UNION ALL
  SELECT '西野 真弘', 'infielder', 'にしの まさひろ', '5' UNION ALL
  SELECT '宗 佑磨', 'infielder', 'むね ゆうま', '6' UNION ALL
  SELECT '野口 智哉', 'infielder', 'のぐち ともや', '9' UNION ALL
  SELECT '大城 滉二', 'infielder', 'おおしろ こうじ', '10' UNION ALL
  SELECT '紅林 弘太郎', 'infielder', 'くればやし こうたろう', '24' UNION ALL
  SELECT '内藤 鵬', 'infielder', 'ないとう ほう', '25' UNION ALL
  SELECT '廣岡 大志', 'infielder', 'ひろおか たいし', '30' UNION ALL
  SELECT '横山 聖哉', 'infielder', 'よこやま せいや', '34' UNION ALL
  SELECT 'ボブ・シーモア', 'infielder', 'ぼぶ・しーもあ', '45' UNION ALL
  SELECT '宜保 翔', 'infielder', 'ぎぼ しょう', '53' UNION ALL
  SELECT '中川 圭太', 'infielder', 'なかがわ けいた', '67' UNION ALL
  SELECT '中西 創大', 'infielder', 'なかにし そうた', '003' UNION ALL
  SELECT '河野 聡太', 'infielder', 'かわの そうた', '045' UNION ALL
  SELECT '今坂 幸暉', 'infielder', 'いまさか ともき', '051' UNION ALL
  SELECT '清水 武蔵', 'infielder', 'しみず むさし', '052' UNION ALL
  SELECT '大里 昂生', 'infielder', 'おおさと こうせい', '122' UNION ALL
  SELECT '香月 一也', 'infielder', 'かつき かずや', '126' UNION ALL
  SELECT '渡部 遼人', 'outfielder', 'わたなべ はると', '0' UNION ALL
  SELECT '西川 龍馬', 'outfielder', 'にしかわ りょうま', '7' UNION ALL
  SELECT '麦谷 祐介', 'outfielder', 'むぎたに ゆうすけ', '8' UNION ALL
  SELECT '杉澤 龍', 'outfielder', 'すぎさわ りゅう', '33' UNION ALL
  SELECT '来田 涼斗', 'outfielder', 'きた りょうと', '38' UNION ALL
  SELECT '池田 陵真', 'outfielder', 'いけだ りょうま', '39' UNION ALL
  SELECT '窪田 洋祐', 'outfielder', 'くぼた ようすけ', '58' UNION ALL
  SELECT '平沼 翔太', 'outfielder', 'ひらぬま しょうた', '61' UNION ALL
  SELECT '杉本 裕太郎', 'outfielder', 'すぎもと ゆうたろう', '99' UNION ALL
  SELECT '三方 陽登', 'outfielder', 'みかた はると', '001' UNION ALL
  SELECT '寺本 聖一', 'outfielder', 'てらもと せいいち', '054' UNION ALL
  SELECT '元 謙太', 'outfielder', 'げん けんだい', '127' UNION ALL
  SELECT '岸田 護', 'coach', 'きしだ まもる', '71' UNION ALL
  SELECT '風岡 尚幸', 'coach', 'かざおか なおゆき', '76' UNION ALL
  SELECT '波留 敏夫', 'coach', 'はる としお', '81' UNION ALL
  SELECT '水本 勝巳', 'coach', 'みずもと かつみ', '88' UNION ALL
  SELECT '齋藤 俊雄', 'coach', 'さいとう としお', '87' UNION ALL
  SELECT '小林 宏', 'coach', 'こばやし ひろし', '89' UNION ALL
  SELECT '厚澤 和幸', 'coach', 'あつざわ かずゆき', '75' UNION ALL
  SELECT '比嘉 幹貴', 'coach', 'ひが もとき', '77' UNION ALL
  SELECT '牧野 塁', 'coach', 'まきの るい', '73' UNION ALL
  SELECT '平井 正史', 'coach', 'ひらい まさふみ', '72' UNION ALL
  SELECT '嶋村 一輝', 'coach', 'しまむら いっき', '78' UNION ALL
  SELECT '川島 慶三', 'coach', 'かわしま けいぞう', '82' UNION ALL
  SELECT '福川 将和', 'coach', 'ふくかわ まさかず', '79' UNION ALL
  SELECT '高橋 信二', 'coach', 'たかはし しんじ', '85' UNION ALL
  SELECT '安達 了一', 'coach', 'あだち りょういち', '83' UNION ALL
  SELECT '小島 脩平', 'coach', 'こじま しゅうへい', '80' UNION ALL
  SELECT '松井 佑介', 'coach', 'まつい ゆうすけ', '70' UNION ALL
  SELECT '由田 慎太郎', 'coach', 'よしだ しんたろう', '86' UNION ALL
  SELECT '山崎 勝己', 'coach', 'やまざき かつき', '74'
) v ON 1 = 1
WHERE t.slug = 'buffaloes'
  AND NOT EXISTS (
    SELECT 1
    FROM tags existing
    WHERE existing.user_id IS NULL
      AND existing.team_id = t.id
      AND existing.name = v.name
  );

COMMIT;
