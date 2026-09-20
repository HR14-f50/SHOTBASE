document.querySelectorAll('[data-tag-search]').forEach((search) => {
  const input = search.querySelector('[data-tag-search-input]');
  const section = search.parentElement;
  const list = section?.querySelector('[data-tag-list], .tagList, .tagCheckboxList, .projectTagList');
  const status = search.querySelector('[data-tag-search-status]');
  if (!input || !list) return;

  const getItems = () => [...list.querySelectorAll('label')].map((label) => ({
    label,
    item: label.closest('.tagManageItem') || label,
  }));

  const apply = () => {
    const query = input.value.trim().toLocaleLowerCase('ja-JP');
    const items = getItems();
    let visible = 0;
    items.forEach(({ label, item }) => {
      const text = `${label.textContent} ${label.dataset.tagReading || ''} ${label.dataset.tagNumber || ''}`.trim().toLocaleLowerCase('ja-JP');
      const matched = !query || text.includes(query);
      item.hidden = !matched;
      if (matched) visible += 1;
    });
    status.textContent = query ? `${visible}件のタグを表示中` : `${items.length}件のタグがあります`;
  };

  input.addEventListener('input', apply);
  list.addEventListener('taglistchange', apply);
  apply();
});
