<?php
// shortcut: sin base de datos todavía; las listas quedan vacías y los POST no se guardan hasta conectar src/connection/connection.php
$estados = ['pendiente' => 'Pendiente', 'validado' => 'Validado', 'anulado' => 'Anulado'];
$metodos = ['transferencia' => 'Transferencia', 'yape' => 'Yape', 'plin' => 'Plin', 'tarjeta' => 'Tarjeta', 'efectivo' => 'Efectivo'];
$clientes = []; // [['id' => 0, 'nombre' => '', 'dni' => ''], ...]
$cursos = [];   // [['id' => 0, 'nombre' => ''], ...]
$pagos = [];    // [['id' => 0, 'ref' => 'REF-0001', 'cliente_id' => 0, 'cliente' => '', 'dni' => '', 'curso_id' => 0, 'curso' => '',
                //   'monto' => 0.0, 'metodo' => 'yape', 'fecha' => 'AAAA-MM-DD', 'comprobante' => '/ruta.pdf', 'estado' => 'pendiente'], ...]
$conteo = ['pendiente' => null, 'validado' => null, 'anulado' => null];
$total_mes = null;
$total = 0;
$por_pagina = 10;

// Filtros de la URL: solo se aceptan textos (un ?estado[]=x no debe romper la página)
$filtro = fn(string $k): string => is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$paginas = max(1, (int) ceil($total / $por_pagina));
$pagina = min($paginas, max(1, (int) $filtro('pagina')));

