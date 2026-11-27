<?php /* Requiere: $title (última miga del breadcrumb) */ ?>
<header class="topbar">
    <button id="sidebar-open" type="button" class="icon-btn menu-btn" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
        <i class="fi fi-rr-menu-burger" aria-hidden="true"></i>
    </button>

    <nav class="breadcrumb" aria-label="Ruta">
        <ol>
            <li>Sendero Digital</li>
            <li aria-current="page"><?= htmlspecialchars($title) ?></li>
        </ol>
    </nav>

    <div class="topbar-actions">
        <form class="search" role="search" action="/src/pages/clientes.php" method="get">
            <i class="fi fi-rr-search" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Buscar estudiante o pago..." aria-label="Buscar estudiante o pago">
        </form>
        <button type="button" class="icon-btn" aria-label="Notificaciones">
            <i class="fi fi-rr-bell" aria-hidden="true"></i>
        </button>
        <span class="avatar avatar-sm" aria-hidden="true"><i class="fi fi-rr-user"></i></span>
    </div>
</header>
