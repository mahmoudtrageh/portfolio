<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ContentSection;
use App\Support\Content;
use App\Support\Thumbnail;
use App\Support\YouTubeFeed;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Edits one content section.
 *
 * Everything about the form — which fields exist, whether they are translated,
 * whether the section is a singleton or a reorderable list — comes from
 * config/dashboard.php, so this one component covers all twelve sections and a
 * new field is a config line rather than new code.
 */
final class SectionEditor extends Component
{
    use WithFileUploads;

    /** Where uploaded images live on the public disk. */
    private const IMAGE_DIR = 'uploads';

    /** The section key, e.g. 'projects'. Locked so the browser cannot swap it. */
    #[Locked]
    public string $section = '';

    /**
     * Pending uploads as $uploads[entry index][field]. Nested, not flat:
     * `wire:model="uploads.0.photo"` makes Livewire treat the dots as a path,
     * so a flat "0.photo" key would never be written to.
     *
     * They live here rather than in $entries so an upload in progress never
     * overwrites the stored path until save() actually commits it.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $uploads = [];

    /**
     * The entries being edited, as plain arrays.
     *
     * A singleton holds exactly one entry at index 0; a list holds many, and
     * their array order is the order they render in on the site.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $entries = [];

    /** Which entry is expanded in the UI. Null means all are collapsed. */
    public ?int $open = null;

    public function mount(string $section): void
    {
        abort_unless(array_key_exists($section, config('dashboard.sections')), 404);

        $this->section = $section;
        $this->load();

        // A singleton has no list to browse, so open its only entry directly.
        if ($this->isSingle()) {
            $this->open = 0;
        }
    }

    /**
     * The schema for this section.
     *
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        /** @var array<string, mixed> $schema */
        $schema = config("dashboard.sections.{$this->section}");

