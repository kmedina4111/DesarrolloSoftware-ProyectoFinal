# Observaciones UX/UI: `index.php` y `src/pages/recuperar.php`

**Lo que está bien:** la jerarquía visual es clara, los `label` están bien asociados a sus campos, el `autocomplete` es correcto, hay aviso de Bloq Mayús, se respeta `prefers-reduced-motion` y el mensaje de recuperación es genérico (no revela qué correos existen).

Las observaciones están ordenadas de mayor a menor gravedad.

---

## 📏 Incumplimientos de `AGENTS.md`

### 0a. CSS inline en los `.php`
**Problema:** `AGENTS.md` prohíbe escribir CSS o JS inline en los `.php`, incluido `<style>`. Las dos páginas lo hacen: `index.php:11` y `recuperar.php:11` meten el contenido de `app.css` dentro de un `<style type="text/tailwindcss">` con `include`. Esto existe porque el CDN de Tailwind solo lee estilos inline.

**Solución:** compilar el CSS (ver punto 18) y enlazarlo con `<link>`. Así desaparecen tanto el `<style>` como el `<script>` del CDN:
```html
<link rel="stylesheet" href="/assets/css/app.build.css">
```

### 0b. Las rutas no coinciden con las que pide `AGENTS.md`
**Problema:** `AGENTS.md` dice `Assets/CSS/` y `Assets/JS/`, pero las carpetas reales son `assets/css/` y `assets/js/`. En macOS da igual, pero el contenedor Docker corre en Linux, donde mayúsculas y minúsculas importan: si alguien sigue `AGENTS.md` al pie de la letra, crea una segunda carpeta y los enlaces se rompen.

**Solución:** decidir un solo nombre. Lo más simple es corregir `AGENTS.md` a `assets/css/` y `assets/js/`, porque las páginas ya usan esas rutas.

---

## 🔴 Graves (afectan la tarea o la accesibilidad)

### 1. En móvil, el formulario queda debajo del panel de marketing
**Problema:** en una sola columna, el panel lila (`p-10`, más la intro, la lista y los tags) va primero, así que el usuario tiene que hacer scroll para llegar al login. Pasa en las dos páginas.

**Solución:** ocultar el panel en móvil (`assets/css/app.css`):
```css
article.brand-panel { @apply hidden md:flex justify-between gap-8 bg-lila/55 border-white/50; }
```
Otra opción, si se quiere conservar el panel: `order-first md:order-none` en `.form-panel`.

### 2. Contraste insuficiente (WCAG AA)
**Problema:** `ink-3 #868e96` sobre blanco da unos 3.3:1, y el texto pequeño necesita 4.5:1. Afecta a `.subtitle`, `.note` y a los íconos de los campos. El panel translúcido (`bg-white/72`) lo empeora. El placeholder `ink-4 #adb5bd` queda en unos 2:1.

**Solución:** usar `ink-2` para el texto secundario, oscurecer el placeholder y hacer más opaco el panel:
```css
.subtitle { @apply mt-1.5 mb-7 text-ink-2; }
.note     { @apply mt-6 text-xs text-center text-ink-2; }
.field > .fi { @apply ... text-ink-2 ...; }
.field input { @apply ... placeholder:text-ink-3 ...; }
article.form-panel { @apply justify-center bg-white/90 border-white/80; }
```

### 3. El botón queda bloqueado al volver atrás
**Problema:** al enviar, `login.js:87` deshabilita el botón y pone "Ingresando...". Si el usuario vuelve con el botón "atrás" del navegador, la página se restaura desde caché (bfcache) con el botón deshabilitado y no puede reintentar.

**Solución:** restablecer el botón en `pageshow` (`assets/js/login.js`):
```js
window.addEventListener('pageshow', () => {
    submitBtn.disabled = false;
    submitBtn.classList.remove('is-loading');
    if (submitLabel) submitLabel.textContent = 'Iniciar sesión';
});
```

### 4. Validación incorrecta en el login
**Problema:** `login.js:80` exige "al menos 4 caracteres". En un login eso no corresponde: la regla de longitud va al crear la contraseña. Además, el mensaje revela la política de contraseñas.

