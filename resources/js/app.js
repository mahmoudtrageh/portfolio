/*
 | Livewire ships its own Alpine; starting a second copy would break both.
 | Livewire powers wire:navigate: internal links swap the page in place
 | instead of a full reload, so moving between pages feels instant.
 */
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/*
 | Reveal-on-scroll
 |
 | Progressive enhancement: sections are visible by default and only animate
 | when observed, so content is never hidden if JS fails or the visitor has
 | reduced-motion enabled.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Runs on first load and again after every wire:navigate page swap.
document.addEventListener('livewire:navigated', () => {
    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        return;
    }

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
});

// Last, so the listeners above catch the first page's livewire:navigated.
Livewire.start();
