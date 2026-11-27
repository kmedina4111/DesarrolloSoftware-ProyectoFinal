<?php
// shortcut: sin autenticación todavía; al conectarla, asignar aquí "Correo o contraseña incorrectos." si falla
$error = null;
$email = $_POST['email'] ?? '';
?>
<!doctype html>
<html lang="es">
<?php $title = 'Iniciar sesión'; include __DIR__ . '/src/modules/head.php'; ?>
<body class="auth">
    <?php include __DIR__ . '/src/modules/aurora.php'; ?>

    <main>
        <section>
            <?php
            $intro_title = 'Gestiona estudiantes, pagos, cursos y certificados en un solo lugar.';
            $intro_text = 'Registra nuevos estudiantes, valida sus pagos, organiza los cursos activos y emite certificados sin salir del panel.';
            $points = [
                ['fi-rr-check-circle', 'Seguimiento de pagos pendientes y validados'],
                ['fi-rr-check-circle', 'Inscritos por curso de un vistazo'],
                ['fi-rr-check-circle', 'Certificados listos para descargar'],
            ];
            include __DIR__ . '/src/modules/brand-panel.php';
            ?>

            <article class="form-panel">
                <h1>Iniciar sesión</h1>
                <p class="subtitle">Ingresa con tu cuenta de administrador</p>

                <p id="form-error" class="alert-error <?= $error ? 'is-visible' : '' ?>" role="alert">
                    <i class="fi fi-rr-exclamation" aria-hidden="true"></i>
                    <span id="form-error-text"><?= htmlspecialchars($error ?? '') ?></span>
                </p>

                <form method="post" action="" novalidate>
                    <label for="email">Correo electrónico</label>
                    <div class="field">
                        <i class="fi fi-rr-envelope" aria-hidden="true"></i>
                        <input id="email" name="email" type="email" value="<?= htmlspecialchars($email) ?>" placeholder="admin@senderodigital.com" autocomplete="username" required>
                    </div>

                    <label for="password">Contraseña</label>
                    <div class="field field-password">
                        <i class="fi fi-rr-lock" aria-hidden="true"></i>
                        <input id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
                        <button id="toggle-password" type="button" class="toggle-pass" aria-label="Mostrar contraseña" aria-pressed="false">
                            <i class="fi fi-rr-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <p id="caps-hint" class="caps-hint" role="status" aria-live="polite">
                        <i class="fi fi-rr-info" aria-hidden="true"></i> Bloq Mayús está activado
                    </p>

                    <div class="options">
                        <label><input type="checkbox" name="remember"> Recordarme</label>
                        <a href="/src/pages/recuperar.php">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit">
                        <span class="spinner" aria-hidden="true"></span>
                        <span class="btn-label">Iniciar sesión</span>
                        <i class="fi fi-rr-arrow-right btn-icon" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="note">¿Problemas para ingresar? Escribe a <a href="mailto:soporte@senderodigital.com">soporte@senderodigital.com</a></p>
            </article>
        </section>
    </main>

    <script src="/assets/js/aurora.js"></script>
    <script src="/assets/js/login.js"></script>
</body>
</html>
