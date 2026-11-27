// Menú lateral. Un solo botón (#sidebar-toggle):
// - escritorio: contrae / expande el menú y recuerda el estado entre páginas
// - móvil: cierra el menú, que se abre con #sidebar-open (barra superior); Escape también lo cierra
(() => {
    const sidebar = document.getElementById('sidebar');
    const openBtn = document.getElementById('sidebar-open');
    const toggleBtn = document.getElementById('sidebar-toggle');
    if (!sidebar || !openBtn || !toggleBtn) return;

    const root = document.documentElement;
    const desktop = window.matchMedia('(min-width: 64rem)'); // mismo corte que lg: de Tailwind

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        openBtn.setAttribute('aria-expanded', String(open));
        (open ? toggleBtn : openBtn).focus();
    };

    const syncToggle = () => {
        const collapsed = root.classList.contains('sidebar-collapsed');
        const text = !desktop.matches ? 'Cerrar menú' : collapsed ? 'Expandir menú' : 'Contraer menú';
        toggleBtn.setAttribute('aria-expanded', String(desktop.matches ? !collapsed : sidebar.classList.contains('is-open')));
        toggleBtn.setAttribute('aria-label', text);
        toggleBtn.title = text;
    };

    openBtn.addEventListener('click', () => { setOpen(true); syncToggle(); });

    toggleBtn.addEventListener('click', () => {
        if (!desktop.matches) {
            setOpen(false);
        } else {
            const collapsed = root.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) { /* sin persistencia */ }
        }
        syncToggle();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) setOpen(false);
    });

    desktop.addEventListener('change', syncToggle);
    syncToggle();
})();
