@php
    use App\Models\ContactMessage;
    use App\Support\Content;

    $sections = config('dashboard.sections');
    $unread = ContactMessage::query()->unread()->count();
    $identity = Content::get('identity');
@endphp

<x-admin.layout>
    <header class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ __('Welcome back, :name', ['name' => strtok((string) $identity['name'], ' ')]) }}
            </h1>
            <p class="mt-1 text-sm" style="color: var(--text-muted);">
                {{ __('Everything on your website, grouped by page.') }}
            </p>
        </div>

        <a href="{{ route('home', ['locale' => App\Support\Settings::primaryLocale()]) }}"
           target="_blank" rel="noopener"
           class="admin-btn">
            {{ __('View site') }} <x-external-arrow />
        </a>
    </header>

    @if ($unread > 0)
        <a href="{{ route('admin.messages') }}" class="admin-notice mb-10">
            <span>{{ trans_choice('{1}You have 1 unread message.|[2,*]You have :count unread messages.', $unread, ['count' => $unread]) }}</span>
            <span aria-hidden="true">→</span>
        </a>
    @endif

    <div class="space-y-10">
        @foreach (config('dashboard.groups') as $group)
            <section>
                <h2 class="admin-group-label">{{ Content::localise($group['label']) }}</h2>

                <div class="admin-list">
                    @foreach ($group['sections'] as $key)
                        @continue(! isset($sections[$key]))
                        @php $section = $sections[$key]; @endphp

                        <a href="{{ route('admin.section', $key) }}" class="admin-list__row">
                            <span class="min-w-0">
                                <span class="block font-medium">{{ Content::localise($section['label']) }}</span>
                                @if (! empty($section['description']))
                                    <span class="block mt-0.5 text-sm" style="color: var(--text-muted);">
                                        {{ Content::localise($section['description']) }}
                                    </span>
                                @endif
                            </span>

                            <span class="flex items-center gap-3 shrink-0 text-sm" style="color: var(--text-faint);">
                                @if (($section['type'] ?? 'list') !== 'single')
                                    {{ trans_choice('{0}No entries|{1}1 entry|[2,*]:count entries', $counts[$key] ?? 0, ['count' => $counts[$key] ?? 0]) }}
                                @endif
                                <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section>
            <h2 class="admin-group-label">{{ __('Preferences') }}</h2>

            <div class="admin-list">
                <a href="{{ route('admin.settings') }}" class="admin-list__row">
                    <span class="min-w-0">
                        <span class="block font-medium">{{ __('Settings') }}</span>
                        <span class="block mt-0.5 text-sm" style="color: var(--text-muted);">
                            {{ __('Languages, theme and the blog switch.') }}
                        </span>
                    </span>
                    <span aria-hidden="true" style="color: var(--text-faint);">→</span>
                </a>
            </div>
        </section>
    </div>
</x-admin.layout>
