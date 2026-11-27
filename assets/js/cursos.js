// Cursos: filtros automáticos, formulario lateral (nuevo / editar) y confirmación de desactivar / eliminar
(() => {
    // 1. Los filtros se aplican al cambiar un select
    const filtros = document.getElementById('filtros');
    filtros?.querySelectorAll('select').forEach((el) => {
        el.addEventListener('change', () => filtros.requestSubmit());
    });

    // 2. Formulario lateral
    const form = document.getElementById('curso-form');
    const title = document.getElementById('curso-form-title');
    const label = form.querySelector('.btn-label');

    const selectRow = (tr) => {
        document.querySelectorAll('tr.is-selected').forEach((r) => r.classList.remove('is-selected'));
        tr?.classList.add('is-selected');
    };

    // Después de un reset el formulario vuelve a "nuevo" (los hidden y el switch recuperan su valor original)
    form.addEventListener('reset', () => {
        title.textContent = 'Nuevo curso';
        label.textContent = 'Crear curso';
        selectRow(null);
    });

    document.getElementById('nuevo-curso').addEventListener('click', () => {
        form.reset();
        form.elements.nombre.focus();
    });

    // 3. Acciones de cada fila (los datos vienen en data-curso)
    const dialog = document.getElementById('quitar-dialog');
    const dialogBadge = document.getElementById('quitar-matriculas');
    const dialogText = document.getElementById('quitar-texto');
    const btnDesactivar = dialog.querySelector('button[value="desactivar"]');
    const btnEliminar = dialog.querySelector('button[value="eliminar"]');

    document.getElementById('cursos-tabla')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const curso = JSON.parse(tr.dataset.curso);

        if (btn.dataset.accion === 'quitar') {
            // Con matrículas activas solo se puede desactivar; el servidor debe comprobarlo también
            const n = Number(curso.inscritos) || 0;
            dialog.querySelector('input[name="id"]').value = curso.id;
            dialogBadge.textContent = `Matrículas activas: ${n}`;
            dialogText.textContent = n
                ? `El curso ${curso.nombre} tiene ${n} matrículas activas; solo puede desactivarse.`
                : `El curso ${curso.nombre} no tiene matrículas activas; puede desactivarse o eliminarse.`;
            btnDesactivar.disabled = !curso.activo;
            btnEliminar.disabled = n > 0;
            dialog.showModal();
            return;
        }

        form.reset();
        ['id', 'nombre', 'descripcion', 'modalidad', 'semanas', 'costo', 'cupo'].forEach((k) => {
            form.elements[k].value = curso[k] ?? '';
        });
        form.elements.activo.checked = Boolean(curso.activo);
        form.elements.accion.value = 'editar';
        title.textContent = 'Editar curso';
        label.textContent = 'Guardar cambios';
        selectRow(tr);
        form.elements.nombre.focus();
    });
})();
