<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login-signup.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['created_at'] = $user['created_at'];
} else {
    echo "User not found.";
    exit();
}

// Fetch user's dynamic applications from the database
$app_query = "SELECT ja.id, ja.status, ja.applied_at, ja.match_score, ja.message, j.title, j.company_name, j.location 
              FROM job_applications ja 
              JOIN jobs j ON ja.job_id = j.id 
              WHERE ja.email = ? 
              ORDER BY ja.applied_at DESC";
$app_stmt = $conn->prepare($app_query);
$app_stmt->bind_param("s", $_SESSION['email']);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
?>

<!-- Show upload status alerts -->
<?php if (isset($_GET['upload'])): ?>
    <div class="max-w-4xl mx-auto mb-4" id="uploadMessage">
        <?php if ($_GET['upload'] === 'success'): ?>
            <div class="bg-green-100 text-green-800 p-3 rounded">✅ Resume uploaded successfully!</div>
        <?php elseif ($_GET['upload'] === 'invalidtype'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ Invalid file type. Please upload PDF, DOC, or DOCX.</div>
        <?php elseif ($_GET['upload'] === 'toolarge'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ File is too large. Maximum size is 5MB.</div>
        <?php elseif ($_GET['upload'] === 'error'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ Something went wrong during upload.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Resume file check
$resumeExists = false;
$resumeFilePath = '';
$resumeExtensions = ['pdf', 'doc', 'docx'];
foreach ($resumeExtensions as $ext) {
    $filePath = "uploads/{$user_id}.$ext";
    if (file_exists($filePath)) {
        $resumeExists = true;
        $resumeFilePath = $filePath;
        break;
    }
}
?>

<?php include 'header.php'; ?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <!-- Profile Header -->
    <div class="max-w-4xl mx-auto mb-10 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-extrabold text-blue-700 dark:text-white">Hello, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Welcome to your dashboard</p>
            </div>
            <a href="edit-profile.php" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-semibold">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Personal Info -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">👤 Personal Info</h3>
            <ul class="space-y-3 text-gray-700 dark:text-gray-300 text-sm">
                <li><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></li>
                <li><strong>Joined:</strong> <?= date("F d, Y", strtotime($_SESSION['created_at'])) ?></li>
                <li><strong>Status:</strong> <span class="text-green-500 font-semibold">Active Profile</span></li>
            </ul>
        </div>

        <!-- Resume -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">📄 Resume</h3>
            <?php if ($resumeExists): ?>
                <div class="mb-4">
                    <a href="<?= $resumeFilePath ?>" target="_blank" class="bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400 px-4 py-2 rounded-lg text-sm font-semibold hover:underline inline-block">
                        <i class="fas fa-file-alt mr-1"></i> View Saved Resume
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">No resume uploaded yet. Having a resume helps local AI match jobs faster.</p>
            <?php endif; ?>
            <form id="resumeUploadForm" action="upload-resume.php" method="post" enctype="multipart/form-data" class="mt-4">
                <input type="file" name="resume" id="resumeFile" accept=".pdf,.doc,.docx" class="block w-full text-xs text-gray-500 mb-2 cursor-pointer" required>
                <button type="submit" class="bg-green-600 text-white px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-green-700">Upload Resume</button>
            </form>
        </div>

        <!-- Applied Jobs -->
        <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">📌 Your Active Applications</h3>
            
            <div class="space-y-4">
                <?php if ($app_result->num_rows > 0): ?>
                    <?php while ($app = $app_result->fetch_assoc()): 
                        // Status styling logic
                        $status = $app['status'];
                        $badgeClass = "bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-400";
                        if ($status === 'Shortlisted' || $status === 'Approved') {
                            $badgeClass = "bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400";
                        } elseif ($status === 'Rejected') {
                            $badgeClass = "bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400";
                        } elseif ($status === 'Reviewing') {
                            $badgeClass = "bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400";
                        }
                    ?>
                        <div class="border border-gray-100 dark:border-gray-700 p-4 rounded-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition duration-200">
                            <div>
                                <h4 class="font-bold text-lg text-gray-800 dark:text-gray-200"><?= htmlspecialchars($app['title']) ?></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($app['company_name']) ?> • 📍 <?= htmlspecialchars($app['location']) ?></p>
                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                                    <span>Applied on: <?= date("M d, Y", strtotime($app['applied_at'])) ?></span>
                                    <span class="text-blue-600 dark:text-blue-400 font-bold">✨ Match Score: <?= $app['match_score'] ?>%</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $badgeClass ?>"><?= $status ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm">You haven't submitted any job applications yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('resumeUploadForm');
    const resumeExists = <?= $resumeExists ? 'true' : 'false' ?>;

    form.addEventListener('submit', function (e) {
        if (resumeExists) {
            const confirmReplace = confirm("A resume already exists. Do you want to replace it?");
            if (!confirmReplace) {
                e.preventDefault();
            }
        }
    });
    
    // Hide the upload status message after 5 seconds
    const uploadMessage = document.getElementById("uploadMessage");
    if (uploadMessage) {
        setTimeout(() => {
            uploadMessage.style.display = "none";
        }, 5000);
    }
</script>

<?php include 'footer.php'; ?>
