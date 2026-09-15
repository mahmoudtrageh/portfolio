import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

window.Alpine = Alpine;

Alpine.start();

/*
 | Reveal-on-scroll
 |
 | Progressive enhancement: sections are visible by default and only animate
 | when observed, so content is never hidden if JS fails or the visitor has
 | reduced-motion enabled.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-up');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.05 }
    );

    document.querySelectorAll('[data-reveal]').forEach((el) => observer.observe(el));
}
