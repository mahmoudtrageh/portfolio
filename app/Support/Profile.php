<?php

declare(strict_types=1);

namespace App\Support;

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
     * @return array{company: string, short_name: string, url: string, products: list<array{name: string, url: string, about: string}>}
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
            'products' => $products,
        ];
    }

    /**
     * @return list<array{role: string, company: string, url: string}>
     */
    public static function previous(): array
    {
        $rows = self::rows('previous_jobs', ['company', 'role', 'url'], 'company');

        return $rows !== [] ? $rows : self::defaultPrevious();
    }

    /**
     * @return list<array{title: string, lead: string, items: list<array{name: string, url: string, about: string}>}>
     */
    public static function projectGroups(): array
    {
        $current = self::current();

        $jobs = self::rows('job_projects', ['name', 'about', 'url'], 'name');
        $freelance = self::rows('freelance_projects', ['name', 'about', 'url'], 'name');

        return array_values(array_filter([
            [
                'title' => __('At :company', ['company' => $current['short_name']]),
                'lead' => __('What I work on today at :company.', ['company' => $current['company']]),
                'items' => array_map(fn (array $p): array => [
                    'name' => $p['name'],
                    'url' => $p['url'],
                    'about' => self::capitalise($p['about']),
                ], $current['products']),
            ],
            [
                'title' => __('At previous jobs'),
                'lead' => __('Products I built and maintained as part of engineering teams.'),
                'items' => $jobs !== [] ? $jobs : self::defaultJobProjects(),
            ],
            [
                'title' => __('As a freelancer'),
                'lead' => __('Seven years of freelance full-stack work, alongside my full-time roles.'),
                'items' => $freelance !== [] ? $freelance : self::defaultFreelanceProjects(),
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
            $rows[] = ['label' => __('Email'), 'value' => $email, 'href' => 'mailto:'.$email, 'external' => false];
        }

        if ($phone !== '') {
            $rows[] = ['label' => __('Phone'), 'value' => $phone, 'href' => 'tel:'.str_replace(' ', '', $phone), 'external' => false];
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
     * A list section, localised and trimmed, without rows missing $required.
     *
     * @param  list<string>  $fields
     * @return list<array<string, string>>
     */
    private static function rows(string $section, array $fields, string $required): array
    {
        $rows = [];

        foreach (Content::list($section) as $entry) {
            $row = [];

            foreach ($fields as $field) {
                $row[$field] = trim((string) ($entry[$field] ?? ''));
            }

            if ($row[$required] !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
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
            ['company' => 'Tazamun', 'role' => ['ar' => 'مهندس Backend', 'en' => 'Backend Engineer'], 'url' => ''],
            ['company' => 'MAWAHEB LLC', 'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'], 'url' => ''],
            ['company' => 'Algoriza', 'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'], 'url' => 'https://www.algoriza.com'],
            ['company' => 'SpectraApps', 'role' => ['ar' => 'متدرّب PHP Laravel', 'en' => 'PHP Laravel Intern'], 'url' => ''],
        ];
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
     * @return array{company: string, short_name: string, url: string, products: list<array{name: string, url: string, about: string}>}
     */
    private static function defaultCurrent(): array
    {
        /** @var array{company: string, short_name: string, url: string, products: list<array{name: string, url: string, about: string}>} $current */
        $current = Content::localise(self::seedCurrent());

        return $current;
    }

    /**
     * @return list<array{role: string, company: string, url: string}>
     */
    private static function defaultPrevious(): array
    {
        /** @var list<array{role: string, company: string, url: string}> $rows */
        $rows = Content::localise(self::seedPrevious());

        return $rows;
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
