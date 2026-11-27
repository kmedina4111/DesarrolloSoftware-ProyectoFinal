<?php /* Uso: $title = '...'; $styles = ['layout', ...] (opcional, hojas extra de assets/css/); include .../src/modules/head.php; */ ?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> · Sendero Digital</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/favicon.svg">
    <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
    <!-- shortcut: Tailwind se compila en el navegador y exige este <style> inline (incumple AGENTS.md);
         al poder ejecutar `npm install && npm run build:css`, cambiar estas 2 líneas por:
         <link rel="stylesheet" href="/assets/css/app.build.css"> -->
    <?php if (in_array('layout', $styles ?? [], true)): ?><script src="/assets/js/sidebar-state.js"></script><?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss"><?php foreach (['app', ...($styles ?? [])] as $css) include __DIR__ . "/../../assets/css/$css.css"; ?></style>
</head>
