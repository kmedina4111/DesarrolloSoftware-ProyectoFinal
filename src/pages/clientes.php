<?php
// shortcut: sin base de datos todavía; las listas quedan vacías y los POST no se guardan hasta conectar src/connection/connection.php
$estados = ['matriculado' => 'Matriculado', 'pendiente' => 'Pendiente', 'inactivo' => 'Inactivo'];
$estudiantes = []; // [['id' => 0, 'dni' => '', 'nombre' => '', 'correo' => '', 'celular' => '', 'estado' => 'matriculado', 'registro' => 'AAAA-MM-DD'], ...]
$conteo = ['registrados' => null, 'matriculado' => null, 'pendiente' => null, 'inactivo' => null];
$total = 0;
$por_pagina = 10;

// Filtros de la URL: solo se aceptan textos (un ?estado[]=x no debe romper la página)
$filtro = fn(string $k): string => is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$paginas = max(1, (int) ceil($total / $por_pagina));
$pagina = min($paginas, max(1, (int) $filtro('pagina')));

$aviso = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 'Todavía no se guardan estudiantes: falta conectar la base de datos.' : null;
$n = fn(?int $v): string => $v === null ? '—' : number_format($v);
?>
<!doctype html>
<html lang="es">
<?php $title = 'Estudiantes'; $styles = ['layout', 'crud']; include __DIR__ . '/../modules/head.php'; ?>
<body class="dashboard">
    <?php include __DIR__ . '/../modules/aurora.php'; ?>

    <div class="shell">
        <?php $active = 'clientes'; include __DIR__ . '/../modules/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/../modules/topbar.php'; ?>

            <div class="page-head">
                <div>
                    <h1>Gestión de Estudiantes / Clientes</h1>
                    <p class="muted">
                        <?= $n($conteo['registrados']) ?> registrados · <?= $n($conteo['matriculado']) ?> matriculados ·
                        <?= $n($conteo['pendiente']) ?> pendientes · <?= $n($conteo['inactivo']) ?> inactivos
                    </p>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn"><i class="fi fi-rr-download" aria-hidden="true"></i> Exportar reporte (PDF/Excel)</button>
                    <button id="nuevo-estudiante" type="button" class="btn btn-dark"><i class="fi fi-rr-plus" aria-hidden="true"></i> Nuevo estudiante</button>
                </div>
            </div>

            <?php if ($aviso): ?>
                <p class="notice" role="status"><i class="fi fi-rr-info" aria-hidden="true"></i> <?= $aviso ?></p>
            <?php endif; ?>

            <div class="crud">
                <div class="crud-main">
                    <form id="filtros" class="filters" method="get" role="search" aria-label="Filtrar estudiantes">
                        <div class="filter-search">
                            <i class="fi fi-rr-search" aria-hidden="true"></i>
                            <input type="search" name="q" value="<?= htmlspecialchars($filtro('q')) ?>" placeholder="Buscar por DNI o nombre completo" aria-label="Buscar por DNI o nombre completo">
                        </div>
                        <select name="estado" aria-label="Estado de matrícula">
                            <option value="">Estado: Todos</option>
                            <?php foreach ($estados as $k => $v): ?><option value="<?= $k ?>" <?= $k === $filtro('estado') ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                        </select>
                        <fieldset class="filter-dates">
                            <legend class="sr-only">Fecha de registro</legend>
                            <input type="date" name="desde" value="<?= htmlspecialchars($filtro('desde')) ?>" aria-label="Registrado desde">
                            <input type="date" name="hasta" value="<?= htmlspecialchars($filtro('hasta')) ?>" aria-label="Registrado hasta">
                        </fieldset>
                        <button type="submit" class="btn">Filtrar</button>
                    </form>

                    <section class="card" aria-label="Lista de estudiantes">
                        <?php if ($estudiantes): ?>
                            <div class="table-scroll">
                                <table class="table">
                                    <thead>
                                        <tr><th>DNI</th><th>Nombres completos</th><th>Correo</th><th>Teléfono celular</th><th>Estado de matrícula</th><th>Acciones</th></tr>
                                    </thead>
                                    <tbody id="estudiantes-tabla">
                                        <?php foreach ($estudiantes as $e): ?>
                                            <tr data-estudiante="<?= htmlspecialchars(json_encode($e)) ?>">
                                                <td class="muted"><?= htmlspecialchars($e['dni']) ?></td>
                                                <td><?= htmlspecialchars($e['nombre']) ?></td>
                                                <td class="muted"><?= htmlspecialchars($e['correo']) ?></td>
                                                <td class="muted"><?= htmlspecialchars($e['celular']) ?></td>
                                                <td><span class="badge badge-<?= htmlspecialchars($e['estado']) ?>"><?= $estados[$e['estado']] ?? htmlspecialchars($e['estado']) ?></span></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <a class="icon-btn" href="/src/pages/pagos.php?q=<?= urlencode($e['dni']) ?>" aria-label="Ver pagos de <?= htmlspecialchars($e['nombre']) ?>"><i class="fi fi-rr-eye" aria-hidden="true"></i></a>
                                                        <button type="button" class="icon-btn" data-accion="editar" aria-label="Editar a <?= htmlspecialchars($e['nombre']) ?>"><i class="fi fi-rr-pencil" aria-hidden="true"></i></button>
                                                        <button type="button" class="icon-btn" data-accion="eliminar" aria-label="Dar de baja a <?= htmlspecialchars($e['nombre']) ?>" <?= $e['estado'] === 'inactivo' ? 'disabled' : '' ?>><i class="fi fi-rr-cross-small" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="empty"><i class="fi fi-rr-users" aria-hidden="true"></i> No hay estudiantes registrados</p>
                        <?php endif; ?>

                        <?php $unidad = 'estudiantes'; include __DIR__ . '/../modules/pagination.php'; ?>
                    </section>
                </div>

                <aside class="card side-form" aria-labelledby="estudiante-form-title">
                    <h2 id="estudiante-form-title">Nuevo estudiante</h2>
                    <p class="muted">Formulario de registro / edición</p>

                    <form id="estudiante-form" class="form" method="post">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="accion" value="registrar">

                        <label for="nombre">Nombres completos</label>
                        <input id="nombre" name="nombre" type="text" maxlength="120" autocomplete="name" placeholder="Nombres y apellidos" required>

                        <label for="dni">DNI</label>
                        <input id="dni" name="dni" type="text" inputmode="numeric" pattern="\d{8}" maxlength="8" placeholder="8 dígitos" title="El DNI tiene 8 dígitos" required>

                        <label for="correo">Correo electrónico</label>
                        <input id="correo" name="correo" type="email" maxlength="120" autocomplete="email" placeholder="correo@ejemplo.com" required>

                        <label for="celular">Teléfono celular</label>
                        <input id="celular" name="celular" type="tel" inputmode="numeric" pattern="9\d{2} ?\d{3} ?\d{3}" maxlength="11" autocomplete="tel-national" placeholder="9XX XXX XXX" title="Celular de 9 dígitos que empieza con 9" required>

                        <div class="form-actions">
                            <button type="reset" class="btn">Cancelar</button>
                            <button type="submit" class="btn btn-dark"><span class="btn-label">Registrar</span></button>
                        </div>
                    </form>
                </aside>
            </div>
        </main>
    </div>

    <dialog id="eliminar-dialog" class="confirm" aria-labelledby="eliminar-title">
        <form method="post">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="">
            <div class="confirm-head">
                <span class="confirm-icon" aria-hidden="true">!</span>
                <h2 id="eliminar-title">¿Eliminar estudiante?</h2>
            </div>
            <p id="eliminar-texto" class="muted"></p>
            <p class="sub">Esta acción no se puede deshacer.</p>
            <div class="form-actions">
                <button type="submit" formmethod="dialog" formnovalidate class="btn">Cancelar</button>
                <button type="submit" class="btn btn-dark">Eliminar</button>
            </div>
        </form>
    </dialog>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/sidebar.js"></script>
    <script src="/assets/js/estudiantes.js"></script>
</body>
</html>
