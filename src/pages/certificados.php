<?php
// shortcut: sin base de datos todavía; las listas quedan vacías y los POST no se guardan hasta conectar src/connection/connection.php
$estados = ['vigente' => 'Vigente', 'regenerado' => 'Regenerado', 'anulado' => 'Anulado'];
$periodos = ['mes' => 'Este mes', 'trimestre' => 'Últimos 3 meses', 'anio' => 'Este año'];
$estudiantes = []; // [['id' => 0, 'nombre' => '', 'dni' => ''], ...]
$cursos = [];      // [['id' => 0, 'nombre' => ''], ...]
// Requisitos para emitir, por par estudiante-curso: ['3-7' => ['pago' => true, 'aprobado' => false], ...]
$requisitos = [];
$certificados = []; // [['id' => 0, 'codigo' => 'SD-2026-0001', 'estudiante_id' => 0, 'estudiante' => '', 'dni' => '',
                    //   'curso_id' => 0, 'curso' => '', 'fecha' => 'AAAA-MM-DD', 'estado' => 'vigente', 'pdf' => '/ruta.pdf'], ...]
$conteo = ['emitidos' => null, 'vigente' => null, 'anulado' => null, 'mes' => null];
$siguiente_codigo = null; // ej. 'SD-2026-0146'
$total = 0;
$por_pagina = 10;

// Filtros de la URL: solo se aceptan textos (un ?estado[]=x no debe romper la página)
$filtro = fn(string $k): string => is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$paginas = max(1, (int) ceil($total / $por_pagina));
$pagina = min($paginas, max(1, (int) $filtro('pagina')));

