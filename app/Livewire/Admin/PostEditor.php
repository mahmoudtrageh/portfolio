<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Support\Content;
use App\Support\Markdown;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Writes one blog post, in both languages.
 *
 * Each locale has its own slug, title, excerpt, body and publish switch, so a
 * post can go live in Arabic while its English translation is still a draft.
 */
final class PostEditor extends Component
{
    use WithFileUploads;

    private const IMAGE_DIR = 'uploads';

    /** Null while creating; set once the post exists. */
    #[Locked]
    public ?int $postId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public mixed $coverUpload = null;

    /** Which language tab is open. Purely a UI concern. */
    public string $tab = 'ar';

    /** Whether the Markdown preview is showing for the open tab. */
    public bool $preview = false;

    public function mount(?Post $post = null): void
    {
        $this->tab = Content::locales()[0];

        if ($post?->exists) {
            $this->postId = $post->id;
            $this->form = [
                'title_ar' => $post->title_ar ?? '',
                'title_en' => $post->title_en ?? '',
                'slug_ar' => $post->slug_ar ?? '',
                'slug_en' => $post->slug_en ?? '',
                'excerpt_ar' => $post->excerpt_ar ?? '',
                'excerpt_en' => $post->excerpt_en ?? '',
                'body_ar' => $post->body_ar ?? '',
                'body_en' => $post->body_en ?? '',
                'published_ar' => (bool) $post->published_ar,
                'published_en' => (bool) $post->published_en,
                'cover' => $post->cover ?? '',
                'published_at' => $post->published_at?->format('Y-m-d\TH:i')
                    ?? now()->format('Y-m-d\TH:i'),
            ];

            return;
        }

        $this->form = [
            'title_ar' => '', 'title_en' => '',
            'slug_ar' => '', 'slug_en' => '',
            'excerpt_ar' => '', 'excerpt_en' => '',
            'body_ar' => '', 'body_en' => '',
            'published_ar' => false, 'published_en' => false,
            'cover' => '',
            'published_at' => now()->format('Y-m-d\TH:i'),
        ];
    }

    /**
     * Fill an empty slug from the title as it is typed, so a writer never has
     * to think about URLs — but never overwrite one they set by hand.
     */
    public function updatedForm(mixed $value, string $key): void
    {
        if (! str_starts_with($key, 'title_')) {
            return;
        }

        $locale = substr($key, 6);

        if (trim((string) ($this->form["slug_{$locale}"] ?? '')) === '') {
            $this->form["slug_{$locale}"] = $this->slugify((string) $value);
        }
    }

    /**
     * Slugify, keeping Arabic letters rather than transliterating them away —
     * Str::slug() would reduce an Arabic title to an empty string.
     */
    private function slugify(string $title): string
    {
        $slug = trim($title);
        $slug = preg_replace('/[^\p{L}\p{N}\s-]+/u', '', $slug) ?? $slug;
        $slug = preg_replace('/[\s_]+/u', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        return Str::lower(Str::limit($slug, 80, ''));
    }

    public function updatedCoverUpload(): void
    {
        $this->validateOnly('coverUpload', [
            'coverUpload' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);
    }

    public function removeCover(): void
    {
        $this->coverUpload = null;
        $this->form['cover'] = '';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'form.published_at' => ['required', 'date'],
            'coverUpload' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];

        foreach (Content::locales() as $locale) {
            // A slug must be unique across posts, but only where it is set.
            $rules["form.slug_{$locale}"] = [
                'nullable', 'string', 'max:120',
                'regex:/^[\p{L}\p{N}-]+$/u',
                Rule::unique('posts', "slug_{$locale}")->ignore($this->postId),
            ];

            $rules["form.title_{$locale}"] = ['nullable', 'string', 'max:200'];
            $rules["form.excerpt_{$locale}"] = ['nullable', 'string', 'max:300'];
            $rules["form.body_{$locale}"] = ['nullable', 'string'];

            // Publishing a locale requires the parts that locale needs to render.
            if ($this->form["published_{$locale}"] ?? false) {
                $rules["form.title_{$locale}"] = ['required', 'string', 'max:200'];
                $rules["form.slug_{$locale}"][0] = 'required';
                $rules["form.body_{$locale}"] = ['required', 'string'];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach (Content::locales() as $locale) {
            $suffix = ' ('.mb_strtoupper($locale).')';

            $attributes["form.title_{$locale}"] = __('Title').$suffix;
            $attributes["form.slug_{$locale}"] = __('Slug').$suffix;
            $attributes["form.excerpt_{$locale}"] = __('Excerpt').$suffix;
            $attributes["form.body_{$locale}"] = __('Body').$suffix;
        }

        $attributes['form.published_at'] = __('Publish date');
        $attributes['coverUpload'] = __('Cover image');

        return $attributes;
    }

    public function save(): void
    {
        $this->validate();

        $data = $this->form;

        if ($this->coverUpload instanceof TemporaryUploadedFile) {
            $previous = (string) ($data['cover'] ?? '');
            $data['cover'] = $this->coverUpload->store(self::IMAGE_DIR, 'public');

            if ($previous !== '' && str_starts_with($previous, self::IMAGE_DIR.'/')) {
                Storage::disk('public')->delete($previous);
            }

            $this->coverUpload = null;
        }

        $post = $this->postId !== null
            ? Post::query()->findOrFail($this->postId)
            : new Post;

        $post->fill($data)->save();

        $this->postId = $post->id;
        $this->form['cover'] = $post->cover ?? '';

        $this->dispatch('saved');
    }

    public function delete(): void
    {
        if ($this->postId === null) {
            return;
        }

        $post = Post::query()->find($this->postId);

        if ($post !== null) {
            if ($post->cover && str_starts_with($post->cover, self::IMAGE_DIR.'/')) {
                Storage::disk('public')->delete($post->cover);
            }

            $post->delete();
        }

        $this->redirectRoute('admin.posts', navigate: false);
    }

    /**
     * Rendered Markdown for the open tab, for the live preview.
     */
    public function previewHtml(): string
    {
        return Markdown::toHtml((string) ($this->form["body_{$this->tab}"] ?? ''));
    }

    public function render(): View
    {
        return view('livewire.admin.post-editor');
    }
}
