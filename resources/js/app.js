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

const debounce = (callback, delay = 350) => {
    let timer;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => callback(...args), delay);
    };
};

document.querySelectorAll('[data-auto-filter-form]').forEach((form) => {
    const submit = debounce(() => form.requestSubmit());

    form.querySelectorAll('input[name="q"]').forEach((input) => {
        input.addEventListener('input', submit);
    });
    form.querySelectorAll('select').forEach((select) => {
        select.addEventListener('change', () => form.requestSubmit());
    });
});

document.querySelectorAll('[data-student-autocomplete]').forEach((autocomplete) => {
    const queryInput = autocomplete.querySelector('[data-student-query]');
    const valueInput = autocomplete.querySelector('[data-student-value]');
    const results = autocomplete.querySelector('[data-student-results]');
    const spinner = autocomplete.querySelector('[data-student-spinner]');
    const form = autocomplete.closest('form');
    let requestController;
    let activeIndex = -1;
    let selectedLabel = queryInput.value;

    const options = () => [...results.querySelectorAll('[role="option"]')];

    const closeResults = () => {
        results.classList.add('hidden');
        queryInput.setAttribute('aria-expanded', 'false');
        queryInput.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const openResults = () => {
        results.classList.remove('hidden');
        queryInput.setAttribute('aria-expanded', 'true');
    };

    const showMessage = (message) => {
        results.replaceChildren();
        const item = document.createElement('div');
        item.className = 'student-search-message';
        item.textContent = message;
        results.append(item);
        openResults();
    };

    const selectStudent = (student) => {
        const label = `${student.name} — ${student.class_name} (${student.identifier})`;
        valueInput.value = student.id;
        queryInput.value = label;
        selectedLabel = label;
        queryInput.setCustomValidity('');
        closeResults();
    };

    const renderResults = (students) => {
        results.replaceChildren();
        activeIndex = -1;

        if (!students.length) {
            showMessage('Siswa tidak ditemukan. Coba nama atau identitas lainnya.');
            return;
        }

        students.forEach((student, index) => {
            const option = document.createElement('button');
            const primary = document.createElement('span');
            const secondary = document.createElement('span');
            option.type = 'button';
            option.id = `${queryInput.id}-option-${index}`;
            option.className = 'student-search-option';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            primary.className = 'student-search-name';
            primary.textContent = student.name;
            secondary.className = 'student-search-meta';
            secondary.textContent = `${student.class_name} · ${student.identifier_label} ${student.identifier}`;
            option.append(primary, secondary);
            option.addEventListener('mousedown', (event) => event.preventDefault());
            option.addEventListener('click', () => selectStudent(student));
            results.append(option);
        });
        openResults();
    };

    const updateActiveOption = (nextIndex) => {
        const availableOptions = options();
        if (!availableOptions.length) return;

        activeIndex = (nextIndex + availableOptions.length) % availableOptions.length;
        availableOptions.forEach((option, index) => {
            const active = index === activeIndex;
            option.classList.toggle('is-active', active);
            option.setAttribute('aria-selected', String(active));
        });
        queryInput.setAttribute('aria-activedescendant', availableOptions[activeIndex].id);
        availableOptions[activeIndex].scrollIntoView({ block: 'nearest' });
    };

    const searchStudents = debounce(async () => {
        const term = queryInput.value.trim();
        if (term.length < 2) {
            requestController?.abort();
            closeResults();
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        const currentController = requestController;
        spinner.classList.remove('hidden');

        try {
            const url = new URL(autocomplete.dataset.url, window.location.origin);
            url.searchParams.set('q', term);
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: currentController.signal,
            });
            if (!response.ok) throw new Error('Pencarian gagal');
            const payload = await response.json();
            renderResults(payload.results ?? []);
        } catch (error) {
            if (error.name !== 'AbortError') showMessage('Pencarian belum dapat dilakukan. Silakan coba lagi.');
        } finally {
            if (requestController === currentController) spinner.classList.add('hidden');
        }
    }, 250);

    queryInput.addEventListener('input', () => {
        if (queryInput.value !== selectedLabel) valueInput.value = '';
        queryInput.setCustomValidity('');
        searchStudents();
    });

    queryInput.addEventListener('focus', () => {
        if (!valueInput.value && queryInput.value.trim().length >= 2) searchStudents();
    });

    queryInput.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            updateActiveOption(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            updateActiveOption(activeIndex - 1);
        } else if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            options()[activeIndex]?.click();
        } else if (event.key === 'Escape') {
            closeResults();
        }
    });

    form?.addEventListener('submit', (event) => {
        if (!valueInput.value) {
            event.preventDefault();
            queryInput.setCustomValidity('Pilih siswa dari hasil pencarian yang muncul.');
            queryInput.reportValidity();
            queryInput.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!autocomplete.contains(event.target)) closeResults();
    });
});
