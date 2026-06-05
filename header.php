<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Job Application Tracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body class="text-gray-800 transition-colors duration-300">

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-800 shadow-md px-6 py-4 flex justify-between items-center transition duration-300">
  <div class="text-2xl font-bold text-blue-600 dark:text-white">JobTracker</div>
  <div class="flex items-center space-x-6">
    <a href="home.php" class="text-blue-600 dark:text-white font-medium hover:text-blue-400">Home</a>
    <a href="about.php" class="hover:text-blue-500 dark:text-white">About</a>
    <a href="contact.php" class="hover:text-blue-500 dark:text-white">Contact</a>

    <?php if (isset($_SESSION['username'])): ?>
      <a href="profile.php" class="flex items-center text-blue-600 dark:text-white hover:text-blue-400">
        <i class="fas fa-user-circle mr-2"></i> Profile
      </a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin/admin.php" class="flex items-center text-blue-600 dark:text-white hover:text-blue-400">
          <i class="fas fa-cogs mr-2"></i> Admin Panel
        </a>
      <?php endif; ?>
      <a href="logout.php" class="flex items-center text-blue-600 dark:text-white hover:text-blue-400">
        <i class="fas fa-sign-out-alt mr-2"></i> Logout
      </a>
    <?php else: ?>
      <a href="login-signup.php" class="flex items-center text-blue-600 dark:text-white hover:text-blue-400">
        <i class="fas fa-sign-in-alt mr-2"></i> Login/Sign Up
      </a>
    <?php endif; ?>

    <!-- Dark Mode Toggle Button -->
    <button onclick="toggleTheme()" id="themeToggle" class="ml-2 text-xl text-gray-800 dark:text-white hover:text-yellow-400 transition">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</nav>