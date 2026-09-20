<?php
$tagPickerTags = $tagPickerTags ?? ($tags ?? []);
$tagPickerSelected = array_map('intval', $tagPickerSelected ?? []);
$tagPickerInputName = $tagPickerInputName ?? 'tag_ids[]';
$tagPickerLabelClass = $tagPickerLabelClass ?? 'tagChoice';
$tagPickerDeleteUrl = $tagPickerDeleteUrl ?? '';
$tagPickerCreatedIds = array_map('intval', $tagPickerCreatedIds ?? []);
$tagPickerEditToken = $tagPickerEditToken ?? '';
$tagPickerGroups = [
  'league' => ['label' => 'リーグ・大会を選ぶ', 'tags' => []],
  'stadium' => ['label' => '球場を選ぶ', 'tags' => []],
  'user' => ['label' => 'ユーザーが追加したタグ', 'tags' => []],
  'other' => ['label' => 'その他のタグを選ぶ', 'tags' => []],
];
$tagPickerTeamTags = [];
$tagPickerPlayerTags = [];
foreach ($tagPickerTags as $tag) {
  $type = $tag['tag_type'] ?? 'custom';
  if ($type === 'team') {
    $teamId = (string)($tag['team_id'] ?? '');
    if ($teamId !== '') $tagPickerTeamTags[$teamId] = $tag;
    continue;
  }
  if ($type === 'player') {
    $teamId = (string)($tag['team_id'] ?? '');
    if ($teamId !== '') $tagPickerPlayerTags[$teamId][(string)($tag['category'] ?? 'other')][] = $tag;
    continue;
  }
  if (in_array($type, ['division', 'event'], true)) {
    $tagPickerGroups['league']['tags'][] = $tag;
  } elseif ($type === 'stadium') {
    $tagPickerGroups['stadium']['tags'][] = $tag;
  } elseif (($tag['user_id'] ?? null) !== null) {
    $tagPickerGroups['user']['tags'][] = $tag;
  } else {
    $tagPickerGroups['other']['tags'][] = $tag;
  }
}
$tagPickerCategoryLabels = [
  'pitcher' => '投手',
  'catcher' => '捕手',
  'infielder' => '内野手',
  'outfielder' => '外野手',
  'coach' => '監督・コーチ',
  'other' => 'その他',
];
$tagPickerStadiumLabels = [
  'home' => '本拠地球場',
  'farm' => 'ファーム球場',
  'regional' => '地方球場',
];
$tagPickerSelectedTeamIds = [];
foreach ($tagPickerTags as $tag) {
  if (!in_array($tag['tag_type'] ?? '', ['team', 'player'], true)) continue;
  if (in_array((int)$tag['id'], $tagPickerSelected, true) && !empty($tag['team_id'])) {
    $tagPickerSelectedTeamIds[] = (string)$tag['team_id'];
  }
}
$tagPickerSelectedTeamIds = array_values(array_unique($tagPickerSelectedTeamIds));
$tagPickerTeamIds = array_values(array_unique(array_merge(array_keys($tagPickerTeamTags), array_keys($tagPickerPlayerTags))));
$tagPickerTeamName = static function (string $teamId, array $categories, array $teamTags): string {
  if (!empty($teamTags[$teamId]['name'])) return (string)$teamTags[$teamId]['name'];
  foreach ($categories as $categoryTags) {
    if (!empty($categoryTags[0]['team_name'])) return (string)$categoryTags[0]['team_name'];
  }
  return '選手';
};
$renderTagChoice = static function (array $tag, array $extraAttributes = []) use ($tagPickerInputName, $tagPickerLabelClass, $tagPickerSelected, $tagPickerDeleteUrl, $tagPickerCreatedIds, $tagPickerEditToken): void {
  $tagId = (int)$tag['id'];
  $tagName = (string)$tag['name'];
  $uniformNumber = trim((string)($tag['uniform_number'] ?? ''));
  $isDeletable = $tagPickerDeleteUrl !== '' && in_array($tagId, $tagPickerCreatedIds, true);
  $attributes = ' data-tag-reading="' . h($tag['reading'] ?? '') . '"';
  if ($uniformNumber !== '') $attributes .= ' data-tag-number="' . h($uniformNumber) . '"';
  foreach ($extraAttributes as $attribute => $value) {
    $attributes .= ' ' . h($attribute) . '="' . h((string)$value) . '"';
  }
  echo '<span class="tagManageItem">';
  echo '<label class="' . h($tagPickerLabelClass) . '"' . $attributes . ' style="' . h(profileTagStyle($tag)) . '">';
  echo '<input type="checkbox" name="' . h($tagPickerInputName) . '" value="' . $tagId . '"' . (in_array($tagId, $tagPickerSelected, true) ? ' checked' : '') . '>'; 
  echo '<span class="tag" style="' . h(profileTagStyle($tag)) . '">#' . h($tagName);
  if ($uniformNumber !== '') echo '<span class="tagUniformNumber">' . h($uniformNumber) . '</span>';
  echo '</span></label>';
  if ($isDeletable) {
    echo '<button type="button" class="tagDeleteButton" data-delete-tag="' . $tagId . '" data-tag-name="' . h($tagName) . '" aria-label="' . h($tagName) . 'を登録タグから削除">×</button>';
  }
  echo '</span>';
};
?>
<details class="tagPickerAccordion" open>
  <summary>タグを選ぶ</summary>
  <div class="tagPickerBody">
    <div class="tagPickerList" data-tag-list<?= $tagPickerDeleteUrl !== '' ? ' data-tag-delete="' . h($tagPickerDeleteUrl) . '"' : '' ?> data-tag-input-name="<?= h($tagPickerInputName) ?>">
      <?php if ($tagPickerTeamIds): ?>
        <section class="tagPickerGroup tagPickerGroup-teams">
          <h3>チームを選ぶ</h3>
          <p class="tagPickerHint" data-player-team-hint<?= $tagPickerSelectedTeamIds ? ' hidden' : '' ?>>チームを選ぶと、そのチームの選手一覧が表示されます。</p>
          <div class="tagPickerTeamTables" data-player-filter>
            <?php foreach ($tagPickerTeamIds as $teamId): ?>
              <?php $categories = $tagPickerPlayerTags[$teamId] ?? []; $teamName = $tagPickerTeamName($teamId, $categories, $tagPickerTeamTags); ?>
              <section class="tagPickerTeamTable" data-team-table="<?= h($teamId) ?>">
                <h4><?= h($teamName) ?></h4>
                <?php if (isset($tagPickerTeamTags[$teamId])): ?><div class="tagList tagPickerTeamList"><?php $renderTagChoice($tagPickerTeamTags[$teamId], ['data-team-filter' => $teamId]); ?></div><?php endif; ?>
                <?php if ($categories): ?>
                  <section class="tagPickerPlayerTeam" data-player-team="<?= h($teamId) ?>"<?= in_array($teamId, $tagPickerSelectedTeamIds, true) ? '' : ' hidden' ?>>
                    <h5>選手を選ぶ</h5>
                    <?php foreach ($tagPickerCategoryLabels as $categoryKey => $categoryLabel): ?>
                      <?php if (empty($categories[$categoryKey])) continue; ?>
                      <?php usort($categories[$categoryKey], static function (array $left, array $right): int {
                        $leftNumber = trim((string)($left['uniform_number'] ?? ''));
                        $rightNumber = trim((string)($right['uniform_number'] ?? ''));
                        $leftGroup = $leftNumber === '' ? 2 : (mb_strlen($leftNumber) >= 3 ? 1 : 0);
                        $rightGroup = $rightNumber === '' ? 2 : (mb_strlen($rightNumber) >= 3 ? 1 : 0);
                        return [$leftGroup, $leftNumber === '' ? PHP_INT_MAX : (int)$leftNumber, $left['name']] <=> [$rightGroup, $rightNumber === '' ? PHP_INT_MAX : (int)$rightNumber, $right['name']];
                      }); ?>
                      <div class="tagPickerCategory" data-player-category="<?= h($categoryKey) ?>">
                        <h6><?= h($categoryLabel) ?></h6>
                        <div class="tagList"><?php foreach ($categories[$categoryKey] as $tag) $renderTagChoice($tag); ?></div>
                      </div>
                    <?php endforeach; ?>
                  </section>
                <?php endif; ?>
              </section>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
      <?php foreach ($tagPickerGroups as $groupKey => $group): ?>
        <?php if (!$group['tags']) continue; ?>
        <section class="tagPickerGroup tagPickerGroup-<?= h($groupKey) ?>">
          <h3><?= h($group['label']) ?></h3>
          <?php if ($groupKey === 'stadium'): ?>
            <?php $stadiumsByCategory = []; foreach ($group['tags'] as $tag) $stadiumsByCategory[(string)($tag['category'] ?? 'regional')][] = $tag; ?>
            <div class="tagPickerStadiumGroups">
              <?php foreach ($tagPickerStadiumLabels as $categoryKey => $categoryLabel): ?>
                <?php if (empty($stadiumsByCategory[$categoryKey])) continue; ?>
                <div class="tagPickerStadiumCategory" data-stadium-category="<?= h($categoryKey) ?>">
                  <h4><?= h($categoryLabel) ?></h4>
                  <div class="tagList"><?php foreach ($stadiumsByCategory[$categoryKey] as $tag) $renderTagChoice($tag); ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="tagList"><?php foreach ($group['tags'] as $tag) $renderTagChoice($tag); ?></div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</details>
