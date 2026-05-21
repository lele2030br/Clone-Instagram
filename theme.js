document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem('theme');
    const themeBtn = document.getElementById('theme-btn');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        if(themeBtn) themeBtn.className = 'ri-sun-line nav-icon';
    }
});
function toggleTheme() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    const themeBtn = document.getElementById('theme-btn');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    if(themeBtn) themeBtn.className = isDark ? 'ri-sun-line nav-icon' : 'ri-moon-line nav-icon';
}
