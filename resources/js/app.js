import './bootstrap'
import Alpine from 'alpinejs'

document.documentElement.classList.add('dark')

window.Alpine = Alpine
Alpine.start()

const intro = document.getElementById('app-intro')
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

if (intro) {
    const hideIntro = () => {
        intro.classList.add('is-hidden')
        window.setTimeout(() => intro.remove(), 800)
    }

    if (reduceMotion || sessionStorage.getItem('schoolSenseIntroPlayed') === 'true') {
        hideIntro()
    } else {
        sessionStorage.setItem('schoolSenseIntroPlayed', 'true')
        window.setTimeout(hideIntro, 2200)
    }
}

const motionSelectors = [
    '.page-kicker',
    '.page-title',
    '.page-subtitle',
    '.glass-card',
    '.panel',
    '.panel-raised',
    '.rail-card',
    '.metric-card',
    '.data-table',
    'form',
    'article',
    'section > .ui-container',
    'section > .ui-container-wide',
].join(',')

if (!reduceMotion) {
    const candidates = Array.from(document.querySelectorAll(motionSelectors))
        .filter((element) => !element.closest('#app-intro') && !element.dataset.motionBound)

    candidates.forEach((element, index) => {
        element.dataset.motionBound = 'true'
        element.classList.add('motion-reveal')
        element.style.setProperty('--motion-delay', `${Math.min(index % 10, 7) * 42}ms`)
    })

    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return
            }

            entry.target.classList.add('is-visible')
            observer.unobserve(entry.target)
        })
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.08,
    })

    candidates.forEach((element) => revealObserver.observe(element))

    document.querySelectorAll('.glass-card, .panel-raised, .panel, .rail-card').forEach((element) => {
        element.classList.add('smart-surface')
    })
}
