<?php
/* Requiere: $total, $por_pagina, $pagina, $paginas (calculados con la página ya acotada a 1..$paginas) y $unidad ('pagos', 'estudiantes', ...).
   Los enlaces conservan los filtros actuales de la URL. */
$url_pagina = fn(int $n): string => htmlspecialchars('?' . http_build_query(['pagina' => $n] + $_GET));
?>
<div class="pagination">
    <p class="muted">Mostrando <?= $total ? (($pagina - 1) * $por_pagina + 1) . '-' . min($pagina * $por_pagina, $total) : 0 ?> de <?= number_format($total) ?> <?= $unidad ?></p>
    <nav aria-label="Paginación">
        <?php if ($pagina > 1): ?><a class="page" href="<?= $url_pagina($pagina - 1) ?>" aria-label="Página anterior">&lt;</a><?php else: ?><span class="page" aria-disabled="true">&lt;</span><?php endif; ?>
        <?php for ($n = max(1, $pagina - 1); $n <= min($paginas, $pagina + 1); $n++): ?>
            <a class="page" href="<?= $url_pagina($n) ?>" <?= $n === $pagina ? 'aria-current="page"' : '' ?>><?= $n ?></a>
        <?php endfor; ?>
        <?php if ($pagina < $paginas): ?><a class="page" href="<?= $url_pagina($pagina + 1) ?>" aria-label="Página siguiente">&gt;</a><?php else: ?><span class="page" aria-disabled="true">&gt;</span><?php endif; ?>
    </nav>
</div>
