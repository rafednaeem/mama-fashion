document.addEventListener('DOMContentLoaded', function () {
    const header = document.querySelector('.site-header');
    const navToggle = document.querySelector('.nav-toggle');
    const navInner = document.querySelector('.nav-inner');
    const dropdowns = document.querySelectorAll('.nav-dropdown');

    if (navToggle && navInner) {
        navToggle.addEventListener('click', () => {
            navInner.classList.toggle('nav-open');
        });
    }

    dropdowns.forEach(drop => {
        const btn = drop.querySelector('.nav-link-dropdown');
        if (!btn) return;
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            drop.classList.toggle('open');
        });
    });

    document.addEventListener('click', () => {
        dropdowns.forEach(d => d.classList.remove('open'));
    });

    // Simple scroll effect
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 16) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
});

