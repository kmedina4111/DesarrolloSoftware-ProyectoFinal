<?php /* Requiere: $intro_title, $intro_text y $points = [['icono-flaticon', 'texto'], ...] */ ?>
<article class="brand-panel">
    <div class="brand">
        <span class="logo" aria-hidden="true"><i></i><i></i><i></i></span>
        Sendero Digital
    </div>

    <div class="intro">
        <p class="intro-title"><?= $intro_title ?></p>
        <p><?= $intro_text ?></p>
        <ul class="points">
            <?php foreach ($points as [$icon, $text]): ?>
                <li><i class="fi <?= $icon ?>" aria-hidden="true"></i> <?= $text ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <ul class="tags">
        <li>Estudiantes</li>
        <li>Pagos</li>
        <li>Cursos</li>
        <li>Certificados</li>
    </ul>
</article>