        return $schema;
    }

    public function isSingle(): bool
    {
        return ($this->schema()['type'] ?? 'list') === 'single';
    }

    /**
     * Read the section's rows out of the database into $entries.
     */
    private function load(): void
    {
        $this->entries = ContentSection::query()
            ->section($this->section)
            ->get()
            ->map(fn (ContentSection $row): array => $this->normalise($row->data))
            ->values()
            ->all();

        // A singleton with no row yet still needs one blank entry to edit.
        if ($this->isSingle() && $this->entries === []) {
            $this->entries = [$this->blank()];
        }
    }

    /**
     * Fill in any field the stored entry is missing, so the form always has
     * every input bound to something and never hits an undefined index.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalise(array $data): array
    {
        $blank = $this->blank();

        foreach ($blank as $key => $default) {
            $data[$key] ??= $default;

            // A translated field stored as a bare string (or missing a locale)
            // is widened to a full locale pair so both inputs bind.
            if (is_array($default) && array_keys($default) === Content::locales()) {
                $data[$key] = is_array($data[$key])
                    ? array_merge($default, $data[$key])
                    : array_fill_keys(Content::locales(), (string) $data[$key]);
            }
        }

        return $data;
    }

    /**
     * An empty entry shaped by the schema.
     *
     * @return array<string, mixed>
     */
    private function blank(): array
    {
        $entry = [];
        $pair = array_fill_keys(Content::locales(), '');

        foreach ($this->schema()['fields'] as $key => $field) {
            $entry[$key] = match ($field['type']) {
                'text', 'textarea', 'markdown' => $pair,
                // One document, served in every language.
                'plain', 'image', 'file' => '',
                'bool' => false,
                'list', 'list.text', 'repeater' => [],
                default => '',
            };
        }

        return $entry;
    }

    public function addEntry(): void
    {
        $this->entries[] = $this->blank();
        $this->open = count($this->entries) - 1;
    }

    public function removeEntry(int $index): void
    {
        // Drop any files this entry owned, then any upload still pending for
        // it — otherwise a save would write a file for a row that is gone.
        foreach ($this->schema()['fields'] as $key => $field) {
            if (in_array($field['type'], ['image', 'file'], true)) {
                $this->deleteImage((string) ($this->entries[$index][$key] ?? ''));
            }
        }

        unset($this->uploads[$index], $this->entries[$index]);
        $this->entries = array_values($this->entries);

        // $entries was just reindexed, so pending uploads keyed by the old
        // positions have to shift with it or they would attach to the wrong row.
        $this->uploads = $this->reindexUploads($index);

        $this->open = null;
    }

    /**
     * Shift pending upload keys down to match $entries after the row at
     * $removed was taken out.
     *
     * @return array<int, array<string, mixed>>
     */
    private function reindexUploads(int $removed): array
    {
        $shifted = [];

        foreach ($this->uploads as $index => $fields) {
            if ($index === $removed) {
                continue;
            }

            $shifted[$index > $removed ? $index - 1 : $index] = $fields;
        }

        return $shifted;
    }

    /**
     * Move an entry one place up or down. Array order is render order, so this
     * is all reordering needs to be.
     */
    public function move(int $index, int $direction): void
    {
        $target = $index + $direction;

        if (! isset($this->entries[$index], $this->entries[$target])) {
            return;
        }

        [$this->entries[$index], $this->entries[$target]]
            = [$this->entries[$target], $this->entries[$index]];

        // Pending uploads are keyed by row position, so they have to travel
        // with the rows they belong to.
        $moved = $this->uploads[$index] ?? null;
        $displaced = $this->uploads[$target] ?? null;

        unset($this->uploads[$index], $this->uploads[$target]);

        if ($moved !== null) {
            $this->uploads[$target] = $moved;
        }

        if ($displaced !== null) {
            $this->uploads[$index] = $displaced;
        }

        if ($this->open === $index) {
            $this->open = $target;
        }
    }

    /**
     * Append a value to a repeatable field ('stack', 'points', 'stats').
     */
    public function addItem(int $index, string $field): void
    {
        $type = $this->schema()['fields'][$field]['type'] ?? null;

        $this->entries[$index][$field][] = match ($type) {
            'list' => '',
            'list.text' => array_fill_keys(Content::locales(), ''),
            'repeater' => $this->blankRepeaterRow($field),
            default => '',
        };
    }

    public function removeItem(int $index, string $field, int $item): void
    {
        unset($this->entries[$index][$field][$item]);

        $this->entries[$index][$field] = array_values($this->entries[$index][$field]);
    }

    /**
     * An empty row for a repeater field.
     *
     * @return array<string, mixed>
     */
    private function blankRepeaterRow(string $field): array
    {
        $row = [];
        $pair = array_fill_keys(Content::locales(), '');

        foreach ($this->schema()['fields'][$field]['fields'] ?? [] as $key => $sub) {
            // 'plain' sub-fields (an ID, a URL) hold one value, not a locale pair.
            $row[$key] = in_array($sub['type'], ['text', 'textarea'], true) ? $pair : '';
        }

        return $row;
    }

    /**
     * Validate an image the moment it is chosen, so a bad file is rejected at
     * the picker rather than silently at save time.
     */
    public function updatedUploads(mixed $value, string $key): void
    {
        // $key arrives as "<index>.<field>", or "<index>.<field>.<locale>" for
        // a file field, which holds one document per language.
        [, $field] = array_pad(explode('.', $key, 3), 3, '');

        $rule = ($this->schema()['fields'][$field]['type'] ?? '') === 'file'
            ? $this->documentRule()
            : $this->imageRule();

        $this->validateOnly("uploads.{$key}", [
            "uploads.{$key}" => $rule,
        ], [], ["uploads.{$key}" => $this->imageLabel($key)]);
    }

    /**
     * Clear a stored image, or cancel one that has been picked but not saved.
     */
    public function removeImage(int $index, string $field): void
    {
        unset($this->uploads[$index][$field]);

        $this->entries[$index][$field] = '';
    }

    /**
     * A downloadable document. PDF only: it is the one format that renders the
     * same everywhere and carries no macro surface.
     *
     * @return list<string>
     */
    private function documentRule(): array
    {
        return ['file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:8192'];
    }

    /**
     * @return list<string>
     */
    private function imageRule(): array
    {
        // SVG is allowed because a logo is often one, but it is served from a
        // path the site controls and is never inlined into the page.
        return ['image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'];
    }

    private function imageLabel(string $key): string
    {
        [, $field] = array_pad(explode('.', $key, 2), 2, '');

        $label = Content::localise($this->schema()['fields'][$field]['label'] ?? $field);

        return is_string($label) ? $label : $field;
    }

    /**
     * Move each pending upload onto the public disk and write its path into the
     * entry, deleting whatever file it replaced.
     */
    private function commitUploads(): void
    {
        foreach ($this->uploads as $index => $fields) {
            foreach ((array) $fields as $field => $file) {
                if (! $file instanceof TemporaryUploadedFile || ! isset($this->entries[$index])) {
                    continue;
                }

                $previous = (string) ($this->entries[$index][$field] ?? '');

                $path = $file->store(self::IMAGE_DIR, 'public');

                // A failed write returns false. Keeping the previous path is
                // better than storing an empty one and losing the old file too.
                if ($path === false) {
                    continue;
                }

                // Fields that declare a ceiling are downscaled on the way in,
                // so an oversized upload costs bandwidth once rather than on
                // every page view.
                $max = $this->schema()['fields'][$field]['max_size'] ?? null;

                if ($max !== null) {
                    Thumbnail::shrink($path, (int) $max);
                }

                $this->entries[$index][$field] = $path;

                $this->deleteImage($previous);
            }
        }

        $this->uploads = [];
    }

    /**
     * Delete a stored image, ignoring anything that is not ours to remove.
     */
    private function deleteImage(string $path): void
    {
        if ($path === '' || ! str_starts_with($path, self::IMAGE_DIR.'/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function save(): void
    {
        // Passing the rules positionally: Livewire resolves `rules` as a magic
        // property when given none, which cannot see a method on this class and
        // throws MissingRulesException for any section with nothing to validate.
        $rules = $this->rules();

        if ($rules !== []) {
            $this->validate($rules, [], $this->validationAttributes());
        }

        // Files land on disk only after the text fields validate, so a failed
        // save never leaves an orphaned upload behind.
        $this->commitUploads();

        // A singleton always has exactly one row. Saving with none — which can
        // happen if the component is driven while its state is empty — would
        // delete the section outright and take the content with it, so refuse
        // rather than destroy.
        if ($this->isSingle() && $this->entries === []) {
            $this->load();

            return;
        }

        // Replace the section wholesale: entries were reordered and removed in
        // memory, so row-by-row diffing would only be a slower way to arrive at
        // the same state. Wrapped in a transaction so a failure part-way cannot
        // leave the section deleted and unwritten.
        DB::transaction(function (): void {
            ContentSection::query()->where('section', $this->section)->delete();

            foreach (array_values($this->entries) as $position => $entry) {
                ContentSection::query()->create([
                    'section' => $this->section,
                    'position' => $position,
                    'data' => $this->clean($entry),
                ]);
            }
        });

        Content::flush();

        // A changed channel ID or video count should show up straight away
        // rather than after the feed cache expires.
        if ($this->section === 'channel') {
            foreach ($this->entries as $entry) {
                YouTubeFeed::flush((string) ($entry['channel_id'] ?? ''));
            }
        }

        $this->dispatch('saved');
        $this->load();
    }

    /**
     * Drop blank repeatable rows so an empty input the user never filled in
     * does not reach the site as an empty bullet.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function clean(array $entry): array
    {
        foreach ($this->schema()['fields'] as $key => $field) {
            if (! in_array($field['type'], ['list', 'list.text', 'repeater'], true)) {
                continue;
            }

            $entry[$key] = array_values(array_filter(
                (array) ($entry[$key] ?? []),
                self::hasContent(...),
            ));
        }

        return $entry;
    }

    /**
     * Does this value hold anything, at any depth?
     *
     * Repeatable fields nest to different depths — a tech stack is a list of
     * strings, a bullet list is a list of locale pairs, and a repeater row is a
     * map of fields each holding a locale pair. Recursing keeps one rule for
     * all three, instead of casting an array to a string and warning.
     */
    private static function hasContent(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $nested) {
                if (self::hasContent($nested)) {
                    return true;
                }
            }

            return false;
        }

        return trim((string) $value) !== '';
    }

    /**
     * Required-field rules: every translated field needs both locales, so the
     * site can never fall back to a blank half.
     *
     * @return array<string, list<string>>
     */
    private function rules(): array
    {
        $rules = [];

        foreach ($this->entries as $i => $entry) {
            foreach ($this->schema()['fields'] as $key => $field) {
                if (in_array($field['type'], ['text', 'textarea', 'markdown'], true)) {
                    // Most translated fields must be filled in both languages so
                    // the site never falls back to a blank half. A field marked
                    // optional (a gloss that only one locale needs) may be empty
                    // — but must still be a string, not an array.
                    $rule = ($field['optional'] ?? false) ? ['nullable', 'string'] : ['required', 'string'];

                    foreach (Content::locales() as $locale) {
                        $rules["entries.{$i}.{$key}.{$locale}"] = $rule;
                    }
                }

                // Re-checked here as well as on selection: the picker's own
                // validation can be bypassed by driving the component directly.
                if ($field['type'] === 'image' && isset($this->uploads[$i][$key])) {
                    $rules["uploads.{$i}.{$key}"] = $this->imageRule();
                }

                if ($field['type'] === 'file' && isset($this->uploads[$i][$key])) {
                    $rules["uploads.{$i}.{$key}"] = $this->documentRule();
                }
            }
        }

        return $rules;
    }

    /**
     * Readable names for the validation messages, so an error reads
     * "Name (EN) is required" rather than quoting the array path.
     *
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->entries as $i => $entry) {
            foreach ($this->schema()['fields'] as $key => $field) {
                // Only translated scalars get a rule, so only they need a name.
                // A repeater's label is a nested array and would not stringify.
                if (! in_array($field['type'], ['text', 'textarea', 'markdown'], true)) {
                    continue;
                }

                $label = Content::localise($field['label'] ?? $key);
                $label = is_string($label) ? $label : $key;

                foreach (Content::locales() as $locale) {
                    $attributes["entries.{$i}.{$key}.{$locale}"]
                        = $label.' ('.mb_strtoupper($locale).')';
                }
            }
        }

        return $attributes;
    }

    public function render(): View
    {
        return view('livewire.admin.section-editor');
    }
}
