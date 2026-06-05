<?php
include __DIR__ . '/../../db.php';
session_start();

$keyword = $_GET['keyword'] ?? '';
$location = $_GET['location'] ?? '';
$category = $_GET['category'] ?? '';

// --- Semantic Search NLP Extraction (PHP Fallback) ---
if ($keyword && !$_GET['location'] && preg_match('/(.*)\s+in\s+(.*)/i', $keyword, $matches)) {
    $keyword = trim($matches[1]);
    $location = trim($matches[2]);
}

$sql = "SELECT * FROM jobs WHERE 1=1";
if ($keyword) $sql .= " AND (title LIKE '%" . $conn->real_escape_string($keyword) . "%' OR description LIKE '%" . $conn->real_escape_string($keyword) . "%' OR skills LIKE '%" . $conn->real_escape_string($keyword) . "%')";
if ($location) $sql .= " AND location LIKE '%" . $conn->real_escape_string($location) . "%'";
if ($category) $sql .= " AND category = '" . $conn->real_escape_string($category) . "'";
$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);

// --- AI Recommendation Engine ---
$recommendedJobs = [];
if (isset($_SESSION['user_id']) && isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $profileQuery = "SELECT message, resume FROM job_applications WHERE email = ? ORDER BY id DESC LIMIT 1";
    $profileStmt = $conn->prepare($profileQuery);
    $profileStmt->bind_param("s", $email);
    $profileStmt->execute();
    $profileRes = $profileStmt->get_result();
    $userText = "";
    if ($profileRow = $profileRes->fetch_assoc()) {
        $userText = $profileRow['message'] . " " . $profileRow['resume'];
    }
    $profileStmt->close();
    if (!empty($userText)) {
        $allJobsRes = $conn->query("SELECT * FROM jobs");
        while ($job = $allJobsRes->fetch_assoc()) {
            $score = 0;
            $skillsList = explode(',', strtolower($job['skills'] . "," . $job['title'])));
            foreach ($skillsList as $skill) {
                $skill = trim($skill);
                if ($skill && stripos($userText, $skill) !== false) {
                    $score += 15;
                }
            }
            if (stripos($userText, strtolower($job['category'])) !== false) {
                $score += 10;
            }
            if ($score > 0) {
                $job['match_score'] = min(100, 40 + $score);
                $recommendedJobs[] = $job;
            }
        }
        usort($recommendedJobs, function($a, $b) { return $b['match_score'] - $a['match_score']; });
        $recommendedJobs = array_slice($recommendedJobs, 0, 3);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>JobTracker - Smart AI Career Hub</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    .glass-nav { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
    .dark .glass-nav { background: rgba(17,24,39,0.8); }
  </style>
</head>
<body class="text-gray-800 transition-colors duration-300 bg-gradient-to-b from-blue-50 via-white to-blue-100 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 min-h-screen flex flex-col">
  <!-- Navbar -->
  <nav class="glass-nav sticky top-0 z-50 shadow-sm px-6 py-4 flex justify-between items-center border-b border-gray-100 dark:border-gray-800">
    <div class="text-2xl font-bold text-blue-600 dark:text-white flex items-center gap-2">
      <span>💼</span> JobTracker <span class="text-xs bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-300 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">AI Portal</span>
    </div>
    <div class="flex items-center space-x-6">
      <a href="home.php" class="text-blue-600 dark:text-blue-400 font-semibold hover:text-blue-500">Home</a>
      <a href="about.php" class="hover:text-blue-500 dark:text-white font-medium">About</a>
      <a href="contact.php" class="hover:text-blue-500 dark:text-white font-medium">Contact</a>
      <?php if (isset($_SESSION['username'])): ?>
        <a href="profile.php" class="text-blue-600 dark:text-blue-400 font-medium hover:underline"><i class="fas fa-user-circle mr-1"></i> Dashboard</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="admin/admin.php" class="text-blue-600 dark:text-blue-400 font-medium hover:underline"><i class="fas fa-cogs mr-1"></i> Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="text-red-500 font-medium hover:underline"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
      <?php else: ?>
        <a href="login-signup.php" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 font-medium transition shadow-md"><i class="fas fa-sign-in-alt mr-1"></i> Login/Sign Up</a>
      <?php endif; ?>
      <button onclick="toggleTheme()" id="themeToggle" class="text-xl text-gray-700 dark:text-white hover:text-yellow-400 transition-colors"><i class="fas fa-moon"></i></button>
    </div>
  </nav>
  <!-- Hero Section -->
  <section class="bg-gradient-to-r from-blue-600 to-indigo-700 dark:from-gray-800 dark:to-gray-950 py-24 text-center text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.1),transparent_40%)]"></div>
    <div class="max-w-4xl mx-auto relative z-10 px-4">
      <span class="bg-blue-500/20 text-blue-200 border border-blue-400/30 px-3 py-1 text-xs rounded-full uppercase tracking-wider font-semibold mb-4 inline-block">Smart Match Technology</span>
      <h1 class="text-5xl md:text-6xl font-extrabold drop-shadow-xl mb-4 tracking-tight">Streamline Your Career Hunt</h1>
      <p class="text-lg md:text-xl text-blue-100 dark:text-gray-300 max-w-2xl mx-auto">Upload your resume once and match automatically with top-tier vacancies backed by offline AI scoring analysis.</p>
    </div>
  </section>
  <!-- Search Bar -->
  <form method="GET" action="home.php" id="searchForm" class="max-w-5xl mx-auto -mt-12 relative z-10 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-5 grid grid-cols-1 md:grid-cols-4 gap-4 border border-gray-100 dark:border-gray-700">
      <div class="relative"><i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i><input type="text" name="keyword" id="keywordInput" placeholder="Try 'developer in remote'..." class="pl-10 pr-3 py-2.5 rounded-xl border dark:border-gray-700 dark:bg-gray-700 dark:text-white w-full focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($keyword); ?>"></div>
      <div class="relative"><i class="fas fa-map-marker-alt absolute left-3 top-3.5 text-gray-400"></i><input type="text" name="location" id="locationInput" placeholder="Location..." class="pl-10 pr-3 py-2.5 rounded-xl border dark:border-gray-700 dark:bg-gray-700 dark:text-white w-full focus:ring-2 focus:ring-blue-500" value="<?php echo htmlspecialchars($location); ?>"></div>
      <div><select name="category" class="p-2.5 rounded-xl border dark:border-gray-700 dark:bg-gray-700 dark:text-white w-full focus:ring-2 focus:ring-blue-500">
        <option value="">All Categories</option>
        <option value="IT" <?php if ($category === 'IT') echo 'selected'; ?>>IT</option>
        <option value="Marketing" <?php if ($category === 'Marketing') echo 'selected'; ?>>Marketing</option>
        <option value="Finance" <?php if ($category === 'Finance') echo 'selected'; ?>>Finance</option>
      </select></div>
      <button type="submit" class="bg-blue-600 text-white py-2.5 rounded-xl hover:bg-blue-700 font-semibold shadow-md">Search Jobs</button>
    </div>
  </form>
  <main class="max-w-6xl mx-auto px-4 py-14 flex-grow">
    <?php if (!empty($recommendedJobs)): ?>
      <section class="mb-14 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 border border-blue-500/20 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center gap-2 mb-6"><span class="text-2xl">✨</span><h2 class="text-2xl font-bold text-blue-800 dark:text-blue-300">AI Personalized Job Recommendations</h2></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($recommendedJobs as $job): ?>
            <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-gray-700 p-5 rounded-2xl shadow hover:shadow-xl relative flex flex-col justify-between">
              <div>
                <div class="absolute top-4 right-4 bg-blue-600 text-white px-2.5 py-1 text-xs rounded-full font-bold"><?php echo $job['match_score']; ?>% Fit</div>
                <h3 class="text-lg font-bold text-blue-700 dark:text-blue-400 mb-2 mt-2"><?php echo htmlspecialchars($job['title']); ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2"><?php echo htmlspecialchars($job['company_name']); ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400">📍 <?php echo htmlspecialchars($job['location']); ?></p>
              </div>
              <div class="mt-4 pt-3 border-t dark:border-gray-700 flex justify-between items-center">
                <a href="job-details.php?id=<?php echo $job['id']; ?>" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-semibold">View Job</a>
                <a href="apply.php?id=<?php echo $job['id']; ?>" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-green-700">Apply</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>
    <h2 class="text-3xl font-extrabold mb-8 text-center text-blue-900 dark:text-blue-100 tracking-tight">Available Positions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php if ($result->num_rows > 0) {
        while ($job = $result->fetch_assoc()) {
          echo "<div class='bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 rounded-2xl shadow hover:shadow-xl transition duration-300 relative flex flex-col justify-between'>";
          echo "<div><div class='absolute top-4 right-4 bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 px-3 py-1 text-xs rounded-full font-semibold'>" . htmlspecialchars($job['category']) . "</div>";
          echo "<h3 class='text-xl font-bold text-blue-800 dark:text-blue-300 mb-2 mt-2'>" . htmlspecialchars($job['title']) . "</h3>";
          echo "<p class='text-sm text-gray-500 dark:text-gray-400 mb-3'><i class='fas fa-building mr-1'></i> " . htmlspecialchars($job['company_name'] ?? 'Company') . "</p>";
          echo "<p class='text-gray-600 dark:text-gray-400 text-sm mb-4'>" . substr(htmlspecialchars($job['description']), 0, 120) . "...</p>";
          echo "<p class='text-sm text-gray-500 dark:text-gray-400 mb-1'><i class='fas fa-map-marker-alt mr-1'></i> " . htmlspecialchars($job['location']) . "</p>";
          echo "<p class='text-sm text-gray-500 dark:text-gray-400 mb-3'><i class='fas fa-calendar-alt mr-1'></i> Apply by: " . htmlspecialchars($job['deadline']) . "</p>";
          echo "</div><div class='border-t dark:border-gray-700 pt-4 mt-4 flex justify-between items-center'>";
          echo "<a href='apply.php?id={$job['id']}' class='bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 text-xs font-semibold shadow'>Apply Now</a>";
          echo "<a href='job-details.php?id={$job['id']}' class='text-blue-600 dark:text-blue-400 hover:underline text-xs font-semibold'>View Details →</a>";
          echo "</div></div>";
        }
      } else {
        echo "<p class='text-center text-gray-600 dark:text-white col-span-full py-12 font-medium'>No job listings match your search criteria.</p>";
      } ?>
    </div>
  </main>
  <!-- Floating AI Chatbot Widget -->
  <div class="fixed bottom-6 right-6 z-50">
    <button onclick="toggleChat()" class="bg-blue-600 text-white p-4 rounded-full shadow-2xl hover:bg-blue-700 transition flex items-center justify-center relative group"><i class="fas fa-robot text-2xl"></i><span class="absolute right-12 bg-gray-900 text-white text-xs px-2.5 py-1 rounded shadow opacity-0 group-hover:opacity-100">Ask AI Assistant</span></button>
    <div id="aiChatBox" class="hidden absolute bottom-16 right-0 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 flex flex-col overflow-hidden max-h-[500px]">
      <div class="bg-blue-600 text-white px-4 py-3 flex justify-between items-center"><div class="flex items-center gap-2"><i class="fas fa-robot text-lg"></i><div><h4 class="font-bold text-sm">JobTracker AI Agent</h4><span class="text-[10px] text-blue-200">Online • Latency Free</span></div></div><button onclick="toggleChat()" class="text-white hover:text-gray-200"><i class="fas fa-times"></i></button></div>
      <div id="chatMessages" class="flex-grow p-4 overflow-y-auto space-y-3 min-h-[300px] text-sm"><div class="flex items-start gap-2"><div class="bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 p-2.5 rounded-2xl rounded-tl-none max-w-[80%]">Hello! I'm your JobTracker AI Assistant. How can I help you today?</div></div></div>
      <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 flex gap-2 overflow-x-auto border-t dark:border-gray-700"><button onclick="sendQuickMessage('Show me active jobs')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs hover:bg-gray-100 whitespace-nowrap text-blue-600 dark:text-blue-400">List Jobs</button><button onclick="sendQuickMessage('How do I apply?')" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs hover:bg-gray-100 whitespace-nowrap text-blue-600 dark:text-blue-400">Help Apply</button></div>
      <div class="p-3 border-t dark:border-gray-700 flex gap-2"><input type="text" id="chatInput" placeholder="Ask anything..." class="flex-grow border rounded-xl px-3 py-1.5 dark:bg-gray-700 dark:text-white dark:border-gray-600 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onkeydown="if(event.key === 'Enter') sendChatMessage()"><button onclick="sendChatMessage()" class="bg-blue-600 text-white px-3.5 py-1.5 rounded-xl hover:bg-blue-700"><i class="fas fa-paper-plane"></i></button></div>
    </div>
  </div>
  <footer class="bg-white dark:bg-gray-900 text-center py-6 border-t border-gray-100 dark:border-gray-800 mt-auto"><p class="text-sm text-gray-500 dark:text-gray-400">© <?php echo date('Y'); ?> JobTracker. All rights reserved.</p></footer>
  <script>
    function toggleTheme(){const isDark=document.documentElement.classList.toggle('dark');localStorage.setItem('theme',isDark?'dark':'light');document.getElementById('themeToggle').innerHTML=isDark?'<i class="fas fa-sun"></i>':'<i class="fas fa-moon"></i>';}
    window.addEventListener('DOMContentLoaded',()=>{const theme=localStorage.getItem('theme')||(new Date().getHours()>=18||new Date().getHours()<=6?'dark':'light');if(theme==='dark'){document.documentElement.classList.add('dark');document.getElementById('themeToggle').innerHTML='<i class="fas fa-sun"></i>'; }else{document.getElementById('themeToggle').innerHTML='<i class="fas fa-moon"></i>';}});
    const searchForm=document.getElementById('searchForm');if(searchForm){searchForm.addEventListener('submit',e=>{const kw=document.getElementById('keywordInput');const loc=document.getElementById('locationInput');const val=kw.value.trim();const match=val.match(/(.*)\s+in\s+(.*)/i);if(match){kw.value=match[1].trim();loc.value=match[2].trim();}});}
    function toggleChat(){document.getElementById('aiChatBox').classList.toggle('hidden');}
    function appendMessage(text,sender='bot'){const container=document.getElementById('chatMessages');const div=document.createElement('div');div.className=`flex items-start gap-2 ${sender==='user'?'justify-end':''}`;const cls=sender==='bot'?'bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 p-2.5 rounded-2xl rounded-tl-none max-w-[80%]':'bg-blue-600 text-white p-2.5 rounded-2xl rounded-tr-none max-w-[80%]';div.innerHTML=`<div class="${cls}">${text}</div>`;container.appendChild(div);container.scrollTop=container.scrollHeight;}
    function sendQuickMessage(txt){appendMessage(txt,'user');processBotResponse(txt);}function sendChatMessage(){const inpt=document.getElementById('chatInput');const val=inpt.value.trim();if(!val)return;appendMessage(val,'user');inpt.value='';processBotResponse(val);}function processBotResponse(q){const query=q.toLowerCase();setTimeout(()=>{if(query.includes('job')||query.includes('list')||query.includes('active')){appendMessage('Here are some active vacancies...');}else if(query.includes('developer')||query.includes('web')){appendMessage('Our Full Stack Web Developer opening...');}else if(query.includes('marketing')){appendMessage('Our Digital Marketing Manager position...');}else if(query.includes('apply')){appendMessage('To apply for a job, click the Apply Now button...');}else if(query.includes('status')||query.includes('my application')){appendMessage('You can track your application status...');}else if(query.includes('resume')||query.includes('upload')){appendMessage('You can upload or update your resume...');}else{appendMessage('I\'m not sure about that...');}},500);}
  </script>
</body>
</html>
