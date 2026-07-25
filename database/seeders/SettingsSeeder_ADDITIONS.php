<?php

/**
 * ── MERGE INSTRUCTIONS ──────────────────────────────────────────────
 *
 * This is NOT a standalone file to drop in. Open your existing
 * database/seeders/SettingsSeeder.php and find the `$settings = [...]`
 * array. Paste the array block below in — right before the closing
 * `];` of that array (i.e. before the SYSTEM group, or anywhere inside
 * the array). Then re-run: php artisan db:seed --class=SettingsSeeder
 *
 * These add a new "content" settings group that powers the CMS-driven
 * homepage sections (Hero headline/subline, stats strip, "Why Choose
 * Us" cards) — previously hardcoded in the frontend, now admin-editable
 * from Admin → Settings → content tab.
 * ──────────────────────────────────────────────────────────────────
 */

// ── CONTENT (CMS-managed homepage sections) ──────────────
[
    'group'       => 'content',
    'key'         => 'hero_headline_en',
    'value'       => 'Building Modern Software Systems',
    'description' => 'Main hero headline on the homepage (English).',
    'is_public'   => true,
],
[
    'group'       => 'content',
    'key'         => 'hero_headline_ar',
    'value'       => 'بناء أنظمة برمجية حديثة',
    'description' => 'Main hero headline on the homepage (Arabic).',
    'is_public'   => true,
],
[
    'group'       => 'content',
    'key'         => 'hero_subline_en',
    'value'       => 'Scalable, secure, and production-grade SaaS platforms for real business problems.',
    'description' => 'Hero subheading text on the homepage (English).',
    'is_public'   => true,
],
[
    'group'       => 'content',
    'key'         => 'hero_subline_ar',
    'value'       => 'منصات SaaS قابلة للتوسع وآمنة ومُنتجة لحل مشكلات الأعمال الحقيقية.',
    'description' => 'Hero subheading text on the homepage (Arabic).',
    'is_public'   => true,
],
[
    'group'       => 'content',
    'key'         => 'homepage_stats',
    'value'       => [
        ['label_en' => 'Products',          'label_ar' => 'منتجات',        'value' => '3+'],
        ['label_en' => 'Uptime',             'label_ar' => 'وقت التشغيل',   'value' => '99.9%'],
        ['label_en' => 'Built for scale',    'label_ar' => 'مبني للتوسع',   'value' => '∞'],
    ],
    'description' => 'Statistics strip shown below the hero section. JSON array of {label_en, label_ar, value}.',
    'is_public'   => true,
],
[
    'group'       => 'content',
    'key'         => 'why_choose_items',
    'value'       => [
        [
            'icon' => '🔒',
            'title_en' => 'Security First',
            'title_ar' => 'الأمان أولاً',
            'description_en' => 'Enterprise-grade security built into every layer, from authentication to data storage.',
            'description_ar' => 'أمان بمستوى المؤسسات مدمج في كل طبقة، من المصادقة إلى تخزين البيانات.',
        ],
        [
            'icon' => '🌍',
            'title_en' => 'Bilingual by Design',
            'title_ar' => 'ثنائي اللغة بالتصميم',
            'description_en' => 'Full Arabic and English support with proper RTL layouts across every product.',
            'description_ar' => 'دعم كامل للعربية والإنجليزية مع تخطيطات RTL صحيحة عبر كل منتج.',
        ],
        [
            'icon' => '⚡',
            'title_en' => 'Built to Scale',
            'title_ar' => 'مبني للتوسع',
            'description_en' => 'Architecture designed to grow with your business, from startup to enterprise.',
            'description_ar' => 'معمارية مصممة للنمو مع أعمالك، من الشركات الناشئة إلى المؤسسات الكبرى.',
        ],
    ],
    'description' => '"Why Choose Us" cards shown on the homepage. JSON array of {icon, title_en, title_ar, description_en, description_ar}.',
    'is_public'   => true,
],
