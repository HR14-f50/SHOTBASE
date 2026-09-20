-- 2026年シーズンの東北楽天ゴールデンイーグルス選手タグ
-- 公式選手名鑑の守備位置、背番号、ふりがなを登録します。
-- 既存タグとチームIDを確認するため、同じSQLを再実行しても重複しません。

START TRANSACTION;

INSERT INTO tags (user_id, name, tag_type, category, reading, uniform_number, team_id)
SELECT NULL, v.name, 'player', v.category, v.reading, v.uniform_number, t.id
FROM teams t
JOIN (
  SELECT '岸 孝之' AS name, 'pitcher' AS category, 'きし たかゆき' AS reading, '11' AS uniform_number UNION ALL
  SELECT '藤原 聡大', 'pitcher', 'ふじわら そうた', '13' UNION ALL
  SELECT 'ロアンシー・コントレラス', 'pitcher', 'ろあんしー・こんとれらす', '15' UNION ALL
  SELECT '古謝 樹', 'pitcher', 'こじゃ たつき', '17' UNION ALL
  SELECT '前田 健太', 'pitcher', 'まえだ けんた', '18' UNION ALL
  SELECT '荘司 康誠', 'pitcher', 'しょうじ こうせい', '19' UNION ALL
  SELECT '伊藤 樹', 'pitcher', 'いとう たつき', '20' UNION ALL
  SELECT '早川 隆久', 'pitcher', 'はやかわ たかひさ', '21' UNION ALL
  SELECT 'ホセ・ウレーニャ', 'pitcher', 'ほせ・うれーにゃ', '22' UNION ALL
  SELECT 'ジュニオル・マルテ', 'pitcher', 'じゅにおる・まるて', '23' UNION ALL
  SELECT '中込 陽翔', 'pitcher', 'なかごみ はると', '26' UNION ALL
  SELECT '酒居 知史', 'pitcher', 'さかい ともひと', '28' UNION ALL
  SELECT '田中 千晴', 'pitcher', 'たなか ちはる', '29' UNION ALL
  SELECT '渡辺 翔太', 'pitcher', 'わたなべ しょうた', '31' UNION ALL
  SELECT '江原 雅裕', 'pitcher', 'えはら まさひろ', '40' UNION ALL
  SELECT '加治屋 蓮', 'pitcher', 'かじや れん', '41' UNION ALL
  SELECT '宋 家豪', 'pitcher', 'そん ちゃーほう', '43' UNION ALL
  SELECT '九谷 瑠', 'pitcher', 'くたに りゅう', '45' UNION ALL
  SELECT '藤平 尚真', 'pitcher', 'ふじひら しょうま', '46' UNION ALL
  SELECT '藤井 聖', 'pitcher', 'ふじい まさる', '47' UNION ALL
  SELECT '西垣 雅矢', 'pitcher', 'にしがき まさや', '49' UNION ALL
  SELECT '津留﨑 大成', 'pitcher', 'つるさき たいせい', '52' UNION ALL
  SELECT '坂井 陽翔', 'pitcher', 'さかい はると', '53' UNION ALL
  SELECT '日當 直喜', 'pitcher', 'ひなた なおき', '54' UNION ALL
  SELECT '鈴木 翔天', 'pitcher', 'すずき そら', '56' UNION ALL
  SELECT '瀧中 瞭太', 'pitcher', 'たきなか りょうた', '57' UNION ALL
  SELECT '辛島 航', 'pitcher', 'からしま わたる', '58' UNION ALL
  SELECT '泰 勝利', 'pitcher', 'たい かつとし', '59' UNION ALL
  SELECT '古賀 康誠', 'pitcher', 'こが こうせい', '61' UNION ALL
  SELECT '西口 直人', 'pitcher', 'にしぐち なおと', '62' UNION ALL
  SELECT '林 優樹', 'pitcher', 'はやし ゆうき', '64' UNION ALL
  SELECT '今野 龍太', 'pitcher', 'こんの りゅうた', '66' UNION ALL
  SELECT '大内 誠弥', 'pitcher', 'おおうち せいや', '67' UNION ALL
  SELECT '内 星龍', 'pitcher', 'うち せいりゅう', '69' UNION ALL
  SELECT '柴田 大地', 'pitcher', 'しばた だいち', '71' UNION ALL
  SELECT '伊藤 大晟', 'pitcher', 'いとう たいせい', '79' UNION ALL
  SELECT '德山 一翔', 'pitcher', 'とくやま かずと', '029' UNION ALL
  SELECT '松田 啄磨', 'pitcher', 'まつだ たくま', '061' UNION ALL
  SELECT 'ヨナウィル・フロリモン・ロドリゲス', 'pitcher', 'よなうぃる・ふろりもん・ろどりげす', '130' UNION ALL
  SELECT '中沢 匠磨', 'pitcher', 'なかざわ たくま', '133' UNION ALL
  SELECT '蕭 齊', 'pitcher', 'しゃお ち', '197' UNION ALL
  SELECT '太田 光', 'catcher', 'おおた ひかる', '2' UNION ALL
  SELECT '伊藤 光', 'catcher', 'いとう ひかる', '27' UNION ALL
  SELECT '田中 貴也', 'catcher', 'たなか たかや', '44' UNION ALL
  SELECT 'YG安田', 'catcher', 'わいじーやすだ', '55' UNION ALL
  SELECT '堀内 謙伍', 'catcher', 'ほりうち けんご', '65' UNION ALL
  SELECT '石原 彪', 'catcher', 'いしはら つよし', '70' UNION ALL
  SELECT '大栄 利哉', 'catcher', 'おおさかえ としや', '72' UNION ALL
  SELECT '水上 桂', 'catcher', 'みずかみ けい', '022' UNION ALL
  SELECT '島原 大河', 'catcher', 'しまはら たいが', '122' UNION ALL
  SELECT '小深田 大翔', 'infielder', 'こぶかた ひろと', '0' UNION ALL
  SELECT '宗山 塁', 'infielder', 'むねやま るい', '1' UNION ALL
  SELECT '浅村 栄斗', 'infielder', 'あさむら ひでと', '3' UNION ALL
  SELECT '村林 一輝', 'infielder', 'むらばやし いつき', '6' UNION ALL
  SELECT '鈴木 大地', 'infielder', 'すずき だいち', '7' UNION ALL
  SELECT 'ルーク・ボイト', 'infielder', 'るーく・ぼいと', '9' UNION ALL
  SELECT '黒川 史陽', 'infielder', 'くろかわ ふみや', '24' UNION ALL
  SELECT '繁永 晟', 'infielder', 'しげなが あきら', '30' UNION ALL
  SELECT '渡邊 佳明', 'infielder', 'わたなべ よしあき', '35' UNION ALL
  SELECT '伊藤 裕季也', 'infielder', 'いとう ゆきや', '39' UNION ALL
  SELECT '平良 竜哉', 'infielder', 'たいら りゅうや', '48' UNION ALL
  SELECT 'ワォーターズ', 'infielder', 'わぉーたーず', '60' UNION ALL
  SELECT '入江 大樹', 'infielder', 'いりえ だいき', '63' UNION ALL
  SELECT '青野 拓海', 'infielder', 'あおの たくみ', '68' UNION ALL
  SELECT '小森 航大郎', 'infielder', 'こもり こうたろう', '73' UNION ALL
  SELECT '陽 柏翔', 'infielder', 'よう ぼうしゃん', '75' UNION ALL
  SELECT '金子 京介', 'infielder', 'かねこ きょうすけ', '98' UNION ALL
  SELECT '岸本 佑也', 'infielder', 'きしもと ゆうや', '132' UNION ALL
  SELECT '辰己 涼介', 'outfielder', 'たつみ りょうすけ', '8' UNION ALL
  SELECT '田中 和基', 'outfielder', 'たなか かずき', '25' UNION ALL
  SELECT '中島 大輔', 'outfielder', 'なかしま だいすけ', '32' UNION ALL
  SELECT 'カーソン・マッカスカー', 'outfielder', 'かーそん・まっかすかー', '34' UNION ALL
  SELECT '吉納 翼', 'outfielder', 'よしのう つばさ', '36' UNION ALL
  SELECT '佐藤 直樹', 'outfielder', 'さとう なおき', '38' UNION ALL
  SELECT '阪上 翔也', 'outfielder', 'さかうえ しょうや', '42' UNION ALL
  SELECT '武藤 敦貴', 'outfielder', 'むとう あつき', '50' UNION ALL
  SELECT '小郷 裕哉', 'outfielder', 'おごう ゆうや', '51' UNION ALL
  SELECT '吉野 創士', 'outfielder', 'よしの そうし', '78' UNION ALL
  SELECT '前田 銀治', 'outfielder', 'まえだ ぎんじ', '079' UNION ALL
  SELECT '幌村 黛汰', 'outfielder', 'ほろむら だいた', '126' UNION ALL
  SELECT '大坪 梓恩', 'outfielder', 'おおつぼ しおん', '129' UNION ALL
  SELECT '吉井 理人', 'coach', 'よしい まさと', '81' UNION ALL
  SELECT '渡辺 直人', 'coach', 'わたなべ なおと', '74' UNION ALL
  SELECT '塩川 達也', 'coach', 'しおかわ たつや', '86' UNION ALL
  SELECT '渡辺 浩司', 'coach', 'わたなべ ひろし', '89' UNION ALL
  SELECT '下園 辰哉', 'coach', 'しもぞの たつや', '76' UNION ALL
  SELECT '雄平', 'coach', 'ゆうへい', '84' UNION ALL
  SELECT '山下 勝巳', 'coach', 'やました かつみ', '94' UNION ALL
  SELECT '久保 裕也', 'coach', 'くぼ ゆうや', '91' UNION ALL
  SELECT '小野寺 力', 'coach', 'おのでら ちから', '92' UNION ALL
  SELECT '石井 貴', 'coach', 'いしい たかし', '80' UNION ALL
  SELECT '青山 浩二', 'coach', 'あおやま こうじ', '82' UNION ALL
  SELECT '塩見 貴洋', 'coach', 'しおみ たかひろ', '96' UNION ALL
  SELECT '井野 卓', 'coach', 'いの すぐる', '93' UNION ALL
  SELECT '下妻 貴寛', 'coach', 'しもつま たかひろ', '97' UNION ALL
  SELECT '森岡 良介', 'coach', 'もりおか りょうすけ', '95' UNION ALL
  SELECT '渡辺 正人', 'coach', 'わたなべ まさと', '83' UNION ALL
  SELECT '川名 慎一', 'coach', 'かわな しんいち', '99' UNION ALL
  SELECT '牧田 明久', 'coach', 'まきだ あきひさ', '85' UNION ALL
  SELECT '真喜志 康永', 'coach', 'まきし やすなが', '87' UNION ALL
  SELECT '鷹野 史寿', 'coach', 'たかの ふみとし', '90'
) v ON 1 = 1
WHERE t.slug = 'eagles'
  AND NOT EXISTS (
    SELECT 1
    FROM tags existing
    WHERE existing.user_id IS NULL
      AND existing.team_id = t.id
      AND existing.name = v.name
  );

COMMIT;
