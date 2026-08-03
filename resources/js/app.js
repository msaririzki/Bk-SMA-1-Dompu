import './bootstrap';

const sidebar = () => document.querySelector('[data-sidebar]');
const sidebarOverlay = () => document.querySelector('[data-sidebar-overlay]');

const setSidebar = (open) => {
    sidebar()?.classList.toggle('-translate-x-full', !open);
    sidebarOverlay()?.classList.toggle('hidden', !open);
    document.body.classList.toggle('sidebar-open', open);
    document.querySelectorAll('[data-nav-toggle]').forEach((button) => button.setAttribute('aria-expanded', String(open)));
};

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-nav-toggle]')) {
        setSidebar(sidebar()?.classList.contains('-translate-x-full') ?? false);
    }
    if (event.target.closest('[data-sidebar-overlay]')) setSidebar(false);

    const publicToggle = event.target.closest('[data-public-nav-toggle]');
    if (publicToggle) {
        const navigation = document.querySelector('[data-public-nav]');
        const opening = navigation?.classList.contains('hidden');
        navigation?.classList.toggle('hidden');
        navigation?.classList.toggle('flex', opening);
        publicToggle.setAttribute('aria-expanded', String(!navigation?.classList.contains('hidden')));
    }

    if (event.target.closest('[data-public-nav] a') && window.innerWidth < 768) {
        document.querySelector('[data-public-nav]')?.classList.add('hidden');
        document.querySelector('[data-public-nav]')?.classList.remove('flex');
        document.querySelector('[data-public-nav-toggle]')?.setAttribute('aria-expanded', 'false');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setSidebar(false);
});

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        sidebarOverlay()?.classList.add('hidden');
        document.body.classList.remove('sidebar-open');
    }
});
