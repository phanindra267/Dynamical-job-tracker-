<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/header.php'; // Assuming your navbar is in header.php
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-100 to-blue-300 dark:from-gray-700 dark:to-gray-900 py-20 text-center">
  <h1 class="text-4xl font-bold text-blue-900 dark:text-white mb-4">About JobTracker</h1>
  <p class="text-lg text-blue-800 dark:text-gray-300 max-w-2xl mx-auto">Your ultimate tool to simplify the job application process, stay organized, and land your dream role faster!</p>
</section>

<!-- About Content -->
<section class="max-w-6xl mx-auto px-6 py-12">
  <div class="grid md:grid-cols-2 gap-10 items-center">
    <div>
      <h2 class="text-2xl font-semibold mb-4 text-blue-700 dark:text-white">Why JobTracker?</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-4">We noticed how frustrating it is to apply for jobs, keep track of applications, and follow up. So we built JobTracker — a smart, simple tool to help you manage your job hunt effectively.</p>
      <ul class="list-disc pl-6 text-gray-700 dark:text-gray-300">
        <li>Track multiple applications at once</li>
        <li>Get reminders for follow-ups</li>
        <li>Organize by status, company, or category</li>
        <li>Save time and reduce stress</li>
      </ul>
    </div>
    <div>
      <img src="logo.png" alt="About Illustration" class="rounded-xl shadow-lg">
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
