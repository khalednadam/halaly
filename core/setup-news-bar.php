<?php
/**
 * Quick script to setup news bar via tinker
 * Run: php artisan tinker
 * Then copy and paste the code below
 */

use Illuminate\Support\Facades\DB;

// Enable news bar
DB::table('static_options')->updateOrInsert(
    ['option_name' => 'news_bar_status'],
    ['option_value' => 'on']
);

// Add English text
DB::table('static_options')->updateOrInsert(
    ['option_name' => 'news_bar_text_en'],
    ['option_value' => '🎉 Special Promotion: Get 20% off on all listings this week!']
);

// Add Arabic text (adjust slug if different)
DB::table('static_options')->updateOrInsert(
    ['option_name' => 'news_bar_text_ar'],
    ['option_value' => '🎉 عرض خاص: احصل على خصم 20% على جميع القوائم هذا الأسبوع!']
);

// Set background color
DB::table('static_options')->updateOrInsert(
    ['option_name' => 'news_bar_bg_color'],
    ['option_value' => '#f8f9fa']
);

// Set text color
DB::table('static_options')->updateOrInsert(
    ['option_name' => 'news_bar_text_color'],
    ['option_value' => '#333333']
);

echo "News bar configured successfully!\n";

