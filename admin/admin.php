<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

// Fetch users count
$user_result = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
$user_data = $user_result->fetch_assoc();
$total_users = $user_data['total_users'] ?? 0;

// Fetch support messages count
$message_result = $conn->query("SELECT COUNT(*) as total_messages FROM contact_messages");
$message_data = $message_result->fetch_assoc();
$total_messages = $message_data['total_messages'] ?? 0;

// Fetch pending approvals dynamically
$pending_result = $conn->query("SELECT COUNT(*) as pending FROM job_applications WHERE status = 'Pending'");
$pending_data = $pending_result->fetch_assoc();
$pending_approvals = $pending_data['pending'] ?? 0;

// Fetch status distribution for Chart.js
$status_counts = ['Pending' => 0, 'Reviewing' => 0, 'Shortlisted' => 0, 'Rejected' => 0];
$counts_res = $conn->query("SELECT status, COUNT(*) as count FROM job_applications GROUP BY status");
while ($row = $counts_res->fetch_assoc()) {
    $status_counts[$row['status']] = (int)$row['count'];
}

// Fetch recent applications for activity feed
$recent_apps = $conn->query("SELECT ja.applicant_name, ja.status, j.title, ja.applied_at 
                             FROM job_applications ja 
                             JOIN jobs j ON ja.job_id = j.id 
                             ORDER BY ja.id DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Panel - JobTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white transition duration-300 min-h-screen flex flex-col">

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-800 shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
  <div class="text-2xl font-bold text-blue-600 dark:text-white">🚀 JobTracker Admin</div>
  <div class="flex items-center space-x-6">
    <a href="../home.php" class="text-blue-600 dark:text-white hover:text-blue-400">Home Portal</a>
    <a href="../about.php" class="hover:text-blue-500 dark:text-white">About</a>
    <a href="../contact.php" class="hover:text-blue-500 dark:text-white">Contact</a>
    <a href="../logout.php" class="text-red-500 hover:underline">
      <i class="fas fa-sign-out-alt mr-1"></i> Logout
    </a>
    <button id="themeToggle" class="text-xl text-gray-700 dark:text-yellow-300 hover:text-yellow-500">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</nav>

<div class="flex flex-1">
  <!-- Sidebar -->
  <div class="w-64 bg-blue-600 text-white p-6 flex flex-col gap-6">
    <div class="border-b border-blue-500 pb-4">
      <h2 class="text-2xl font-extrabold tracking-tight">Admin Console</h2>
      <p class="text-xs text-blue-200 mt-1">Logged in as: <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
    <ul class="space-y-3 flex-grow">
      <li>
        <a href="admin.php" class="flex items-center hover:bg-blue-500 p-2.5 rounded-xl transition font-semibold bg-blue-700">
          <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="manage-users.php" class="flex items-center hover:bg-blue-500 p-2.5 rounded-xl transition">
          <i class="fas fa-users mr-3 w-5 text-center"></i> Manage Users
        </a>
      </li>
      <li>
        <a href="view-messages.php" class="flex items-center hover:bg-blue-500 p-2.5 rounded-xl transition">
          <i class="fas fa-envelope mr-3 w-5 text-center"></i> Support Messages
        </a>
      </li>
      <li>
        <a href="job-applications.php" class="flex items-center hover:bg-blue-500 p-2.5 rounded-xl transition">
          <i class="fas fa-briefcase mr-3 w-5 text-center"></i> Job Applications
        </a>
      </li>
      <li>
        <a href="settings.php" class="flex items-center hover:bg-blue-500 p-2.5 rounded-xl transition">
          <i class="fas fa-cogs mr-3 w-5 text-center"></i> Settings
        </a>
      </li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="flex-1 p-8 overflow-y-auto">
    <h1 class="text-3xl font-extrabold text-blue-700 dark:text-white mb-6">Recruitment Insights</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Candidates</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?= $total_users ?></p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inquiries Received</h3>
        <p class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?= $total_messages ?></p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 border-l-4 border-l-yellow-500">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Approvals</h3>
        <p class="text-3xl font-bold text-yellow-600 mt-2"><?= $pending_approvals ?></p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Applications Chart -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <h2 class="text-xl font-bold text-blue-700 dark:text-white mb-4">Application Pipeline Breakdown</h2>
        <div class="relative max-h-64 flex justify-center">
          <canvas id="pipelineChart"></canvas>
        </div>
      </div>

      <!-- Activity Feed -->
      <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700">
        <h2 class="text-xl font-bold text-blue-700 dark:text-white mb-4">Recent Submissions</h2>
        <ul class="space-y-4">
          <?php if ($recent_apps->num_rows > 0): ?>
            <?php while ($app = $recent_apps->fetch_assoc()): 
                $status = $app['status'];
                $timeText = date("M d, H:i", strtotime($app['applied_at']));
                $icon = "fa-user-plus text-blue-500";
                if ($status === 'Shortlisted') $icon = "fa-check-circle text-green-500";
                if ($status === 'Rejected') $icon = "fa-times-circle text-red-500";
            ?>
              <li class="flex items-center gap-3 border-b dark:border-gray-700 pb-3">
                <i class="fas <?= $icon ?> text-lg w-6 text-center"></i>
                <div>
                  <span class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($app['applicant_name']) ?></span> 
                  applied for 
                  <span class="text-blue-600 dark:text-blue-400 font-semibold"><?= htmlspecialchars($app['title']) ?></span>
                </div>
                <span class="text-xs text-gray-400 ml-auto"><?= $timeText ?></span>
              </li>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-center text-gray-500 py-6 text-sm">No recent application activities found.</p>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('pipelineChart').getContext('2d');
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Pending', 'Reviewing', 'Shortlisted', 'Rejected'],
      datasets: [{
        label: 'Applications Status',
        data: [
          <?= $status_counts['Pending'] ?>, 
          <?= $status_counts['Reviewing'] ?>, 
          <?= $status_counts['Shortlisted'] ?>, 
          <?= $status_counts['Rejected'] ?>
        ],
        backgroundColor: ['#F59E0B', '#3B82F6', '#10B981', '#EF4444'],
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });
</script>

<!-- Theme Toggle Script -->
<script>
  const themeToggle = document.getElementById('themeToggle');
  const htmlElement = document.documentElement;

  function applyTheme(theme) {
    if (theme === 'dark') {
      htmlElement.classList.add('dark');
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
      htmlElement.classList.remove('dark');
      themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
  }

  themeToggle.addEventListener('click', () => {
    const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
  });

  window.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);
  });
</script>

<!-- Footer -->
<footer class="bg-white dark:bg-gray-800 text-center py-4 shadow-inner mt-auto">
  <p class="text-sm text-gray-500 dark:text-gray-300">© <?= date("Y") ?> JobTracker Admin Panel. All rights reserved.</p>
</footer>

</body>
</html>
