{{--
    Browser-tab icon.

    Uses the uploaded favicon when there is one, and falls back to the bundled
    favicon.ico otherwise — so the tab is never blank, and clearing the field in
    the dashboard restores the default rather than breaking the link.
--}}
@php
    use App\Support\Content;

    $uploaded = trim((string) (Content::get('identity')['favicon'] ?? ''));

    // The type attribute tells the browser which format it is being handed;
    // without it an SVG can be fetched and then discarded.
    $type = match (strtolower(pathinfo($uploaded, PATHINFO_EXTENSION))) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        default => null,
    };
@endphp

@if ($uploaded !== '' && $type !== null)
    <link rel="icon" type="{{ $type }}" href="{{ Content::image($uploaded) }}">
    {{-- iOS ignores rel="icon" and looks for this one when a page is saved
         to the home screen. --}}
    <link rel="apple-touch-icon" href="{{ Content::image($uploaded) }}">
@else
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@endif