**Solución:** comprobar solo que el campo no esté vacío:
```js
if (passwordInput && passwordInput.value === '') {
    e.preventDefault();
    showError('Ingresa tu contraseña.');
    passwordInput.focus();
    return;
}
```

### 5. Los errores no se asocian al campo
**Problema:** el error sale en un banner arriba, pero el input no recibe `aria-invalid` ni `aria-describedby`, y su borde no cambia de color.

**Solución:** marcar el campo que falló y limpiar la marca al escribir:
```js
const showError = (msg, input) => {
    errorText.textContent = msg;
    errorBox.classList.add('is-visible');
    input.setAttribute('aria-invalid', 'true');
    input.setAttribute('aria-describedby', 'form-error');
};
const clearError = () => {
    errorBox.classList.remove('is-visible');
    [emailInput, passwordInput].forEach(i => i.removeAttribute('aria-invalid'));
};
```
```css
.field input[aria-invalid="true"] { @apply border-error bg-error-bg/40; }
```

---

## 🟠 Medios (consistencia y claridad)

### 6. Las dos páginas validan distinto
**Problema:** el login usa `novalidate` con un banner propio. Recuperar usa el globo nativo del navegador y no tiene estado de carga.

**Solución:** en `recuperar.php`, agregar `novalidate` al `<form>`, el mismo bloque `#form-error` que en el login y cargar el mismo script. Para eso `login.js` tiene que funcionar sin campo de contraseña; ya comprueba `if (passwordInput ...)`, así que basta con incluirlo y darle al botón un `<span class="btn-label">`.

### 7. Recuperar deja el formulario visible después del éxito
**Problema:** el mensaje verde aparece, pero el formulario sigue ahí con el campo vacío, y el usuario duda y vuelve a enviar.

**Solución:** mostrar el mensaje **en lugar** del formulario:
```php
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <p class="alert" role="status">
        <i class="fi fi-rr-check-circle" aria-hidden="true"></i>
        Si <strong><?= htmlspecialchars($_POST['email'] ?? '') ?></strong> está registrado, recibirás un enlace en unos minutos.
    </p>
    <form method="post" action="">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <button type="submit">Reenviar enlace</button>
    </form>
<?php else: ?>
    <!-- formulario actual -->
<?php endif; ?>
```

### 8. El login no muestra nada si falla
**Problema:** `action=""` envía los datos a la misma página y nada los procesa, así que la página se recarga sin mensaje y se pierde el correo escrito.

**Solución:** cuando se conecte la autenticación, devolver el error al mismo banner y conservar el correo:
```php
<input id="email" name="email" type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" ...>
<p id="form-error" class="alert-error <?= $error ? 'is-visible' : '' ?>" role="alert">
    <span id="form-error-text"><?= htmlspecialchars($error ?? '') ?></span>
</p>
```
Usar un mensaje genérico: "Correo o contraseña incorrectos" (sin decir cuál de los dos falló).

### 9. Textos que se contradicen
| Texto actual | Problema | Propuesta |
|---|---|---|
| "Contacta al administrador del sistema" (en el login de administrador) | Si el usuario es el administrador, ¿a quién contacta? | "¿Problemas para ingresar? Escribe a soporte@senderodigital.com" |
| "El correo con el que te registraste" | Un administrador no se registra | "Escribe el correo de tu cuenta" |
| `h2` "¿Olvidaste tu contraseña?" + `h1` "Recuperar contraseña" | Repetición | `h2`: "Recupera el acceso a tu panel" |
| "Inscripciones por curso en tiempo real" | Promete una función que no existe en los mockups | "Inscritos por curso de un vistazo" |

### 10. Los tags parecen botones
**Problema:** `.tags li` se eleva al pasar el mouse, y eso indica que se puede hacer clic, pero no hacen nada.

**Solución:** quitar el efecto hover:
```css
.tags li { @apply px-3 py-1.5 rounded-lg bg-white; }
```

### 11. Orden de encabezados
**Problema:** el `h2` del panel lila aparece en el DOM antes que el `h1`, y un lector de pantalla los lee al revés.

