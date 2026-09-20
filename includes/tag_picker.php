<?php
$tagPickerTags = $tagPickerTags ?? ($tags ?? []);
$tagPickerSelected = array_map('intval', $tagPickerSelected ?? []);
$tagPickerInputName = $tagPickerInputName ?? 'tag_ids[]';
$tagPickerLabelClass = $tagPickerLabelClass ?? 'tagChoice';
$tagPickerDeleteUrl = $tagPickerDeleteUrl ?? '';
$tagPickerCreatedIds = array_map('intval', $tagPickerCreatedIds ?? []);
$tagPickerEditToken = $tagPickerEditToken ?? '';
$tagPickerGroups = [
  'team' => ['label' => 'チームを選ぶ', 'tags' => []],
  'league' => ['label' => 'リーグ・大会を選ぶ', 'tags' => []],
  'stadium' => ['label' => '球場を選ぶ', 'tags' => []],
  'players' => ['label' => '選手を選ぶ', 'tags' => []],
  'custom' => ['label' => 'その他のタグを選ぶ', 'tags' => []],
];
foreach ($tagPickerTags as $tag) {
  $type = $tag['tag_type'] ?? 'custom';
  $group = $type === 'team' ? 'team' : (in_array($type, ['division', 'event'], true) ? 'league' : ($type === 'stadium' ? 'stadium' : ($type === 'player' ? 'players' : 'custom')));
  $tagPickerGroups[$group]['tags'][] = $tag;
}
$tagPickerCategoryLabels = [
  'pitcher' => '投手',
  'catcher' => '捕手',
  'infielder' => '内野手',
  'outfielder' => '外野手',
  'coach' => '監督・コーチ',
  'other' => 'その他',
];
$tagPickerSelectedTeamIds = [];
foreach (array_merge($tagPickerGroups['team']['tags'], $tagPickerGroups['players']['tags']) as $tag) {
  if (in_array((int)$tag['id'], $tagPickerSelected, true) && !empty($tag['team_id'])) {
    $tagPickerSelectedTeamIds[] = (string)$tag['team_id'];
  }
}
$tagPickerSelectedTeamIds = array_values(array_unique($tagPickerSelectedTeamIds));
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
      <?php foreach ($tagPickerGroups as $groupKey => $group): ?>
        <?php if (!$group['tags']) continue; ?>
        <section class="tagPickerGroup tagPickerGroup-<?= h($groupKey) ?>">
          <h3><?= h($group['label']) ?></h3>
          <?php if ($groupKey === 'team'): ?>
            <p class="tagPickerHint">チームを選ぶと、そのチームの選手一覧が表示されます。</p>
            <div class="tagList tagPickerTeamList">
              <?php foreach ($group['tags'] as $tag) $renderTagChoice($tag, ['data-team-filter' => (string)($tag['team_id'] ?? '')]); ?>
            </div>
          <?php elseif ($groupKey === 'players'): ?>
            <p class="tagPickerHint" data-player-team-hint<?= $tagPickerSelectedTeamIds ? ' hidden' : '' ?>>先にチームを選択してください。</p>
          <?php endif; ?>
          <?php if ($groupKey === 'players'): ?>
            <?php $playersByTeam = []; foreach ($group['tags'] as $tag) $playersByTeam[(string)($tag['team_id'] ?? '')][(string)($tag['category'] ?? 'other')][] = $tag; ?>
            <div class="tagPickerPlayerTeams" data-player-filter>
              <?php foreach ($playersByTeam as $teamId => $categories): ?>
                <?php $teamName = $categories[array_key_first($categories)][0]['team_name'] ?? '選手'; ?>
                <section class="tagPickerPlayerTeam" data-player-team="<?= h($teamId) ?>"<?= in_array($teamId, $tagPickerSelectedTeamIds, true) ? '' : ' hidden' ?>>
                  <h4><?= h($teamName) ?></h4>
                  <?php foreach ($tagPickerCategoryLabels as $categoryKey => $categoryLabel): ?>
                    <?php if (empty($categories[$categoryKey])) continue; ?>
                    <?php usort($categories[$categoryKey], static function (array $left, array $right): int {
                      $leftNumber = trim((string)($left['uniform_number'] ?? ''));
                      $rightNumber = trim((string)($right['uniform_number'] ?? ''));
                      $leftGroup = $leftNumber === '' ? 2 : (mb_strlen($leftNumber) >= 3 ? 1 : 0);
                      $rightGroup = $rightNumber === '' ? 2 : (mb_strlen($rightNumber) >= 3 ? 1 : 0);
                      return [$leftGroup, $leftNumber === '' ? PHP_INT_MAX : (int)$leftNumber, $left['name']] <=> [$rightGroup, $rightNumber === '' ? PHP_INT_MAX : (int)$rightNumber, $right['name']];
                    }); ?>
                    <div class="tagPickerCategory">
                      <h5><?= h($categoryLabel) ?></h5>
                      <div class="tagList"><?php foreach ($categories[$categoryKey] as $tag) $renderTagChoice($tag); ?></div>
                    </div>
                  <?php endforeach; ?>
                </section>
              <?php endforeach; ?>
            </div>
          <?php elseif ($groupKey !== 'team'): ?>
            <div class="tagList"><?php foreach ($group['tags'] as $tag) $renderTagChoice($tag); ?></div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</details>
