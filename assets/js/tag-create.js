document.querySelectorAll('[data-tag-create]').forEach((area) => {
  const button = area.querySelector('button');
  const input = area.querySelector('input');
  const message = area.querySelector('[role="status"]');
  const form = area.closest('form');
  const list = form.querySelector('[data-tag-list]');
  const tagInputName = list.dataset.tagInputName || area.dataset.tagInputName || 'tag_ids[]';
  const makeDeleteButton = (tag) => {
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'tagDeleteButton';
    remove.dataset.deleteTag = tag.id;
    remove.dataset.tagName = tag.name;
    remove.setAttribute('aria-label', `${tag.name}を登録タグから削除`);
    remove.textContent = '×';
    return remove;
  };
  button.addEventListener('click', async () => {
    if (!input.value.trim() || !input.reportValidity()) {
      message.textContent = 'タグ名を入力してください。';
      return;
    }
    button.disabled = true;
    try {
      const body = new FormData();
      body.append('csrf', form.elements.csrf.value);
      if (form.elements.edit_token) body.append('edit_token', form.elements.edit_token.value);
      body.append('name', input.value.trim());
      const response = await fetch(area.dataset.tagCreate, { method: 'POST', body });
      const result = await response.json();
      if (!response.ok || result.error) throw new Error(result.error || 'タグを作成できませんでした。');
      const tags = result.tags || [result];
      tags.forEach((tag) => {
        let checkbox = [...list.querySelectorAll('input')].find((item) => item.value === String(tag.id));
        if (!checkbox) {
          const label = document.createElement('label');
          label.className = 'tagChoice';
          label.style.cssText = tag.style || '';
          checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = tagInputName;
          checkbox.value = String(tag.id);
          const text = document.createElement('span');
          text.textContent = '#' + tag.name;
          label.append(checkbox, text);
          if (list.dataset.tagDelete) {
            const wrapper = document.createElement('span');
            wrapper.className = 'tagManageItem';
            wrapper.append(label);
            if (tag.deletable) wrapper.append(makeDeleteButton(tag));
            list.append(wrapper);
          } else list.append(label);
        }
        if (list.querySelectorAll('input:checked').length < 10) checkbox.checked = true;
      });
      input.value = '';
      message.textContent = `${tags.length}個のタグを追加しました。写真には最大10件まで選択されます。`;
    } catch (error) {
      message.textContent = error.message;
    } finally {
      button.disabled = false;
    }
  });
  list.addEventListener('change', (event) => {
    if (list.querySelectorAll('input:checked').length > 10) {
      event.target.checked = false;
      message.textContent = '写真に付けられるタグは10件までです。';
    }
  });
  list.addEventListener('click', async (event) => {
    const remove = event.target.closest('[data-delete-tag]');
    if (!remove || !list.dataset.tagDelete) return;
    if (!confirm(`「${remove.dataset.tagName}」を登録タグから削除しますか？\n今回追加した未使用タグを削除します。`)) return;
    remove.disabled = true;
    try {
      const body = new FormData();
      body.append('csrf', form.elements.csrf.value);
      if (form.elements.edit_token) body.append('edit_token', form.elements.edit_token.value);
      body.append('id', remove.dataset.deleteTag);
      const response = await fetch(list.dataset.tagDelete, { method: 'POST', body });
      const result = await response.json();
      if (!response.ok || !result.deleted) throw new Error(result.error || 'タグを削除できませんでした。');
      remove.closest('.tagManageItem').remove();
      message.textContent = '登録タグを削除しました。';
      input.focus();
    } catch (error) {
      message.textContent = error.message;
      remove.disabled = false;
    }
  });
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.isComposing) {
      event.preventDefault();
      button.click();
    }
  });
});