**Solución:** convertir el `h2` en un párrafo con el mismo estilo:
```html
<p class="intro-title">Gestiona estudiantes, pagos, cursos y certificados en un solo lugar.</p>
```
```css
.intro-title { @apply text-[22px] font-bold leading-snug; }
```

---

## 🟡 Menores (accesibilidad y pulido)

| # | Problema | Solución |
|---|---|---|
| 12 | Los íconos `<i class="fi">` son decorativos y algunos lectores de pantalla leen el carácter de la fuente. | Agregar `aria-hidden="true"` a cada `<i class="fi ...">`. |
| 13 | El botón del ojo cambia `aria-label` **y** usa `aria-pressed`, así que el lector anuncia "Ocultar contraseña, presionado". | Dejar `aria-label="Mostrar contraseña"` fijo y conservar solo `aria-pressed`; borrar la línea `login.js:34`. |
| 14 | `role="alert"` y `aria-live="polite"` se contradicen (`index.php:53`). | Dejar solo `role="alert"`. |
| 15 | El aviso de Bloq Mayús está en rojo, pero es una advertencia, no un error. | Usar un tono ámbar: `text-[#b35c00]`. |
| 16 | El botón del ojo mide unos 26px y el checkbox y los enlaces usan texto de 13px. El mínimo recomendado en móvil es 44px. | `.toggle-pass { @apply p-2.5 ... }` y `py-2` en `.options label` y `.options a`. |
| 17 | "¿Olvidaste...?" va subrayado y "Volver a iniciar sesión" no; ningún enlace tiene `focus-visible` propio. | `a { @apply underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violeta rounded; }` |

---

## ⚡ Rendimiento (afecta la percepción)

### 18. CDN de Tailwind en el navegador
**Problema:** compila el CSS en tiempo real con JS. Eso produce un destello de página sin estilos al cargar, y si el CDN falla la página queda rota. La documentación de Tailwind dice que no es para producción. Lo mismo vale para los íconos de Flaticon.

**Solución:** compilar el CSS una sola vez con el CLI de Tailwind y servir el archivo generado:
```bash
npx @tailwindcss/cli -i assets/css/app.css -o assets/css/app.build.css --minify
```
```html
<link rel="stylesheet" href="assets/css/app.build.css">
```
`app.css` tiene que empezar con `@import "tailwindcss";`. Los íconos se pueden descargar y servir desde `assets/`. Este cambio también resuelve el punto 0a (el `<style>` inline).

### 19. El fondo animado consume mucho
**Problema:** son 5 orbes con `blur(80px)`, más `backdrop-blur` anidados, más un bucle `requestAnimationFrame` (`aurora.js:16-22`) que nunca se detiene, aunque el mouse esté quieto o la pestaña oculta.

**Solución:** pausar el bucle cuando el orbe ya llegó a su destino y reactivarlo al mover el mouse:
```js
let running = false;
function animate() {
    currentX += (targetX - currentX) * 0.08;
    currentY += (targetY - currentY) * 0.08;
    cursorOrb.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translate(-50%, -50%)`;
    running = Math.abs(targetX - currentX) + Math.abs(targetY - currentY) > 0.5;
    if (running) requestAnimationFrame(animate);
}
window.addEventListener('pointermove', (e) => {
    targetX = e.clientX; targetY = e.clientY;
    if (!running) { running = true; requestAnimationFrame(animate); }
}, { passive: true });
```
Además, quitar el `backdrop-blur-md` de `article`: el de `section` ya hace el efecto.

---

## 🧱 Mantenimiento

### 20. Código repetido entre páginas
**Problema:** el `<head>`, el fondo animado y el panel lila están copiados en las dos páginas, con rutas `../../` que se rompen fácilmente.

**Solución:** sacar el bloque repetido a parciales y usar rutas absolutas desde la raíz del sitio:
```php
<?php $title = 'Iniciar sesión'; include __DIR__ . '/src/modules/head.php'; ?>
```
```html
<!-- dentro de head.php -->
<link rel="icon" href="/assets/favicon/favicon.svg">
<script src="/assets/js/aurora.js"></script>
```

---

> **Alcance:** no probé las páginas en un navegador real ni con lector de pantalla. Los valores de contraste están calculados sobre fondo blanco sólido; sobre el fondo de colores pueden ser algo peores.
