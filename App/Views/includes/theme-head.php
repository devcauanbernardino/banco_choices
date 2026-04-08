<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../config/public_url.php';
?>
<link rel="stylesheet" href="<?= htmlspecialchars(public_asset_url('assets/css/public-language-selector.css')) ?>">
<script>
(function(){try{var k='bancochoices-theme',t=localStorage.getItem(k);if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);document.documentElement.setAttribute('data-bs-theme',t);}}catch(e){}})();
(function(){try{if(localStorage.getItem('bancochoices-sidebar-collapsed')==='1')document.documentElement.classList.add('sidebar-collapsed');}catch(e){}})();
</script>
