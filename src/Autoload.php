<?php
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $prefix = '';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to next
        $relative_class = $class;
    } else {
        $relative_class = substr($class, $len);
    }
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});
?>
