<?php
spl_autoload_register(function ($className) {
    $file = dirname(__DIR__) . "\\" . strtolower($className) . '.php';
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
    if (file_exists($file)) {
        include $file;
    }
});
