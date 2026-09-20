document.querySelectorAll('[data-player-filter]').forEach((playerList) => {
  const list = playerList.closest('[data-tag-list]');
  if (!list) return;
  const hint = list.querySelector('[data-player-team-hint]');
  const panels = [...playerList.querySelectorAll('[data-player-team]')];
  const apply = () => {
    const selectedTeams = new Set(
      [...list.querySelectorAll('[data-team-filter] input:checked')]
        .map((input) => input.closest('[data-team-filter]')?.dataset.teamFilter || '')
        .filter(Boolean)
    );
    panels.forEach((panel) => {
      panel.hidden = !selectedTeams.has(panel.dataset.playerTeam || '');
    });
    if (hint) hint.hidden = selectedTeams.size > 0;
  };
  list.addEventListener('change', (event) => {
    if (event.target.matches('[data-team-filter] input')) apply();
  });
  apply();
});
