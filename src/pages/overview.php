<?php
// shortcut: sin sesión ni base de datos todavía; todos los datos quedan vacíos hasta conectar src/connection/connection.php
$periodos = ['mes' => 'Este mes', 'hoy' => 'Hoy', 'semana' => 'Semana', 'anio' => 'Año'];
$periodo = $_GET['periodo'] ?? 'mes';
if (!is_string($periodo) || !isset($periodos[$periodo])) $periodo = 'mes';

// [etiqueta, icono, valor, nota]: valor y nota en null = sin datos
$stats = [
    ['Estudiantes registrados', 'fi-rr-users', null, null],
    ['Pagos validados', 'fi-rr-credit-card', null, null],
    ['Cursos activos', 'fi-rr-book-alt', null, null],
    ['Certificados emitidos', 'fi-rr-diploma', null, null],
];
$pagos_pendientes = []; // [['cliente' => '', 'curso' => '', 'monto' => 0.0], ...]
$actividad = [];        // [['tipo' => 'estudiante|pago|certificado|curso', 'texto' => '', 'hace' => ''], ...]
$cursos = [];           // [['nombre' => '', 'inscritos' => 0, 'cupo' => 0], ...]
?>
<!doctype html>
<html lang="es">
<?php $title = 'Dashboard'; $styles = ['layout', 'overview']; include __DIR__ . '/../modules/head.php'; ?>
<body class="dashboard">
    <div class="aurora" aria-hidden="true">
        <span class="orb orb-a"></span>
        <span class="orb orb-b"></span>
        <span class="orb orb-c"></span>
        <span class="orb orb-d"></span>
        <span id="orb-cursor" class="orb orb-cursor"></span>
    </div>

    <div class="shell">
        <?php $active = 'dashboard'; include __DIR__ . '/../modules/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/../modules/topbar.php'; ?>

            <div class="page-head">
                <h1>Dashboard</h1>
                <nav class="tabs" aria-label="Periodo">
                    <?php foreach ($periodos as $key => $label): ?>
                        <a href="?periodo=<?= $key ?>" <?= $key === $periodo ? 'aria-current="true"' : '' ?>><?= $label ?></a>
                    <?php endforeach; ?>
                </nav>
                <div class="page-actions">
                    <button type="button" class="btn"><i class="fi fi-rr-filter" aria-hidden="true"></i> Filtro</button>
                    <a class="btn btn-dark" href="/src/pages/pagos.php"><i class="fi fi-rr-plus" aria-hidden="true"></i> Registrar pago</a>
                </div>
            </div>

            <section class="stats" aria-label="Resumen">
                <?php foreach ($stats as [$label, $icon, $value, $note]): ?>
                    <article class="stat">
                        <span class="stat-icon" aria-hidden="true"><i class="fi <?= $icon ?>"></i></span>
                        <p class="stat-label"><?= $label ?></p>
                        <p class="stat-value"><?= $value === null ? '—' : htmlspecialchars($value) ?></p>
                        <?php if ($note): ?><p class="stat-note"><?= htmlspecialchars($note) ?></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>

            <div class="panels">
                <section class="panel">
                    <div class="panel-head">
                        <h2>Pagos por validar</h2>
                        <a href="/src/pages/pagos.php">Ver todos</a>
                    </div>
                    <?php if ($pagos_pendientes): ?>
                        <table class="table">
                            <thead><tr><th>Cliente</th><th>Curso</th><th>Monto</th><th>Estado</th></tr></thead>
                            <tbody>
                                <?php foreach ($pagos_pendientes as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['cliente']) ?></td>
                                        <td class="muted"><?= htmlspecialchars($p['curso']) ?></td>
                                        <td>S/ <?= number_format($p['monto'], 2) ?></td>
                                        <td><span class="badge badge-pendiente">Pendiente</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="empty"><i class="fi fi-rr-credit-card" aria-hidden="true"></i> No hay pagos por validar</p>
                    <?php endif; ?>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h2>Actividad reciente</h2>
                    </div>
                    <?php if ($actividad): ?>
                        <ul class="activity">
                            <?php foreach ($actividad as $a): ?>
                                <li data-tipo="<?= htmlspecialchars($a['tipo']) ?>">
                                    <p><?= htmlspecialchars($a['texto']) ?></p>
                                    <p class="muted"><?= htmlspecialchars($a['hace']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty"><i class="fi fi-rr-time-past" aria-hidden="true"></i> Sin actividad reciente</p>
                    <?php endif; ?>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h2>Estado de cursos</h2>
                        <a href="/src/pages/cursos.php">Ver cursos</a>
                    </div>
                    <?php if ($cursos): ?>
                        <ul class="courses">
                            <?php foreach ($cursos as $c): ?>
                                <li>
                                    <span><?= htmlspecialchars($c['nombre']) ?></span>
                                    <progress value="<?= (int) $c['inscritos'] ?>" max="<?= max(1, (int) $c['cupo']) ?>" aria-label="Inscritos en <?= htmlspecialchars($c['nombre']) ?>"></progress>
                                    <span class="muted"><?= (int) $c['inscritos'] ?> inscritos</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="empty"><i class="fi fi-rr-book-alt" aria-hidden="true"></i> Aún no hay cursos registrados</p>
                    <?php endif; ?>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h2>Accesos rápidos</h2>
                    </div>
                    <nav class="quick" aria-label="Accesos rápidos">
                        <a href="/src/pages/clientes.php"><i class="fi fi-rr-plus" aria-hidden="true"></i> Nuevo estudiante</a>
                        <a href="/src/pages/pagos.php">Registrar pago</a>
                        <a href="/src/pages/cursos.php">Crear curso</a>
                        <a href="/src/pages/certificados.php">Emitir certificado</a>
                    </nav>
                </section>
            </div>
        </main>
    </div>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/sidebar.js"></script>
</body>
</html>
