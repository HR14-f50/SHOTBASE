const header = document.querySelector('.headerReveal');
let previousScroll = window.scrollY;
let movement = 0;
window.addEventListener('scroll', () => {
  const current = Math.max(0, window.scrollY);
  const delta = current - previousScroll;
  movement = Math.sign(delta) === Math.sign(movement) ? movement + delta : delta;
  if (current < 50 || movement < -5) header?.classList.remove('isHeaderHidden');
  else if (movement > 15 && current > 100) header?.classList.add('isHeaderHidden');
  previousScroll = current;
}, {passive:true});

// タグの選択状態はその場でURLへ反映します。
document.querySelectorAll('[data-tag-filter]').forEach((form) => {
  form.addEventListener('change', (event) => {
    if (!(event.target instanceof HTMLInputElement) || event.target.name !== 'tags[]') return;
    const params = new URLSearchParams(new FormData(form));
    const url = new URL(form.action, window.location.href);
    url.search = params.toString();
    url.hash = form.dataset.tagFilterHash || '';
    window.location.assign(url.toString());
  });
});
const bulk = document.querySelector('[data-bulk-tags]');
if (bulk) {
  const photos = [...bulk.querySelectorAll('[name="photos[]"]')];
  const update = () => { bulk.querySelector('[data-photo-selection]').textContent = `${photos.filter(p=>p.checked).length}枚選択中`; };
  bulk.addEventListener('change', update);
  bulk.addEventListener('submit', (event) => {
    if (event.submitter?.value === 'bulk_delete' && !window.confirm('選択した写真を削除しますか？この操作は取り消せません。')) event.preventDefault();
  });
  bulk.querySelector('[data-select-photos]').addEventListener('click', () => {
    const checked = !photos.every(p=>p.checked);
    photos.forEach(p=>{p.checked=checked;});
    update();
  });
}
const picker = document.querySelector('[data-theme-picker]');
if (picker) {
  const choices = [...picker.querySelectorAll('[name="theme_key"]')];
  const status = picker.querySelector('[data-theme-status]');
  let saved = picker.dataset.savedTheme;
  let pending = null;
  let busy = false;
  const apply = (choice) => {
    picker.querySelectorAll('[name="theme_key"]').forEach((theme) => document.body.classList.remove(`theme-${theme.value}`));
    document.body.classList.add(`theme-${choice.value}`);
    document.body.style.setProperty('--profile-accent', choice.dataset.accent);
    document.body.style.setProperty('--profile-soft', choice.dataset.soft);
  };
  const save = async () => {
    if (busy) return;
    busy = true;
    while (pending) {
      const choice = pending;
      pending = null;
      status.textContent = 'テーマを保存中です…';
      try {
        const body = new FormData();
        body.append('csrf', picker.closest('form').elements.csrf.value);
        body.append('theme_key', choice.value);
        const response = await fetch(picker.dataset.themePicker, {method:'POST',body});
        const result = await response.json();
        if (!response.ok || !result.saved) throw new Error(result.error || '保存できませんでした。');
        saved = choice.value;
        status.textContent = 'テーマカラーを保存しました。';
      } catch (error) {
        if (!pending) {
          const previous = choices.find(c=>c.value===saved);
          previous.checked = true;
          apply(previous);
          status.textContent = `${error.message} 色を選び直して再試行してください。`;
        }
      }
    }
    busy = false;
  };
  picker.addEventListener('change', (event) => {
    if (event.target.name !== 'theme_key') return;
    apply(event.target);
    pending = event.target;
    save();
  });
  picker.closest('form').addEventListener('submit', (event) => {
    if (busy) { event.preventDefault(); status.textContent = 'テーマの保存完了後に、もう一度プロフィールを保存してください。'; }
  });
}
