// Certificación: filtros automáticos, formulario lateral (emitir / regenerar) con requisitos y confirmación de anulación
(() => {
    // 1. Los filtros se aplican al cambiar un select
    const filtros = document.getElementById('filtros');
    filtros?.querySelectorAll('select').forEach((el) => {
        el.addEventListener('change', () => filtros.requestSubmit());
    });

    // 2. Formulario lateral
    const form = document.getElementById('certificado-form');
    const title = document.getElementById('certificado-form-title');
    const sub = document.getElementById('certificado-form-sub');
    const label = form.querySelector('.btn-label');
    const submit = form.querySelector('button[type="submit"]');
    const preview = document.getElementById('codigo-preview');
    const previewText = preview.textContent;
    const reqPago = document.getElementById('req-pago');
    const reqAprobado = document.getElementById('req-aprobado');
    const requisitos = JSON.parse(form.dataset.requisitos || '{}');

    const setReq = (el, texto, valor) => {
        el.textContent = `${texto}: ${valor === undefined ? '—' : valor ? 'Sí' : 'No'}`;
        el.classList.toggle('badge-si', valor === true);
        el.classList.toggle('badge-no', valor === false);
    };

    // Solo se puede emitir con pago validado y curso aprobado (el servidor debe comprobarlo también)
    const checkRequisitos = () => {
        const r = requisitos[`${form.elements.estudiante_id.value}-${form.elements.curso_id.value}`];
        setReq(reqPago, 'Pago validado', r?.pago);
        setReq(reqAprobado, 'Curso aprobado', r?.aprobado);
        submit.disabled = !(r?.pago && r?.aprobado);
    };

    form.elements.estudiante_id.addEventListener('change', checkRequisitos);
    form.elements.curso_id.addEventListener('change', checkRequisitos);

    const selectRow = (tr) => {
        document.querySelectorAll('tr.is-selected').forEach((r) => r.classList.remove('is-selected'));
        tr?.classList.add('is-selected');
    };

    // Después de un reset el formulario vuelve a "emitir" (los campos recuperan su valor original)
    form.addEventListener('reset', () => {
        title.textContent = 'Emitir / editar certificado';
        sub.textContent = 'Requiere pago validado y curso aprobado';
        label.textContent = 'Emitir certificado';
        preview.textContent = previewText;
        selectRow(null);
        setTimeout(checkRequisitos); // los selects se restablecen después del evento reset
    });

    document.getElementById('nuevo-certificado').addEventListener('click', () => {
        form.reset();
        form.elements.estudiante_id.focus();
    });

    // 3. Acciones de cada fila (los datos vienen en data-certificado)
    const dialog = document.getElementById('anular-dialog');
    const dialogText = document.getElementById('anular-texto');

    document.getElementById('certificados-tabla')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-accion]');
        if (!btn) return;
        const tr = btn.closest('tr');
        const cert = JSON.parse(tr.dataset.certificado);

        if (btn.dataset.accion === 'anular') {
            dialog.querySelector('input[name="id"]').value = cert.id;
            dialogText.textContent = `Se revocará el certificado ${cert.codigo} de ${cert.estudiante}.`;
            dialog.showModal();
            return;
        }

        // Regenerar: mismo estudiante y curso, nueva fecha de emisión
        form.reset();
        setTimeout(() => {
            form.elements.id.value = cert.id;
            form.elements.estudiante_id.value = cert.estudiante_id;
            form.elements.curso_id.value = cert.curso_id;
            form.elements.accion.value = 'regenerar';
            title.textContent = 'Regenerar certificado';
            sub.textContent = `Certificado seleccionado: ${cert.codigo}`;
            label.textContent = 'Regenerar certificado';
            preview.textContent = cert.codigo;
            selectRow(tr);
            checkRequisitos();
            form.elements.fecha.focus();
        });
    });
})();
