<?php

declare(strict_types=1);

/**
 * Páginas públicas: sempre tema claro (sem modo escuro).
 * Deve vir antes de qualquer CSS que dependa de data-bs-theme.
 */
?>
<script>
(function () {
    try {
        document.documentElement.setAttribute('data-theme', 'light');
        document.documentElement.setAttribute('data-bs-theme', 'light');
        document.documentElement.style.colorScheme = 'light';
    } catch (e) {}
})();
</script>
