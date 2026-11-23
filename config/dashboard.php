<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Dashboard schema
|--------------------------------------------------------------------------
|
| Describes every editable section: its label, whether it is a singleton or an
| ordered list, and the fields each entry holds. The dashboard renders itself
| from this, so adding a field is a line here rather than new UI.
|
| Field types:
|   text        single-line, translated (renders an AR and an EN input)
|   textarea    multi-line, translated
|   markdown    multi-line, translated, rendered as Markdown when displayed
|   plain       single-line, NOT translated (URLs, slugs, icon names)
|   date        a calendar date, NOT translated, stored as YYYY-MM-DD
|   select      one of 'options' (value => translated label), or empty
|   list        repeatable plain strings (a tech stack)
|   list.text   repeatable translated strings (bullet points)
|   repeater    repeatable group of sub-fields
|   bool        checkbox
|   image       uploaded image, stored on the public disk; the value is a path
|   file        uploaded document (PDF), same storage; the value is a path
|
| 'label' and 'description' are the dashboard's own chrome; content itself
| stays bilingual.
|
| Only what the public site shows is listed. Sections from earlier designs
| (hero, timeline, skills, …) keep their stored data but are not editable here.
|
*/

// Remote / On-site, shared by the current job and every previous job.
$workMode = [
    'type' => 'select',
    'label' => ['ar' => 'طريقة العمل', 'en' => 'Work mode'],
    'options' => [
        'remote' => ['ar' => 'عن بُعد', 'en' => 'Remote'],
        'onsite' => ['ar' => 'من المقر', 'en' => 'On-site'],
    ],
    'hint' => [
        'ar' => 'اختياري. يظهر بخط صغير بجانب الوظيفة.',
        'en' => 'Optional. Shown in small text beside the job.',
    ],
];

