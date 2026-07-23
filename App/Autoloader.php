<?php
/**
 * Autoloader PSR-4 Nativo em PHP para a estrutura App\
 * Não requer Composer nem alterações no pipeline de deploy FTP.
 */
date_default_timezone_set('America/Sao_Paulo');

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
