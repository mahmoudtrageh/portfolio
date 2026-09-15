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
|   list        repeatable plain strings (a tech stack)
|   list.text   repeatable translated strings (bullet points)
|   repeater    repeatable group of sub-fields
|   bool        checkbox
|   image       uploaded image, stored on the public disk; the value is a path
|   file        uploaded document (PDF), same storage; the value is a path
|
| 'label' is the dashboard's own chrome; content itself stays bilingual.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar groups
    |--------------------------------------------------------------------------
    |
    | Sections are listed under these headings, in this order. A section whose
    | key is not in any group falls into the last group, so adding a section
    | without touching this list still shows up rather than disappearing.
    |
    */

    'groups' => [
        'profile' => [
            'label' => ['ar' => 'الملف الشخصي', 'en' => 'Profile'],
            'sections' => ['identity', 'hero', 'about', 'contact', 'socials'],
        ],
        'work' => [
            'label' => ['ar' => 'الأعمال', 'en' => 'Work'],
            'sections' => ['projects', 'other_projects', 'timeline', 'freelance', 'side_projects', 'skills', 'what_i_build'],
        ],
        'credentials' => [
            'label' => ['ar' => 'المؤهلات', 'en' => 'Credentials'],
            'sections' => ['education', 'certificates'],
        ],
        'content' => [
            'label' => ['ar' => 'المحتوى', 'en' => 'Content'],
            'sections' => ['writing', 'channel'],
        ],
    ],

    'sections' => [

        'identity' => [
            'label' => ['ar' => 'الهوية', 'en' => 'Identity'],
            'type' => 'single',
            'fields' => [
                'logo' => ['type' => 'text', 'label' => ['ar' => 'الشعار (نص)', 'en' => 'Logo (text)']],
                'logo_image' => [
                    'type' => 'image',
                    'label' => ['ar' => 'شعار الوضع الفاتح', 'en' => 'Logo (light mode)'],
                    // Replaces the text logo in the navbar when set.
                    'hint' => [
                        'ar' => 'اختياري. تحل محل الشعار النصي في القائمة العلوية. يُفضّل PNG أو SVG بخلفية شفافة، بألوان داكنة تظهر على الخلفية الفاتحة.',
                        'en' => 'Optional. Replaces the text logo in the navbar. PNG or SVG with a transparent background works best — dark artwork, to read against the light background.',
                    ],
                ],
                'logo_image_dark' => [
                    'type' => 'image',
                    'label' => ['ar' => 'شعار الوضع الداكن', 'en' => 'Logo (dark mode)'],
                    'hint' => [
                        'ar' => 'اختياري. يُستخدم في الوضع الداكن. لو تركته فارغًا، يُستخدم شعار الوضع الفاتح في الوضعين.',
                        'en' => 'Optional. Used in dark mode. Leave empty and the light-mode logo is used for both themes.',
                    ],
                ],
                'favicon' => [
                    'type' => 'image',
                    // Drawn at 16–32px in a tab, so a full-size upload is
                    // downscaled once here rather than on every page load.
                    'max_size' => 512,
                    'label' => ['ar' => 'أيقونة الموقع', 'en' => 'Favicon'],
                    'hint' => [
                        'ar' => 'اختياري. الأيقونة في تبويب المتصفح. يُفضّل مربّعة ٥١٢×٥١٢ بصيغة PNG أو SVG. تُستخدم الأيقونة الافتراضية لو تُركت فارغة.',
                        'en' => 'Optional. The icon in the browser tab. A square 512×512 PNG or SVG works best. The default icon is used when this is empty.',
                    ],
                ],
                'photo' => [
                    'type' => 'image',
                    'label' => ['ar' => 'صورة الواجهة', 'en' => 'Header image'],
                    'hint' => [
                        'ar' => 'اختياري. تظهر بجانب الاسم في أعلى الصفحة. يُفضّل صورة مربّعة.',
                        'en' => 'Optional. Shown beside your name at the top of the page. A square image works best.',
                    ],
                ],
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'role' => ['type' => 'text', 'label' => ['ar' => 'المسمى الوظيفي', 'en' => 'Role']],
                'email' => ['type' => 'plain', 'label' => ['ar' => 'البريد', 'en' => 'Email']],
                'phone' => ['type' => 'plain', 'label' => ['ar' => 'الهاتف', 'en' => 'Phone']],
                'location' => ['type' => 'text', 'label' => ['ar' => 'الموقع', 'en' => 'Location']],
                'available' => ['type' => 'text', 'label' => ['ar' => 'حالة التوفر', 'en' => 'Availability']],
                'cv' => [
                    'type' => 'file',
                    'label' => ['ar' => 'السيرة الذاتية (PDF)', 'en' => 'CV (PDF)'],
                    'hint' => [
                        'ar' => 'ملف واحد بالإنجليزية، يُستخدم في اللغتين. زر التحميل في أعلى الصفحة يختفي حتى ترفع الملف.',
                        'en' => 'One English file, served in both languages. The download button at the top of the page stays hidden until you upload it.',
                    ],
                ],
                'spoken' => ['type' => 'text', 'label' => ['ar' => 'اللغات', 'en' => 'Languages spoken']],
                'case_studies_enabled' => [
                    'type' => 'bool',
                    'label' => ['ar' => 'تفعيل صفحات دراسات الحالة', 'en' => 'Enable case-study pages'],
                    'hint' => [
                        'ar' => 'عند الإيقاف يختفي زر «اقرأ دراسة الحالة» من كل البطاقات، وصفحات المشاريع تُرجع 404. النصوص المكتوبة تبقى محفوظة وتظهر فور إعادة التفعيل.',
                        'en' => 'Off removes the "Read the case study" link from every card, and the project pages 404. Anything you have written is kept, and appears again the moment you switch it back on.',
                    ],
                ],
                'blog_enabled' => [
                    'type' => 'bool',
                    'label' => ['ar' => 'تفعيل المدوّنة', 'en' => 'Enable the blog'],
                    'hint' => [
                        'ar' => 'عند إيقافها تختفي المدوّنة من الموقع: الرابط في القائمة، وقسم آخر المقالات بالصفحة الرئيسية، وخلاصة RSS، وصفحات المقالات نفسها (تُرجع 404). المقالات المكتوبة تبقى كما هي وتعود بمجرد إعادة التفعيل.',
                        'en' => 'Off takes the blog off the site: the navbar link, the latest-posts section on the homepage, the RSS feed, and the blog pages themselves (they 404). The posts you have written are kept, and come back as they were the moment you switch it on again.',
                    ],
                ],
            ],
        ],

        'hero' => [
            'label' => ['ar' => 'الواجهة', 'en' => 'Hero'],
            'type' => 'single',
            'fields' => [
                'eyebrow' => ['type' => 'textarea', 'label' => ['ar' => 'السطر التمهيدي', 'en' => 'Intro line']],
                'headline' => ['type' => 'textarea', 'label' => ['ar' => 'العنوان', 'en' => 'Headline']],
                'summary' => ['type' => 'textarea', 'label' => ['ar' => 'الملخص', 'en' => 'Summary']],
                'experience' => [
                    'type' => 'text',
                    'label' => ['ar' => 'سنوات الخبرة', 'en' => 'Years of experience'],
                    'hint' => [
                        'ar' => 'الرقم فقط، مثل ٦+. عدد المشاريع وعدد الشركات يُحسبان تلقائيًا من محتوى الموقع.',
                        'en' => 'Just the figure, e.g. 6+. The project and company counts are worked out from the site’s own content.',
                    ],
                ],
            ],
        ],

        'projects' => [
            'label' => ['ar' => 'المشاريع', 'en' => 'Projects'],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'slug' => ['type' => 'plain', 'label' => ['ar' => 'المعرّف', 'en' => 'Slug']],
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'category' => ['type' => 'text', 'label' => ['ar' => 'التصنيف', 'en' => 'Category']],
                'year' => ['type' => 'text', 'label' => ['ar' => 'السنة', 'en' => 'Year']],
                'status' => ['type' => 'text', 'label' => ['ar' => 'الحالة', 'en' => 'Status']],
                'featured' => ['type' => 'bool', 'label' => ['ar' => 'مميز', 'en' => 'Featured']],
                'tagline' => ['type' => 'textarea', 'label' => ['ar' => 'الوصف المختصر', 'en' => 'Tagline']],
                'description' => ['type' => 'textarea', 'label' => ['ar' => 'الوصف', 'en' => 'Description']],
                'metric' => ['type' => 'textarea', 'label' => ['ar' => 'النتيجة', 'en' => 'Outcome']],
                'stack' => ['type' => 'list', 'label' => ['ar' => 'التقنيات', 'en' => 'Tech stack']],
                'highlights' => ['type' => 'list.text', 'label' => ['ar' => 'النقاط', 'en' => 'Highlights']],

                /*
                | Case study. Everything below appears only on the project's own
                | page at /{locale}/projects/{slug}, not on the home page. Fill
                | in what a project warrants and leave the rest empty — each
                | section hides itself, and the page link only shows once there
                | is something behind it.
                */
                'cover' => [
                    'type' => 'image',
                    'label' => ['ar' => 'صورة الغلاف', 'en' => 'Cover image'],
                    'hint' => [
                        'ar' => 'اختياري. تظهر أعلى صفحة المشروع وفي مشاركات الروابط.',
                        'en' => 'Optional. Shown at the top of the project page and when the link is shared.',
                    ],
                ],
                'role' => [
                    'type' => 'text',
                    'optional' => true,
                    'label' => ['ar' => 'دوري في المشروع', 'en' => 'My role'],
                ],
                'timeline' => [
                    'type' => 'text',
                    'optional' => true,
                    'label' => ['ar' => 'المدة', 'en' => 'Duration'],
                ],
                'team' => [
                    'type' => 'text',
                    'optional' => true,
                    'label' => ['ar' => 'الفريق', 'en' => 'Team'],
                ],
                'context' => [
                    'type' => 'markdown',
                    'optional' => true,
                    'label' => ['ar' => 'الخلفية والسياق', 'en' => 'Background & context'],
                    'hint' => [
                        'ar' => 'ما هو المشروع، ولمن، ولماذا كان مطلوبًا.',
                        'en' => 'What the project is, who it is for, and why it was needed.',
                    ],
                ],
                'challenges' => [
                    'type' => 'markdown',
                    'optional' => true,
                    'label' => ['ar' => 'التحديات', 'en' => 'Challenges'],
                    'hint' => [
                        'ar' => 'المشكلات الصعبة التي واجهتها، وما الذي جعلها صعبة.',
                        'en' => 'The hard problems you hit, and what made them hard.',
                    ],
                ],
                'solution' => [
                    'type' => 'markdown',
                    'optional' => true,
                    'label' => ['ar' => 'الحل والتنفيذ', 'en' => 'Solution & implementation'],
                    'hint' => [
                        'ar' => 'كيف حللتها، والقرارات المعمارية التي اتخذتها.',
                        'en' => 'How you solved them, and the architectural decisions you took.',
                    ],
                ],
                'results' => [
                    'type' => 'markdown',
                    'optional' => true,
                    'label' => ['ar' => 'النتائج والأثر', 'en' => 'Results & impact'],
                    'hint' => [
                        'ar' => 'ما الذي تغيّر بعد التسليم. الأرقام تُقنع أكثر من الصفات.',
                        'en' => 'What changed after delivery. Figures persuade more than adjectives.',
                    ],
                ],
                'lessons' => [
                    'type' => 'markdown',
                    'optional' => true,
                    'label' => ['ar' => 'الدروس المستفادة', 'en' => 'Lessons learned'],
                ],
                'url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'رابط المشروع', 'en' => 'Live URL'],
                ],
                'repo' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'رابط المستودع', 'en' => 'Repository URL'],
                ],
            ],
        ],

        'other_projects' => [
            'label' => ['ar' => 'مشاريع أخرى', 'en' => 'Other projects'],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'note' => ['type' => 'text', 'label' => ['ar' => 'ملاحظة', 'en' => 'Note']],
            ],
        ],

        'timeline' => [
            'label' => ['ar' => 'الخبرة', 'en' => 'Experience'],
            'type' => 'list',
            'title' => 'org',
            'fields' => [
                'org' => ['type' => 'text', 'label' => ['ar' => 'الجهة', 'en' => 'Organisation']],
                'role' => ['type' => 'text', 'label' => ['ar' => 'الدور', 'en' => 'Role']],
                'period' => ['type' => 'text', 'label' => ['ar' => 'الفترة', 'en' => 'Period']],
                'current' => ['type' => 'bool', 'label' => ['ar' => 'حالي', 'en' => 'Current']],
                'summary' => ['type' => 'textarea', 'label' => ['ar' => 'الملخص', 'en' => 'Summary']],
                'points' => ['type' => 'list.text', 'label' => ['ar' => 'النقاط', 'en' => 'Points']],
                'product' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'اسم المنتج', 'en' => 'Product name'],
                    'hint' => [
                        'ar' => 'اختياري. المنتج الذي شحنته في هذا الدور، مثل اسم تطبيق.',
                        'en' => 'Optional. The product you shipped in this role, e.g. an app name.',
                    ],
                ],
                'product_url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'رابط المنتج', 'en' => 'Product URL'],
                    'hint' => [
                        'ar' => 'اختياري. رابط المنتج، مثل صفحته على App Store.',
                        'en' => 'Optional. A link to it, e.g. its App Store page.',
                    ],
                ],
            ],
        ],

        'freelance' => [
            'label' => ['ar' => 'العمل الحر', 'en' => 'Freelancing'],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'اسم المشروع', 'en' => 'Project name']],
                'platform' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'المنصة', 'en' => 'Platform'],
                    'hint' => [
                        'ar' => 'مثل: مستقل، أو اسم العميل المباشر.',
                        'en' => 'e.g. Mostaql, or a direct client name.',
                    ],
                ],
                'period' => ['type' => 'text', 'label' => ['ar' => 'التاريخ', 'en' => 'Date']],
                'summary' => ['type' => 'textarea', 'label' => ['ar' => 'الوصف', 'en' => 'Summary']],
                'stack' => ['type' => 'list', 'label' => ['ar' => 'التقنيات', 'en' => 'Tech stack']],
                'rating' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'التقييم', 'en' => 'Rating'],
                    'hint' => [
                        'ar' => 'من ٥، مثل 5 أو 4.8. اتركه فارغًا لإخفاء النجوم.',
                        'en' => 'Out of 5, e.g. 5 or 4.8. Leave empty to hide the stars.',
                    ],
                ],
                'client' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'اسم العميل', 'en' => 'Client name'],
                ],
                'review' => [
                    'type' => 'textarea',
                    'optional' => true,
                    'label' => ['ar' => 'رأي العميل', 'en' => 'Client review'],
                    'hint' => [
                        'ar' => 'اقتباس من تقييم العميل. اتركه فارغًا لإخفائه.',
                        'en' => 'A quote from the client’s review. Leave empty to hide it.',
                    ],
                ],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
            ],
        ],

        'side_projects' => [
            'label' => ['ar' => 'مشاريع جانبية', 'en' => 'Side projects'],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'tagline' => ['type' => 'text', 'label' => ['ar' => 'الوصف المختصر', 'en' => 'Tagline']],
                'summary' => ['type' => 'textarea', 'label' => ['ar' => 'الوصف', 'en' => 'Summary']],
                'status' => [
                    'type' => 'text',
                    'optional' => true,
                    'label' => ['ar' => 'الحالة', 'en' => 'Status'],
                    'hint' => [
                        'ar' => 'مثل: قيد التطوير، أو على الإنترنت. اتركه فارغًا لإخفائه.',
                        'en' => 'e.g. In development, or Live. Leave empty to hide it.',
                    ],
                ],
                'stack' => ['type' => 'list', 'label' => ['ar' => 'التقنيات', 'en' => 'Tech stack']],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
            ],
        ],

        'education' => [
            'label' => ['ar' => 'التعليم', 'en' => 'Education'],
            'type' => 'list',
            'title' => 'school',
            'fields' => [
                'school' => ['type' => 'text', 'label' => ['ar' => 'الجهة', 'en' => 'School']],
                'degree' => ['type' => 'text', 'label' => ['ar' => 'الدرجة', 'en' => 'Degree']],
                'period' => ['type' => 'text', 'label' => ['ar' => 'الفترة', 'en' => 'Period']],
                'note' => ['type' => 'textarea', 'label' => ['ar' => 'ملاحظة', 'en' => 'Note']],
            ],
        ],

        'certificates' => [
            'label' => ['ar' => 'الشهادات', 'en' => 'Certificates'],
            'type' => 'list',
            'title' => 'name',
            'fields' => [
                'name' => ['type' => 'text', 'label' => ['ar' => 'الاسم', 'en' => 'Name']],
                'issuer' => ['type' => 'text', 'label' => ['ar' => 'الجهة المانحة', 'en' => 'Issuer']],
                'date' => ['type' => 'text', 'label' => ['ar' => 'التاريخ', 'en' => 'Date']],
                'image' => [
                    'type' => 'image',
                    'label' => ['ar' => 'صورة الشهادة', 'en' => 'Certificate image'],
                    'hint' => [
                        'ar' => 'اختياري. تظهر كصورة مصغّرة، وتُفتح بالحجم الكامل عند الضغط.',
                        'en' => 'Optional. Shown as a thumbnail that opens full size when clicked.',
                    ],
                ],
                'url' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'رابط التحقق', 'en' => 'Verification URL'],
                    'hint' => [
                        'ar' => 'اختياري. رابط التحقق من الشهادة، مثل رابط Coursera.',
                        'en' => 'Optional. A link that verifies the certificate, e.g. a Coursera verify URL.',
                    ],
                ],
            ],
        ],

        'skills' => [
            'label' => ['ar' => 'المهارات', 'en' => 'Skills'],
            'type' => 'list',
            'title' => 'group',
            'fields' => [
                'group' => ['type' => 'text', 'label' => ['ar' => 'المجموعة', 'en' => 'Group']],
                'items' => ['type' => 'list', 'label' => ['ar' => 'العناصر', 'en' => 'Items']],
            ],
        ],

        'channel' => [
            'label' => ['ar' => 'قناة يوتيوب', 'en' => 'YouTube channel'],
            'type' => 'single',
            'fields' => [
                'name' => [
                    'type' => 'text',
                    'label' => ['ar' => 'اسم القناة', 'en' => 'Channel name'],
                    'hint' => [
                        'ar' => 'اتركه فارغًا لإخفاء القناة من الموقع.',
                        'en' => 'Leave empty to hide the channel from the site.',
                    ],
                ],
                'blurb' => [
                    'type' => 'textarea',
                    'label' => ['ar' => 'الوصف', 'en' => 'Description'],
                    'hint' => [
                        'ar' => 'سطر أو سطران عن محتوى القناة.',
                        'en' => 'A line or two about what the channel covers.',
                    ],
                ],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
                'channel_id' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'مُعرّف القناة', 'en' => 'Channel ID'],
                    'hint' => [
                        'ar' => 'يبدأ بـ UC. عند إضافته تُجلب أحدث الفيديوهات تلقائيًا من يوتيوب. تجده في YouTube Studio ← الإعدادات ← القناة ← إعدادات متقدمة.',
                        'en' => 'Starts with UC. Add it and the latest videos are pulled from YouTube automatically, so the list stays current on its own. Find it in YouTube Studio → Settings → Channel → Advanced settings.',
                    ],
                ],
                'video_limit' => [
                    'type' => 'plain',
                    'label' => ['ar' => 'عدد الفيديوهات', 'en' => 'How many videos'],
                    'hint' => [
                        'ar' => 'عدد الفيديوهات المعروضة من التغذية. الافتراضي ٣. اتركه فارغًا للافتراضي.',
                        'en' => 'How many videos from the feed to show. Defaults to 3. Leave empty for the default.',
                    ],
                ],
                'videos' => [
                    'type' => 'repeater',
                    'label' => ['ar' => 'فيديوهات مختارة', 'en' => 'Featured videos'],
                    'hint' => [
                        'ar' => 'اختياري. ضع مُعرّف الفيديو من يوتيوب (الجزء بعد v= في الرابط) والعنوان. الصورة المصغّرة تُجلب تلقائيًا.',
                        'en' => 'Optional. Enter the YouTube video ID (the part after v= in the URL) and a title. The thumbnail is fetched automatically.',
                    ],
                    'fields' => [
                        'id' => ['type' => 'plain', 'label' => ['ar' => 'مُعرّف الفيديو', 'en' => 'Video ID']],
                        'title' => ['type' => 'text', 'label' => ['ar' => 'العنوان', 'en' => 'Title']],
                    ],
                ],
            ],
        ],

        'writing' => [
            'label' => ['ar' => 'الكتابة', 'en' => 'Writing'],
            'type' => 'list',
            'title' => 'title',
            'fields' => [
                'title' => [
                    'type' => 'text',
                    'label' => ['ar' => 'العنوان', 'en' => 'Title'],
                    'hint' => [
                        'ar' => 'العنوان الأصلي للمقال كما نُشر.',
                        'en' => 'The article’s own published title. Keep it as published in both fields if it was only published in Arabic.',
                    ],
                ],
                'gloss' => [
                    'type' => 'text',
                    // Only the locale that needs a translation fills this in.
                    'optional' => true,
                    'label' => ['ar' => 'الترجمة التوضيحية', 'en' => 'Translated gloss'],
                    'hint' => [
                        'ar' => 'اختياري. ترجمة قصيرة للعنوان، تظهر تحته لقارئ لا يعرف لغة المقال.',
                        'en' => 'Optional. A short translation shown under the title, so a reader who does not read the article’s language still knows the subject. Leave empty to hide it.',
                    ],
                ],
                'publisher' => ['type' => 'plain', 'label' => ['ar' => 'جهة النشر', 'en' => 'Publisher']],
                'date' => ['type' => 'text', 'label' => ['ar' => 'التاريخ', 'en' => 'Date']],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
            ],
        ],

        'what_i_build' => [
            'label' => ['ar' => 'ماذا أبني', 'en' => 'What I build'],
            'type' => 'list',
            'title' => 'title',
            'fields' => [
                'title' => ['type' => 'text', 'label' => ['ar' => 'العنوان', 'en' => 'Title']],
                'body' => ['type' => 'textarea', 'label' => ['ar' => 'النص', 'en' => 'Body']],
            ],
        ],

        'about' => [
            'label' => ['ar' => 'عني', 'en' => 'About'],
            'type' => 'single',
            'fields' => [
                'intro' => ['type' => 'textarea', 'label' => ['ar' => 'المقدمة', 'en' => 'Intro']],
                'sections' => [
                    'type' => 'repeater',
                    'label' => ['ar' => 'الفقرات', 'en' => 'Sections'],
                    'fields' => [
                        'title' => ['type' => 'text', 'label' => ['ar' => 'العنوان', 'en' => 'Title']],
                        'body' => ['type' => 'textarea', 'label' => ['ar' => 'النص', 'en' => 'Body']],
                    ],
                ],
            ],
        ],

        'contact' => [
            'label' => ['ar' => 'التواصل', 'en' => 'Contact'],
            'type' => 'single',
            'fields' => [
                'pitch' => ['type' => 'textarea', 'label' => ['ar' => 'النص', 'en' => 'Pitch']],
            ],
        ],

        'socials' => [
            'label' => ['ar' => 'الروابط', 'en' => 'Social links'],
            'type' => 'list',
            'title' => 'label',
            'fields' => [
                'label' => ['type' => 'plain', 'label' => ['ar' => 'الاسم', 'en' => 'Label']],
                'url' => ['type' => 'plain', 'label' => ['ar' => 'الرابط', 'en' => 'URL']],
                'icon' => ['type' => 'plain', 'label' => ['ar' => 'الأيقونة', 'en' => 'Icon']],
            ],
        ],

    ],
];
