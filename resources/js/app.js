import './bootstrap'

function setTheme(mode) {
    if (mode === 'dark') {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('theme') || 'dark'
    setTheme(saved)

    const toggle = document.getElementById('themeToggle')

    toggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.contains('dark')
        setTheme(isDark ? 'light' : 'dark')
    })
})