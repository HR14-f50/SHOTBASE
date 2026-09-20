<div class="tagSearch" data-tag-search>
  <label for="tagSearchInput<?= h((string)($tagSearchId ?? '')) ?>">タグを検索</label>
  <input
    type="search"
    id="tagSearchInput<?= h((string)($tagSearchId ?? '')) ?>"
    data-tag-search-input
    placeholder="タグ名・ふりがな・背番号を入力"
    autocomplete="off"
    enterkeyhint="search"
  >
  <p class="tagSearchStatus accountHelp" data-tag-search-status role="status" aria-live="polite"></p>
</div>
