<x-admin.layout :title="$post ? __('Edit post') : __('New post')">
    <livewire:admin.post-editor :post="$post" />
</x-admin.layout>
