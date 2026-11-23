<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The content behind the Home, Projects and Contact pages.
 *
 * Everything is edited in the dashboard (see config/dashboard.php). Each
 * section falls back to the built-in copy below while it is still empty, so
 * the site never renders blank between a deploy and the migration that seeds
 * the dashboard.
 */
final class Profile
{
    /**
     * @return array{company: string, short_name: string, url: string, work_mode: string, products: list<array{name: string, url: string, about: string}>}
     */
    public static function current(): array
    {
        $stored = Content::get('current_job', []);
        $fallback = self::defaultCurrent();

        if (! is_array($stored) || trim((string) ($stored['company'] ?? '')) === '') {
            return $fallback;
        }

        $products = array_values(array_filter(
            array_map(fn (array $p): array => [
                'name' => trim((string) ($p['name'] ?? '')),
                'url' => trim((string) ($p['url'] ?? '')),
                'about' => trim((string) ($p['about'] ?? '')),
            ], (array) ($stored['products'] ?? [])),
            fn (array $p): bool => $p['name'] !== '',
        ));

        return [
            'company' => trim((string) $stored['company']),
            'short_name' => trim((string) ($stored['short_name'] ?? '')) ?: trim((string) $stored['company']),
            'url' => trim((string) ($stored['url'] ?? '')),
            'work_mode' => self::workMode(trim((string) ($stored['work_mode'] ?? ''))),
            'products' => $products,
        ];
    }

    /**
     * Previous jobs, newest first: by end month, then start month (YYYY-MM,
     * set in the dashboard). Jobs with no dates count as the most recent, so
     * they stay on top in their dashboard order and dated jobs settle below:
     * dating only the oldest job puts it at the bottom. Only the start year
     * is displayed.
     *
     * @return list<array{role: string, company: string, url: string, work_mode: string, year: string}>
     */
    public static function previous(): array
    {
        $rows = [];

        foreach (Content::list('previous_jobs') as $entry) {
            $company = self::field($entry, 'company');

            if ($company !== '') {
                $rows[] = [
                    'role' => self::field($entry, 'role'),
                    'company' => $company,
                    'url' => self::field($entry, 'url'),
                    'work_mode' => self::workMode(self::field($entry, 'work_mode')),
                    'start' => self::field($entry, 'start'),
                    'end' => self::field($entry, 'end'),
                ];
            }
        }

        if ($rows === []) {
            $rows = self::defaultPrevious();
        }

        // YYYY-MM strings compare correctly as text; an undated job sorts as
        // '9999' (newest). usort is stable, so equal keys (e.g. several undated
        // jobs) keep their dashboard order.
        $key = fn (array $row): array => [$row['end'] ?: $row['start'] ?: '9999', $row['start'] ?: '9999'];
        usort($rows, fn (array $a, array $b): int => $key($b) <=> $key($a));

        return array_map(fn (array $row): array => [
            'role' => $row['role'],
            'company' => $row['company'],
            'url' => $row['url'],
            'work_mode' => $row['work_mode'],
            'year' => preg_match('/^\d{4}/', $row['start'], $m) === 1 ? self::year($m[0]) : '',
        ], $rows);
    }

