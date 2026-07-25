<?php

namespace Database\Seeders;

use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Cms\Models\StaticPage;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHomepageSections();
        $this->seedFaqs();
        $this->seedMenus();
        $this->seedStaticPages();

        $this->command->info('✓ CMS content seeded.');
    }

    private function seedHomepageSections(): void
    {
        $sections = [
            [
                'type'        => 'hero',
                'title_en'    => 'Building Modern Software Systems',
                'title_ar'    => 'بناء أنظمة برمجية حديثة',
                'subtitle_en' => 'Scalable, secure, and production-grade SaaS platforms for real business problems.',
                'subtitle_ar' => 'منصات SaaS قابلة للتوسع وآمنة ومُنتجة لحل مشكلات الأعمال الحقيقية.',
                'content'     => json_encode([
                    'badge_en' => 'Software Products Company',
                    'badge_ar' => 'شركة منتجات برمجية',
                    'cta_primary_en' => 'Explore Products',
                    'cta_primary_ar' => 'استكشف المنتجات',
                    'cta_primary_url' => '/products',
                    'cta_secondary_en' => 'View Roadmap',
                    'cta_secondary_ar' => 'خارطة الطريق',
                    'cta_secondary_url' => '/roadmap',
                ]),
                'is_enabled'  => true,
                'sort_order'  => 10,
            ],
            [
                'type'        => 'stats',
                'title_en'    => null,
                'title_ar'    => null,
                'subtitle_en' => null,
                'subtitle_ar' => null,
                'is_enabled'  => true,
                'sort_order'  => 20,
                'content'     => json_encode([]),
            ],
            [
                'type'        => 'why_choose',
                'title_en'    => 'Built Different',
                'title_ar'    => 'مبنية بشكل مختلف',
                'subtitle_en' => 'Why YS Systems',
                'subtitle_ar' => 'لماذا YS Systems',
                'content'     => json_encode([
                    'items' => [
                        [
                            'icon' => 'Lock',
                            'title_en' => 'Security First',
                            'title_ar' => 'الأمان أولاً',
                            'desc_en'  => 'Enterprise-grade security built into every layer, from authentication to data storage.',
                            'desc_ar'  => 'أمان بمستوى المؤسسات مدمج في كل طبقة، من المصادقة إلى تخزين البيانات.',
                        ],
                        [
                            'icon' => 'Globe',
                            'title_en' => 'Bilingual by Design',
                            'title_ar' => 'ثنائي اللغة بالتصميم',
                            'desc_en'  => 'Full Arabic and English support with proper RTL layouts across every product.',
                            'desc_ar'  => 'دعم كامل للعربية والإنجليزية مع تخطيطات RTL صحيحة عبر كل منتج.',
                        ],
                        [
                            'icon' => 'Zap',
                            'title_en' => 'Built to Scale',
                            'title_ar' => 'مبني للتوسع',
                            'desc_en'  => 'Architecture designed to grow with your business, from startup to enterprise.',
                            'desc_ar'  => 'معمارية مصممة للنمو مع أعمالك، من الشركات الناشئة إلى المؤسسات الكبرى.',
                        ],
                    ],
                ]),
                'is_enabled'  => true,
                'sort_order'  => 30,
            ],
            [
                'type'        => 'products',
                'title_en'    => 'Our Products',
                'title_ar'    => 'منتجاتنا',
                'subtitle_en' => 'A growing ecosystem of software solutions.',
                'subtitle_ar' => 'منظومة متنامية من الحلول البرمجية.',
                'content'     => null,
                'is_enabled'  => true,
                'sort_order'  => 40,
            ],
            [
                'type'        => 'cta',
                'title_en'    => 'Ready to Get Started?',
                'title_ar'    => 'هل أنت مستعد للبدء؟',
                'subtitle_en' => 'Discover how our products can help your business grow.',
                'subtitle_ar' => 'اكتشف كيف يمكن لمنتجاتنا أن تساعد عملك على النمو.',
                'content'     => json_encode([
                    'primary_text_en' => 'Contact Us',
                    'primary_text_ar' => 'تواصل معنا',
                    'primary_url'     => '/contact',
                    'secondary_text_en' => 'Browse Products',
                    'secondary_text_ar' => 'استعرض المنتجات',
                    'secondary_url'   => '/products',
                ]),
                'is_enabled'  => true,
                'sort_order'  => 50,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['type' => $section['type'], 'sort_order' => $section['sort_order']],
                $section
            );
        }
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['question_en' => 'What is YS Systems?', 'question_ar' => 'ما هي YS Systems؟', 'answer_en' => 'YS Systems & Software is a software company that builds modern, scalable, and secure software systems, SaaS platforms, and industry-specific business solutions for companies of all sizes.', 'answer_ar' => 'YS Systems & Software هي شركة برمجيات تبني أنظمة برمجية حديثة وقابلة للتوسع وآمنة ومنصات SaaS وحلول أعمال متخصصة للشركات من جميع الأحجام.', 'sort_order' => 10],
            ['question_en' => 'What products do you offer?', 'question_ar' => 'ما هي المنتجات التي تقدمونها؟', 'answer_en' => 'We develop a range of software products including YS-Matrix, YS-Sports, and Vortex Trader_Y. Visit our Products page for detailed information about each product.', 'answer_ar' => 'نقوم بتطوير مجموعة من المنتجات البرمجية بما في ذلك YS-Matrix و YS-Sports و Vortex Trader_Y. تفضل بزيارة صفحة المنتجات للحصول على معلومات مفصلة عن كل منتج.', 'sort_order' => 20],
            ['question_en' => 'How can I get started?', 'question_ar' => 'كيف يمكنني البدء؟', 'answer_en' => 'The best way to get started is to explore our products and documentation. If you have specific questions or want to discuss a partnership, feel free to reach out through our Contact page.', 'answer_ar' => 'أفضل طريقة للبدء هي استكشاف منتجاتنا والتوثيق. إذا كانت لديك أسئلة محددة أو ترغب في مناقشة شراكة، فلا تتردد في التواصل معنا من خلال صفحة الاتصال.', 'sort_order' => 30],
            ['question_en' => 'Do you offer enterprise support?', 'question_ar' => 'هل تقدمون دعمًا للمؤسسات؟', 'answer_en' => 'Yes, we provide support for all our products. You can reach us through the Contact page, and our team will assist you with your inquiry.', 'answer_ar' => 'نعم، نقدم الدعم لجميع منتجاتنا. يمكنك التواصل معنا من خلال صفحة الاتصال وسيقوم فريقنا بمساعدتك في استفسارك.', 'sort_order' => 40],
            ['question_en' => 'Is my data secure with your platform?', 'question_ar' => 'هل بياناتي آمنة على منصتكم؟', 'answer_en' => 'We take security seriously. We implement industry-standard security measures including encryption at rest and in transit, access controls, and regular security assessments. Visit our Security page for more details.', 'answer_ar' => 'نحن نأخذ الأمان على محمل الجد. ننفذ تدابير أمنية قياسية في الصناعة بما في ذلك التشفير أثناء التخزين والنقل وضوابط الوصول والتقييمات الأمنية المنتظمة. تفضل بزيارة صفحة الأمان لمزيد من التفاصيل.', 'sort_order' => 50],
            ['question_en' => 'How do you handle privacy?', 'question_ar' => 'كيف تتعاملون مع الخصوصية؟', 'answer_en' => 'We are committed to protecting your privacy. We only collect information necessary to provide our services, do not sell personal data to third parties, and implement appropriate security measures. See our Privacy Policy for full details.', 'answer_ar' => 'نحن ملتزمون بحماية خصوصيتك. نجمع فقط المعلومات اللازمة لتقديم خدماتنا، ولا نبيع البيانات الشخصية لأطراف ثالثة، وننفذ تدابير أمنية مناسبة. راجع سياسة الخصوصية للحصول على التفاصيل الكاملة.', 'sort_order' => 60],
            ['question_en' => 'Can I request a feature?', 'question_ar' => 'هل يمكنني طلب ميزة؟', 'answer_en' => 'Absolutely! We welcome feature suggestions. You can check our Roadmap to see what is planned or contact us with your ideas.', 'answer_ar' => 'بالتأكيد! نحن نرحب باقتراحات الميزات. يمكنك الاطلاع على خارطة الطريق لمعرفة ما هو مخطط له أو التواصل معنا بأفكارك.', 'sort_order' => 70],
            ['question_en' => 'Do you provide documentation?', 'question_ar' => 'هل توفرون توثيقًا؟', 'answer_en' => 'Yes, comprehensive documentation is available for all our products. Visit our Docs section to get started.', 'answer_ar' => 'نعم، يتوفر توثيق شامل لجميع منتجاتنا. تفضل بزيارة قسم التوثيق للبدء.', 'sort_order' => 80],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question_en' => $faq['question_en']],
                array_merge($faq, ['status' => 'published'])
            );
        }
    }

    private function seedMenus(): void
    {
        // Header navigation
        $header = Menu::updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Header Navigation', 'is_active' => true]
        );

        $headerItems = [
            ['title_en' => 'Products',     'title_ar' => 'المنتجات',     'url' => '/products',  'sort_order' => 10],
            ['title_en' => 'Ecosystem',    'title_ar' => 'المنظومة',    'url' => '/ecosystem', 'sort_order' => 20],
            ['title_en' => 'Docs',         'title_ar' => 'التوثيق',     'url' => '/docs',      'sort_order' => 30],
            ['title_en' => 'Roadmap',      'title_ar' => 'خارطة الطريق','url' => '/roadmap',   'sort_order' => 40],
            ['title_en' => 'Updates',      'title_ar' => 'المستجدات',   'url' => '/updates',   'sort_order' => 50],
            ['title_en' => 'About',        'title_ar' => 'عن الشركة',   'url' => '/about',     'sort_order' => 60],
        ];

        foreach ($headerItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $header->id, 'url' => $item['url']],
                array_merge($item, ['is_active' => true, 'target' => '_self'])
            );
        }

        // Footer — Products
        $footerProducts = Menu::updateOrCreate(
            ['location' => 'footer_products'],
            ['name' => 'Footer Products', 'is_active' => true]
        );

        $footerProductItems = [
            ['title_en' => 'YS-Matrix',       'title_ar' => 'YS-Matrix',       'url' => '/products/ys-matrix',       'sort_order' => 10],
            ['title_en' => 'YS-Sports',       'title_ar' => 'YS-Sports',       'url' => '/products/ys-sports',       'sort_order' => 20],
            ['title_en' => 'Vortex Trader_Y', 'title_ar' => 'Vortex Trader_Y', 'url' => '/products/vortex-trader-y', 'sort_order' => 30],
        ];

        foreach ($footerProductItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerProducts->id, 'url' => $item['url']],
                array_merge($item, ['is_active' => true, 'target' => '_self'])
            );
        }

        // Footer — Company
        $footerCompany = Menu::updateOrCreate(
            ['location' => 'footer_company'],
            ['name' => 'Footer Company', 'is_active' => true]
        );

        $footerCompanyItems = [
            ['title_en' => 'About',   'title_ar' => 'عن الشركة',   'url' => '/about',  'sort_order' => 10],
            ['title_en' => 'Careers', 'title_ar' => 'الوظائف',     'url' => '/careers','sort_order' => 20],
            ['title_en' => 'Contact', 'title_ar' => 'تواصل معنا',  'url' => '/contact','sort_order' => 30],
        ];

        foreach ($footerCompanyItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerCompany->id, 'url' => $item['url']],
                array_merge($item, ['is_active' => true, 'target' => '_self'])
            );
        }

        // Footer — Resources
        $footerResources = Menu::updateOrCreate(
            ['location' => 'footer_resources'],
            ['name' => 'Footer Resources', 'is_active' => true]
        );

        $footerResourceItems = [
            ['title_en' => 'Docs',          'title_ar' => 'التوثيق',          'url' => '/docs',      'sort_order' => 10],
            ['title_en' => 'Roadmap',       'title_ar' => 'خارطة الطريق',     'url' => '/roadmap',   'sort_order' => 20],
            ['title_en' => 'Updates',       'title_ar' => 'المستجدات',        'url' => '/updates',   'sort_order' => 30],
            ['title_en' => 'Status',        'title_ar' => 'حالة النظام',      'url' => '/status',    'sort_order' => 40],
            ['title_en' => 'FAQ',           'title_ar' => 'الأسئلة الشائعة',  'url' => '/faq',       'sort_order' => 50],
            ['title_en' => 'Security',      'title_ar' => 'الأمان',           'url' => '/security',  'sort_order' => 60],
        ];

        foreach ($footerResourceItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerResources->id, 'url' => $item['url']],
                array_merge($item, ['is_active' => true, 'target' => '_self'])
            );
        }

        // Footer — Legal
        $footerLegal = Menu::updateOrCreate(
            ['location' => 'footer_legal'],
            ['name' => 'Footer Legal', 'is_active' => true]
        );

        $footerLegalItems = [
            ['title_en' => 'Privacy Policy', 'title_ar' => 'سياسة الخصوصية',            'url' => '/privacy',      'sort_order' => 10],
            ['title_en' => 'Terms of Service', 'title_ar' => 'شروط الخدمة',             'url' => '/terms',        'sort_order' => 20],
            ['title_en' => 'Cookie Policy', 'title_ar' => 'سياسة ملفات تعريف الارتباط', 'url' => '/cookie-policy','sort_order' => 30],
        ];

        foreach ($footerLegalItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerLegal->id, 'url' => $item['url']],
                array_merge($item, ['is_active' => true, 'target' => '_self'])
            );
        }
    }

    private function seedStaticPages(): void
    {
        $pages = [
            [
                'slug'        => 'about',
                'title_en'    => 'About YS Systems',
                'title_ar'    => 'عن YS Systems',
                'excerpt_en'  => 'We build modern, scalable, and secure software systems that solve real business problems.',
                'excerpt_ar'  => 'نبني أنظمة برمجية حديثة وقابلة للتوسع وآمنة تحل مشكلات الأعمال الحقيقية.',
                'content_en'  => json_encode([
                    ['label' => 'Our Mission', 'text' => 'To create scalable, secure, modern, and professional software products that solve real business problems for companies of all sizes.'],
                    ['label' => 'Our Vision',  'text' => 'Building modern software systems, SaaS platforms, and industry-specific business solutions that empower businesses to grow.'],
                ]),
                'content_ar'  => json_encode([
                    ['label' => 'مهمتنا', 'text' => 'إنشاء منتجات برمجية قابلة للتوسع وآمنة وحديثة واحترافية تحل مشكلات الأعمال الحقيقية للشركات من جميع الأحجام.'],
                    ['label' => 'رؤيتنا', 'text' => 'بناء أنظمة برمجية حديثة ومنصات SaaS وحلول أعمال متخصصة تمكّن الشركات من النمو.'],
                ]),
                'status'      => 'published',
                'published_at' => now(),
                'sort_order'  => 10,
            ],
            [
                'slug'        => 'privacy',
                'title_en'    => 'Privacy Policy',
                'title_ar'    => 'سياسة الخصوصية',
                'content_en'  => json_encode([
                    ['title' => 'Information We Collect', 'body' => 'We collect information you provide directly to us, such as when you contact us or use our services.'],
                    ['title' => 'How We Use Your Information', 'body' => 'We use information to provide and improve our services, communicate with you, and ensure security.'],
                    ['title' => 'Information Sharing', 'body' => 'We do not sell your personal information to third parties. We may share it with service providers only when necessary.'],
                    ['title' => 'Security', 'body' => 'We take appropriate security measures to protect your information from unauthorized access or disclosure.'],
                    ['title' => 'Contact Us', 'body' => 'If you have questions about this Privacy Policy, please contact us at cantactys@gmail.com'],
                ]),
                'content_ar'  => json_encode([
                    ['title' => 'المعلومات التي نجمعها', 'body' => 'نجمع المعلومات التي تقدمها مباشرةً لنا، مثل عندما تتواصل معنا أو تستخدم خدماتنا.'],
                    ['title' => 'كيف نستخدم معلوماتك', 'body' => 'نستخدم المعلومات لتقديم خدماتنا وتحسينها، والتواصل معك، وضمان الأمان.'],
                    ['title' => 'مشاركة المعلومات', 'body' => 'لا نبيع معلوماتك الشخصية لأطراف ثالثة. قد نشاركها مع مزودي الخدمات فقط عند الضرورة.'],
                    ['title' => 'الأمان', 'body' => 'نتخذ تدابير أمنية مناسبة لحماية معلوماتك من الوصول غير المصرح به أو الإفصاح.'],
                    ['title' => 'الاتصال بنا', 'body' => 'إذا كانت لديك أسئلة حول سياسة الخصوصية، يرجى التواصل معنا على cantactys@gmail.com'],
                ]),
                'status'      => 'published',
                'published_at' => now(),
                'sort_order'  => 20,
            ],
            [
                'slug'        => 'terms',
                'title_en'    => 'Terms of Service',
                'title_ar'    => 'شروط الخدمة',
                'content_en'  => json_encode([
                    ['title' => 'Acceptance of Terms', 'body' => 'By using YS Systems & Software services, you agree to be bound by these terms and conditions.'],
                    ['title' => 'Use of Services', 'body' => 'Our services must be used for lawful purposes only. Any use that conflicts with applicable laws is prohibited.'],
                    ['title' => 'Intellectual Property', 'body' => 'All content, software, and trademarks associated with our services are the property of YS Systems & Software.'],
                    ['title' => 'Disclaimer', 'body' => 'We provide our services "as is" without warranties of any kind, either express or implied.'],
                    ['title' => 'Updates to Terms', 'body' => 'We reserve the right to modify these terms at any time. We will notify you of material changes via email.'],
                    ['title' => 'Contact', 'body' => 'For questions about these Terms, please contact us at cantactys@gmail.com'],
                ]),
                'content_ar'  => json_encode([
                    ['title' => 'قبول الشروط', 'body' => 'باستخدامك لخدمات YS Systems & Software، فإنك توافق على الالتزام بهذه الشروط والأحكام.'],
                    ['title' => 'استخدام الخدمات', 'body' => 'يجب استخدام خدماتنا للأغراض المشروعة فقط. يُحظر أي استخدام يتعارض مع القوانين المعمول بها.'],
                    ['title' => 'الملكية الفكرية', 'body' => 'جميع المحتوى والبرمجيات والعلامات التجارية المرتبطة بخدماتنا هي ملك لـ YS Systems & Software.'],
                    ['title' => 'إخلاء المسؤولية', 'body' => 'نقدم خدماتنا "كما هي" دون ضمانات صريحة أو ضمنية من أي نوع.'],
                    ['title' => 'تحديثات الشروط', 'body' => 'نحتفظ بحق تعديل هذه الشروط في أي وقت. سنبلغك بالتغييرات الجوهرية عبر البريد الإلكتروني.'],
                    ['title' => 'التواصل', 'body' => 'للاستفسار عن هذه الشروط، يرجى التواصل معنا على cantactys@gmail.com'],
                ]),
                'status'      => 'published',
                'published_at' => now(),
                'sort_order'  => 30,
            ],
            [
                'slug'        => 'cookie-policy',
                'title_en'    => 'Cookie Policy',
                'title_ar'    => 'سياسة ملفات تعريف الارتباط',
                'content_en'  => json_encode([
                    ['title' => 'What Are Cookies?', 'body' => 'Cookies are small text files that are placed on your computer or mobile device when you visit a website. They are widely used to make websites work or work more efficiently, as well as to provide information to the site owners.'],
                    ['title' => 'How We Use Cookies', 'body' => "We use cookies for the following purposes:\n\n• Essential Cookies: Required for our website to function and cannot be switched off. They are usually set only in response to actions you take such as logging in or filling in forms.\n\n• Analytics Cookies: Help us understand how visitors interact with our website by collecting information anonymously. We use this data to improve our site performance and user experience."],
                    ['title' => 'Consent', 'body' => 'When you first visit our site, we will show you a pop-up explaining our cookie policy. You can choose to accept all cookies, reject non-essential cookies, or customize your preferences. Your preferences are stored in your browser for use on future visits.'],
                    ['title' => 'Managing Preferences', 'body' => 'You can change your cookie preferences at any time by adjusting your browser settings. Most browsers allow you to delete or reject cookies. Please note that disabling essential cookies may affect the functionality of our site.'],
                    ['title' => 'Third Parties', 'body' => 'We do not use any third-party services that place non-essential cookies without your explicit consent. Any third-party cookies we may request will be managed according to their respective privacy policies.'],
                    ['title' => 'Contact Us', 'body' => 'If you have any questions about our Cookie Policy, please contact us at cantactys@gmail.com.'],
                ]),
                'content_ar'  => json_encode([
                    ['title' => 'ما هي ملفات تعريف الارتباط؟', 'body' => 'ملفات تعريف الارتباط هي ملفات نصية صغيرة يتم وضعها على جهاز الكمبيوتر أو الجهاز المحمول الخاص بك عند زيارة موقع ويب. تُستخدم على نطاق واسع لجعل مواقع الويب تعمل أو تعمل بكفاءة أكبر، بالإضافة إلى توفير معلومات لأصحاب الموقع.'],
                    ['title' => 'كيف نستخدم ملفات تعريف الارتباط', 'body' => "نستخدم ملفات تعريف الارتباط للأغراض التالية:\n\n• ملفات تعريف الارتباط الأساسية: مطلوبة لتشغيل موقعنا الإلكتروني ولا يمكن إيقاف تشغيلها. يتم تعيينها عادةً فقط استجابةً لإجراءات قمت بها مثل تسجيل الدخول أو ملء النماذج.\n\n• ملفات تعريف الارتباط التحليلية: تساعدنا على فهم كيفية تفاعل الزوار مع موقعنا من خلال جمع المعلومات بشكل مجهول. نستخدم هذه المعلومات لتحسين أداء موقعنا وتجربة المستخدم."],
                    ['title' => 'الموافقة', 'body' => 'عند زيارتك لموقعنا لأول مرة، سنعرض لك نافذة منبثقة تشرح سياسة ملفات تعريف الارتباط الخاصة بنا. يمكنك اختيار قبول جميع ملفات تعريف الارتباط أو رفض ملفات تعريف الارتباط غير الأساسية أو تخصيص تفضيلاتك. يتم تخزين تفضيلاتك في متصفحك لاستخدامها في الزيارات المستقبلية.'],
                    ['title' => 'إدارة التفضيلات', 'body' => 'يمكنك تغيير تفضيلات ملفات تعريف الارتباط الخاصة بك في أي وقت عن طريق ضبط إعدادات متصفحك. يمكن لمعظم المتصفحات حذف ملفات تعريف الارتباط أو رفضها. يرجى ملاحظة أن تعطيل ملفات تعريف الارتباط الأساسية قد يؤثر على وظائف موقعنا.'],
                    ['title' => 'جهات خارجية', 'body' => 'لا نستخدم خدمات جهات خارجية تضع ملفات تعريف ارتباط غير أساسية دون موافقتك الصريحة. أي ملفات تعريف ارتباط تابعة لجهات خارجية سنطلبها سيتم إدارتها وفقًا لسياسات الخصوصية الخاصة بها.'],
                    ['title' => 'الاتصال بنا', 'body' => 'إذا كانت لديك أسئلة حول سياسة ملفات تعريف الارتباط الخاصة بنا، يرجى التواصل معنا على cantactys@gmail.com.'],
                ]),
                'status'      => 'published',
                'published_at' => now(),
                'sort_order'  => 40,
            ],
            [
                'slug'        => 'security',
                'title_en'    => 'Security at YS Systems',
                'title_ar'    => 'الأمان في YS Systems',
                'excerpt_en'  => 'How we protect our platforms and our customers\' data.',
                'excerpt_ar'  => 'كيف نحمي منصاتنا وبيانات عملائنا.',
                'content_en'  => json_encode([
                    ['title' => 'Our Security Commitment', 'body' => 'Security is the foundation of everything we build. We follow industry best practices to protect our customers\' data and maintain the integrity of our platforms. Our team continuously assesses and improves our security posture to address evolving threats.'],
                    ['title' => 'Infrastructure Security', 'body' => 'We employ multiple layers of security controls to protect our infrastructure, including firewalls, intrusion detection systems, and regular security patching. Data is stored in secure data centers with strict access controls.'],
                    ['title' => 'Encryption', 'body' => 'All data transmitted between our clients and our servers is encrypted using TLS/SSL. Data at rest is encrypted using strong encryption standards to protect it from unauthorized access.'],
                    ['title' => 'Access Control', 'body' => 'We enforce strict access controls based on the principle of least privilege. Access to systems and data is secured with multi-factor authentication and regularly reviewed.'],
                    ['title' => 'Security Assessment', 'body' => 'We conduct regular security assessments, including vulnerability scanning and code review, to proactively identify and address potential risks.'],
                    ['title' => 'Data Protection', 'body' => 'We implement measures to protect sensitive data, including role-based access controls, data classification, and secure storage and disposal practices.'],
                ]),
                'content_ar'  => json_encode([
                    ['title' => 'التزامنا بالأمان', 'body' => 'الأمان هو أساس كل ما نبنيه. نتبع أفضل الممارسات في الصناعة لحماية بيانات عملائنا والحفاظ على سلامة منصاتنا. فريقنا يقيّم باستمرار ويحسن وضعنا الأمني لمواجهة التهديدات المتطورة.'],
                    ['title' => 'أمان البنية التحتية', 'body' => 'نستخدم طبقات متعددة من الضوابط الأمنية لحماية البنية التحتية لدينا، بما في ذلك جدران الحماية وأنظمة كشف التسلل والتحديثات الأمنية المنتظمة. يتم تخزين البيانات في مراكز بيانات آمنة مع ضوابط وصول صارمة.'],
                    ['title' => 'التشفير', 'body' => 'جميع البيانات المنقولة بين عملائنا وخوادمنا مشفرة باستخدام TLS/SSL. البيانات المخزنة مشفرة باستخدام معايير تشفير قوية لحمايتها من الوصول غير المصرح به.'],
                    ['title' => 'التحكم في الوصول', 'body' => 'نطبق ضوابط وصول صارمة بناءً على مبدأ أقل امتياز. يتم تأمين الوصول إلى الأنظمة والبيانات بالمصادقة متعددة العوامل ومراجعته بانتظام.'],
                    ['title' => 'تقييم الأمان', 'body' => 'نجري تقييمات أمنية منتظمة، بما في ذلك فحص الثغرات الأمنية ومراجعة الكود، لتحديد ومعالجة المخاطر المحتملة بشكل استباقي.'],
                    ['title' => 'حماية البيانات', 'body' => 'نطبق إجراءات لحماية البيانات الحساسة، بما في ذلك ضوابط الوصول المستندة إلى الأدوار، وتصنيف البيانات، وممارسات التخزين والتخلص الآمنة.'],
                ]),
                'status'      => 'published',
                'published_at' => now(),
                'sort_order'  => 50,
            ],
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
