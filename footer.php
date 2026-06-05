<script>
  function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    document.getElementById('themeToggle').innerHTML = isDark
      ? '<i class="fas fa-sun"></i>'
      : '<i class="fas fa-moon"></i>';
  }

  window.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme') || (new Date().getHours() >= 18 || new Date().getHours() <= 6 ? 'dark' : 'light');
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
      document.getElementById('themeToggle').innerHTML = '<i class="fas fa-sun"></i>';
    } else {
      document.getElementById('themeToggle').innerHTML = '<i class="fas fa-moon"></i>';
    }
  });
</script>

<footer class="bg-white dark:bg-gray-800 text-center py-4 shadow-inner mt-10">
  <p class="text-sm text-gray-500 dark:text-gray-300">© <?= date("Y") ?> JobTracker. All rights reserved.</p>
</footer>

</body>
</html>
