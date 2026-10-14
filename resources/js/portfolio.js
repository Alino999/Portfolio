const root = document.documentElement;
const themeButton = document.querySelector('[data-theme-toggle]');
const themeKey = 'portfolio-theme';

function applyTheme(theme) {
    const selectedTheme = theme === 'dark' ? 'dark' : 'light';
    root.dataset.theme = selectedTheme;
    themeButton?.setAttribute('aria-pressed', String(selectedTheme === 'dark'));
    themeButton?.setAttribute('aria-label', selectedTheme === 'dark' ? themeButton.dataset.lightLabel : themeButton.dataset.darkLabel);
    themeButton?.querySelector('[data-theme-icon]')?.replaceChildren(document.createTextNode(selectedTheme === 'dark' ? '☀' : '☾'));
}

let savedTheme = null;
try {
    savedTheme = localStorage.getItem(themeKey);
} catch {
    savedTheme = null;
}
applyTheme(savedTheme || 'light');

themeButton?.addEventListener('click', () => {
    const nextTheme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme);
    try {
        localStorage.setItem(themeKey, nextTheme);
    } catch {
        // Theme still applies for the current page if storage is unavailable.
    }
});

const navigation = document.querySelector('[data-site-nav]');
const updateNavigation = () => navigation?.classList.toggle('is-scrolled', window.scrollY > 12);
updateNavigation();
window.addEventListener('scroll', updateNavigation, { passive: true });

const revealItems = document.querySelectorAll('[data-reveal]');
if (revealItems.length) root.classList.add('portfolio-enhanced');
if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        }
    }, { threshold: 0.12 });

    revealItems.forEach((item) => revealObserver.observe(item));
} else {
    revealItems.forEach((item) => item.classList.add('is-visible'));
}

const projectCards = [...document.querySelectorAll('[data-project-card]')];
const projectSearch = document.querySelector('[data-project-search]');
const projectFilters = [...document.querySelectorAll('[data-project-filter]')];
const projectEmptyState = document.querySelector('[data-project-empty]');
let activeProjectCategory = 'all';

function filterProjects() {
    const normalize = (value) => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();
    const query = normalize((projectSearch?.value || '').trim());
    let visibleCount = 0;

    for (const card of projectCards) {
        const categoryMatches = activeProjectCategory === 'all' || card.dataset.projectCategory === activeProjectCategory;
        const queryMatches = !query || normalize(card.dataset.projectSearchText || '').includes(query);
        const visible = categoryMatches && queryMatches;
        card.hidden = !visible;
        visibleCount += Number(visible);
    }

    if (projectEmptyState) projectEmptyState.hidden = visibleCount !== 0;
}

projectFilters.forEach((button) => button.addEventListener('click', () => {
    activeProjectCategory = button.dataset.projectFilter || 'all';
    projectFilters.forEach((filter) => filter.setAttribute('aria-pressed', String(filter === button)));
    filterProjects();
}));
projectSearch?.addEventListener('input', filterProjects);

document.querySelectorAll('[data-portfolio-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-portfolio-track]');
    const previousButton = carousel.querySelector('[data-carousel-prev]');
    const nextButton = carousel.querySelector('[data-carousel-next]');
    const toggleButton = carousel.querySelector('[data-carousel-toggle]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let userPaused = reducedMotion.matches;
    let pointerPaused = false;
    let focusPaused = false;
    let inViewport = !('IntersectionObserver' in window);
    let autoplayTimer;
    const updateControls = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        previousButton.disabled = track.scrollLeft <= 1;
        nextButton.disabled = track.scrollLeft >= maxScroll - 1;
    };
    const scrollTrack = (direction) => {
        const slide = track.querySelector('.portfolio-carousel-slide');
        const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
        track.scrollBy({ left: direction * ((slide?.getBoundingClientRect().width || track.clientWidth) + gap), behavior: 'smooth' });
    };
    const updateToggleButton = () => {
        toggleButton.setAttribute('aria-pressed', String(userPaused));
        toggleButton.setAttribute('aria-label', userPaused ? toggleButton.dataset.resumeLabel : toggleButton.dataset.pauseLabel);
        toggleButton.title = userPaused ? toggleButton.dataset.resumeLabel : toggleButton.dataset.pauseLabel;
        toggleButton.querySelector('i').className = userPaused ? 'bi bi-play-fill' : 'bi bi-pause-fill';
    };
    const canAutoplay = () => !userPaused
        && !pointerPaused
        && !focusPaused
        && !document.hidden
        && inViewport
        && !reducedMotion.matches
        && track.scrollWidth > track.clientWidth + 1;
    const syncAutoplay = () => {
        window.clearInterval(autoplayTimer);
        if (!canAutoplay()) return;

        autoplayTimer = window.setInterval(() => {
            const maxScroll = track.scrollWidth - track.clientWidth;
            if (track.scrollLeft >= maxScroll - 2) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                scrollTrack(1);
            }
        }, 4500);
    };

    previousButton.addEventListener('click', () => scrollTrack(-1));
    nextButton.addEventListener('click', () => scrollTrack(1));
    toggleButton.addEventListener('click', () => {
        userPaused = !userPaused;
        if (!userPaused && carousel.contains(document.activeElement)) focusPaused = false;
        updateToggleButton();
        syncAutoplay();
    });
    carousel.addEventListener('mouseenter', () => {
        pointerPaused = true;
        syncAutoplay();
    });
    carousel.addEventListener('mouseleave', () => {
        pointerPaused = false;
        syncAutoplay();
    });
    carousel.addEventListener('focusin', () => {
        focusPaused = true;
        syncAutoplay();
    });
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            focusPaused = false;
            syncAutoplay();
        }
    });
    document.addEventListener('visibilitychange', syncAutoplay);
    reducedMotion.addEventListener('change', (event) => {
        if (event.matches) userPaused = true;
        updateToggleButton();
        syncAutoplay();
    });
    track.addEventListener('scroll', updateControls, { passive: true });
    const trackObserver = new MutationObserver(() => {
        updateControls();
        syncAutoplay();
    });
    trackObserver.observe(track, { childList: true, subtree: true, attributes: true, attributeFilter: ['hidden'] });
    window.addEventListener('resize', () => {
        updateControls();
        syncAutoplay();
    });
    if ('IntersectionObserver' in window) {
        const visibilityObserver = new IntersectionObserver(([entry]) => {
            inViewport = entry.isIntersecting;
            syncAutoplay();
        });
        visibilityObserver.observe(carousel);
    }
    updateToggleButton();
    updateControls();
    syncAutoplay();
});