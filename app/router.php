<?php
// public/router.php
//Ruta api https://personaltaskphp.up.railway.app
if (php_sapi_name() == 'cli-server') {
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false; // Deja que PHP sirva archivos estáticos
    }
}

require __DIR__ . '/index.php'; // Redirige todo a Slim
?>