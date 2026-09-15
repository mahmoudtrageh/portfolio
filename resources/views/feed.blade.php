<?php echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"; ?>
@php
    use App\Support\Content;
    use App\Support\Markdown;

    $identity = Content::get('identity');
    $self = route('feed', ['locale' => $locale]);
@endphp
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ $identity['name'] }} — {{ __('Blog') }}</title>
        <link>{{ route('blog', ['locale' => $locale]) }}</link>
        <description>{{ Content::string('hero.summary') }}</description>
        <language>{{ $locale }}</language>
        <atom:link href="{{ $self }}" rel="self" type="application/rss+xml" />
        @if ($posts->isNotEmpty())
            <lastBuildDate>{{ $posts->first()->published_at->toRfc2822String() }}</lastBuildDate>
        @endif

        @foreach ($posts as $post)
            @php $url = route('post', ['locale' => $locale, 'slug' => $post->slug($locale)]); @endphp
            <item>
                <title>{{ $post->title($locale) }}</title>
                <link>{{ $url }}</link>
                <guid isPermaLink="true">{{ $url }}</guid>
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
                <description>{{ $post->excerpt($locale) }}</description>
                <content:encoded><![CDATA[{!! Markdown::toHtml($post->body($locale)) !!}]]></content:encoded>
            </item>
        @endforeach
    </channel>
</rss>
