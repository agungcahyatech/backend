<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_title = 'My Website';
    public string $site_description = '';
    public ?string $site_logo = null;
    public ?string $site_favicon = null;
    public string $primary_color = '#3B82F6';
    public string $secondary_color = '#64748B';
    public string $accent_color = '#F59E0B';
    public string $success_color = '#10B981';
    public string $meta_title = '';
    public string $meta_description = '';
    public string $meta_keywords = '';
    public string $google_analytics_id = '';
    public string $instagram_url = '';
    public string $facebook_url = '';
    public string $whatsapp_number = '';
    public string $youtube_url = '';
    public string $digiflazz_username = '';
    public string $digiflazz_api_key = '';
    public string $apigames_username = '';
    public string $apigames_api_key = '';
    public string $bangjeff_username = '';
    public string $bangjeff_api_key = '';
    public string $tokopay_merchant_id = '';
    public string $tokopay_api_key = '';
    public bool $tokopay_sandbox = true;
    public string $duitku_merchant_id = '';
    public string $duitku_api_key = '';
    public bool $duitku_sandbox = true;
    public string $fonnte_token = '';
    public string $fonnte_device_id = '';
    public bool $fonnte_enabled = false;

    public static function group(): string
    {
        return 'general';
    }

    public static function repository(): string
    {
        return 'database';
    }
} 