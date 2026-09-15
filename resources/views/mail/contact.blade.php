<x-mail::message>
# New contact message

**From:** {{ $senderName }} &lt;{{ $senderEmail }}&gt;

{{ $body }}

<x-mail::button :url="'mailto:'.$senderEmail">
Reply to {{ $senderName }}
</x-mail::button>
</x-mail::message>
