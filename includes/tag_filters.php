<?php
declare(strict_types=1);

function selectedTagIds(): array
{
  $raw = $_GET['tags'] ?? (isset($_GET['tag']) ? [$_GET['tag']] : []);
  return array_slice(array_values(array_unique(array_filter(array_map('intval', (array)$raw), fn($id) => $id > 0))), 0, 20);
}

function renderTagFilter(array $tags, array $selected, string $action, array $hidden = [], string $hash = ''): void
{
  ?>
  <form method="get" action="<?= h($action) ?>" class="tagFilterForm" data-tag-filter data-tag-filter-hash="<?= h($hash) ?>">
    <?php foreach ($hidden as $key => $value): ?>
      <?php if (!is_array($value)): ?><input type="hidden" name="<?= h($key) ?>" value="<?= h((string)$value) ?>"><?php endif; ?>
    <?php endforeach; ?>
    <p class="accountHelp">選んだタグをすべて含む写真で絞り込み</p>
    <div class="projectTagList">
      <?php foreach ($tags as $tag): ?>
        <label class="tagChoice" style="<?= h(profileTagStyle($tag)) ?>"><input type="checkbox" name="tags[]" value="<?= (int)$tag['id'] ?>" <?= in_array((int)$tag['id'], $selected, true) ? 'checked' : '' ?>><span>#<?= h($tag['name']) ?></span><?php if (isset($tag['use_count'])): ?><span class="tagUseCount"><?= (int)$tag['use_count'] ?></span><?php endif; ?></label>
      <?php endforeach; ?>
    </div>
    <div class="actionRow"><a href="<?= h($action . (str_contains($action, '?') ? '&' : '?') . http_build_query($hidden) . $hash) ?>">絞り込みを解除</a></div>
  </form>
  <?php
}
