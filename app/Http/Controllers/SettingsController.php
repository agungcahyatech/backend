<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        // Daftar setting yang diizinkan untuk ditampilkan di API
        $allowedSettings = [
            // General Settings
            'site_name',
            'site_description',
            'site_logo',
            'site_favicon',
            'contact_email',
            'contact_phone',
            'contact_address',
            'social_facebook',
            'social_instagram',
            'social_twitter',
            'social_youtube',
            'maintenance_mode',
            'maintenance_message',
            'google_analytics_id',
            'facebook_pixel_id',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'footer_text',
            'copyright_text',
            'terms_of_service_url',
            'privacy_policy_url',
            'help_center_url',
            'about_us_url',
            'contact_us_url',
            
            // Appearance Settings
            'primary_color',
            'secondary_color',
            'accent_color',
            'text_color',
            'background_color',
            'header_style',
            'footer_style',
            'button_style',
            'border_radius',
            'font_family',
            'font_size',
            'theme_mode',
            'custom_css',
            'custom_js',
            'logo_width',
            'logo_height',
            'favicon_type',
            'loading_animation',
            'scroll_behavior',
            'sidebar_position',
            'navbar_style',
            'card_style',
            'shadow_style',
            'border_style',
            
            // SEO Settings
            'seo_title',
            'seo_description',
            'seo_keywords',
            'seo_author',
            'seo_robots',
            'seo_canonical',
            'seo_og_title',
            'seo_og_description',
            'seo_og_image',
            'seo_og_type',
            'seo_twitter_card',
            'seo_twitter_title',
            'seo_twitter_description',
            'seo_twitter_image',
            'seo_schema_markup',
            'seo_google_verification',
            'seo_bing_verification',
            'seo_yandex_verification',
            'seo_baidu_verification',
            'seo_sitemap_url',
            'seo_robots_txt',
            'seo_structured_data',
            'seo_amp_enabled',
            'seo_pwa_enabled',
        ];

        $settings = Setting::whereIn('key', $allowedSettings)->get();
        
        // Order settings by the position in allowedSettings array
        $orderedSettings = collect($settings)->sortBy(function ($setting) use ($allowedSettings) {
            return array_search($setting->key, $allowedSettings);
        })->values();
        
        return response()->json([
            'success' => true,
            'data' => $orderedSettings
        ]);
    }

    /**
     * Get social media settings
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialMedia()
    {
        // Daftar setting social media yang diizinkan untuk ditampilkan di API
        $socialMediaSettings = [
            'instagram_url',
            'facebook_url', 
            'youtube_url',
            'whatsapp_number',
        ];

        $settings = Setting::whereIn('key', $socialMediaSettings)->get();
        
        // Format response dengan struktur yang lebih friendly
        $formattedSettings = [];
        foreach ($settings as $setting) {
            $formattedSettings[$setting->key] = $setting->value;
        }

        // Set default values jika setting tidak ada
        $defaultSettings = [
            'instagram_url' => '',
            'facebook_url' => '',
            'youtube_url' => '',
            'whatsapp_number' => '',
        ];

        $finalSettings = array_merge($defaultSettings, $formattedSettings);

        return response()->json([
            'success' => true,
            'message' => 'Social media settings retrieved successfully',
            'data' => [
                'social_media' => $finalSettings
            ]
        ]);
    }
} 