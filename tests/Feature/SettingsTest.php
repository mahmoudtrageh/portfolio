<?php

declare(strict_types=1);

use App\Livewire\Admin\SettingsEditor;
use App\Models\User;
use Livewire\Livewire;

/*
| The site-wide switches, and the dashboard page that edits them.
*/

beforeEach(function (): void {
    $this->admin = User::factory()->create();
});

/*
| Defaults
*/

it('leaves every feature on when nothing has been set', function (): void {
    forgetSettings();

    $this->get('/en')->assertOk()->assertSee('href="/ar"', escape: false);
    $this->get('/ar')->assertOk();
    $this->get('/en')->assertSee("localStorage.getItem('theme')", escape: false);
});

/*
| Languages
*/

it('redirects the other language to the published one when multilingual is off', function (): void {
    setSetting('multilingual', false);
    setSetting('primary_locale', 'en');

    $this->get('/ar')->assertRedirect('/en');
    $this->get('/ar/projects/boxesvdr')->assertRedirect('/en/projects/boxesvdr');
});

it('keeps serving the published language when multilingual is off', function (): void {
    setSetting('multilingual', false);
    setSetting('primary_locale', 'ar');

    $this->get('/ar')->assertOk();
    $this->get('/en')->assertRedirect('/ar');
});

it('hides the language switcher when multilingual is off', function (): void {
    setSetting('multilingual', false);
    setSetting('primary_locale', 'en');

    $this->get('/en')
        ->assertOk()
        ->assertDontSee('title="'.__('Switch language').'"', escape: false);
});

it('drops the other language from hreflang and the sitemap when off', function (): void {
    setSetting('multilingual', false);
    setSetting('primary_locale', 'en');

    $this->get('/en')->assertOk()->assertDontSee('hreflang="ar"', escape: false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/en', escape: false)
        ->assertDontSee('<loc>'.route('home', ['locale' => 'ar']).'</loc>', escape: false);
});

/*
| Theme
*/

it('ships the theme toggle and its script by default', function (): void {
    $this->get('/en')
        ->assertOk()
        ->assertSee('aria-label="'.__('Toggle theme').'"', escape: false)
        ->assertSee("localStorage.getItem('theme')", escape: false);
});

it('removes the toggle and pins the theme when the switch is off', function (): void {
    setSetting('theme_toggle', false);
    setSetting('theme', 'dark');

    $response = $this->get('/en')->assertOk();

    $response->assertDontSee('aria-label="'.__('Toggle theme').'"', escape: false);
    // The stored preference is ignored rather than consulted.
    $response->assertDontSee("localStorage.getItem('theme')", escape: false);
    // Dark is already applied in the served HTML, before any script runs.
    $response->assertSee('<html lang="en" dir="ltr" class="dark">', escape: false);
});

it('pins to light without the dark class', function (): void {
    setSetting('theme_toggle', false);
    setSetting('theme', 'light');

    $this->get('/en')
        ->assertOk()
        ->assertSee('<html lang="en" dir="ltr">', escape: false);
});

/*
| The dashboard page
*/

it('requires a login to reach the settings page', function (): void {
    $this->get('/admin/settings')->assertRedirect('/admin/login');
});

it('shows every switch to an admin', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/settings')
        ->assertOk()
        ->assertSee(__('Blog'))
        ->assertSee(__('Both languages'))
        ->assertSee(__('Light and dark'));
});

it('saves the switches from the dashboard', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SettingsEditor::class)
        ->set('blogEnabled', false)
        ->set('multilingual', false)
        ->set('primaryLocale', 'ar')
        ->set('themeToggle', false)
        ->set('theme', 'dark')
        ->call('save')
        ->assertHasNoErrors();

    $this->get('/ar/blog')->assertNotFound();
    $this->get('/en')->assertRedirect('/ar');
    $this->get('/ar')->assertOk()->assertSee('class="dark"', escape: false);
});

it('refuses a locale the site is not built in', function (): void {
    Livewire::actingAs($this->admin)
        ->test(SettingsEditor::class)
        ->set('primaryLocale', 'fr')
        ->call('save')
        ->assertHasErrors('primaryLocale');
});

it('links to the settings page from the dashboard and sidebar', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.settings'), escape: false);
});
