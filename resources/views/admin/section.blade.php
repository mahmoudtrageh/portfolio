<x-admin.layout :title="App\Support\Content::localise(config('dashboard.sections.'.$section.'.label'))">
    <livewire:admin.section-editor :section="$section" />
</x-admin.layout>
