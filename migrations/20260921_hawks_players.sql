-- 2026年シーズンの福岡ソフトバンクホークス選手タグ
-- 公式選手名鑑の守備位置、背番号、ふりがなを登録します。
-- 既存タグとチームIDを確認するため、同じSQLを再実行しても重複しません。

START TRANSACTION;

INSERT INTO tags (user_id, name, tag_type, category, reading, uniform_number, team_id)
SELECT NULL, v.name, 'player', v.category, v.reading, v.uniform_number, t.id
FROM teams t
JOIN (
  SELECT 'カーター・スチュワート・ジュニア' AS name, 'pitcher' AS category, 'かーたー・すちゅわーと・じゅにあ' AS reading, '2' AS uniform_number UNION ALL
  SELECT '上沢 直之', 'pitcher', 'うわさわ なおゆき', '10' UNION ALL
  SELECT '津森 宥紀', 'pitcher', 'つもり ゆうき', '11' UNION ALL
  SELECT '稲川 竜汰', 'pitcher', 'いながわ りゅうた', '13' UNION ALL
  SELECT 'ジャクソン・ラトリッジ', 'pitcher', 'じゃくそん・らとりっじ', '14' UNION ALL
  SELECT '東浜 巨', 'pitcher', 'ひがしはま なお', '16' UNION ALL
  SELECT '張 峻瑋', 'pitcher', 'ちゃん じゅんうぇい', '17' UNION ALL
  SELECT '徐 若熙', 'pitcher', 'しゅー るおしー', '18' UNION ALL
  SELECT '大津 亮介', 'pitcher', 'おおつ りょうすけ', '19' UNION ALL
  SELECT '村上 泰斗', 'pitcher', 'むらかみ たいと', '20' UNION ALL
  SELECT '藤原 大翔', 'pitcher', 'ふじわら はると', '22' UNION ALL
  SELECT '鈴木 豪太', 'pitcher', 'すずき ごうた', '26' UNION ALL
  SELECT '岩井 俊介', 'pitcher', 'いわい しゅんすけ', '27' UNION ALL
  SELECT '安德 駿', 'pitcher', 'あんとく しゅん', '28' UNION ALL
  SELECT '大江 竜聖', 'pitcher', 'おおえ りゅうせい', '29' UNION ALL
  SELECT '中村 稔弥', 'pitcher', 'なかむら としや', '30' UNION ALL
  SELECT 'アレクサンダー・アルメンタ', 'pitcher', 'あれくさんだー・あるめんた', '34' UNION ALL
  SELECT 'リバン・モイネロ', 'pitcher', 'りばん・もいねろ', '35' UNION ALL
  SELECT '杉山 一樹', 'pitcher', 'すぎやま かずき', '40' UNION ALL
  SELECT '前田 悠伍', 'pitcher', 'まえだ ゆうご', '41' UNION ALL
  SELECT '伊藤 優輔', 'pitcher', 'いとう ゆうすけ', '42' UNION ALL
  SELECT '北斗', 'pitcher', 'ほくと', '43' UNION ALL
  SELECT '大関 友久', 'pitcher', 'おおぜき ともひさ', '47' UNION ALL
  SELECT '藤井 皓哉', 'pitcher', 'ふじい こうや', '48' UNION ALL
  SELECT '松本 晴', 'pitcher', 'まつもと はる', '49' UNION ALL
  SELECT '相良 雅斗', 'pitcher', 'さがら まさと', '50' UNION ALL
  SELECT '前田 純', 'pitcher', 'まえだ じゅん', '51' UNION ALL
  SELECT '大山 凌', 'pitcher', 'おおやま りょう', '53' UNION ALL
  SELECT 'ロベルト・オスナ', 'pitcher', 'ろべると・おすな', '54' UNION ALL
  SELECT '木村 大成', 'pitcher', 'きむら たいせい', '58' UNION ALL
  SELECT '大竹 風雅', 'pitcher', 'おおたけ ふうが', '59' UNION ALL
  SELECT '大野 稼頭央', 'pitcher', 'おおの かずお', '60' UNION ALL
  SELECT 'ダーウィンゾン・ヘルナンデス', 'pitcher', 'だーうぃんぞん・へるなんです', '63' UNION ALL
  SELECT '上茶谷 大河', 'pitcher', 'かみちゃたに たいが', '64' UNION ALL
  SELECT '松本 裕樹', 'pitcher', 'まつもと ゆうき', '66' UNION ALL
  SELECT '木村 光', 'pitcher', 'きむら ひかる', '68' UNION ALL
  SELECT '岩崎 峻典', 'pitcher', 'いわさき しゅんすけ', '69' UNION ALL
  SELECT 'ルイス・ロドリゲス', 'pitcher', 'るいす・ろどりげす', '85' UNION ALL
  SELECT '小林 樹斗', 'pitcher', 'こばやし たつと', '95' UNION ALL
  SELECT '長谷川 威展', 'pitcher', 'はせがわ たけひろ', '120' UNION ALL
  SELECT '宮里 優吾', 'pitcher', 'みやさと ゆうわ', '126' UNION ALL
  SELECT '河野 伸一朗', 'pitcher', 'かわの しんいちろう', '128' UNION ALL
  SELECT '川口 冬弥', 'pitcher', 'かわぐち とうや', '132' UNION ALL
  SELECT '村田 賢一', 'pitcher', 'むらた けんいち', '134' UNION ALL
  SELECT '津嘉山 憲志郎', 'pitcher', 'つかやま けんしろう', '137' UNION ALL
  SELECT '相原 雄太', 'pitcher', 'あいはら ゆうた', '138' UNION ALL
  SELECT '井﨑 燦志郎', 'pitcher', 'いざき さんしろう', '139' UNION ALL
  SELECT '岡田 皓一朗', 'pitcher', 'おかだ こういちろう', '140' UNION ALL
  SELECT '澤柳 亮太郎', 'pitcher', 'さわやなぎ りょうたろう', '141' UNION ALL
  SELECT 'ハモンド', 'pitcher', 'はもんど', '145' UNION ALL
  SELECT '大矢 琉晟', 'pitcher', 'おおや りゅうせい', '146' UNION ALL
  SELECT '山崎 琢磨', 'pitcher', 'やまさき たくま', '148' UNION ALL
  SELECT '熊谷 太雅', 'pitcher', 'くまがい たいが', '149' UNION ALL
  SELECT '塩士 暖', 'pitcher', 'しおじ だん', '152' UNION ALL
  SELECT '長﨑 蓮汰', 'pitcher', 'ながさき れんた', '155' UNION ALL
  SELECT '田上 奏大', 'pitcher', 'たのうえ そうた', '157' UNION ALL
  SELECT '長水 啓眞', 'pitcher', 'ながみず けいしん', '160' UNION ALL
  SELECT '内野 海斗', 'pitcher', 'うちの かいと', '161' UNION ALL
  SELECT '岡植 純平', 'pitcher', 'おかうえ じゅんぺい', '162' UNION ALL
  SELECT '佐々木 明都', 'pitcher', 'ささき あきと', '163' UNION ALL
  SELECT '飛田 悠成', 'pitcher', 'とびた ゆうせい', '169' UNION ALL
  SELECT 'ダリオ・サルディ', 'pitcher', 'だりお・さるでぃ', '176' UNION ALL
  SELECT '渡邉 陸', 'catcher', 'わたなべ りく', '00' UNION ALL
  SELECT '嶺井 博希', 'catcher', 'みねい ひろき', '12' UNION ALL
  SELECT '山本 祐大', 'catcher', 'やまもと ゆうだい', '39' UNION ALL
  SELECT '谷川原 健太', 'catcher', 'たにがわら けんた', '45' UNION ALL
  SELECT '石塚 綜一郎', 'catcher', 'いしづか そういちろう', '55' UNION ALL
  SELECT '海野 隆司', 'catcher', 'うみの たかし', '62' UNION ALL
  SELECT '藤田 悠太郎', 'catcher', 'ふじた ゆうたろう', '65' UNION ALL
  SELECT '大友 宗', 'catcher', 'おおとも そう', '125' UNION ALL
  SELECT '牧原 巧汰', 'catcher', 'まきはら こうた', '130' UNION ALL
  SELECT '池田 栞太', 'catcher', 'いけだ かんた', '133' UNION ALL
  SELECT '盛島 稜大', 'catcher', 'もりしま りょうた', '171' UNION ALL
  SELECT '川瀬 晃', 'infielder', 'かわせ ひかる', '0' UNION ALL
  SELECT 'ジーター・ダウンズ', 'infielder', 'じーたー・だうんず', '4' UNION ALL
  SELECT '山川 穂高', 'infielder', 'やまかわ ほたか', '5' UNION ALL
  SELECT '今宮 健太', 'infielder', 'いまみや けんた', '6' UNION ALL
  SELECT '中村 晃', 'infielder', 'なかむら あきら', '7' UNION ALL
  SELECT '牧原 大成', 'infielder', 'まきはら たいせい', '8' UNION ALL
  SELECT '栗原 陵矢', 'infielder', 'くりはら りょうや', '24' UNION ALL
  SELECT '庄子 雄大', 'infielder', 'しょうじ ゆうだい', '25' UNION ALL
  SELECT '廣瀨 隆太', 'infielder', 'ひろせ りゅうた', '33' UNION ALL
  SELECT 'イヒネ イツア', 'infielder', 'いひね いつあ', '36' UNION ALL
  SELECT '宇野 真仁朗', 'infielder', 'うの しんじろう', '46' UNION ALL
  SELECT '秋広 優人', 'infielder', 'あきひろ ゆうと', '52' UNION ALL
  SELECT '髙橋 隆慶', 'infielder', 'たかはし たかのり', '56' UNION ALL
  SELECT '石見 颯真', 'infielder', 'いしみ そうま', '67' UNION ALL
  SELECT '野村 勇', 'infielder', 'のむら いさみ', '99' UNION ALL
  SELECT 'ザイレン', 'infielder', 'ざいれん', '121' UNION ALL
  SELECT '藤野 恵音', 'infielder', 'ふじの けいお', '122' UNION ALL
  SELECT '桑原 秀侍', 'infielder', 'くわはら しゅうじ', '124' UNION ALL
  SELECT '広瀬 結煌', 'infielder', 'ひろせ ゆうき', '127' UNION ALL
  SELECT '佐倉 俠史朗', 'infielder', 'さくら きょうしろう', '129' UNION ALL
  SELECT '中澤 恒貴', 'infielder', 'なかざわ こうき', '131' UNION ALL
  SELECT '江崎 歩', 'infielder', 'えざき あゆむ', '144' UNION ALL
  SELECT '大橋 令和', 'infielder', 'おおはし れお', '150' UNION ALL
  SELECT '山下 恭吾', 'infielder', 'やました きょうご', '159' UNION ALL
  SELECT '西尾 歩真', 'infielder', 'にしお あゆま', '170' UNION ALL
  SELECT 'ジョナサン・モレノ', 'infielder', 'じょなさん・もれの', '174' UNION ALL
  SELECT 'デービッド・アルモンテ', 'infielder', 'でーびっど・あるもんて', '175' UNION ALL
  SELECT '近藤 健介', 'outfielder', 'こんどう けんすけ', '3' UNION ALL
  SELECT '柳田 悠岐', 'outfielder', 'やなぎた ゆうき', '9' UNION ALL
  SELECT '周東 佑京', 'outfielder', 'しゅうとう うきょう', '23' UNION ALL
  SELECT '正木 智也', 'outfielder', 'まさき ともや', '31' UNION ALL
  SELECT '柳町 達', 'outfielder', 'やなぎまち たつる', '32' UNION ALL
  SELECT '笹川 吉康', 'outfielder', 'ささがわ よしやす', '44' UNION ALL
  SELECT '緒方 理貢', 'outfielder', 'おがた りく', '57' UNION ALL
  SELECT '川村 友斗', 'outfielder', 'かわむら ゆうと', '61' UNION ALL
  SELECT '山本 恵大', 'outfielder', 'やまもと けいた', '77' UNION ALL
  SELECT '大泉 周也', 'outfielder', 'おおいずみ しゅうや', '123' UNION ALL
  SELECT '漁府 輝羽', 'outfielder', 'ぎょふ こうは', '143' UNION ALL
  SELECT '木下 勇人', 'outfielder', 'きのした はやと', '147' UNION ALL
  SELECT '鈴木 貴大', 'outfielder', 'すずき たかひろ', '151' UNION ALL
  SELECT '生海', 'outfielder', 'いくみ', '154' UNION ALL
  SELECT 'エミール セラーノ プレンサ', 'outfielder', 'えみーる せらーの ぷれんさ', '158' UNION ALL
  SELECT '重松 凱人', 'outfielder', 'しげまつ かいと', '166' UNION ALL
  SELECT '佐藤 航太', 'outfielder', 'さとう こうた', '168' UNION ALL
  SELECT 'ホセ・オスーナ', 'outfielder', 'ほせ・おすーな', '173' UNION ALL
  SELECT '小久保 裕紀', 'coach', 'こくぼ ひろき', '90' UNION ALL
  SELECT '倉野 信次', 'coach', 'くらの しんじ', '94' UNION ALL
  SELECT '若田部 健一', 'coach', 'わかたべ けんいち', '72' UNION ALL
  SELECT '中田 賢一', 'coach', 'なかた けんいち', '71' UNION ALL
  SELECT '村松 有人', 'coach', 'むらまつ ありひと', '93' UNION ALL
  SELECT '長谷川 勇也', 'coach', 'はせがわ ゆうや', '78' UNION ALL
  SELECT '本多 雄一', 'coach', 'ほんだ ゆういち', '80' UNION ALL
  SELECT '大西 崇之', 'coach', 'おおにし たかゆき', '79' UNION ALL
  SELECT '細川 亨', 'coach', 'ほそかわ とおる', '87' UNION ALL
  SELECT '斉藤 和巳', 'coach', 'さいとう かずみ', '88' UNION ALL
  SELECT '小笠原 孝', 'coach', 'おがさわら たかし', '73' UNION ALL
  SELECT '奥村 政稔', 'coach', 'おくむら まさと', '86' UNION ALL
  SELECT '森笠 繁', 'coach', 'もりかさ しげる', '74' UNION ALL
  SELECT '金子 圭輔', 'coach', 'かねこ けいすけ', '91' UNION ALL
  SELECT '城所 龍磨', 'coach', 'きどころ りゅうま', '96' UNION ALL
  SELECT '髙谷 裕亮', 'coach', 'たかや ひろあき', '84' UNION ALL
  SELECT '大越 基', 'coach', 'おおこし もとい', '92' UNION ALL
  SELECT 'フェリペ ナテル', 'coach', 'ふぇりぺ なてる', '97' UNION ALL
  SELECT '寺原 隼人', 'coach', 'てらはら はやと', '76' UNION ALL
  SELECT '大道 典良', 'coach', 'おおみち のりよし', '75' UNION ALL
  SELECT '髙田 知季', 'coach', 'たかた ともき', '82' UNION ALL
  SELECT '高波 文一', 'coach', 'たかなみ ふみかず', '98' UNION ALL
  SELECT '清水 将海', 'coach', 'しみず まさうみ', '83' UNION ALL
  SELECT '笹川 隆', 'coach', 'ささがわ たかし', '014' UNION ALL
  SELECT '牧田 和久', 'coach', 'まきた かずひさ', '013' UNION ALL
  SELECT '中谷 将大', 'coach', 'なかたに まさひろ', '018' UNION ALL
  SELECT '川越 英隆', 'coach', 'かわごえ ひでたか', '012' UNION ALL
  SELECT '星野 順治', 'coach', 'ほしの じゅんじ', '020' UNION ALL
  SELECT '荒金 久雄', 'coach', 'あらかね ひさお', '023' UNION ALL
  SELECT '関川 浩一', 'coach', 'せきかわ こういち', '019' UNION ALL
  SELECT '森 浩之', 'coach', 'もり ひろゆき', '022' UNION ALL
  SELECT '井出 竜也', 'coach', 'いで たつや', '021' UNION ALL
  SELECT '奈良原 浩', 'coach', 'ならはら ひろし', '011'
) v ON 1 = 1
WHERE t.slug = 'hawks'
  AND NOT EXISTS (
    SELECT 1
    FROM tags existing
    WHERE existing.user_id IS NULL
      AND existing.team_id = t.id
      AND existing.name = v.name
  );

COMMIT;
