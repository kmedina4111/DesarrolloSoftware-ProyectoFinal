<?php
// shortcut: sin base de datos todavía; las listas quedan vacías y los POST no se guardan hasta conectar src/connection/connection.php
$modalidades = ['presencial' => 'Presencial', 'virtual' => 'Virtual', 'hibrido' => 'Híbrido'];
$duraciones = ['corta' => 'Hasta 6 semanas', 'media' => '7 a 10 semanas', 'larga' => 'Más de 10 semanas'];
$costos = ['bajo' => 'Hasta S/ 300', 'medio' => 'S/ 300 a 500', 'alto' => 'Más de S/ 500'];
$estados = ['activo' => 'Activo', 'inactivo' => 'Inactivo'];
$cursos = []; // [['id' => 0, 'nombre' => '', 'descripcion' => '', 'modalidad' => 'virtual', 'semanas' => 0,
              //   'costo' => 0.0, 'inscritos' => 0, 'cupo' => 0, 'activo' => true], ...]
$conteo = ['activos' => null, 'inactivos' => null, 'inscritos' => null];
$total = 0;
$por_pagina = 10;

// Filtros de la URL: solo se aceptan textos (un ?estado[]=x no debe romper la página)
$filtro = fn(string $k): string => is_string($_GET[$k] ?? null) ? trim($_GET[$k]) : '';
$paginas = max(1, (int) ceil($total / $por_pagina));
$pagina = min($paginas, max(1, (int) $filtro('pagina')));

