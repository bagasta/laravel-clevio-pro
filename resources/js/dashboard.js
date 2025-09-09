function initDashboardFilters() {
  const searchInput = document.getElementById('agent-search');
  const typeSelect = document.getElementById('agent-type-filter');
  const memSelect = document.getElementById('agent-memory-filter');
  const globalSearch = document.getElementById('global-agent-search');
  const tbody = document.getElementById('agent-table-body');
  if (!tbody) return; // not on dashboard

  let emptyRowEl = null;

  const ensureEmptyRow = () => {
    if (emptyRowEl) return emptyRowEl;
    emptyRowEl = document.createElement('tr');
    emptyRowEl.dataset.dynamicEmpty = '1';
    const td = document.createElement('td');
    td.colSpan = 8;
    td.className = 'p-10 text-center text-gray-500';
    td.textContent = 'No matching agents.';
    emptyRowEl.appendChild(td);
    return emptyRowEl;
  };

  const filter = () => {
    const q = (searchInput?.value || '').toLowerCase().trim();
    const fType = (typeSelect?.value || '').toLowerCase().trim();
    const fMem = (memSelect?.value || '').toLowerCase().trim(); // '', 'on', 'off'
    let shown = 0;

    const rows = tbody.querySelectorAll('tr[data-agent-row]');
    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();
      const matchesQuery = !q || text.includes(q);

      // Memory: 5th cell contains On/Off badge
      let matchesMem = true;
      if (fMem) {
        const memCell = row.querySelector('td:nth-child(5)');
        const memText = (memCell?.textContent || '').toLowerCase();
        matchesMem = memText.includes(fMem);
      }

      // Type: 6th cell contains type badge
      let matchesType = true;
      if (fType) {
        const typeCell = row.querySelector('td:nth-child(6)');
        const typeText = (typeCell?.textContent || '').toLowerCase().trim();
        matchesType = typeText.includes(fType);
      }

      const show = matchesQuery && matchesMem && matchesType;
      row.classList.toggle('hidden', !show);
      if (show) shown++;
    });

    // Manage empty message
    const hasEmpty = !!tbody.querySelector('tr[data-dynamic-empty]');
    if (shown === 0) {
      if (!hasEmpty) tbody.appendChild(ensureEmptyRow());
    } else if (hasEmpty) {
      tbody.querySelector('tr[data-dynamic-empty]')?.remove();
    }
  };

  // Keep nav search and page search in sync
  if (globalSearch && searchInput) {
    // One-time initial sync (prefer nav's non-empty value)
    if (globalSearch.value && globalSearch.value !== searchInput.value) {
      searchInput.value = globalSearch.value;
    } else if (searchInput.value && !globalSearch.value) {
      globalSearch.value = searchInput.value;
    }

    const syncFromGlobal = () => { searchInput.value = globalSearch.value; filter(); };
    const syncFromLocal = () => { globalSearch.value = searchInput.value; };
    globalSearch.addEventListener('input', syncFromGlobal);
    searchInput.addEventListener('input', () => { syncFromLocal(); filter(); });
  } else {
    searchInput?.addEventListener('input', filter);
  }
  typeSelect?.addEventListener('change', filter);
  memSelect?.addEventListener('change', filter);

  // Initial pass (in case of prefilled values)
  filter();
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initDashboardFilters);
} else {
  initDashboardFilters();
}
