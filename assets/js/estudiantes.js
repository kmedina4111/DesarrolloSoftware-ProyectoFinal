// Estudiantes: filtros automáticos, formulario lateral (nuevo / editar) y confirmación de baja
(() => {
    // 1. Los filtros se aplican al cambiar un select o una fecha
    const filtros = document.getElementById('filtros');
    filtros?.querySelectorAll('select, input[type="date"]').forEach((el) => {
        el.addEventListener('change', () => filtros.requestSubmit());
    });

    // 2. Formulario lateral
    const form = document.getElementById('estudiante-form');
    const title = document.getElementById('estudiante-form-title');
    const label = form.querySelector('.btn-label');

    const selectRow = (tr) => {
        document.querySelectorAll('tr.is-selected').forEach((r) => r.classList.remove('is-selected'));
        tr?.classList.add('is-selected');
    };

    // Después de un reset el formulario vuelve a "nuevo" (los hidden recuperan su value original)
    form.addEventListener('reset', () => {
        title.textContent = 'Nuevo estudiante';
        label.textContent = 'Registrar';
        selectRow(null);
    });

    document.getElementById('nuevo-estudiante').addEventListener('click', () => {
        form.reset();
        form.elements.nombre.focus();
    });

    // 3. Acciones de cada fila (los datos vienen en data-estudiante)
    const dialog = document.getElementById('eliminar-dialog');
    const dialogText = document.getElementById('eliminar-texto');

    document.getElementById('estudiantes-tabla')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const est = JSON.parse(tr.dataset.estudiante);

        if (btn.dataset.accion === 'eliminar') {
            dialog.querySelector('input[name="id"]').value = est.id;
            dialogText.textContent = `Se dará de baja a ${est.nombre}.`;
            dialog.showModal();
            return;
        }

        form.reset();
        ['id', 'nombre', 'dni', 'correo', 'celular'].forEach((k) => {
            form.elements[k].value = est[k] ?? '';
        });
        form.elements.accion.value = 'editar';
        title.textContent = 'Editar estudiante';
        label.textContent = 'Guardar cambios';
        selectRow(tr);
        form.elements.nombre.focus();
    });
})();
