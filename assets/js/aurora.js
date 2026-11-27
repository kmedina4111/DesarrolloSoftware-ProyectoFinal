// Fondo aurora: el orbe #orb-cursor sigue al puntero con suavizado
(() => {
    const cursorOrb = document.getElementById('orb-cursor');
    if (!cursorOrb || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let targetX = window.innerWidth * 0.5;
    let targetY = window.innerHeight * 0.5;
    let currentX = targetX;
    let currentY = targetY;
    let running = false;

    // El bucle se detiene cuando el orbe llega al puntero y se reactiva al moverlo
    function animate() {
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;
        cursorOrb.style.transform = `translate3d(${currentX}px, ${currentY}px, 0) translate(-50%, -50%)`;
        running = Math.abs(targetX - currentX) + Math.abs(targetY - currentY) > 0.5;
        if (running) requestAnimationFrame(animate);
    }

    window.addEventListener('pointermove', (e) => {
        targetX = e.clientX;
        targetY = e.clientY;
        if (!running) {
            running = true;
            requestAnimationFrame(animate);
        }
    }, { passive: true });
})();
