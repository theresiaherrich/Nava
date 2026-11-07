// Scoped A3 layout interactions. No changes to A1, A2, or A4.
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('app-sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const menuToggle = document.querySelector('[data-a3-menu-toggle]');
  if (!sidebar) return;
  const setOpen = (open) => {
    sidebar.classList.toggle('is-open', open);
    if (overlay) overlay.hidden = !open;
    if (menuToggle) {
      menuToggle.setAttribute('aria-expanded', String(open));
      menuToggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
    }
    document.body.classList.toggle('a3-drawer-open', open);
  };
  menuToggle?.addEventListener('click', () => setOpen(!sidebar.classList.contains('is-open')));
  overlay?.addEventListener('click', () => setOpen(false));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false);
  });
  window.matchMedia('(min-width: 769px)').addEventListener('change', (event) => { if(event.matches) setOpen(false); });
  sidebar.querySelectorAll('.sb-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const group = button.closest('.sb-group');
      const open = group?.classList.toggle('is-open') ?? false;
      button.setAttribute('aria-expanded', String(open));
    });
  });
  const input = document.getElementById('a3-menu-search');
  const results = document.getElementById('a3-search-results');
  const links = [...sidebar.querySelectorAll('.sb-sub[href], .sb-brand[href]')];
  if (input && results) {
    input.addEventListener('input', () => {
      const term = input.value.trim().toLocaleLowerCase('id');
      results.replaceChildren();
      if (term.length < 2) { results.hidden = true; return; }
      const matches = links.filter((link) => link.textContent.trim().toLocaleLowerCase('id').includes(term)).slice(0, 7);
      matches.forEach((link) => {
        const a = document.createElement('a');
        a.href = link.href;
        a.textContent = link.textContent.trim().replace(/\s+/g, ' ');
        results.appendChild(a);
      });
      if (!matches.length) { const p = document.createElement('p'); p.textContent = 'Menu tidak ditemukan atau belum tersedia.'; results.appendChild(p); }
      results.hidden = false;
    });
    document.addEventListener('click', (event) => {
      if (!event.target.closest('.a3-search')) results.hidden = true;
    });
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') { results.hidden = true; input.blur(); }
    });
  }
});
