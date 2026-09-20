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
  'players' => ['label' => '選手を選ぶ', 'tags' => []],
  'custom' => ['label' => 'その他のタグを選ぶ', 'tags' => []],
];
foreach ($tagPickerTags as $tag) {
  $type = $tag['tag_type'] ?? 'custom';
  $group = $type === 'team' ? 'team' : (in_array($type, ['division', 'event'], true) ? 'league' : ($type === 'player' ? 'players' : 'custom'));
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
$renderTagChoice = static function (array $tag) use ($tagPickerInputName, $tagPickerLabelClass, $tagPickerSelected, $tagPickerDeleteUrl, $tagPickerCreatedIds, $tagPickerEditToken): void {
  $tagId = (int)$tag['id'];
  $tagName = (string)$tag['name'];
  $isDeletable = $tagPickerDeleteUrl !== '' && in_array($tagId, $tagPickerCreatedIds, true);
  echo '<span class="tagManageItem">';
  echo '<label class="' . h($tagPickerLabelClass) . '" data-tag-reading="' . h($tag['reading'] ?? '') . '" style="' . h(profileTagStyle($tag)) . '">';
  echo '<input type="checkbox" name="' . h($tagPickerInputName) . '" value="' . $tagId . '"' . (in_array($tagId, $tagPickerSelected, true) ? ' checked' : '') . '>'; 
  echo '<span class="tag" style="' . h(profileTagStyle($tag)) . '">#' . h($tagName) . '</span></label>';
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
          <?php if ($groupKey === 'players'): ?>
            <?php $playersByTeam = []; foreach ($group['tags'] as $tag) $playersByTeam[$tag['team_name'] ?? '選手'][(string)($tag['category'] ?? 'other')][] = $tag; ?>
            <?php foreach ($playersByTeam as $teamName => $categories): ?>
              <h4><?= h($teamName) ?></h4>
              <?php foreach ($tagPickerCategoryLabels as $categoryKey => $categoryLabel): ?>
                <?php if (empty($categories[$categoryKey])) continue; ?>
                <div class="tagPickerCategory">
                  <h5><?= h($categoryLabel) ?></h5>
                  <div class="tagList"><?php foreach ($categories[$categoryKey] as $tag) $renderTagChoice($tag); ?></div>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="tagList"><?php foreach ($group['tags'] as $tag) $renderTagChoice($tag); ?></div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
  </div>
</details>