    /**
     * A four-digit year for display, in Arabic-Indic digits on the Arabic
     * site (matching the certificate dates).
     */
    public static function year(string $year): string
    {
        return app()->getLocale() === 'ar'
            ? strtr($year, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩'])
            : $year;
    }

    /**
     * The technologies listed on the home page, trimmed and without blanks.
     * Falls back to the built-in list only while the section was never saved,
     * so emptying it in the dashboard hides the line.
     *
     * @return list<string>
     */
    public static function techStack(): array
    {
        $stored = Content::get('tech_stack', []);

        if (! is_array($stored) || ! array_key_exists('items', $stored)) {
            return self::seedTechStack();
        }

        return array_values(array_filter(
            array_map(fn (mixed $item): string => is_scalar($item) ? trim((string) $item) : '', (array) $stored['items']),
            fn (string $item): bool => $item !== '',
        ));
    }

    /**
     * @return list<array{title: string, lead: string, items: list<array{name: string, url: string, about: string}>}>
     */
    public static function projectGroups(): array
    {
        $current = self::current();

        $jobs = self::projects('job_projects');
        $freelance = self::projects('freelance_projects');
        // No built-in fallback: emptying the list in the dashboard hides the group.
        $side = self::projects('side_projects');

        return array_values(array_filter([
            [
                'title' => self::t('At :company', ['company' => $current['short_name']]),
                'lead' => self::t('What I work on today at :company.', ['company' => $current['company']]),
                'items' => array_map(fn (array $p): array => [
                    'name' => $p['name'],
                    'url' => $p['url'],
                    'about' => self::capitalise($p['about']),
                ], $current['products']),
            ],
            [
                'title' => self::t('At previous jobs'),
                'lead' => self::t('Products I built and maintained as part of engineering teams.'),
                'items' => $jobs !== [] ? $jobs : self::defaultJobProjects(),
            ],
            [
                'title' => self::t('As a freelancer'),
                'lead' => self::t('Seven years of freelance full-stack work, alongside my full-time roles.'),
                'items' => $freelance !== [] ? $freelance : self::defaultFreelanceProjects(),
            ],
            [
                'title' => self::t('Side projects'),
                'lead' => self::t('Products I built and run on my own.'),
                'items' => $side,
            ],
        ], fn (array $group): bool => $group['items'] !== []));
    }

    /**
     * Email, phone and every social link that is not still a placeholder.
     *
     * @return list<array{label: string, value: string, href: string, external: bool}>
     */
    public static function contacts(): array
    {
        $identity = Content::get('identity');
        $rows = [];

        $email = trim((string) ($identity['email'] ?? ''));
        $phone = trim((string) ($identity['phone'] ?? ''));

        if ($email !== '') {
            $rows[] = ['label' => self::t('Email'), 'value' => $email, 'href' => 'mailto:'.$email, 'external' => false];
        }

        if ($phone !== '') {
            $rows[] = ['label' => self::t('Phone'), 'value' => $phone, 'href' => 'tel:'.str_replace(' ', '', $phone), 'external' => false];
        }

        foreach (Content::list('socials') as $social) {
            $url = trim((string) ($social['url'] ?? ''));

            if ($url === '' || str_contains($url, '«')) {
                continue;
            }

            $rows[] = [
                'label' => (string) $social['label'],
                'value' => rtrim((string) preg_replace('#^https?://(www\.)?#', '', $url), '/'),
                'href' => $url,
                'external' => true,
            ];
        }

        return $rows;
    }

    /**
     * The label for a stored work mode ('remote', 'onsite'), localised; empty
     * when unset or unknown. Current and previous jobs share one option list.
     */
    private static function workMode(string $value): string
    {
        $label = $value !== '' ? config("dashboard.sections.previous_jobs.fields.work_mode.options.{$value}") : null;
        $label = is_array($label) ? Content::localise($label) : '';

        return is_string($label) ? $label : '';
    }

    /**
     * Age in whole years from the dashboard birthdate, or null while it is
     * unset or unreadable.
     */
    public static function age(): ?int
    {
        return self::yearsSince('identity.birthdate');
    }

    /**
     * Years of experience, in whole years, from the dashboard's software
     * career start date; null while it is unset, unreadable or under a year.
     */
    public static function experience(): ?int
    {
        return self::yearsSince('identity.career_start');
    }

    /**
     * How many projects the Projects page lists, across every group.
     */
    public static function projectCount(): int
    {
        return array_sum(array_map(fn (array $group): int => count($group['items']), self::projectGroups()));
    }

    /**
     * "14 projects" / "١٤ مشروعًا", with the Arabic noun form the count needs;
     * empty for none.
     */
    public static function projectCountLabel(): string
    {
        return self::countLabel(self::projectCount());
    }

    /**
     * The project-count wording for a given number, in the active locale.
     */
    public static function countLabel(int $count): string
    {
        if ($count === 0) {
            return '';
        }

        if (app()->getLocale() !== 'ar') {
            return $count === 1 ? self::t('1 project') : self::t(':count projects', ['count' => (string) $count]);
        }

        $digits = strtr((string) $count, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);

        return match (true) {
            $count === 1 => 'مشروع واحد',
            $count === 2 => 'مشروعان',
            $count % 100 >= 3 && $count % 100 <= 10 => $digits.' مشاريع',
            default => $digits.' مشروعًا',
        };
    }

    /**
     * Whole years from a stored Y-m-d date until today, or null when the date
     * is unset, unreadable, or less than a year ago.
     */
    private static function yearsSince(string $key): ?int
    {
        $date = trim((string) Content::get($key, ''));

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return null;
        }

        try {
            $years = (int) Carbon::parse($date)->diffInYears(Carbon::today());
        } catch (\Throwable) {
            return null;
        }

        return $years > 0 ? $years : null;
    }

