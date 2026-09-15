{{--
    Theme toggle. The initial class is applied by the inline script in the
    layout head; this only handles user-initiated changes and persistence.
--}}
<button
    type="button"
    x-data="{
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            document.documentElement.style.colorScheme = this.dark ? 'dark' : 'light';
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        },
    }"
    x-on:click="toggle()"
    class="p-2 rounded-lg border transition-colors"
    style="border-color: var(--border); color: var(--text-muted);"
    onmouseover="this.style.color='var(--text)'; this.style.borderColor='var(--border-strong)'"
    onmouseout="this.style.color='var(--text-muted)'; this.style.borderColor='var(--border)'"
    :aria-pressed="dark.toString()"
    aria-label="{{ __('Toggle theme') }}"
    title="{{ __('Toggle theme') }}"
>
    {{-- Sun --}}
    <svg x-show="dark" x-cloak class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
    </svg>
    {{-- Moon --}}
    <svg x-show="!dark" class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         aria-hidden="true">
        <path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/>
    </svg>
</button>