// Start/end of a previous job, as a month (YYYY-MM). Sorts the jobs on the
// home page; the start year is also shown beside the job.
$month = fn (array $label, array $hint): array => ['type' => 'month', 'label' => $label, 'hint' => $hint];

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar groups
    |--------------------------------------------------------------------------
    |
    | Mirrors the public site: the details used everywhere, then one group per
    | page.
    |
    */

    'groups' => [
        'site' => [
            'label' => ['ar' => 'الموقع', 'en' => 'Site'],
            'sections' => ['identity', 'socials'],
        ],
        'home' => [
            'label' => ['ar' => 'الصفحة الرئيسية', 'en' => 'Home page'],
            'sections' => ['current_job', 'previous_jobs', 'tech_stack'],
        ],
        'projects' => [
            'label' => ['ar' => 'صفحة المشاريع', 'en' => 'Projects page'],
            'sections' => ['job_projects', 'freelance_projects'],
        ],
    ],

    'sections' => [

        'identity' => [
            'label' => ['ar' => 'الملف الشخصي', 'en' => 'Profile'],
            'description' => [
                'ar' => 'الاسم والمسمى الوظيفي والصورة والسيرة الذاتية وبيانات التواصل.',
                'en' => 'Name, job title, photo, CV and contact details.',
            ],
            'type' => 'single',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'role' => [
                    'type' => 'text',
                    'label' => ['ar' => 'المسمى الوظيفي', 'en' => 'Job title'],
                    'hint' => [
                        'ar' => 'يظهر في عنوان تبويب المتصفح (الاسم — المسمى) وفي سطر التعريف بالصفحة الرئيسية.',
                        'en' => 'Shown in the browser tab (Name — Job title) and in the intro line on the home page.',
                    ],
                ],
                'description' => [
                    'type' => 'textarea',
                    'optional' => true,
                    'label' => ['ar' => 'وصف الموقع', 'en' => 'Site description'],
                    'hint' => [
                        'ar' => 'جملة أو جملتان تظهران في نتائج البحث وعند مشاركة الرابط.',
                        'en' => 'One or two sentences shown in search results and link previews.',
                    ],
                ],
                'photo' => [
                    'type' => 'image',
                    'label' => ['ar' => 'الصورة الشخصية', 'en' => 'Photo'],
                    'hint' => [
                        'ar' => 'تظهر بجانب المقدمة في الصفحة الرئيسية. يُفضّل صورة عرضية.',
                        'en' => 'Shown beside the intro on the home page. A landscape photo works best.',
                    ],
                ],
                'birthdate' => [
                    'type' => 'date',
                    'label' => ['ar' => 'تاريخ الميلاد', 'en' => 'Birthdate'],
                    'hint' => [
                        'ar' => 'اختياري. يُستخدم لحساب عمرك وإظهاره على صورتك في الصفحة الرئيسية. التاريخ نفسه لا يُعرض.',
                        'en' => 'Optional. Used to work out your age, shown on your photo on the home page. The date itself is never shown.',
                    ],
                ],
                'career_start' => [
                    'type' => 'date',
                    'label' => ['ar' => 'بداية مسيرتك في البرمجة', 'en' => 'Software career start'],
                    'hint' => [
                        'ar' => 'اختياري. يُستخدم لحساب سنوات خبرتك وإظهارها على صورتك في الصفحة الرئيسية. التاريخ نفسه لا يُعرض.',
                        'en' => 'Optional. Used to work out your years of experience, shown on your photo on the home page. The date itself is never shown.',
                    ],
                ],
                'location' => [
                    'type' => 'text',
                    'optional' => true,
                    'label' => ['ar' => 'الموقع', 'en' => 'Location'],
                    'hint' => [
                        'ar' => 'اختياري. مثل: القاهرة، مصر. يظهر على صورتك في الصفحة الرئيسية.',
                        'en' => 'Optional. For example: Cairo, Egypt. Shown on your photo on the home page.',
                    ],
                ],
                'favicon' => [
                    'type' => 'image',
                    // Drawn at 16–32px in a tab, so a full-size upload is
                    // downscaled once here rather than on every page load.
                    'max_size' => 512,
                    'label' => ['ar' => 'أيقونة التبويب', 'en' => 'Browser-tab icon'],
                    'hint' => [
                        'ar' => 'اختياري. صورة مربّعة PNG. لو تُركت فارغة تُستخدم صورة وجهك المقصوصة.',
                        'en' => 'Optional. A square PNG. Leave empty to use the cropped photo of your face.',
                    ],
                ],
                'cv' => [
                    'type' => 'file',
                    'label' => ['ar' => 'السيرة الذاتية (PDF)', 'en' => 'CV (PDF)'],
                    'hint' => [
                        'ar' => 'رابط «حمّل سيرتي الذاتية» في الصفحة الرئيسية يفتح هذا الملف، ويختفي لو لم يُرفع ملف.',
                        'en' => 'The "Download my CV" link on the home page opens this file. The link is hidden while nothing is uploaded.',
                    ],
                ],
                'email' => ['type' => 'plain', 'label' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email']],
                'phone' => ['type' => 'plain', 'label' => ['ar' => 'الهاتف', 'en' => 'Phone']],
            ],
        ],

        'socials' => [
            'label' => ['ar' => 'روابط التواصل', 'en' => 'Social links'],
            'description' => [
                'ar' => 'تظهر في صفحة التواصل وأسفل كل صفحة.',
                'en' => 'Listed on the Contact page and in the footer.',
            ],
            'type' => 'list',
            'title' => 'label',
            'fields' => [
                'label' => ['type' => 'plain', 'label' => ['ar' => 'الاسم', 'en' => 'Label']],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
                'icon' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'الأيقونة', 'en' => 'Icon'],
                    'hint' => ['ar' => 'مثل: github أو linkedin', 'en' => 'For example: github or linkedin'],
                ],
            ],
        ],

        'current_job' => [
            'label' => ['ar' => 'الوظيفة الحالية', 'en' => 'Current job'],
            'description' => [
                'ar' => 'الشركة والمنتجات التي تعمل عليها. تظهر في الصفحة الرئيسية وأول مجموعة في صفحة المشاريع.',
                'en' => 'Your company and the products you work on. Shown on the home page and first on the Projects page.',
            ],
            'type' => 'single',
            'fields' => [
                'company' => ['type' => 'plain', 'label' => ['ar' => 'الشركة', 'en' => 'Company']],
                'short_name' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'اسم مختصر', 'en' => 'Short name'],
                    'hint' => [
                        'ar' => 'عنوان المجموعة في صفحة المشاريع، مثل: في Boxes.',
                        'en' => 'Used for the group heading on the Projects page, e.g. At Boxes.',
                    ],
                ],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'رابط الشركة', 'en' => 'Company website']],
                'work_mode' => $workMode,
                'products' => [
                    'type' => 'repeater',
                    'label' => ['ar' => 'المنتجات', 'en' => 'Products'],
                    'fields' => [
                        'name' => ['type' => 'plain', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                        'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
                        'about' => ['type' => 'text', 'label' => ['ar' => 'وصف قصير', 'en' => 'Short description']],
                    ],
                ],
            ],
        ],

        'previous_jobs' => [
            'label' => ['ar' => 'الوظائف السابقة', 'en' => 'Previous jobs'],
            'description' => [
                'ar' => 'سطور «سابقًا…» في الصفحة الرئيسية، مرتبة بالتاريخ من الأحدث للأقدم.',
                'en' => 'The "Previously…" lines on the home page, sorted by date, newest first.',
            ],
            'type' => 'list',
            'title' => 'company',
            'fields' => [
                'company' => ['type' => 'plain', 'label' => ['ar' => 'الشركة', 'en' => 'Company']],
                'role' => ['type' => 'text', 'label' => ['ar' => 'المسمى', 'en' => 'Role']],
                'work_mode' => $workMode,
                'start' => $month(
                    ['ar' => 'تاريخ البدء', 'en' => 'Start date'],
                    ['ar' => 'اختياري. سنة البدء تظهر بجانب الوظيفة، والتاريخ يُستخدم في الترتيب.', 'en' => 'Optional. The year is shown beside the job; the date sorts the list.'],
                ),
                'end' => $month(
                    ['ar' => 'تاريخ الانتهاء', 'en' => 'End date'],
                    ['ar' => 'اختياري. يُستخدم لترتيب الوظائف فقط ولا يظهر في الموقع.', 'en' => 'Optional. Only used to sort the jobs; not shown on the site.'],
                ),
                'url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'رابط الشركة', 'en' => 'Company website'],
                    'hint' => ['ar' => 'اختياري.', 'en' => 'Optional.'],
                ],
            ],
        ],

        'tech_stack' => [
            'label' => ['ar' => 'التقنيات', 'en' => 'Tech stack'],
            'description' => [
                'ar' => 'وسوم التقنيات بعد الوظائف في الصفحة الرئيسية، بالترتيب.',
                'en' => 'The tech tags after your jobs on the home page, in order.',
            ],
            'type' => 'single',
            'fields' => [
                'items' => [
                    'type' => 'list',
                    'label' => ['ar' => 'التقنيات', 'en' => 'Technologies'],
                    'hint' => [
                        'ar' => 'تقنية واحدة في كل خانة، مثل: Laravel. تختفي الوسوم لو كانت القائمة فارغة.',
                        'en' => 'One per box, e.g. Laravel. The tags hide while the list is empty.',
                    ],
                ],
            ],
        ],

        'job_projects' => [
            'label' => ['ar' => 'مشاريع وظائف سابقة', 'en' => 'Projects at previous jobs'],
            'description' => [
                'ar' => 'منتجات عملت عليها في وظائف سابقة.',
                'en' => 'Products you worked on at previous jobs.',
            ],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'about' => ['type' => 'text', 'label' => ['ar' => 'وصف قصير', 'en' => 'Short description']],
                'url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'الرابط', 'en' => 'URL'],
                    'hint' => ['ar' => 'اختياري.', 'en' => 'Optional.'],
                ],
            ],
        ],

        'freelance_projects' => [
            'label' => ['ar' => 'مشاريع العمل الحر', 'en' => 'Freelance projects'],
            'description' => [
                'ar' => 'مشاريع نفّذتها كعمل حر.',
                'en' => 'Projects you delivered as a freelancer.',
            ],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'about' => ['type' => 'text', 'label' => ['ar' => 'وصف قصير', 'en' => 'Short description']],
                'url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'الرابط', 'en' => 'URL'],
                    'hint' => ['ar' => 'اختياري.', 'en' => 'Optional.'],
                ],
            ],
        ],

    ],
];
