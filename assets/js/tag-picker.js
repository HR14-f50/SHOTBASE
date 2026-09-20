document.querySelectorAll('[data-player-filter]').forEach((playerList) => {
  const list = playerList.closest('[data-tag-list]');
  if (!list) return;
  const hint = list.querySelector('[data-player-team-hint]');
  const panels = [...playerList.querySelectorAll('[data-player-team]')];
  const accordion = list.closest('.tagPickerAccordion');
  const searchInput = (list.closest('form') || list.parentElement)?.querySelector('[data-tag-search-input]');
  const panelHasVisibleMatch = (panel) => [...panel.querySelectorAll('label')].some((label) => {
    const item = label.closest('.tagManageItem') || label;
    return !label.hidden && !item.hidden;
  });
  const apply = () => {
    const query = searchInput?.value.trim() || '';
    const selectedTeams = new Set(
      [...list.querySelectorAll('[data-team-filter] input:checked')]
        .map((input) => input.closest('[data-team-filter]')?.dataset.teamFilter || '')
        .filter(Boolean)
    );
    if (query) {
      if (accordion) accordion.open = true;
      panels.forEach((panel) => {
        panel.hidden = !panelHasVisibleMatch(panel);
      });
      if (hint) {
        const hasMatch = panels.some((panel) => !panel.hidden);
        hint.hidden = hasMatch;
        hint.textContent = hasMatch ? '検索結果を表示しています。' : '一致する選手がいません。';
      }
      return;
    }
    panels.forEach((panel) => {
      panel.hidden = !selectedTeams.has(panel.dataset.playerTeam || '');
    });
    if (hint) {
      hint.hidden = selectedTeams.size > 0;
      hint.textContent = '先にチームを選択してください。';
    }
  };
  list.addEventListener('change', (event) => {
    if (event.target.matches('[data-team-filter] input')) apply();
  });
  searchInput?.addEventListener('input', apply);
  apply();
});
