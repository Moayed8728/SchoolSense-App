import './bootstrap'
import Alpine from 'alpinejs'

document.documentElement.classList.add('dark')

window.Alpine = Alpine
Alpine.start()

const intro = document.getElementById('app-intro')

if (intro) {
    const hideIntro = () => {
        intro.classList.add('is-hidden')
        window.setTimeout(() => intro.remove(), 800)
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || sessionStorage.getItem('schoolSenseIntroPlayed') === 'true') {
        hideIntro()
    } else {
        sessionStorage.setItem('schoolSenseIntroPlayed', 'true')
        window.setTimeout(hideIntro, 2200)
    }
}