$aviso = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 'Todavía no se emiten certificados: falta conectar la base de datos.' : null;
$opciones = function (array $lista, string $actual): string {
    $html = '';
    foreach ($lista as $k => $v) $html .= '<option value="' . htmlspecialchars($k) . '"' . ((string) $k === $actual ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
    return $html;
};
$lista_cursos = array_column($cursos, 'nombre', 'id');
?>
<!doctype html>
<html lang="es">
<?php $title = 'Certificación'; $styles = ['layout', 'crud', 'certificados']; include __DIR__ . '/../modules/head.php'; ?>
<body class="dashboard">
    <?php include __DIR__ . '/../modules/aurora.php'; ?>

    <div class="shell">
        <?php $active = 'certificados'; include __DIR__ . '/../modules/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/../modules/topbar.php'; ?>

            <div class="page-head">
                <div>
                    <h1>Gestión de Certificados</h1>
                    <ul class="counts" aria-label="Resumen de certificados">
                        <li class="badge badge-emitido">Emitidos: <?= $conteo['emitidos'] ?? '—' ?></li>
                        <li class="badge badge-vigente">Vigentes: <?= $conteo['vigente'] ?? '—' ?></li>
                        <li class="badge">Anulados: <?= $conteo['anulado'] ?? '—' ?></li>
                        <li class="muted">Este mes: <?= $conteo['mes'] ?? '—' ?> emitidos</li>
                    </ul>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn"><i class="fi fi-rr-download" aria-hidden="true"></i> Exportar reporte (PDF/Excel)</button>
                    <button id="nuevo-certificado" type="button" class="btn btn-dark"><i class="fi fi-rr-plus" aria-hidden="true"></i> Emitir certificado</button>
                </div>
            </div>

            <?php if ($aviso): ?>
                <p class="notice" role="status"><i class="fi fi-rr-info" aria-hidden="true"></i> <?= $aviso ?></p>
            <?php endif; ?>

            <div class="crud">
                <div class="crud-main">
                    <form id="filtros" class="filters" method="get" role="search" aria-label="Filtrar certificados">
                        <div class="filter-search">
                            <i class="fi fi-rr-search" aria-hidden="true"></i>
                            <input type="search" name="q" value="<?= htmlspecialchars($filtro('q')) ?>" placeholder="Buscar por código de validación o DNI" aria-label="Buscar por código de validación o DNI">
                        </div>
                        <select name="curso" aria-label="Curso"><option value="">Curso: Todos</option><?= $opciones($lista_cursos, $filtro('curso')) ?></select>
                        <select name="periodo" aria-label="Periodo de emisión"><option value="">Periodo de emisión</option><?= $opciones($periodos, $filtro('periodo')) ?></select>
                        <select name="estado" aria-label="Estado"><option value="">Estado: Todos</option><?= $opciones($estados, $filtro('estado')) ?></select>
                        <button type="submit" class="btn">Filtrar</button>
                    </form>

                    <section class="card" aria-label="Lista de certificados">
                        <?php if ($certificados): ?>
                            <div class="table-scroll">
                                <table class="table">
                                    <thead>
                                        <tr><th>Código</th><th>Estudiante / DNI</th><th>Curso</th><th>Fecha de emisión</th><th>Estado</th><th>Acciones</th></tr>
                                    </thead>
                                    <tbody id="certificados-tabla">
                                        <?php foreach ($certificados as $c): ?>
                                            <tr data-certificado="<?= htmlspecialchars(json_encode($c)) ?>">
                                                <td class="code"><?= htmlspecialchars($c['codigo']) ?></td>
                                                <td><?= htmlspecialchars($c['estudiante']) ?><span class="sub">DNI <?= htmlspecialchars($c['dni']) ?></span></td>
                                                <td class="muted"><?= htmlspecialchars($c['curso']) ?></td>
                                                <td class="muted"><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) ?></td>
                                                <td><span class="badge badge-<?= htmlspecialchars($c['estado']) ?>"><?= $estados[$c['estado']] ?? htmlspecialchars($c['estado']) ?></span></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <?php if ($c['pdf'] && $c['estado'] !== 'anulado'): ?>
                                                            <a class="icon-btn" href="<?= htmlspecialchars($c['pdf']) ?>" download aria-label="Descargar certificado <?= htmlspecialchars($c['codigo']) ?>"><i class="fi fi-rr-download" aria-hidden="true"></i></a>
                                                        <?php else: ?>
                                                            <button type="button" class="icon-btn" disabled aria-label="Descarga no disponible"><i class="fi fi-rr-download" aria-hidden="true"></i></button>
                                                        <?php endif; ?>
                                                        <button type="button" class="icon-btn" data-accion="regenerar" aria-label="Regenerar certificado <?= htmlspecialchars($c['codigo']) ?>" <?= $c['estado'] === 'anulado' ? 'disabled' : '' ?>><i class="fi fi-rr-refresh" aria-hidden="true"></i></button>
                                                        <button type="button" class="icon-btn" data-accion="anular" aria-label="Anular certificado <?= htmlspecialchars($c['codigo']) ?>" <?= $c['estado'] === 'anulado' ? 'disabled' : '' ?>><i class="fi fi-rr-ban" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="empty"><i class="fi fi-rr-diploma" aria-hidden="true"></i> No hay certificados emitidos</p>
                        <?php endif; ?>

                        <?php $unidad = 'certificados'; include __DIR__ . '/../modules/pagination.php'; ?>
                    </section>
                </div>

                <aside class="card side-form" aria-labelledby="certificado-form-title">
                    <h2 id="certificado-form-title">Emitir / editar certificado</h2>
                    <p id="certificado-form-sub" class="muted">Requiere pago validado y curso aprobado</p>

                    <form id="certificado-form" class="form" method="post" data-requisitos="<?= htmlspecialchars(json_encode((object) $requisitos)) ?>">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="accion" value="emitir">

                        <label for="estudiante_id">Estudiante</label>
                        <select id="estudiante_id" name="estudiante_id" required>
                            <option value="">Selecciona un estudiante</option>
                            <?php foreach ($estudiantes as $e): ?><option value="<?= (int) $e['id'] ?>"><?= htmlspecialchars($e['nombre'] . ' - ' . $e['dni']) ?></option><?php endforeach; ?>
                        </select>

                        <ul class="requisitos" aria-live="polite">
                            <li id="req-pago" class="badge">Pago validado: —</li>
                            <li id="req-aprobado" class="badge">Curso aprobado: —</li>
                        </ul>

                        <label for="curso_id">Curso</label>
                        <select id="curso_id" name="curso_id" required>
                            <option value="">Selecciona un curso</option>
                            <?php foreach ($cursos as $c): ?><option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                        </select>

                        <label for="fecha">Fecha de emisión</label>
                        <input id="fecha" name="fecha" type="date" value="<?= date('Y-m-d') ?>" required>

                        <p class="form-label">Código de validación (vista previa)</p>
                        <div class="code-preview">
                            <strong id="codigo-preview"><?= htmlspecialchars($siguiente_codigo ?? 'SD-AAAA-NNNN') ?></strong>
                            <span class="sub">Se genera al emitir el certificado</span>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn">Cancelar</button>
                            <button type="submit" class="btn btn-dark" disabled><span class="btn-label">Emitir certificado</span></button>
                        </div>
                    </form>
                </aside>
            </div>
        </main>
    </div>

    <dialog id="anular-dialog" class="confirm" aria-labelledby="anular-title">
        <form method="post">
            <input type="hidden" name="accion" value="anular">
            <input type="hidden" name="id" value="">
            <div class="confirm-head">
                <span class="confirm-icon" aria-hidden="true">!</span>
                <h2 id="anular-title">¿Anular certificado?</h2>
            </div>
            <p id="anular-texto" class="muted"></p>
            <p class="sub">El código dejará de ser válido.</p>
            <div class="form-actions">
                <button type="submit" formmethod="dialog" formnovalidate class="btn">Cancelar</button>
                <button type="submit" class="btn btn-dark">Anular certificado</button>
            </div>
        </form>
    </dialog>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/sidebar.js"></script>
    <script src="/assets/js/certificados.js"></script>
</body>
</html>