    /**
     * Location from the dashboard, already localised; empty when unset.
     */
    public static function location(): string
    {
        $location = Content::get('identity.location', '');

        return is_string($location) ? trim($location) : '';
    }

    /**
     * A project list section, localised and trimmed, without nameless rows.
     *
     * @return list<array{name: string, url: string, about: string}>
     */
    private static function projects(string $section): array
    {
        $rows = [];

        foreach (Content::list($section) as $entry) {
            $name = self::field($entry, 'name');

            if ($name !== '') {
                $rows[] = ['name' => $name, 'url' => self::field($entry, 'url'), 'about' => self::field($entry, 'about')];
            }
        }

        return $rows;
    }

    /**
     * One already-localised value from a stored entry, as a trimmed string.
     *
     * @param  array<string, mixed>  $entry
     */
    private static function field(array $entry, string $key): string
    {
        $value = $entry[$key] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /**
     * __() is typed array|string (a key can name a whole group); every key
     * used here is a sentence, so narrow it to the string it always is.
     *
     * @param  array<string, string>  $replace
     */
    private static function t(string $key, array $replace = []): string
    {
        $value = __($key, $replace);

        return is_string($value) ? $value : $key;
    }

    private static function capitalise(string $text): string
    {
        return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Built-in copy
    |--------------------------------------------------------------------------
    |
    | Used while a dashboard section is empty, and seeded into the dashboard by
    | the 2026_11_21 migration. Bilingual so the seed can store both locales;
    | the fallbacks read the active one.
    |
    */

    /**
     * @return array<string, mixed>
     */
    public static function seedCurrent(): array
    {
        return [
            'company' => 'Boxes Intelligent Communications',
            'short_name' => 'Boxes',
            'url' => 'https://boxes.sa/',
            'products' => [
                ['name' => 'Boxes VDR', 'url' => 'https://boxesvdr.com/en', 'about' => ['ar' => 'غرفة بيانات افتراضية آمنة', 'en' => 'a secure virtual data room']],
                ['name' => 'Linkatik', 'url' => 'https://linkatik.com/', 'about' => ['ar' => 'منصة روابط شخصية', 'en' => 'a bio-link platform']],
                ['name' => 'ASP Paris', 'url' => 'https://aspparis.com/', 'about' => ['ar' => 'جهة تدريب مهني', 'en' => 'a professional training provider']],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function seedPrevious(): array
    {
        return [
            ['company' => 'Tazamun', 'role' => ['ar' => 'مهندس Backend', 'en' => 'Backend Engineer'], 'url' => '', 'start' => '2023-09', 'end' => '2025-05'],
            ['company' => 'MAWAHEB LLC', 'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'], 'url' => '', 'start' => '2023-03', 'end' => '2023-09'],
            ['company' => 'Algoriza', 'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'], 'url' => 'https://www.algoriza.com', 'start' => '2020-08', 'end' => '2023-03'],
            ['company' => 'SpectraApps', 'role' => ['ar' => 'متدرّب PHP Laravel', 'en' => 'PHP Laravel Intern'], 'url' => '', 'start' => '2018-12', 'end' => '2019-04'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function seedTechStack(): array
    {
        return ['PHP', 'Laravel', 'Livewire', 'MySQL', 'PostgreSQL', 'Redis', 'Docker', 'Nginx', 'AWS', 'GitHub Actions'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function seedJobProjects(): array
    {
        return [
            ['name' => ['ar' => 'PetroApp', 'en' => 'PetroApp'], 'url' => 'https://petroapp.com/egypt/ar/companies-ar/', 'about' => ['ar' => 'تطبيق خدمات سيارات بأكثر من ٥٠٠ ألف تحميل', 'en' => 'A car services app with over 500K downloads']],
            ['name' => ['ar' => 'Vowalaa', 'en' => 'Vowalaa'], 'url' => 'https://www.vowalaa.com/', 'about' => ['ar' => 'نظام ERP سحابي للمخزون والمبيعات والمحاسبة', 'en' => 'A cloud ERP for inventory, sales and accounting']],
            ['name' => ['ar' => 'استشرني', 'en' => 'Consult Me (Estashirni)'], 'url' => 'https://play.google.com/store/apps/details?id=com.tazamun.estashirni&hl=en_US', 'about' => ['ar' => 'يربط الناس بمحامين للاستشارات القانونية', 'en' => 'Connects people with lawyers for legal advice']],
            ['name' => ['ar' => 'Palmcodes', 'en' => 'Palmcodes'], 'url' => '', 'about' => ['ar' => 'متجر بطاقات عملات رقمية', 'en' => 'A cryptocurrency cards store']],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function seedFreelanceProjects(): array
    {
        return [
            ['name' => ['ar' => 'MatX', 'en' => 'MatX'], 'url' => 'https://matxapp.com', 'about' => ['ar' => 'سوق للأثاث ومواد التصميم', 'en' => 'A marketplace for furniture and design materials']],
            ['name' => ['ar' => 'DSync Solutions', 'en' => 'DSync Solutions'], 'url' => 'https://www.dsyncsolutions.com/en/', 'about' => ['ar' => 'منصة لتتبّع الأصول', 'en' => 'An asset tracking platform']],
            ['name' => ['ar' => 'عمرة بدل', 'en' => 'Umrah Badl'], 'url' => 'https://play.google.com/store/apps/details?id=com.umrahbadl.umrahbadl', 'about' => ['ar' => 'يربط من يريد أداء العمرة عن قريب له بمتطوعين', 'en' => 'Connects people who want Umrah performed for a relative with volunteers']],
        ];
    }

    /**
     * Seeded into the dashboard by SideProjectsSeeder.
     *
     * @return list<array<string, mixed>>
     */
    public static function seedSideProjects(): array
    {
        return [
            ['name' => ['ar' => 'وظيفة', 'en' => 'Waazefa'], 'url' => 'https://waazefa.com/', 'about' => ['ar' => 'منصة وظائف تقنية في مصر', 'en' => 'A tech job board for Egypt']],
            ['name' => ['ar' => 'قطفة', 'en' => 'Qotfa'], 'url' => 'https://qotfa.com/', 'about' => ['ar' => 'نظام جاهز لشركات التوصيل', 'en' => 'A ready-made system for delivery companies']],
            ['name' => ['ar' => 'كوميز', 'en' => 'Koomiz'], 'url' => 'https://koomiz.binaasoft.com/', 'about' => ['ar' => 'نقاط بيع ومخزون وتقارير للمتاجر', 'en' => 'Point of sale, inventory and reports for shops']],
            ['name' => ['ar' => 'سستم إدارة', 'en' => 'BinaaSoft Shop'], 'url' => 'https://shop.binaasoft.com/', 'about' => ['ar' => 'برنامج لمحلات الموبايل ومراكز الصيانة', 'en' => 'Desktop software for phone shops and repair centres']],
        ];
    }

    /**
     * @return array{company: string, short_name: string, url: string, work_mode: string, products: list<array{name: string, url: string, about: string}>}
     */
    private static function defaultCurrent(): array
    {
        /** @var array{company: string, short_name: string, url: string, work_mode: string, products: list<array{name: string, url: string, about: string}>} $current */
        $current = Content::localise(self::seedCurrent() + ['work_mode' => '']);

        return $current;
    }

    /**
     * @return list<array{role: string, company: string, url: string, work_mode: string, start: string, end: string}>
     */
    private static function defaultPrevious(): array
    {
        /** @var list<array{role: string, company: string, url: string, start: string, end: string}> $rows */
        $rows = Content::localise(self::seedPrevious());

        return array_map(fn (array $row): array => $row + ['work_mode' => ''], $rows);
    }

    /**
     * @return list<array{name: string, url: string, about: string}>
     */
    private static function defaultJobProjects(): array
    {
        /** @var list<array{name: string, url: string, about: string}> $rows */
        $rows = Content::localise(self::seedJobProjects());

        return $rows;
    }

    /**
     * @return list<array{name: string, url: string, about: string}>
     */
    private static function defaultFreelanceProjects(): array
    {
        /** @var list<array{name: string, url: string, about: string}> $rows */
        $rows = Content::localise(self::seedFreelanceProjects());

        return $rows;
    }
}
