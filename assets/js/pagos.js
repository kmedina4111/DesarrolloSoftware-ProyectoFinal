// Pagos: filtros automáticos, formulario lateral (registrar / validar / editar) y confirmación de anulación
(() => {
    // 1. Los filtros se aplican al cambiar un select o una fecha
    const filtros = document.getElementById('filtros');
    filtros?.querySelectorAll('select, input[type="date"]').forEach((el) => {
        el.addEventListener('change', () => filtros.requestSubmit());
    });

    // 2. Formulario lateral
    const form = document.getElementById('pago-form');
    const sub = document.getElementById('pago-form-sub');
    const label = form.querySelector('.btn-label');
    const fileInput = document.getElementById('comprobante');
    const fileName = document.getElementById('comprobante-nombre');

    const selectRow = (tr) => {
        document.querySelectorAll('tr.is-selected').forEach((r) => r.classList.remove('is-selected'));
        tr?.classList.add('is-selected');
    };

    // Después de un reset el formulario vuelve a "nuevo pago" (los hidden recuperan su value="" original)
    form.addEventListener('reset', () => {
        sub.textContent = 'Nuevo pago';
        label.textContent = 'Registrar';
        fileName.textContent = 'Ningún archivo';
        selectRow(null);
    });

    fileInput.addEventListener('change', () => {
        fileName.textContent = fileInput.files[0]?.name ?? 'Ningún archivo';
    });

    document.getElementById('nuevo-pago').addEventListener('click', () => {
        form.reset();
        form.elements.cliente_id.focus();
    });

    // 3. Acciones de cada fila (los datos del pago vienen en data-pago)
    const dialog = document.getElementById('anular-dialog');
    const dialogText = document.getElementById('anular-texto');
    const soles = (n) => `S/ ${Number(n).toFixed(2)}`;

    document.getElementById('pagos-tabla')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const pago = JSON.parse(tr.dataset.pago);

        if (btn.dataset.accion === 'anular') {
            dialog.querySelector('input[name="id"]').value = pago.id;
            dialogText.textContent = `Se anulará el pago ${pago.ref} de ${pago.cliente} por ${soles(pago.monto)}.`;
            dialog.showModal();
            return;
        }

        form.reset();
        ['id', 'cliente_id', 'curso_id', 'monto', 'fecha', 'metodo'].forEach((k) => {
            form.elements[k].value = pago[k] ?? '';
        });
        form.elements.accion.value = btn.dataset.accion;
        sub.textContent = `Pago seleccionado: ${pago.ref}`;
        label.textContent = btn.dataset.accion === 'validar' ? 'Validar pago' : 'Guardar cambios';
        selectRow(tr);
        form.elements.cliente_id.focus();
    });
})();
