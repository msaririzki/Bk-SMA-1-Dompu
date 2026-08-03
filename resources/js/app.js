import './bootstrap';

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-nav-toggle]');
    if (toggle) document.querySelector('[data-sidebar]')?.classList.toggle('-translate-x-full');
    const publicToggle = event.target.closest('[data-public-nav-toggle]');
    if (publicToggle) {
        const navigation = document.querySelector('[data-public-nav]');
        const opening = navigation?.classList.contains('hidden');
        navigation?.classList.toggle('hidden');
        navigation?.classList.toggle('flex', opening);
        publicToggle.setAttribute('aria-expanded', String(!navigation?.classList.contains('hidden')));
    }
});