$aviso = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' ? 'Todavía no se guardan cursos: falta conectar la base de datos.' : null;
$opciones = function (array $lista, string $actual): string {
    $html = '';
    foreach ($lista as $k => $v) $html .= '<option value="' . $k . '"' . ($k === $actual ? ' selected' : '') . '>' . $v . '</option>';
    return $html;
};
?>
<!doctype html>
<html lang="es">
<?php $title = 'Cursos'; $styles = ['layout', 'crud', 'cursos']; include __DIR__ . '/../modules/head.php'; ?>
<body class="dashboard">
    <?php include __DIR__ . '/../modules/aurora.php'; ?>

    <div class="shell">
        <?php $active = 'cursos'; include __DIR__ . '/../modules/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/../modules/topbar.php'; ?>

            <div class="page-head">
                <div>
                    <h1>Gestión de Cursos</h1>
                    <ul class="counts" aria-label="Resumen de cursos">
                        <li class="badge badge-activo">Activos: <?= $conteo['activos'] ?? '—' ?></li>
                        <li class="badge">Inactivos: <?= $conteo['inactivos'] ?? '—' ?></li>
                        <li class="muted">Total inscritos: <?= $conteo['inscritos'] ?? '—' ?></li>
                    </ul>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn"><i class="fi fi-rr-download" aria-hidden="true"></i> Exportar reporte (PDF/Excel)</button>
                    <button id="nuevo-curso" type="button" class="btn btn-dark"><i class="fi fi-rr-plus" aria-hidden="true"></i> Nuevo curso</button>
                </div>
            </div>

            <?php if ($aviso): ?>
                <p class="notice" role="status"><i class="fi fi-rr-info" aria-hidden="true"></i> <?= $aviso ?></p>
            <?php endif; ?>

            <div class="crud">
                <div class="crud-main">
                    <form id="filtros" class="filters" method="get" role="search" aria-label="Filtrar cursos">
                        <div class="filter-search">
                            <i class="fi fi-rr-search" aria-hidden="true"></i>
                            <input type="search" name="q" value="<?= htmlspecialchars($filtro('q')) ?>" placeholder="Buscar por nombre del curso" aria-label="Buscar por nombre del curso">
                        </div>
                        <select name="modalidad" aria-label="Modalidad"><option value="">Modalidad</option><?= $opciones($modalidades, $filtro('modalidad')) ?></select>
                        <select name="duracion" aria-label="Duración"><option value="">Duración</option><?= $opciones($duraciones, $filtro('duracion')) ?></select>
                        <select name="costo" aria-label="Costo"><option value="">Costo</option><?= $opciones($costos, $filtro('costo')) ?></select>
                        <select name="estado" aria-label="Estado"><option value="">Estado: Todos</option><?= $opciones($estados, $filtro('estado')) ?></select>
                        <button type="submit" class="btn">Filtrar</button>
                    </form>

                    <section class="card" aria-label="Lista de cursos">
                        <?php if ($cursos): ?>
                            <div class="table-scroll">
                                <table class="table">
                                    <thead>
                                        <tr><th>Curso</th><th>Modalidad</th><th>Duración</th><th>Costo</th><th>Inscritos</th><th>Estado</th><th>Acciones</th></tr>
                                    </thead>
                                    <tbody id="cursos-tabla">
                                        <?php foreach ($cursos as $c): $estado = $c['activo'] ? 'activo' : 'inactivo'; ?>
                                            <tr data-curso="<?= htmlspecialchars(json_encode($c)) ?>">
                                                <td><?= htmlspecialchars($c['nombre']) ?><span class="sub"><?= htmlspecialchars($c['descripcion']) ?></span></td>
                                                <td class="muted"><?= $modalidades[$c['modalidad']] ?? htmlspecialchars($c['modalidad']) ?></td>
                                                <td class="muted"><?= (int) $c['semanas'] ?> semanas</td>
                                                <td>S/ <?= number_format($c['costo'], 2) ?></td>
                                                <td>
                                                    <?= (int) $c['inscritos'] ?> / <?= (int) $c['cupo'] ?>
                                                    <progress class="cupo" value="<?= (int) $c['inscritos'] ?>" max="<?= max(1, (int) $c['cupo']) ?>" aria-label="Cupo ocupado"></progress>
                                                </td>
                                                <td><span class="badge badge-<?= $estado ?>"><?= $estados[$estado] ?></span></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <a class="icon-btn" href="/src/pages/pagos.php?curso=<?= (int) $c['id'] ?>" aria-label="Ver pagos de <?= htmlspecialchars($c['nombre']) ?>"><i class="fi fi-rr-eye" aria-hidden="true"></i></a>
                                                        <button type="button" class="icon-btn" data-accion="editar" aria-label="Editar <?= htmlspecialchars($c['nombre']) ?>"><i class="fi fi-rr-pencil" aria-hidden="true"></i></button>
                                                        <button type="button" class="icon-btn" data-accion="quitar" aria-label="Desactivar o eliminar <?= htmlspecialchars($c['nombre']) ?>"><i class="fi fi-rr-cross-small" aria-hidden="true"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="empty"><i class="fi fi-rr-book-alt" aria-hidden="true"></i> No hay cursos registrados</p>
                        <?php endif; ?>

                        <?php $unidad = 'cursos'; include __DIR__ . '/../modules/pagination.php'; ?>
                    </section>
                </div>

                <aside class="card side-form" aria-labelledby="curso-form-title">
                    <h2 id="curso-form-title">Nuevo curso</h2>
                    <p class="muted">Formulario de creación / edición</p>

                    <form id="curso-form" class="form" method="post">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="accion" value="registrar">

                        <label for="nombre">Nombre del curso</label>
                        <input id="nombre" name="nombre" type="text" maxlength="120" placeholder="Ej. Marketing digital" required>

                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" maxlength="255" placeholder="Temas principales del curso"></textarea>

                        <div class="form-row">
                            <div>
                                <label for="modalidad">Modalidad</label>
                                <select id="modalidad" name="modalidad" required>
                                    <option value="">Elegir</option>
                                    <?= $opciones($modalidades, '') ?>
                                </select>
                            </div>
                            <div>
                                <label for="semanas">Duración (semanas)</label>
                                <input id="semanas" name="semanas" type="number" min="1" max="104" step="1" placeholder="8" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div>
                                <label for="costo">Costo (S/)</label>
                                <input id="costo" name="costo" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0.00" required>
                            </div>
                            <div>
                                <label for="cupo">Cupo máximo</label>
                                <input id="cupo" name="cupo" type="number" min="1" step="1" placeholder="30" required>
                            </div>
                        </div>

                        <label class="switch">
                            <input id="activo" name="activo" type="checkbox" value="1" checked>
                            <span>Activo</span>
                        </label>

                        <div class="form-actions">
                            <button type="reset" class="btn">Cancelar</button>
                            <button type="submit" class="btn btn-dark"><span class="btn-label">Crear curso</span></button>
                        </div>
                    </form>
                </aside>
            </div>
        </main>
    </div>

    <dialog id="quitar-dialog" class="confirm" aria-labelledby="quitar-title">
        <form method="post">
            <input type="hidden" name="id" value="">
            <div class="confirm-head">
                <span class="confirm-icon" aria-hidden="true">!</span>
                <h2 id="quitar-title">¿Desactivar curso?</h2>
            </div>
            <p id="quitar-matriculas" class="badge"></p>
            <p id="quitar-texto" class="muted"></p>
            <p class="sub">Eliminar lo borra de forma permanente.</p>
            <div class="confirm-actions">
                <button type="submit" formmethod="dialog" formnovalidate class="btn">Cancelar</button>
                <button type="submit" name="accion" value="desactivar" class="btn">Desactivar</button>
                <button type="submit" name="accion" value="eliminar" class="btn btn-dark">Eliminar</button>
            </div>
        </form>
    </dialog>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/sidebar.js"></script>
    <script src="/assets/js/cursos.js"></script>
</body>
</html>
