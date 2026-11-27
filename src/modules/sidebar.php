<?php
/* Requiere: $active = clave del menú ('dashboard', 'clientes', 'pagos', 'cursos', 'certificados') */
// shortcut: usuario fijo hasta tener login; luego leerlo de la sesión
$usuario = ['nombre' => 'Administrador', 'rol' => 'Administrador del Sistema'];

// Un enlace null = sección fuera de alcance por ahora (se muestra deshabilitada)
$menu = [
    'Menú principal' => [
        'dashboard'    => ['Dashboard', 'fi-rr-apps', '/src/pages/overview.php'],
        'clientes'     => ['Estudiantes', 'fi-rr-users', '/src/pages/clientes.php'],
        'pagos'        => ['Pagos', 'fi-rr-credit-card', '/src/pages/pagos.php'],
        'cursos'       => ['Cursos', 'fi-rr-book-alt', '/src/pages/cursos.php'],
        'certificados' => ['Certificación', 'fi-rr-diploma', '/src/pages/certificados.php'],
        'analiticas'   => ['Analíticas', 'fi-rr-chart-histogram', null],
        'reportes'     => ['Reportes', 'fi-rr-document', null],
        'mensajes'     => ['Mensajes', 'fi-rr-envelope', null],
    ],
    'Soporte' => [
        'ayuda'   => ['Ayuda', 'fi-rr-interrogation', null],
        'ajustes' => ['Ajustes', 'fi-rr-settings', null],
    ],
];
?>
<aside id="sidebar" class="sidebar">
    <div class="sidebar-head">
        <div class="brand">
            <span class="logo" aria-hidden="true"><i></i><i></i><i></i></span>
            <span class="brand-name">Sendero Digital</span>
        </div>
        <button id="sidebar-toggle" type="button" class="sidebar-toggle" aria-label="Contraer menú" aria-controls="sidebar" aria-expanded="true" title="Contraer menú">
            <span class="sidebar-icon" aria-hidden="true"></span>
        </button>
    </div>

    <nav aria-label="Menú principal">
        <?php foreach ($menu as $group => $items): ?>
            <p class="nav-title"><?= $group ?></p>
            <ul class="nav">
                <?php foreach ($items as $key => [$label, $icon, $href]): ?>
                    <li>
                        <?php if ($href): ?>
                            <a href="<?= $href ?>" title="<?= $label ?>" <?= $key === $active ? 'aria-current="page"' : '' ?>>
                                <i class="fi <?= $icon ?>" aria-hidden="true"></i> <span class="nav-label"><?= $label ?></span>
                            </a>
                        <?php else: ?>
                            <span aria-disabled="true" title="<?= $label ?> (próximamente)">
                                <i class="fi <?= $icon ?>" aria-hidden="true"></i> <span class="nav-label"><?= $label ?></span>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </nav>

    <div class="account">
        <p class="nav-title">Cuenta</p>
        <div class="user">
            <span class="avatar" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
            <div class="user-info">
                <p class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></p>
                <p class="user-role"><?= htmlspecialchars($usuario['rol']) ?></p>
            </div>
        </div>
    </div>
</aside>
