<?php
include 'db.php';

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$job = null;
$success = false;
$error = '';

// Ensure uploads folder exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if ($job_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['resume']['tmp_name'];
        $fileName = basename($_FILES['resume']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['doc', 'docx', 'pdf'])) {
            $uploadPath = 'uploads/' . time() . '_' . $fileName;
            move_uploaded_file($fileTmp, $uploadPath);

            // --- Local AI Match Score Vector Calculation ---
            $matchScore = 0;
            if ($job) {
                $skillsText = strtolower($job['skills'] ?? '');
                $applicantText = strtolower($message . " " . $fileName);
                
                $skillsArray = explode(',', $skillsText);
                $totalSkills = count($skillsArray);
                $matchedSkills = 0;
                
                foreach ($skillsArray as $skill) {
                    $skill = trim($skill);
                    if (!empty($skill) && stripos($applicantText, $skill) !== false) {
                        $matchedSkills++;
                    }
                }
                
                // Weights: 60% skills overlap, 40% general keyword triggers
                $skillsScore = $totalSkills > 0 ? ($matchedSkills / $totalSkills) * 60 : 30;
                
                $keywords = ['develop', 'engineer', 'lead', 'manage', 'creative', 'analyze', 'solve', 'communication', 'team', 'independent'];
                $keywordMatches = 0;
                foreach ($keywords as $kw) {
                    if (stripos($applicantText, $kw) !== false) {
                        $keywordMatches++;
                    }
                }
                $keywordScore = min(40, $keywordMatches * 8);
                $matchScore = min(100, intval($skillsScore + $keywordScore + 15)); // base index offset
            }

            // Insert into DB with match score
            $stmt = $conn->prepare("INSERT INTO job_applications (job_id, applicant_name, email, resume, message, match_score, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("issssi", $job_id, $name, $email, $uploadPath, $message, $matchScore);
            $stmt->execute();
            $stmt->close();
            $success = true;
        } else {
            $error = "Only .doc, .docx and .pdf files are allowed.";
        }
    } else {
        $error = "Please upload a resume.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply for Job - JobTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-950 min-h-screen flex items-center justify-center py-10 px-4">

  <div class="max-w-2xl w-full bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300">
    <a href="home.php" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium flex items-center gap-1 mb-4">
      <i class="fas fa-arrow-left"></i> Back to Jobs
    </a>

    <?php if ($job): ?>
      <div class="mb-6">
        <span class="text-xs bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300 px-2.5 py-1 rounded-full font-bold uppercase tracking-wider">Application Form</span>
        <h2 class="text-3xl font-extrabold text-blue-800 dark:text-white mt-2">Apply for: <?= htmlspecialchars($job['title']) ?></h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1"><i class="fas fa-building mr-1"></i> <?= htmlspecialchars($job['company_name']) ?></p>
      </div>

      <?php if ($success): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 dark:bg-green-950/50 dark:text-green-300 dark:border-green-800 p-4 rounded-xl mb-6 text-sm font-semibold shadow-inner flex items-center gap-2">
          <span>✅</span> Application submitted successfully! Rerouting in 3 seconds...
        </div>
        <script>
          setTimeout(() => {
            window.location.href = 'home.php';
          }, 3000);
        </script>
      <?php elseif ($error): ?>
        <div class="bg-red-100 border border-red-200 text-red-800 dark:bg-red-950/50 dark:text-red-300 dark:border-red-800 p-4 rounded-xl mb-6 text-sm font-semibold shadow-inner">
          ❌ <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
          <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Jane Doe" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
          <input type="email" name="email" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="jane@example.com" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Resume (.doc/.docx/.pdf)</label>
          <input type="file" name="resume" accept=".doc,.docx,.pdf" required class="w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" />
        </div>
        <div>
          <div class="flex justify-between items-center mb-1">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message / Cover Letter</label>
            <!-- AI Cover Letter Trigger Button -->
            <button type="button" onclick="generateAICoverLetter()" class="text-xs text-blue-600 dark:text-blue-400 font-semibold hover:underline flex items-center gap-1">
              <span>✨</span> Draft AI Cover Letter
            </button>
          </div>
          <textarea id="message" name="message" rows="5" required class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Please describe why you are a good match for this role..."></textarea>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-4 rounded-xl hover:bg-blue-700 font-bold transition shadow-lg"><i class="fas fa-paper-plane mr-2"></i>Submit Application</button>
      </form>
    <?php else: ?>
      <p class="text-red-600 dark:text-red-400 font-semibold text-lg">Invalid Job ID.</p>
    <?php endif; ?>
  </div>

  <script>
      // Local Client-Side AI Cover Letter Drafter
      function generateAICoverLetter() {
          const jobTitle = "<?= htmlspecialchars($job['title']) ?>";
          const company = "<?= htmlspecialchars($job['company_name']) ?>";
          const skills = "<?= htmlspecialchars($job['skills']) ?>";
          
          const coverLetter = `Dear Hiring Team at ${company},

I am writing to express my strong interest in the ${jobTitle} position. With my technical skills and enthusiasm for building robust solutions, I am confident in my ability to make a significant contribution to your team.

I have hands-on experience and skills matching your requirements, specifically in: ${skills}. Throughout my work, I have focused on writing clean, maintainable code, solving complex problems, and collaborating effectively in team settings.

Thank you for your time and consideration. I look forward to the opportunity to discuss how my qualifications align with your engineering goals.

Sincerely,
[Your Name]`;

          document.getElementById('message').value = coverLetter;
      }
  </script>
</body>
</html>
