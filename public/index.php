<?php
// public/index.php - Front controller

// Autoload (simple)
spl_autoload_register(function ($class) {
    $base = __DIR__ . '/../src/';
    $path = $base . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

// Simple routing based on 'page' query parameter
$page = $_GET['page'] ?? 'home';
$controllerClass = 'Controllers\\' . ucfirst($page) . 'Controller';
if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    $controller->handle();
} else {
    // fallback to HomeController
    $home = new Controllers\HomeController();
    $home->handle();
}
?>
