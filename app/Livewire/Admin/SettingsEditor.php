<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Settings;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Edits the site-wide switches.
 *
 * Deliberately not SectionEditor: these are single, non-bilingual booleans
 * whose sub-choices only apply while their switch is off, which is a different
 * form to the repeatable translated entries that component is built around.
 */
final class SettingsEditor extends Component
{
    public bool $blogEnabled = true;

    public bool $multilingual = true;

    public string $primaryLocale = 'en';

    public bool $themeToggle = true;

    public string $theme = 'light';

    public function mount(): void
    {
        $this->blogEnabled = Settings::blogEnabled();
        $this->multilingual = Settings::multilingual();
        $this->primaryLocale = Settings::primaryLocale();
        $this->themeToggle = Settings::themeToggle();
        $this->theme = Settings::theme();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'blogEnabled' => ['boolean'],
            'multilingual' => ['boolean'],
            // Only a locale the site is actually built in may be primary.
            'primaryLocale' => ['required', 'string', 'in:'.implode(',', Content::locales())],
            'themeToggle' => ['boolean'],
            'theme' => ['required', 'string', 'in:light,dark'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        ContentSection::query()->updateOrCreate(
            ['section' => Settings::SECTION, 'position' => 0],
            ['data' => [
                'blog_enabled' => $this->blogEnabled,
                'multilingual' => $this->multilingual,
                'primary_locale' => $this->primaryLocale,
                'theme_toggle' => $this->themeToggle,
                'theme' => $this->theme,
            ]],
        );

        Content::flush();

        $this->dispatch('saved');

        session()->flash('settings.saved', true);
    }

    public function render(): View
    {
        return view('livewire.admin.settings-editor', [
            'locales' => Content::locales(),
        ]);
    }
}
