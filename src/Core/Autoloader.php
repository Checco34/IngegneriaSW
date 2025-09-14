<?php
spl_autoload_register(function ($class) {
    $prefixMap = [
        'App\\' => __DIR__ . '/../',
        'Firebase\\JWT\\' => __DIR__ . '/../Lib/Firebase/JWT/'
    ];

    foreach ($prefixMap as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});