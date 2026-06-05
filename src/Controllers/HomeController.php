<?php
namespace Controllers;

class HomeController {
    public function handle() {
        // Initialize DB connection (global $conn already created via autoload)
        global $conn;
        // Load view
        include __DIR__ . '/../Views/home.php';
    }
}
?>