$aviso = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 'Todavía no se guardan pagos: falta conectar la base de datos.' : null;
$selected = fn(string $a, string $b): string => $a === $b ? 'selected' : '';
?>
<!doctype html>
<html lang="es">
<?php $title = 'Pagos'; $styles = ['layout', 'crud', 'pagos']; include __DIR__ . '/../modules/head.php'; ?>
<body class="dashboard">
    <div class="aurora" aria-hidden="true">
        <span class="orb orb-a"></span>
        <span class="orb orb-b"></span>
        <span class="orb orb-c"></span>
        <span class="orb orb-d"></span>
        <span id="orb-cursor" class="orb orb-cursor"></span>
    </div>

    <div class="shell">
        <?php $active = 'pagos'; include __DIR__ . '/../modules/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/../modules/topbar.php'; ?>

            <div class="page-head">
                <div>
                    <h1>Gestión de Pagos</h1>
                    <ul class="counts" aria-label="Resumen de pagos">
                        <li class="badge badge-pendiente">Pendientes: <?= $conteo['pendiente'] ?? '—' ?></li>
                        <li class="badge badge-validado">Validados: <?= $conteo['validado'] ?? '—' ?></li>
                        <li class="badge">Anulados: <?= $conteo['anulado'] ?? '—' ?></li>
                        <li class="muted">Total del mes: <?= $total_mes === null ? '—' : 'S/ ' . number_format($total_mes, 2) ?></li>
                    </ul>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn"><i class="fi fi-rr-download" aria-hidden="true"></i> Exportar reporte (PDF/Excel)</button>
                    <button id="nuevo-pago" type="button" class="btn btn-dark"><i class="fi fi-rr-plus" aria-hidden="true"></i> Registrar pago</button>
                </div>
            </div>

            <?php if ($aviso): ?>
                <p class="notice" role="status"><i class="fi fi-rr-info" aria-hidden="true"></i> <?= $aviso ?></p>
            <?php endif; ?>

            <div class="crud">
                <div class="crud-main">
                    <form id="filtros" class="filters" method="get" role="search" aria-label="Filtrar pagos">
                        <div class="filter-search">
                            <i class="fi fi-rr-search" aria-hidden="true"></i>
                            <input type="search" name="q" value="<?= htmlspecialchars($filtro('q')) ?>" placeholder="Buscar cliente, DNI o ref." aria-label="Buscar cliente, DNI o referencia">
                        </div>
                        <select name="estado" aria-label="Estado">
                            <option value="">Estado: Todos</option>
                            <?php foreach ($estados as $k => $v): ?><option value="<?= $k ?>" <?= $selected($k, $filtro('estado')) ?>><?= $v ?></option><?php endforeach; ?>
                        </select>
                        <select name="metodo" aria-label="Método de pago">
                            <option value="">Método de pago</option>
                            <?php foreach ($metodos as $k => $v): ?><option value="<?= $k ?>" <?= $selected($k, $filtro('metodo')) ?>><?= $v ?></option><?php endforeach; ?>
                        </select>
                        <select name="curso" aria-label="Curso">
                            <option value="">Curso</option>
                            <?php foreach ($cursos as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $selected((string) $c['id'], $filtro('curso')) ?>><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                        </select>
                        <fieldset class="filter-dates">
                            <legend class="sr-only">Rango de fechas</legend>
                            <input type="date" name="desde" value="<?= htmlspecialchars($filtro('desde')) ?>" aria-label="Desde">
                            <input type="date" name="hasta" value="<?= htmlspecialchars($filtro('hasta')) ?>" aria-label="Hasta">
                        </fieldset>
                        <button type="submit" class="btn">Filtrar</button>
                    </form>

                    <section class="card" aria-label="Lista de pagos">
                        <?php if ($pagos): ?>
                            <div class="table-scroll">
                                <table class="table">
                                    <thead>
                                        <tr><th>Cliente</th><th>Curso</th><th>Monto</th><th>Método de pago</th><th>Fecha</th><th>Comprobante</th><th>Estado</th><th>Acciones</th></tr>
                                    </thead>
                                    <tbody id="pagos-tabla">
                                        <?php foreach ($pagos as $p): ?>
                                            <tr data-pago="<?= htmlspecialchars(json_encode($p)) ?>">
                                                <td><?= htmlspecialchars($p['cliente']) ?><span class="sub">DNI <?= htmlspecialchars($p['dni']) ?></span></td>
                                                <td class="muted"><?= htmlspecialchars($p['curso']) ?></td>
                                                <td>S/ <?= number_format($p['monto'], 2) ?></td>
                                                <td class="muted"><?= htmlspecialchars($metodos[$p['metodo']] ?? $p['metodo']) ?></td>
                                                <td class="muted"><?= htmlspecialchars(date('d/m/Y', strtotime($p['fecha']))) ?></td>
                                                <td>
                                                    <?php if ($p['comprobante']): ?>
                                                        <a class="file-link" href="<?= htmlspecialchars($p['comprobante']) ?>" target="_blank" rel="noopener">
                                                            <i class="fi fi-rr-document" aria-hidden="true"></i> Ver <?= htmlspecialchars(strtoupper(pathinfo($p['comprobante'], PATHINFO_EXTENSION))) ?>
                                                        </a>
                                                    <?php else: ?>—<?php endif; ?>
                                                </td>
                                                <td><span class="badge badge-<?= htmlspecialchars($p['estado']) ?>"><?= $estados[$p['estado']] ?? htmlspecialchars($p['estado']) ?></span></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <button type="button" class="icon-btn" data-accion="validar" aria-label="Validar pago <?= htmlspecialchars($p['ref']) ?>" <?= $p['estado'] === 'pendiente' ? '' : 'disabled' ?>><i class="fi fi-rr-check" aria-hidden="true"></i></button>
                                                        <button type="button" class="icon-btn" data-accion="editar" aria-label="Editar pago <?= htmlspecialchars($p['ref']) ?>"><i class="fi fi-rr-pencil" aria-hidden="true"></i></button>
                                                        <button type="button" class="icon-btn" data-accion="anular" aria-label="Anular pago <?= htmlspecialchars($p['ref']) ?>" <?= $p['estado'] === 'anulado' ? 'disabled' : '' ?>><i class="fi fi-rr-ban" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="empty"><i class="fi fi-rr-credit-card" aria-hidden="true"></i> No hay pagos registrados</p>
                        <?php endif; ?>

                        <?php $unidad = 'pagos'; include __DIR__ . '/../modules/pagination.php'; ?>
                    </section>
                </div>

                <aside class="card side-form" aria-labelledby="pago-form-title">
                    <h2 id="pago-form-title">Registrar / validar pago</h2>
                    <p id="pago-form-sub" class="muted">Nuevo pago</p>

                    <form id="pago-form" class="form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="accion" value="registrar">

                        <label for="cliente_id">Cliente</label>
                        <select id="cliente_id" name="cliente_id" required>
                            <option value="">Selecciona un cliente</option>
                            <?php foreach ($clientes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre'] . ' - ' . $c['dni']) ?></option><?php endforeach; ?>
                        </select>

                        <label for="curso_id">Curso</label>
                        <select id="curso_id" name="curso_id" required>
                            <option value="">Selecciona un curso</option>
                            <?php foreach ($cursos as $c): ?><option value="<?= (int) $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
                        </select>

                        <div class="form-row">
                            <div>
                                <label for="monto">Monto (S/)</label>
                                <input id="monto" name="monto" type="number" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" required>
                            </div>
                            <div>
                                <label for="fecha">Fecha</label>
                                <input id="fecha" name="fecha" type="date" required>
                            </div>
                        </div>

                        <label for="metodo">Método de pago</label>
                        <select id="metodo" name="metodo" required>
                            <option value="">Selecciona un método</option>
                            <?php foreach ($metodos as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
                        </select>

                        <label for="comprobante">Comprobante</label>
                        <label class="file-drop" for="comprobante">
                            <i class="fi fi-rr-file-upload" aria-hidden="true"></i>
                            <span>
                                <span id="comprobante-nombre">Ningún archivo</span>
                                <span class="sub">Adjuntar o reemplazar archivo (PDF, JPG o PNG)</span>
                            </span>
                        </label>
                        <input id="comprobante" name="comprobante" type="file" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">

                        <div class="form-actions">
                            <button type="reset" class="btn">Cancelar</button>
                            <button type="submit" class="btn btn-dark"><span class="btn-label">Registrar</span></button>
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
                <h2 id="anular-title">¿Anular pago?</h2>
            </div>
            <p id="anular-texto" class="muted"></p>
            <p class="sub">Esta acción no se puede deshacer.</p>
            <div class="form-actions">
                <button type="submit" formmethod="dialog" formnovalidate class="btn">Cancelar</button>
                <button type="submit" class="btn btn-dark">Anular pago</button>
            </div>
        </form>
    </dialog>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/sidebar.js"></script>
    <script src="/assets/js/pagos.js"></script>
</body>
</html>
