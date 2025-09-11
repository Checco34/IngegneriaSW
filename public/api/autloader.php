<?php
// autoloader.php

spl_autoload_register(function ($class) {
    // 1. Logica per le classi del tuo progetto (App\)
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // 2. Logica per la libreria Firebase\JWT
    $prefix = 'Firebase\\JWT\\';
    $base_dir = __DIR__ . '/../../src/Lib/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
});