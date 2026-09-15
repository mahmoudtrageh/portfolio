<x-admin.layout :title="__('Messages')">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="font-display text-2xl">{{ __('Messages') }}</h1>

        @if ($unread > 0)
            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                  style="background: var(--accent-fill); color: var(--accent-text);">
                {{ trans_choice(':count unread|:count unread', $unread, ['count' => $unread]) }}
            </span>
        @endif
    </div>

    @if (session('status'))
        <p class="card p-4 mb-4 text-sm" style="color: var(--text-muted);">
            {{ session('status') }}
        </p>
    @endif

    @if ($messages->isEmpty())
        <p class="card p-8 text-center text-sm" style="color: var(--text-muted);">
            {{ __('No messages yet. Anything sent through the contact form appears here.') }}
        </p>
    @else
        <ul class="space-y-3">
            @foreach ($messages as $message)
                {{-- Unread messages carry the accent edge, so the list can be
                     scanned without reading every card. --}}
                <li class="card p-5 @if (! $message->isRead()) border-inline-start @endif"
                    @if (! $message->isRead()) style="border-inline-start-width: 3px; border-color: var(--accent);" @endif>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold">{{ $message->name }}</p>

                            <a href="mailto:{{ $message->email }}"
                               class="text-sm hover:opacity-70 transition-opacity"
                               style="color: var(--accent);">
                                {{ $message->email }}
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs"
                             style="color: var(--text-faint);">
                            <time datetime="{{ $message->created_at->toIso8601String() }}">
                                {{ $message->created_at->isoFormat('LL — HH:mm') }}
                            </time>

                            @if (! $message->mailed)
                                {{-- The message is safe in the database; only the
                                     notification email failed. --}}
                                <span class="px-2 py-0.5 rounded-full"
                                      style="background: var(--accent-soft); color: var(--accent);"
                                      title="{{ __('Stored, but the notification email did not send.') }}">
                                    {{ __('Not emailed') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <p class="mt-3 text-sm leading-relaxed whitespace-pre-line"
                       style="color: var(--text-muted);">{{ $message->body }}</p>

                    <div class="mt-4 pt-3 border-t flex flex-wrap items-center gap-2"
                         style="border-color: var(--border);">
                        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode(__('Re: your message')) }}"
                           class="px-4 py-2 rounded-full text-xs font-semibold"
                           style="background: var(--accent-fill); color: var(--accent-text);">
                            {{ __('Reply') }}
                        </a>

                        @if (! $message->isRead())
                            <form method="POST" action="{{ route('admin.messages.update', $message) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-full text-xs font-semibold"
                                        style="border: 1px solid var(--border);">
                                    {{ __('Mark as read') }}
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.messages.update', $message) }}"
                              class="ms-auto"
                              onsubmit="return confirm('{{ __('Delete this message permanently?') }}')">
                            @csrf
                            <input type="hidden" name="delete" value="1">

                            <button type="submit" class="px-4 py-2 rounded-full text-xs font-semibold"
                                    style="color: var(--text-faint);">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-6">
            {{ $messages->links() }}
        </div>
    @endif
</x-admin.layout>
