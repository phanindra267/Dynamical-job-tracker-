<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

// Handle status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $app_id = intval($_POST['app_id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $app_id);
    $stmt->execute();
    $stmt->close();
    header("Location: job-applications.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Fetch file path before deleting
    $stmt = $conn->prepare("SELECT resume FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resume_path = '';
    if ($row = $result->fetch_assoc()) {
        $resume_path = $row['resume'];
    }
    $stmt->close();

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Remove resume file
    if ($resume_path && file_exists("../" . $resume_path)) {
        unlink("../" . $resume_path);
    }

    header("Location: job-applications.php");
    exit();
}

// Fetch job applications
$query = "SELECT ja.id, ja.applicant_name, ja.email, ja.message, ja.resume, ja.status, ja.match_score, j.title as job_title
          FROM job_applications ja
          JOIN jobs j ON ja.job_id = j.id
          ORDER BY ja.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Applications - JobTracker Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white p-6 transition duration-300">
  <div class="max-w-7xl mx-auto">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="admin.php" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-semibold flex items-center gap-1 mb-2">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="text-3xl font-extrabold text-blue-700 dark:text-white">Candidates Applications</h1>
        </div>
        <a href="../home.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md">Portal Home</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow border border-gray-100 dark:border-gray-700 overflow-x-auto">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-blue-600 text-white text-sm uppercase tracking-wider">
            <th class="px-4 py-3 text-left rounded-l-xl">ID</th>
            <th class="px-4 py-3 text-left">Applicant Info</th>
            <th class="px-4 py-3 text-left">Target Position</th>
            <th class="px-4 py-3 text-left">AI Score</th>
            <th class="px-4 py-3 text-left">Resume Log</th>
            <th class="px-4 py-3 text-left">Cover Letter</th>
            <th class="px-4 py-3 text-left">Workflow Status</th>
            <th class="px-4 py-3 text-center rounded-r-xl">Action</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): 
                $match = $row['match_score'] ?? 0;
                $scoreColor = "text-red-500 font-bold";
                if ($match >= 75) {
                    $scoreColor = "text-green-600 font-extrabold";
                } elseif ($match >= 50) {
                    $scoreColor = "text-yellow-600 font-bold";
                }
            ?>
              <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 transition duration-150">
                <td class="px-4 py-4 font-semibold">#<?= $row['id'] ?></td>
                <td class="px-4 py-4">
                  <div class="font-bold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($row['applicant_name']) ?></div>
                  <div class="text-xs text-gray-400"><?= htmlspecialchars($row['email']) ?></div>
                </td>
                <td class="px-4 py-4 font-semibold text-blue-600 dark:text-blue-400"><?= htmlspecialchars($row['job_title']) ?></td>
                <td class="px-4 py-4">
                  <span class="<?= $scoreColor ?>">✨ <?= $match ?>%</span>
                </td>
                <td class="px-4 py-4">
                  <a href="../<?= $row['resume'] ?>" target="_blank" class="bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400 px-3 py-1.5 rounded-lg text-xs font-semibold hover:underline inline-flex items-center gap-1">
                    <i class="fas fa-file-pdf"></i> View File
                  </a>
                </td>
                <td class="px-4 py-4 max-w-xs truncate" title="<?= htmlspecialchars($row['message']) ?>">
                  <?= htmlspecialchars($row['message']) ?>
                </td>
                <td class="px-4 py-4">
                  <!-- Auto-submitting Form for Inline Status Update -->
                  <form method="POST" class="inline-block">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                    <select name="status" onchange="this.form.submit()" class="text-xs font-bold border rounded-lg p-1.5 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:bg-gray-700 dark:text-white dark:border-gray-600">
                      <option value="Pending" <?php if($row['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                      <option value="Reviewing" <?php if($row['status'] === 'Reviewing') echo 'selected'; ?>>Reviewing</option>
                      <option value="Shortlisted" <?php if($row['status'] === 'Shortlisted') echo 'selected'; ?>>Shortlisted</option>
                      <option value="Rejected" <?php if($row['status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                    </select>
                  </form>
                </td>
                <td class="px-4 py-4 text-center">
                  <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this application?')" class="text-red-500 hover:text-red-700 font-semibold inline-flex items-center gap-1">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400 font-medium">No applications submitted yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
