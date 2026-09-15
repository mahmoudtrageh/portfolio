<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Portfolio content
|--------------------------------------------------------------------------
|
| Single source of truth for every piece of content on the site, stored in
| both Arabic and English from day one (see plan §13a).
|
| Sourced from the CV: "Senior Full Stack Developer.pdf". Roles, dates,
| employers, figures and the tech stack below are taken from it verbatim.
|
| ⚠️  PLACEHOLDERS — anything still written as «...» is NOT verified. Find them
|     with:  grep -n "«" config/portfolio.php
|     They render with a hatched amber highlight so nothing unverified can ship
|     unnoticed. Replace each one, and the highlight disappears on its own.
|
*/

return [

    'locales' => ['ar', 'en'],

    'dir' => [
        'ar' => 'rtl',
        'en' => 'ltr',
    ],

    'locale_names' => [
        'ar' => 'عربي',
        'en' => 'EN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Site settings
    |--------------------------------------------------------------------------
    |
    | Feature switches, edited from the dashboard under Settings. These decide
    | whether a feature exists at all, rather than what it says, so unlike the
    | rest of this file they are not bilingual.
    |
    */

    'settings' => [
        // The blog: its pages, feed, nav link, homepage teaser and sitemap
        // entries all appear and disappear together.
        'blog_enabled' => true,

        // Publish in both languages. Off serves `primary_locale` only and
        // hides the language switcher; the other locale's URLs redirect there
        // rather than 404, so links already in the wild keep working.
        'multilingual' => true,
        'primary_locale' => 'en',

        // Let visitors choose light or dark. Off pins the site to `theme` and
        // removes the toggle.
        'theme_toggle' => true,
        'theme' => 'light',

        // Case-study pages. Off hides every "Read the case study" link and
        // 404s the project pages, so half-written studies stay unpublished.
        // The text you have written is kept either way.
        'case_studies_enabled' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    */

    'identity' => [
        'logo' => ['ar' => 'محمود.', 'en' => 'Mahmoud.'],
        // Uploaded from the dashboard; empty falls back to public/favicon.ico.
        'favicon' => '',
        'name' => ['ar' => 'محمود طه', 'en' => 'Mahmoud Taha'],
        'role' => [
            'ar' => 'مهندس Backend أول — Laravel و PHP',
            'en' => 'Senior Backend Engineer — Laravel & PHP',
        ],
        'email' => 'geo.mahmoudtaha@gmail.com',
        'phone' => '+20 106 399 3558',
        'location' => ['ar' => 'طنطا، مصر', 'en' => 'Tanta, Egypt'],
        'available' => ['ar' => 'متاح لفرص جديدة', 'en' => 'Available for new opportunities'],
        // One English PDF, uploaded from the dashboard and served in both
        // languages. Empty until then, which hides the download button rather
        // than linking at a file that is not there.
        'cv' => '',
        'spoken' => [
            'ar' => 'العربية (لغة أم) · الإنجليزية (إجادة عملية)',
            'en' => 'Arabic (native) · English (professional working proficiency)',
        ],
    ],

    'socials' => [
        ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/mahmoud-t-rageh/', 'icon' => 'linkedin'],
        ['label' => 'GitHub', 'url' => 'https://github.com/mahmoudtrageh', 'icon' => 'github'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero (plan §3)
    |--------------------------------------------------------------------------
    */

    'hero' => [
        'eyebrow' => [
            'ar' => '٦+ سنوات في بناء وتوسيع تطبيقات Laravel و PHP — تجارة إلكترونية، SaaS، وأنظمة متعددة المستأجرين، لفرق في مصر والإمارات.',
            'en' => '6+ years building and scaling Laravel/PHP applications — e-commerce, SaaS and multi-tenant platforms, for teams in Egypt and the UAE.',
        ],
        'headline' => [
            'ar' => 'مهندس Backend، بامتلك النظام من أول تجهيز السيرفر لحد أتمتة النشر.',
            'en' => 'Backend engineer who owns the system end-to-end — from server provisioning through deployment automation.',
        ],
        'summary' => [
            'ar' => 'متخصص في Laravel و PHP. شغّال على دورة حياة الـ Backend كاملة: تصميم APIs، معمارية قواعد البيانات، CI/CD، الحاويات، والبنية التحتية السحابية.',
            'en' => 'Specialised in Laravel and PHP, across the full backend lifecycle: API design, database architecture, CI/CD, containerization and cloud infrastructure.',
        ],
        // The other two figures are counted from the site's own content.
        'experience' => ['ar' => '٦+', 'en' => '6+'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Projects (plan §4) — "Selected Projects" from the CV
    |--------------------------------------------------------------------------
    */

    'projects' => [
        [
            'slug' => 'boxesvdr',
            'featured' => true,
            'name' => ['ar' => 'BoxesVDR', 'en' => 'BoxesVDR'],
            'category' => ['ar' => 'SaaS متعدد المستأجرين', 'en' => 'Multi-tenant SaaS'],
            'year' => ['ar' => '٢٠٢٦', 'en' => '2026'],
            'status' => ['ar' => 'على Production', 'en' => 'In production'],
            'tagline' => [
                'ar' => 'بنية SaaS متعددة المستأجرين على Huawei Cloud.',
                'en' => 'Multi-tenant SaaS infrastructure on Huawei Cloud.',
            ],
            'description' => [
                'ar' => 'بناء البنية متعددة المستأجرين التي تدير توجيه المستأجرين وشهادات SSL عبر سيرفرات dev و staging و production.',
                'en' => 'Built the multi-tenant infrastructure powering tenant routing and SSL across dev, staging, and production servers.',
            ],
            'metric' => [
                'ar' => 'هجرة دومين إنتاجية كاملة (withaqvdr.com ← boxesvdr.com) عبر ٣ سيرفرات حيّة، بتحويل nginx و DNS بدون أي توقف للخدمة.',
                'en' => 'A full production domain migration (withaqvdr.com → boxesvdr.com) across 3 live servers, with a zero-downtime nginx and DNS cutover.',
            ],
            'stack' => ['Laravel', 'Nginx', 'Huawei Cloud', 'Cloudflare', 'AlmaLinux', 'GitHub Actions'],
            'url' => 'https://boxesvdr.com',
            'highlights' => [
                [
                    'ar' => 'توجيه المستأجرين عبر subdomain مع بوابات وصول auth_request على مستوى الـ nginx.',
                    'en' => 'Subdomain-based tenant routing with auth_request access gates enforced at the nginx layer.',
                ],
                [
                    'ar' => 'هجرة دومين إنتاجية عبر ٣ سيرفرات حيّة بتحويل nginx و DNS بدون downtime.',
                    'en' => 'Led a production domain migration across 3 live servers with a zero-downtime nginx and DNS cutover.',
                ],
                [
                    'ar' => 'مراجعة تكلفة سحابية على بنية Huawei Cloud (ME-Riyadh) حدّدت إنفاق ~٣٢٤–٣٤٥$ شهريًا، وخفّضت التكاليف المتكررة بتحويل عناوين EIP للـ dev/staging من فوترة ثابتة لفوترة حسب الاستهلاك.',
                    'en' => 'Ran a cloud cost audit across Huawei Cloud (ME-Riyadh) identifying ~$324–345/month in spend, cutting recurring cost by converting dev/staging EIPs from fixed to pay-per-traffic billing.',
                ],
                [
                    'ar' => 'تصميم خطوط CI/CD عبر GitHub Actions تدعم نشرًا قابلًا للتكرار عبر البيئات الثلاث.',
                    'en' => 'Designed CI/CD pipelines via GitHub Actions supporting repeatable deployments across all three environments.',
                ],
                [
                    'ar' => 'إدارة SSL/TLS عبر شهادات Cloudflare Origin.',
                    'en' => 'SSL/TLS management via Cloudflare Origin Certificates.',
                ],
            ],
        ],
        [
            'slug' => 'petroapp',
            'featured' => true,
            'name' => ['ar' => 'PetroApp', 'en' => 'PetroApp'],
            'category' => ['ar' => 'نظام إدارة', 'en' => 'Management system'],
            'year' => ['ar' => '٢٠٢٥', 'en' => '2025'],
            'status' => ['ar' => 'مُسلّم', 'en' => 'Delivered'],
            'tagline' => [
                'ar' => 'نظام إدارة مراكز غسيل سيارات، بأكثر من ٥٠٠ ألف تحميل للتطبيق.',
                'en' => 'A car wash management system with 500K+ app downloads.',
            ],
            'description' => [
                'ar' => 'بناء الواجهة الخلفية لنظام إدارة مراكز غسيل سيارات بأكثر من ٥٠٠ ألف تحميل للتطبيق، ضمن فريق هندسي يضم أكثر من ١٠٠ شخص، مع خطوط CI/CD عبر Jenkins و ArgoCD.',
                'en' => 'Built the backend for a car wash management system with 500K+ app downloads, working as part of a 100+ person engineering team on select features, with CI/CD pipelines using Jenkins and ArgoCD.',
            ],
            'metric' => [
                'ar' => 'مجموعة اختبارات من ٦٨٤+ اختبار تغطي منطق النظام، مع خط CI/CD كامل ومراقبة APM في الإنتاج.',
                'en' => 'A 684+ test suite covering the system’s logic, backed by a full CI/CD pipeline and APM monitoring in production.',
            ],
            'stack' => ['Laravel', 'Jenkins', 'ArgoCD', 'Docker', 'Datadog'],
            'url' => 'https://petroapp.com',
            'highlights' => [
                [
                    'ar' => 'بناء الواجهة الخلفية لنظام إدارة مراكز الغسيل مع مجموعة اختبارات ٦٨٤+.',
                    'en' => 'Built the washing-centre management backend with a 684+ test suite.',
                ],
                [
                    'ar' => 'تنفيذ خطوط CI/CD باستخدام Jenkins و ArgoCD و Docker.',
                    'en' => 'Implemented CI/CD pipelines with Jenkins, ArgoCD and Docker.',
                ],
                [
                    'ar' => 'تكامل Datadog APM لمراقبة الأداء في الإنتاج.',
                    'en' => 'Integrated Datadog APM for production performance monitoring.',
                ],
            ],
        ],
        [
            'slug' => 'linkatik',
            'featured' => true,
            'name' => ['ar' => 'Linkatik', 'en' => 'Linkatik'],
            'category' => ['ar' => 'منصة متعددة المواقع', 'en' => 'Multi-site platform'],
            'year' => ['ar' => '٢٠٢٥', 'en' => '2025'],
            'status' => ['ar' => 'على Production', 'en' => 'In production'],
            'tagline' => [
                'ar' => 'بنية إنتاج متعددة المواقع، تخدم ٢٬٧٥٦ اشتراكًا نشطًا.',
                'en' => 'Multi-site production infrastructure serving 2,756 active subscriptions.',
            ],
            'description' => [
                'ar' => 'بناء وإدارة البنية الإنتاجية لمنصة متعددة المواقع، تخدم حاليًا ٢٬٧٥٦ اشتراكًا نشطًا.',
                'en' => 'Built and manage the production infrastructure for a multi-site platform, currently serving 2,756 active subscriptions.',
            ],
            'metric' => [
                'ar' => 'بنية إنتاجية واحدة بتخدم عدة مواقع، مع SSL مُدار عبر نطاقات فرعية متعددة.',
                'en' => 'A single production stack serving multiple sites, with SSL managed across multiple subdomains.',
            ],
            'stack' => ['Laravel', 'Next.js', 'MySQL', 'Nginx', 'PM2'],
            'url' => 'https://linkatik.com',
            'highlights' => [
                [
                    'ar' => 'تصميم وإدارة البنية الإنتاجية لمنصة متعددة المواقع.',
                    'en' => 'Architected and manage the production infrastructure for a multi-site platform.',
                ],
                [
                    'ar' => 'إعداد Nginx و PHP-FPM و PM2، وإدارة SSL عبر عدة نطاقات فرعية.',
                    'en' => 'Nginx / PHP-FPM / PM2 configuration and SSL across multiple subdomains.',
                ],
            ],
        ],
        [
            'slug' => 'matx',
            'featured' => true,
            'name' => ['ar' => 'MatX', 'en' => 'MatX'],
            'category' => ['ar' => 'تجارة إلكترونية', 'en' => 'E-commerce'],
            'year' => ['ar' => '٢٠٢٤', 'en' => '2024'],
            'status' => ['ar' => 'مُسلّم', 'en' => 'Delivered'],
            'tagline' => [
                'ar' => 'منصة تجارة إلكترونية متعددة البائعين، بخط معالجة نماذج ثلاثية الأبعاد.',
                'en' => 'A multi-vendor e-commerce platform with a 3D product model pipeline.',
            ],
            'description' => [
                'ar' => 'هندسة منصة تجارة إلكترونية متعددة البائعين بخصائص مدعومة بالذكاء الاصطناعي، منها خط معالجة لنماذج المنتجات ثلاثية الأبعاد، مع بناء طبقة الـ API التي تستهلكها واجهة Next.js ولوحة البائعين.',
                'en' => 'Engineered a multi-vendor e-commerce platform with AI-powered features, including a 3D product model pipeline. Built the backend API layer consumed by a Next.js storefront and vendor dashboard.',
            ],
            'metric' => [
                'ar' => 'طبقة API واحدة بتخدم واجهة المتجر ولوحة تحكم البائعين، مع خط نماذج ثلاثية الأبعاد للمنتجات.',
                'en' => 'One API layer serving both the storefront and the vendor dashboard, plus a 3D product model pipeline.',
            ],
            'stack' => ['Laravel', 'Next.js', 'MySQL'],
            'url' => 'https://matxapp.com',
            'highlights' => [
                [
                    'ar' => 'هندسة منصة تجارة إلكترونية متعددة البائعين بخصائص مدعومة بالذكاء الاصطناعي.',
                    'en' => 'Engineered a multi-vendor e-commerce platform with AI-powered features.',
                ],
                [
                    'ar' => 'بناء طبقة API الخلفية اللي بتستهلكها واجهة Next.js ولوحة تحكم البائعين.',
                    'en' => 'Built the backend API layer consumed by a Next.js storefront and vendor dashboard.',
                ],
            ],
        ],
        [
            'slug' => 'umrahbadl',
            'featured' => false,
            'name' => ['ar' => 'Umrah Badl', 'en' => 'Umrah Badl'],
            'category' => ['ar' => 'سفر', 'en' => 'Travel'],
            'year' => ['ar' => '٢٠٢٣', 'en' => '2023'],
            'status' => ['ar' => 'مُسلّم', 'en' => 'Delivered'],
            'tagline' => [
                'ar' => 'تطبيق حج وعمرة بواجهة Flutter.',
                'en' => 'A Hajj/Umrah application with a Flutter client.',
            ],
            'description' => [
                'ar' => 'قيادة تطوير الواجهة الخلفية لتطبيق سفر للحج والعمرة، بالتكامل مع تطبيق Flutter للهواتف.',
                'en' => 'Led backend development for a Hajj/Umrah travel application, integrating with a Flutter mobile client.',
            ],
            'metric' => [
                'ar' => 'واجهة خلفية واحدة بتخدم عملاء الويب وتطبيق Flutter المحمول.',
                'en' => 'A single backend serving both web and the Flutter mobile client.',
            ],
            'stack' => ['Laravel', 'Bootstrap', 'MySQL', 'Flutter'],
            'highlights' => [],
        ],
        [
            'slug' => 'asp-de-paris',
            'featured' => false,
            'name' => ['ar' => 'ASP Paris', 'en' => 'ASP Paris'],
            'category' => ['ar' => 'API', 'en' => 'API'],
            'year' => ['ar' => '٢٠٢٣', 'en' => '2023'],
            'status' => ['ar' => 'مُسلّم', 'en' => 'Delivered'],
            'tagline' => [
                'ar' => 'واجهة Laravel API بنشر آلي على cPanel.',
                'en' => 'A Laravel API with automated deployment to cPanel.',
            ],
            'description' => [
                'ar' => 'تطوير ونشر واجهة برمجية على Laravel مع CI/CD مؤتمت عبر GitHub Actions إلى استضافة cPanel.',
                'en' => 'Developed and deployed a Laravel API with automated CI/CD via GitHub Actions to cPanel hosting.',
            ],
            'metric' => [
                'ar' => 'نشر آلي بالكامل عبر GitHub Actions لبيئة cPanel.',
                'en' => 'Fully automated deployment to cPanel through GitHub Actions.',
            ],
            'stack' => ['Laravel', 'cPanel', 'GitHub Actions'],
            'url' => 'https://aspparis.com',
            'highlights' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Other projects — listed without case-study pages (from the CV)
    |--------------------------------------------------------------------------
    */

    'other_projects' => [
        [
            'name' => ['ar' => 'Dsyncsolutions', 'en' => 'Dsyncsolutions'],
            'note' => ['ar' => 'نظام جرد للشركات', 'en' => 'Corporate inventory system'],
            'year' => ['ar' => '٢٠٢٣', 'en' => '2023'],
            'summary' => ['ar' => 'نظام جرد للشركات.', 'en' => 'A corporate inventory system.'],
            'stack' => ['Laravel', 'MySQL'],
        ],
        [
            'name' => ['ar' => 'Longimanus Liveaboard', 'en' => 'Longimanus Liveaboard'],
            'note' => ['ar' => 'واجهة خلفية لمركز غوص', 'en' => 'Diving centre backend'],
            'year' => ['ar' => '٢٠٢٢', 'en' => '2022'],
            'summary' => [
                'ar' => 'واجهة خلفية لمركز غوص، لإدارة الرحلات والحجوزات.',
                'en' => 'A diving-centre backend, managing trips and bookings.',
            ],
            'stack' => ['Laravel', 'MySQL'],
        ],
        [
            'name' => ['ar' => 'Larabuilder', 'en' => 'Larabuilder'],
            'note' => ['ar' => 'تحسين أداء أداة بناء مواقع', 'en' => 'Site builder performance work'],
            // No year on the CV for this one; the card omits it rather than guess.
            'summary' => [
                'ar' => 'عمل على تحسين أداء أداة بناء مواقع.',
                'en' => 'Performance work on a site builder.',
            ],
            'stack' => ['Laravel'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Career timeline (plan §5) — verbatim from the CV
    |--------------------------------------------------------------------------
    */

    'timeline' => [
        [
            'org' => ['ar' => 'Boxes Intelligent Communications — مصر', 'en' => 'Boxes Intelligent Communications, Egypt'],
            'role' => ['ar' => 'مهندس برمجيات أول', 'en' => 'Senior Software Engineer'],
            'period' => ['ar' => 'فبراير ٢٠٢٦ — حتى الآن', 'en' => 'Feb 2026 — Present'],
            'current' => true,
            'summary' => [
                'ar' => 'امتلاك المعمارية الخلفية لمنصة SaaS متعددة المستأجرين (BoxesVDR)، من تجهيز السيرفر لأتمتة النشر.',
                'en' => 'Owning the backend architecture of a multi-tenant SaaS platform (BoxesVDR), from server provisioning to deployment automation.',
            ],
            'points' => [
                [
                    'ar' => 'امتلاك المعمارية الخلفية لـ BoxesVDR: توجيه مستأجرين عبر subdomain مع بوابات auth_request، إدارة SSL/TLS عبر Cloudflare Origin، واستراتيجية بيئات عبر ٣ سيرفرات AlmaLinux/nginx.',
                    'en' => 'Own the backend architecture for BoxesVDR: subdomain tenant routing with auth_request gates, SSL/TLS via Cloudflare Origin Certificates, and an environment strategy across 3 AlmaLinux/nginx servers.',
                ],
                [
                    'ar' => 'قيادة هجرة دومين إنتاجية كاملة (withaqvdr.com ← boxesvdr.com) عبر ٣ سيرفرات حيّة بدون توقف.',
                    'en' => 'Led a full production domain migration (withaqvdr.com → boxesvdr.com) across 3 live servers with zero downtime.',
                ],
                [
                    'ar' => 'مراجعة تكلفة Huawei Cloud (ME-Riyadh) حدّدت ~٣٢٤–٣٤٥$ شهريًا وخفّضت التكاليف المتكررة بتحويل EIPs من فوترة ثابتة لفوترة بالاستهلاك.',
                    'en' => 'Ran a Huawei Cloud (ME-Riyadh) cost audit identifying ~$324–345/month, cutting recurring costs by moving EIPs from fixed to pay-per-traffic billing.',
                ],
                [
                    'ar' => 'تصميم خطوط CI/CD عبر GitHub Actions لنشر قابل للتكرار عبر dev و staging و production.',
                    'en' => 'Designed CI/CD pipelines via GitHub Actions for repeatable deployments across dev, staging and production.',
                ],
            ],
        ],
        [
            'org' => ['ar' => 'MatX — عن بُعد / عمل حر', 'en' => 'MatX, Remote / Freelance'],
            'role' => ['ar' => 'مهندس Backend مستقل', 'en' => 'Freelance Backend Engineer'],
            'period' => ['ar' => 'مايو ٢٠٢٥ — فبراير ٢٠٢٦', 'en' => 'May 2025 — Feb 2026'],
            'current' => false,
            'summary' => [
                'ar' => 'هندسة المعمارية الخلفية لمنصة تجارة إلكترونية متعددة البائعين.',
                'en' => 'Engineered the backend architecture for a multi-vendor e-commerce platform.',
            ],
            'points' => [
                [
                    'ar' => 'هندسة المعمارية الخلفية لمنصة تجارة إلكترونية متعددة البائعين باستخدام Laravel و MySQL.',
                    'en' => 'Engineered backend architecture for a multi-vendor e-commerce platform using Laravel and MySQL.',
                ],
                [
                    'ar' => 'بناء خدمات خلفية تدعم خصائص مدعومة بالذكاء الاصطناعي وخط معالجة لنماذج المنتجات ثلاثية الأبعاد.',
                    'en' => 'Implemented backend services supporting AI-powered product features and a 3D product model pipeline.',
                ],
                [
                    'ar' => 'العمل عبر التطوير الخلفي والتسليم الإنتاجي لبناء المنصة وإطلاقها.',
                    'en' => 'Worked across backend development and production delivery to build and ship the platform.',
                ],
            ],
        ],
        [
            'org' => ['ar' => 'Tazamun — الإمارات', 'en' => 'Tazamun, UAE'],
            'role' => ['ar' => 'مهندس Backend', 'en' => 'Backend Engineer'],
            'period' => ['ar' => 'سبتمبر ٢٠٢٣ — مايو ٢٠٢٥', 'en' => 'Sept 2023 — May 2025'],
            'current' => false,
            'summary' => [
                'ar' => 'قيادة تطوير الواجهة الخلفية لخصائص المنتج الأساسية.',
                'en' => 'Led backend development for the platform’s core product features.',
            ],
            'points' => [
                [
                    'ar' => 'بناء وصيانة RESTful APIs وخدمات بتدعم منطق العمل الأساسي للمنصة.',
                    'en' => 'Built and maintained RESTful APIs and services supporting the platform’s core business logic.',
                ],
                [
                    'ar' => 'العمل المباشر مع مهندسي الواجهة والمصممين لتسليم خصائص كاملة من المواصفات للإنتاج.',
                    'en' => 'Worked directly with frontend engineers and designers to ship end-to-end functionality from spec to production.',
                ],
                [
                    'ar' => 'ترجمة قيود البنية التحتية لصيغة يقدر أصحاب القرار غير التقنيين يتصرفوا على أساسها، وده قصّر الدورة بين الهندسة وقرارات المنتج.',
                    'en' => 'Translated infrastructure constraints into terms non-technical stakeholders could act on, shortening the loop between engineering and product decisions.',
                ],
            ],
        ],
        [
            'org' => ['ar' => 'MAWAHEB LLC — الإمارات', 'en' => 'MAWAHEB LLC, UAE'],
            'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'],
            'period' => ['ar' => 'مارس ٢٠٢٣ — سبتمبر ٢٠٢٣', 'en' => 'March 2023 — Sept 2023'],
            'current' => false,
            'summary' => [
                'ar' => 'الواجهة الخلفية لتطبيق حجز خدمات تنظيف.',
                'en' => 'The backend for a cleaning booking services application.',
            ],
            'points' => [
                [
                    'ar' => 'تطوير الواجهة الخلفية لتطبيق حجز خدمات تنظيف، وتبسيط سير عمل الجدولة وإلغاء خطوات الحجز اليدوية.',
                    'en' => 'Developed the backend for a cleaning booking services application, streamlining the scheduling workflow and eliminating manual booking steps.',
                ],
                [
                    'ar' => 'تنفيذ هجرات بيانات ودمج أدوات خارجية، بتوحيد خدمات مدفوعة منفصلة في حل داخلي واحد.',
                    'en' => 'Executed data migrations and integrated third-party tools, consolidating previously separate paid services into a single in-house solution.',
                ],
                [
                    'ar' => 'حل المشكلات التقنية المُبلّغ عنها من العملاء مباشرة.',
                    'en' => 'Resolved customer-reported technical issues directly.',
                ],
            ],
            // The app shipped in this role, listed on the App Store under
            // Mawaheb LLC.
            'product' => 'Tekram — Home Cleaning Service',
            'product_url' => 'https://apps.apple.com/us/app/tekram-home-cleaning-service/id1642682373',
        ],
        [
            'org' => ['ar' => 'Algoriza — مصر', 'en' => 'Algoriza, Egypt'],
            'role' => ['ar' => 'مطوّر Backend', 'en' => 'Backend Developer'],
            'period' => ['ar' => 'أغسطس ٢٠٢٠ — مارس ٢٠٢٣', 'en' => 'August 2020 — March 2023'],
            'current' => false,
            'summary' => [
                'ar' => 'حلول خلفية لمنصات تجارة إلكترونية وشحن بتخدم عملاء ويب وموبايل.',
                'en' => 'Backend solutions for e-commerce and shipping platforms serving web and mobile clients.',
            ],
            'points' => [
                [
                    'ar' => 'تصميم وتنفيذ حلول خلفية لمنصات تجارة إلكترونية وشحن بتخدم عملاء ويب وموبايل.',
                    'en' => 'Designed and implemented backend solutions for e-commerce and shipping platforms serving both web and mobile clients.',
                ],
                [
                    'ar' => 'بناء وصيانة لوحة تحكم إدارية و RESTful APIs بتستهلكها تطبيقات Android و iOS.',
                    'en' => 'Built and maintained an admin dashboard and RESTful APIs consumed by both Android and iOS applications.',
                ],
                [
                    'ar' => 'نقطة الحل الأساسية للمشكلات التقنية المُبلّغ عنها، مع الحفاظ على جودة خدمة ثابتة عبر حجم كبير من طلبات الدعم.',
                    'en' => 'Served as a primary point of resolution for customer-reported technical issues, maintaining consistent service quality across a high volume of support requests.',
                ],
            ],
        ],
        [
            'org' => ['ar' => 'SpectraApps — مصر', 'en' => 'SpectraApps, Egypt'],
            'role' => ['ar' => 'متدرّب PHP / Laravel', 'en' => 'PHP Laravel Intern'],
            'period' => ['ar' => 'ديسمبر ٢٠١٨ — أبريل ٢٠١٩', 'en' => 'December 2018 — April 2019'],
            'current' => false,
            'summary' => [
                'ar' => 'أول تجربة مهنية في تطوير Laravel.',
                'en' => 'First professional experience in Laravel development.',
            ],
            'points' => [
                [
                    'ar' => 'المساهمة في تطوير تطبيقات Laravel، وبناء الأساس في هندسة الـ Backend والعمل ضمن فريق.',
                    'en' => 'Contributed to Laravel application development, building foundational skills in backend engineering and team-based delivery.',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Education (plan §6)
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Freelancing — independent work, shown in its own Experience tab
    |--------------------------------------------------------------------------
    |
    | The Mostaql entries and their client reviews were read off the platform's
    | own review pages. Their dates are derived from relative "X years and Y
    | months ago" stamps, so they are accurate to the month rather than the day.
    | Reviews are quoted in the language the client wrote them in.
    |
    */

    'freelance' => [
        [
            'name' => ['ar' => 'Woosh', 'en' => 'Woosh'],
            'platform' => '',
            'period' => ['ar' => '٢٠٢٥', 'en' => '2025'],
            'summary' => [
                'ar' => 'موقع مجموعة ترفيهية سعودية تضم عدة شركات فرعية — مراكز، فعاليات، وحلول أعمال — بواجهة ثنائية اللغة.',
                'en' => 'The site for a Saudi entertainment group spanning several subsidiaries — centres, events and business solutions — with a bilingual front end.',
            ],
            'stack' => ['Laravel', 'MySQL'],
            'rating' => '',
            'client' => '',
            'review' => ['ar' => '', 'en' => ''],
            'url' => 'https://www.woosh-ksa.com/',
        ],
        [
            'name' => ['ar' => 'موقع للربط بين الموردين والمحلات', 'en' => 'Supplier-to-shop marketplace'],
            'platform' => 'Mostaql',
            'period' => ['ar' => 'فبراير ٢٠٢١', 'en' => 'February 2021'],
            'summary' => [
                'ar' => 'منصة تربط الموردين بالمحلات، بلوحة تحكم لإدارة الطلبات والحسابات.',
                'en' => 'A platform connecting suppliers with retail shops, with a dashboard for orders and accounts.',
            ],
            'stack' => ['Laravel', 'MySQL', 'Bootstrap'],
            'rating' => '5',
            'client' => 'عبدالله ا.',
            'review' => ['ar' => 'تم استلام المشروع', 'en' => 'تم استلام المشروع'],
            'url' => '',
        ],
        [
            'name' => ['ar' => 'موقع لتسجيل في بطولة كرة قدم', 'en' => 'Football tournament registration site'],
            'platform' => 'Mostaql',
            'period' => ['ar' => 'أبريل ٢٠٢٠', 'en' => 'April 2020'],
            'summary' => [
                'ar' => 'موقع لتسجيل الفرق واللاعبين في بطولة كرة قدم، مع إدارة المشاركات.',
                'en' => 'A site for registering teams and players in a football tournament, with entry management.',
            ],
            'stack' => ['Laravel', 'MySQL'],
            'rating' => '5',
            'client' => 'حسين ا.',
            'review' => ['ar' => 'مبدع وسريع في التعامل والردود', 'en' => 'مبدع وسريع في التعامل والردود'],
            'url' => '',
        ],
        [
            'name' => ['ar' => 'تجهيز صفحة خدمات وربط بوابة دفع', 'en' => 'Services page with payment gateway integration'],
            'platform' => 'Mostaql',
            'period' => ['ar' => 'فبراير ٢٠٢٠', 'en' => 'February 2020'],
            'summary' => [
                'ar' => 'صفحة خدمات مربوطة ببوابة دفع إلكتروني، من التصميم حتى تأكيد العملية.',
                'en' => 'A services page wired to an online payment gateway, from layout through to transaction confirmation.',
            ],
            'stack' => ['Laravel', 'MySQL', 'Payment gateway'],
            'rating' => '5',
            'client' => 'Ibrahim A.',
            'review' => [
                'ar' => 'عمل رائع اخي محمود طه، سعدت بالعمل معك ونتطلع للعمل مرة خرى',
                'en' => 'عمل رائع اخي محمود طه، سعدت بالعمل معك ونتطلع للعمل مرة خرى',
            ],
            'url' => '',
        ],
        [
            'name' => ['ar' => 'تسجيل دورات و دفع إلكتروني', 'en' => 'Course registration with online payment'],
            'platform' => 'Mostaql',
            'period' => ['ar' => 'أغسطس ٢٠١٩', 'en' => 'August 2019'],
            'summary' => [
                'ar' => 'نظام تسجيل في الدورات التدريبية مع دفع إلكتروني ومتابعة للمسجّلين.',
                'en' => 'A course-registration system with online payment and enrolment tracking.',
            ],
            'stack' => ['Laravel', 'MySQL', 'Payment gateway'],
            'rating' => '4.8',
            'client' => 'Majd L.',
            'review' => [
                'ar' => 'سررت بالتعامل مع الاستاذ محمود جدا في التعامل، حصلت على المطلوب و في الوقت المحدد. كل الشكر له انصح بالتعامل معه.',
                'en' => 'سررت بالتعامل مع الاستاذ محمود جدا في التعامل، حصلت على المطلوب و في الوقت المحدد. كل الشكر له انصح بالتعامل معه.',
            ],
            'url' => '',
        ],

        // Also shown elsewhere on the site; listed here so the freelancing tab
        // is the complete picture of independent work.
        [
            'name' => ['ar' => 'MatX', 'en' => 'MatX'],
            'platform' => '',
            'period' => ['ar' => '٢٠٢٤', 'en' => '2024'],
            'summary' => [
                'ar' => 'منصة تجارة إلكترونية متعددة البائعين مع خصائص مدعومة بالذكاء الاصطناعي.',
                'en' => 'A multi-vendor e-commerce platform with AI-powered features.',
            ],
            'stack' => ['Laravel', 'Next.js', 'MySQL'],
            'rating' => '',
            'client' => '',
            'review' => ['ar' => '', 'en' => ''],
            'url' => '',
        ],
        // Dsyncsolutions and Longimanus live in 'other_projects' instead, so
        // they are listed once — matching the CV's "Additional projects".
    ],

    /*
    |--------------------------------------------------------------------------
    | Side projects — built without a client or a brief
    |--------------------------------------------------------------------------
    */

    'side_projects' => [
        [
            'name' => ['ar' => 'وظيفة', 'en' => 'Waazefa'],
            'tagline' => ['ar' => 'وظائف تقنية، بلا ضجيج', 'en' => 'Technical jobs, without the noise'],
            'summary' => [
                'ar' => 'لوحة وظائف تقنية للسوق المصري: نشر الوظائف والبحث فيها، وإدارة السير الذاتية للمتقدمين، وتصفية المرشحين لأصحاب العمل.',
                'en' => 'A technical job board for the Egyptian market: posting and searching roles, résumé management for candidates, and filtering tools for employers.',
            ],
            'status' => ['ar' => 'قيد التطوير', 'en' => 'In development'],
            'stack' => ['Laravel', 'MySQL'],
            'url' => 'https://waazefa.com/',
        ],
    ],

    'education' => [
        [
            'school' => ['ar' => 'جامعة طنطا — مصر', 'en' => 'Tanta University, Egypt'],
            'degree' => ['ar' => 'بكالوريوس جيوفيزياء', 'en' => 'B.S. Geophysics'],
            'period' => ['ar' => '٢٠٢٠', 'en' => '2020'],
            'note' => [
                'ar' => 'انتقال ذاتي التوجيه لهندسة البرمجيات من سنة ٢٠١٨، مدعوم بشهادات مهنية متواصلة في تطوير الويب.',
                'en' => 'A self-directed transition into software engineering since 2018, backed by continuous professional certification in web development.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Certificates (from the CV)
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Writing — volunteer articles, titles read off the published pages
    |--------------------------------------------------------------------------
    |
    | The articles are in Arabic. The English column keeps the original title
    | (a translated title would misrepresent what the link leads to) and adds a
    | 'gloss' underneath so an English reader still knows the subject.
    |
    */

    'writing' => [
        [
            'title' => [
                'ar' => 'الثلاثي البريطاني الفائز بجائزة نوبل في الفيزياء ٢٠١٦ — ماذا تعرف عنهم؟',
                'en' => 'الثلاثي البريطاني الفائز بجائزة نوبل في الفيزياء 2016 — ماذا تعرف عنهم؟',
            ],
            'gloss' => [
                'ar' => '',
                'en' => 'The British trio who won the 2016 Nobel Prize in Physics — what do you know about them?',
            ],
            'publisher' => 'EgyResMag',
            'date' => ['ar' => 'أكتوبر ٢٠١٦', 'en' => 'October 2016'],
            'url' => 'https://egyresmag.com/الثلاثي-البريطاني-الفائز-بجائزة-نوبل/',
        ],
        [
            'title' => [
                'ar' => '١٠ نصائح لتحديد أهدافك بطريقة صحيحة بدون إضاعة للوقت!',
                'en' => '10 نصائح لتحديد أهدافك بطريقة صحيحة بدون إضاعة للوقت!',
            ],
            'gloss' => [
                'ar' => '',
                'en' => '10 tips for setting your goals properly, without wasting time',
            ],
            'publisher' => 'Arageek',
            'date' => ['ar' => 'فبراير ٢٠١٦', 'en' => 'February 2016'],
            'url' => 'https://www.arageek.com/10-advice-to-set-your-correct-goals',
        ],
        [
            'title' => [
                'ar' => 'هل يمكن السفر بين النجوم بسرعة عالية كما في أفلام الخيال العلمي؟',
                'en' => 'هل يمكن السفر بين النجوم بسرعة عالية كما في أفلام الخيال العلمي؟',
            ],
            'gloss' => [
                'ar' => '',
                'en' => 'Is interstellar travel at high speed possible, as in science-fiction films?',
            ],
            'publisher' => 'EgyResMag',
            'date' => ['ar' => 'مايو ٢٠١٦', 'en' => 'May 2016'],
            'url' => 'https://egyresmag.com/هل-يمكن-السفر-بين-النجوم-بسرعة-عالية-كم/',
        ],
        [
            'title' => [
                'ar' => 'زراعة قوقعة أذن صناعية ومحاكاة حاسة السمع لدى البشر!',
                'en' => 'زراعة قوقعة أذن صناعية ومحاكاة حاسة السمع لدى البشر!',
            ],
            'gloss' => [
                'ar' => '',
                'en' => 'Cochlear implants and simulating the human sense of hearing',
            ],
            'publisher' => 'EgyResMag',
            'date' => ['ar' => 'مارس ٢٠١٦', 'en' => 'March 2016'],
            'url' => 'https://egyresmag.com/زراعة-قوقعة-أذن-صناعية-ومحاكاة-حاسة-ال/',
        ],
        [
            'title' => [
                'ar' => '١٠ عوائق وهمية تمنعك من البدء في التدوين الآن!',
                'en' => '10 عوائق وهمية تمنعك من البدء في التدوين الآن!',
            ],
            'gloss' => [
                'ar' => '',
                'en' => '10 imaginary obstacles stopping you from starting to blog',
            ],
            'publisher' => 'Arageek',
            'date' => ['ar' => 'سبتمبر ٢٠١٥', 'en' => 'September 2015'],
            'url' => 'https://www.arageek.com/10-things-prevent-you-from-writing',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | YouTube channel
    |--------------------------------------------------------------------------
    */

    'channel' => [
        'name' => ['ar' => 'محمود طه', 'en' => 'Mahmoud Taha — محمود طه'],
        'blurb' => ['ar' => 'قناتي على يوتيوب.', 'en' => 'My YouTube channel.'],
        'url' => 'https://www.youtube.com/@MahmoudTahaRageh',
        // Set the UC… channel ID from the dashboard and the latest uploads are
        // pulled from YouTube's RSS feed automatically.
        'channel_id' => '',
        'video_limit' => '',
        'videos' => [],
    ],

    'certificates' => [
        [
            'name' => ['ar' => 'هياكل البيانات والخوارزميات', 'en' => 'Data Structures & Algorithms'],
            'issuer' => ['ar' => 'Udemy', 'en' => 'Udemy'],
            'date' => ['ar' => 'مايو ٢٠٢٥', 'en' => 'May 2025'],
            // Scan and verification link read off the certificate.
            'image' => 'uploads/cert-dsa.jpg',
            'url' => 'https://ude.my/UC-013d57ac-2144-42d4-a733-60de3f21ff8e',
        ],
        [
            'name' => ['ar' => 'أساسيات البرمجة: الخوارزميات', 'en' => 'Programming Foundations: Algorithms'],
            'issuer' => ['ar' => 'LinkedIn Learning', 'en' => 'LinkedIn Learning'],
            'date' => ['ar' => 'أبريل ٢٠٢٢', 'en' => 'April 2022'],
        ],
        [
            'name' => ['ar' => 'PHP Full Stack Web Developer Bootcamp ٢٠٢٠', 'en' => 'The Complete 2020 PHP Full Stack Web Developer Bootcamp'],
            'issuer' => ['ar' => 'Udemy', 'en' => 'Udemy'],
            'date' => ['ar' => 'أغسطس ٢٠٢٠', 'en' => 'August 2020'],
            // Scan, exact date and verification link read off the certificate.
            'image' => 'uploads/cert-php-bootcamp.jpg',
            'url' => 'https://ude.my/UC-74a90330-486e-4d74-ad55-f216e96780d5',
        ],
        [
            'name' => ['ar' => 'دبلومة تطوير الويب المتكامل', 'en' => 'Full Stack Web Development Diploma'],
            'issuer' => ['ar' => 'Route', 'en' => 'Route'],
            'date' => ['ar' => 'مايو ٢٠١٨', 'en' => 'May 2018'],
        ],

        /*
        | Scanned certificates. Names, issuers, dates and the Coursera verify
        | codes were read off the certificate images themselves. The image paths
        | point at files under storage/app/public/uploads.
        */
        [
            'name' => ['ar' => 'مقدمة في HTML5', 'en' => 'Introduction to HTML5'],
            'issuer' => ['ar' => 'جامعة ميشيغان — Coursera', 'en' => 'University of Michigan — Coursera'],
            'date' => ['ar' => 'نوفمبر ٢٠١٦', 'en' => 'November 2016'],
            'image' => 'uploads/cert-html5.png',
            'url' => 'https://coursera.org/verify/LAB4Z5QZ2BL2',
        ],
        [
            'name' => ['ar' => 'مقدمة في CSS3', 'en' => 'Introduction to CSS3'],
            'issuer' => ['ar' => 'جامعة ميشيغان — Coursera', 'en' => 'University of Michigan — Coursera'],
            'date' => ['ar' => 'مارس ٢٠١٨', 'en' => 'March 2018'],
            'image' => 'uploads/cert-css3.png',
            'url' => 'https://coursera.org/verify/69YZEVH5C47J',
        ],
        [
            'name' => ['ar' => 'التفاعلية باستخدام JavaScript', 'en' => 'Interactivity with JavaScript'],
            'issuer' => ['ar' => 'جامعة ميشيغان — Coursera', 'en' => 'University of Michigan — Coursera'],
            'date' => ['ar' => 'أبريل ٢٠١٨', 'en' => 'April 2018'],
            'image' => 'uploads/cert-javascript.png',
            'url' => 'https://coursera.org/verify/QBBU9DVMVVRP',
        ],
        [
            'name' => ['ar' => 'تعلَّم كيف تتعلم', 'en' => 'Learning How to Learn'],
            'issuer' => ['ar' => 'جامعة كاليفورنيا سان دييغو — Coursera', 'en' => 'UC San Diego — Coursera'],
            'date' => ['ar' => 'سبتمبر ٢٠١٦', 'en' => 'September 2016'],
            'image' => 'uploads/cert-learning-how-to-learn.png',
            'url' => 'https://coursera.org/verify/E9EM2ML4ADHE',
        ],
        [
            'name' => ['ar' => 'برنامج M²GATE لريادة الأعمال', 'en' => 'M²GATE Entrepreneurship Programme'],
            'issuer' => [
                'ar' => 'معهد ويليام ديفيدسون — جامعة ميشيغان',
                'en' => 'William Davidson Institute — University of Michigan',
            ],
            'date' => ['ar' => 'يوليو ٢٠١٨', 'en' => 'July 2018'],
            'image' => 'uploads/cert-m2gate.png',
            'url' => '',
        ],
        [
            'name' => [
                'ar' => 'شهادة تقدير — مسابقة المخترع الصغير',
                'en' => 'Certificate of Appreciation — Young Inventor competition',
            ],
            'issuer' => [
                'ar' => 'وزارة التربية والتعليم — إدارة الاتحادات الطلابية',
                'en' => 'Ministry of Education — Student Unions Administration',
            ],
            'date' => ['ar' => 'أبريل ٢٠١٣', 'en' => 'April 2013'],
            'image' => 'uploads/cert-young-inventor.jpg',
            'url' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | What I build (plan §7) — grounded in the CV's actual scope
    |--------------------------------------------------------------------------
    */

    'what_i_build' => [
        [
            'title' => ['ar' => 'أنظمة SaaS متعددة المستأجرين', 'en' => 'Multi-tenant SaaS systems'],
            'body' => [
                'ar' => 'توجيه المستأجرين عبر subdomain، بوابات وصول على مستوى الـ nginx، واستراتيجية بيئات كاملة عبر dev و staging و production.',
                'en' => 'Subdomain-based tenant routing, access gates enforced at the nginx layer, and a complete environment strategy across dev, staging and production.',
            ],
        ],
        [
            'title' => ['ar' => 'تصميم APIs', 'en' => 'API design'],
            'body' => [
                'ar' => 'RESTful APIs بتخدم عملاء ويب وموبايل — لوحات تحكم إدارية، تطبيقات Android و iOS، وواجهات Next.js و Flutter.',
                'en' => 'RESTful APIs serving both web and mobile clients — admin dashboards, Android and iOS apps, and Next.js and Flutter front ends.',
            ],
        ],
        [
            'title' => ['ar' => 'بنية تحتية وسحابة', 'en' => 'Infrastructure & cloud'],
            'body' => [
                'ar' => 'تجهيز سيرفرات AlmaLinux/nginx، إدارة SSL/TLS، Cloudflare، ومراجعة تكلفة السحابة وخفضها فعليًا.',
                'en' => 'AlmaLinux/nginx provisioning, SSL/TLS management, Cloudflare, and auditing cloud spend down to a lower bill.',
            ],
        ],
        [
            'title' => ['ar' => 'CI/CD وأتمتة النشر', 'en' => 'CI/CD & deployment automation'],
            'body' => [
                'ar' => 'خطوط نشر قابلة للتكرار عبر GitHub Actions و Jenkins و ArgoCD و Docker — من الـ commit للإنتاج.',
                'en' => 'Repeatable deployment pipelines through GitHub Actions, Jenkins, ArgoCD and Docker — commit to production.',
            ],
        ],
        [
            'title' => ['ar' => 'الاختبارات والمراقبة', 'en' => 'Testing & monitoring'],
            'body' => [
                'ar' => 'تغطية اختبارات جادة (٦٨٤+ اختبار في PetroApp) ومراقبة أداء الإنتاج عبر Datadog APM.',
                'en' => 'Serious test coverage (684+ tests on PetroApp) and production performance monitoring via Datadog APM.',
            ],
        ],
        [
            'title' => ['ar' => 'هجرات وتوحيد الأنظمة', 'en' => 'Migrations & consolidation'],
            'body' => [
                'ar' => 'هجرات دومين ونقل بيانات بدون توقف، وتوحيد خدمات مدفوعة منفصلة في حلول داخلية واحدة.',
                'en' => 'Zero-downtime domain migrations and data moves, and consolidating separate paid services into single in-house solutions.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Skills (plan §8) — from the CV
    |--------------------------------------------------------------------------
    */

    'skills' => [
        [
            'group' => ['ar' => 'اللغات', 'en' => 'Languages'],
            // Version numbers are omitted: skill chips render identically in
            // both locales, and Latin digits are not allowed on the Arabic page.
            'items' => ['PHP', 'JavaScript', 'SQL', 'Bash'],
        ],
        [
            'group' => ['ar' => 'الأطر', 'en' => 'Frameworks'],
            'items' => ['Laravel', 'Livewire', 'jQuery', 'Bootstrap', 'Tailwind CSS'],
        ],
        [
            'group' => ['ar' => 'قواعد البيانات', 'en' => 'Databases'],
            'items' => ['MySQL', 'PostgreSQL', 'Redis', 'Query optimisation'],
        ],
        [
            'group' => ['ar' => 'واجهات برمجية', 'en' => 'APIs'],
            'items' => ['RESTful API design', 'API integration', 'Webhooks'],
        ],
        [
            'group' => ['ar' => 'المعمارية', 'en' => 'Architecture'],
            'items' => ['Multi-tenant SaaS', 'Microservices', 'Queues', 'Caching'],
        ],
        [
            'group' => ['ar' => 'البنية التحتية', 'en' => 'Infrastructure'],
            'items' => ['Docker', 'Nginx', 'Linux', 'AWS', 'Huawei Cloud', 'cPanel'],
        ],
        [
            'group' => ['ar' => 'النشر والأتمتة', 'en' => 'CI/CD & DevOps'],
            'items' => ['GitHub Actions', 'Jenkins', 'ArgoCD', 'Git'],
        ],
        [
            'group' => ['ar' => 'الاختبار والمراقبة', 'en' => 'Testing & Monitoring'],
            'items' => ['PHPUnit', 'Pest', 'Datadog', 'Debugging'],
        ],
        [
            'group' => ['ar' => 'أدوات الذكاء الاصطناعي', 'en' => 'AI tooling'],
            'items' => ['Cursor', 'Claude Code', 'ChatGPT'],
        ],
        [
            'group' => ['ar' => 'ممارسات', 'en' => 'Practices'],
            'items' => ['Agile', 'Scrum', 'Code review', 'Technical documentation'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testimonials (plan §9)
    |--------------------------------------------------------------------------
    |
    | ⚠️  EMPTY BY DESIGN. The plan (§9) calls collecting 3–5 written
    |     testimonials the first practical step before launch. Add entries here
    |     and the testimonials section/page starts rendering automatically.
    |
    */

    'testimonials' => [
        // [
        //     'name'  => ['ar' => '...', 'en' => '...'],
        //     'title' => ['ar' => '...', 'en' => '...'],
        //     'quote' => ['ar' => '...', 'en' => '...'],
        //     'photo' => '/images/testimonials/....jpg',
        //     'url'   => null,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | About narrative (plan §10)
    |--------------------------------------------------------------------------
    */

    'about' => [
        'intro' => [
            'ar' => 'مهندس Backend بخبرة ٦+ سنوات في بناء وتوسيع تطبيقات Laravel و PHP عبر التجارة الإلكترونية و SaaS والأنظمة متعددة المستأجرين. شغّلت أنظمة إنتاج لفرق في مصر والإمارات، ومرتاح إني أمتلك النظام من أوله لآخره — من تجهيز السيرفر لحد أتمتة النشر.',
            'en' => 'A backend engineer with 6+ years building and scaling Laravel/PHP applications across e-commerce, SaaS and multi-tenant platforms. I have shipped production systems for teams in Egypt and the UAE, and I am comfortable owning a system end-to-end — from server provisioning through deployment automation.',
        ],
        'sections' => [
            [
                'title' => ['ar' => 'من الجيوفيزياء للـ Backend', 'en' => 'From geophysics to backend'],
                'body' => [
                    'ar' => 'درست جيوفيزياء في جامعة طنطا وتخرجت سنة ٢٠٢٠، لكن الانتقال لهندسة البرمجيات بدأ قبلها بسنتين — من ٢٠١٨، ذاتي التوجيه بالكامل ومدعوم بشهادات مهنية متواصلة. الجيوفيزياء علّمتني أقرا إشارة وسط ضوضاء وأفكّك مشكلة كبيرة لطبقات، وده بالظبط اللي بعمله وأنا بلاحق سبب بطء في الإنتاج أو بصمّم قاعدة بيانات.',
                    'en' => 'I read geophysics at Tanta University, graduating in 2020 — but the move into software engineering had already started two years earlier, in 2018, entirely self-directed and backed by continuous professional certification. Geophysics taught me to read a signal through noise and break a large problem into layers, which is exactly what I do when tracing a slowdown in production or designing a schema.',
                ],
            ],
            [
                'title' => ['ar' => 'امتلاك النظام كامل', 'en' => 'Owning the whole system'],
                'body' => [
                    'ar' => 'أغلب شغلي مش بيقف عند حدود الكود. في BoxesVDR بامتلك المعمارية الخلفية كاملة: توجيه المستأجرين، بوابات الوصول، شهادات SSL، وثلاث بيئات على سيرفرات AlmaLinux. قدت هجرة دومين إنتاجية عبر ٣ سيرفرات حيّة بدون توقف، وعملت مراجعة تكلفة سحابية طلّعت إنفاق شهري قدرت أخفّضه فعليًا. الجزء ده من الشغل — اللي بين آخر سطر كود وأول مستخدم — هو اللي بيفرق.',
                    'en' => 'Most of my work does not stop at the edge of the codebase. On BoxesVDR I own the backend architecture outright: tenant routing, access gates, SSL certificates, and three environments across AlmaLinux servers. I led a production domain migration across 3 live servers with zero downtime, and ran a cloud cost audit that surfaced monthly spend I could actually reduce. That stretch of work — between the last line of code and the first user — is where the difference gets made.',
                ],
            ],
            [
                'title' => ['ar' => 'الشغل مع الفرق', 'en' => 'Working with teams'],
                'body' => [
                    'ar' => 'اشتغلت مع فرق في مصر والإمارات، وجزء كبير من الدور بيبقى ترجمة: أحوّل قيود البنية التحتية لصيغة يقدر أصحاب القرار غير التقنيين يتصرفوا على أساسها. ده بيقصّر الدورة بين الهندسة وقرارات المنتج، وبيمنع قرارات بتتاخد وهي ناقصة معلومة تقنية مهمة.',
                    'en' => 'I have worked with teams in Egypt and the UAE, and a large part of the role is translation: turning infrastructure constraints into terms non-technical stakeholders can act on. That shortens the loop between engineering and product decisions, and stops calls being made without a technical fact that mattered.',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact (plan §11)
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'pitch' => [
            'ar' => 'أخبرني عن مشروعك أو فرصتك — وسأرد في أقرب وقت.',
            'en' => 'Tell me about your project or opportunity — I’ll get back to you shortly.',
        ],
    ],
];
