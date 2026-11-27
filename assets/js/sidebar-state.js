// Se carga en <head>: aplica el estado guardado del menú antes de pintar la página (sin parpadeo)
try {
    document.documentElement.classList.toggle('sidebar-collapsed', localStorage.getItem('sidebar') === 'collapsed');
} catch (e) { /* localStorage bloqueado: el menú queda expandido */ }
