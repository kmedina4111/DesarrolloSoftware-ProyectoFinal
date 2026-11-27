<?php
$sent = $_SERVER['REQUEST_METHOD'] === 'POST';
$email = $_POST['email'] ?? '';
?>
<!doctype html>
<html lang="es">
<?php $title = 'Recuperar contraseña'; include __DIR__ . '/../modules/head.php'; ?>
<body class="auth">
    <?php include __DIR__ . '/../modules/aurora.php'; ?>

    <main>
        <section>
            <?php
            $intro_title = 'Recupera el acceso a tu panel.';
            $intro_text = 'Escribe el correo de tu cuenta y te enviaremos un enlace para crear una nueva contraseña.';
            $points = [
                ['fi-rr-clock', 'El enlace es válido por 30 minutos'],
                ['fi-rr-inbox', 'Revisa también tu carpeta de spam'],
                ['fi-rr-headset', 'Si no recibes nada, escribe a soporte@senderodigital.com'],
            ];
            include __DIR__ . '/../modules/brand-panel.php';
            ?>

            <article class="form-panel">
                <h1>Recuperar contraseña</h1>
                <p class="subtitle">Te enviaremos las instrucciones a tu correo</p>

                <?php if ($sent): ?>
                    <!-- shortcut: no envía correo todavía; conectar con la tabla de usuarios y un mailer -->
                    <p class="alert" role="status">
                        <i class="fi fi-rr-check-circle" aria-hidden="true"></i>
                        <span>Si <strong><?= htmlspecialchars($email) ?></strong> está registrado, recibirás un enlace en unos minutos.</span>
                    </p>

                    <form method="post" action="">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                        <button type="submit">
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-label">Reenviar enlace</span>
                            <i class="fi fi-rr-refresh btn-icon" aria-hidden="true"></i>
                        </button>
                    </form>
                <?php else: ?>
                    <p id="form-error" class="alert-error" role="alert">
                        <i class="fi fi-rr-exclamation" aria-hidden="true"></i>
                        <span id="form-error-text"></span>
                    </p>

                    <form method="post" action="" novalidate>
                        <label for="email">Correo electrónico</label>
                        <div class="field">
                            <i class="fi fi-rr-envelope" aria-hidden="true"></i>
                            <input id="email" name="email" type="email" placeholder="admin@senderodigital.com" autocomplete="email" required>
                        </div>

                        <button type="submit">
                            <span class="spinner" aria-hidden="true"></span>
                            <span class="btn-label">Enviar enlace</span>
                            <i class="fi fi-rr-paper-plane btn-icon" aria-hidden="true"></i>
                        </button>
                    </form>
                <?php endif; ?>

                <a class="back" href="/index.php"><i class="fi fi-rr-arrow-left" aria-hidden="true"></i> Volver a iniciar sesión</a>
            </article>
        </section>
    </main>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/login.js"></script>
</body>
</html>
