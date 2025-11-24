<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Setting;
use App\Filament\Forms\Components\CloudinaryFileUpload;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class Settings extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Manajemen Website';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.settings';

    // Define all the properties that will be used in the form
    public $site_name = '';
    public $site_description = '';
    public $prefix_pesanan = '';
    public $prefix_deposit = '';
    public $site_logo = [];
    public $site_favicon = [];
    public $primary_color = '#3B82F6';
    public $secondary_color = '#64748B';
    public $accent_color = '#F59E0B';
    public $success_color = '#10B981';
    public $meta_title = '';
    public $meta_description = '';
    public $meta_keywords = '';
    public $instagram_url = '';
    public $facebook_url = '';
    public $whatsapp_number = '';
    public $youtube_url = '';
    public $digiflazz_username = '';
    public $digiflazz_api_key = '';
    public $digiflazz_secret_key = '';
    public $apigames_username = '';
    public $apigames_api_key = '';
    public $bangjeff_username = '';
    public $bangjeff_api_key = '';
    public $tokopay_merchant_id = '';
    public $tokopay_secret_key = '';
    public $tokopay_sandbox = true;
    public $duitku_merchant_id = '';
    public $duitku_api_key = '';
    public $duitku_sandbox = true;
    public $fonnte_token = '';
    public $fonnte_device_id = '';
    public $fonnte_enabled = false;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->action('save')
                ->color('primary')
                ->keyBindings(['mod+s']), // opsional shortcut
        ];
    }

    public function mount(): void
    {
        $this->loadSettings();
    }

    public function loadSettings(): void
    {
        $settings = $this->getSettingsData();
        
        $this->site_name = $settings['site_name'];
        $this->site_description = $settings['site_description'];
        $this->prefix_pesanan = $settings['prefix_pesanan'];
        $this->prefix_deposit = $settings['prefix_deposit'];
        // Handle logo and favicon as arrays for FileUpload
        // Handle logo and favicon - CloudinaryFileUpload can handle both URLs and file uploads
        $this->site_logo = $settings['site_logo'] ? [$settings['site_logo']] : [];
        $this->site_favicon = $settings['site_favicon'] ? [$settings['site_favicon']] : [];
        $this->primary_color = $settings['primary_color'];
        $this->secondary_color = $settings['secondary_color'];
        $this->accent_color = $settings['accent_color'];
        $this->success_color = $settings['success_color'];
        $this->meta_title = $settings['meta_title'];
        $this->meta_description = $settings['meta_description'];
        $this->meta_keywords = $settings['meta_keywords'];
        $this->instagram_url = $settings['instagram_url'];
        $this->facebook_url = $settings['facebook_url'];
        $this->whatsapp_number = $settings['whatsapp_number'];
        $this->youtube_url = $settings['youtube_url'];
        $this->digiflazz_username = $settings['digiflazz_username'];
        $this->digiflazz_api_key = $settings['digiflazz_api_key'];
        $this->digiflazz_secret_key = $settings['digiflazz_secret_key'];
        $this->apigames_username = $settings['apigames_username'];
        $this->apigames_api_key = $settings['apigames_api_key'];
        $this->bangjeff_username = $settings['bangjeff_username'];
        $this->bangjeff_api_key = $settings['bangjeff_api_key'];
        $this->tokopay_merchant_id = $settings['tokopay_merchant_id'];
        $this->tokopay_secret_key = $settings['tokopay_secret_key'];
        $this->tokopay_sandbox = $settings['tokopay_sandbox'];
        $this->duitku_merchant_id = $settings['duitku_merchant_id'];
        $this->duitku_api_key = $settings['duitku_api_key'];
        $this->duitku_sandbox = $settings['duitku_sandbox'];
        $this->fonnte_token = $settings['fonnte_token'];
        $this->fonnte_device_id = $settings['fonnte_device_id'];
        $this->fonnte_enabled = $settings['fonnte_enabled'];
    }

    public function getSiteLogoState(): array
    {
        $settings = $this->getSettingsData();
        return $settings['site_logo'] ? [$settings['site_logo']] : [];
    }

    public function getSiteFaviconState(): array
    {
        $settings = $this->getSettingsData();
        return $settings['site_favicon'] ? [$settings['site_favicon']] : [];
    }

    // Method to ensure file state remains stable
    public function updatedSiteLogo($value): void
    {
        // Keep the file state stable after upload
        if (is_array($value) && !empty($value)) {
            $this->site_logo = $value;
        }
    }

    public function updatedSiteFavicon($value): void
    {
        // Keep the file state stable after upload
        if (is_array($value) && !empty($value)) {
            $this->site_favicon = $value;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        // Tab General
                        Forms\Components\Tabs\Tab::make('General')
                            ->schema([
                                Forms\Components\Section::make('General')
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label('Site Name')
                                            ->required()
                                            ->helperText('Nama website')
                                            ->live(),
                                        
                                        Forms\Components\TextInput::make('site_description')
                                            ->label('Site Description')
                                            ->required()
                                            ->helperText('Deskripsi website')
                                            ->live(),
                                        
                                        Forms\Components\TextInput::make('prefix_pesanan')
                                            ->label('Prefix Pesanan')
                                            ->required()
                                            ->helperText('Prefix untuk nomor pesanan')
                                            ->live(),
                                        
                                        Forms\Components\TextInput::make('prefix_deposit')
                                            ->label('Prefix Deposit')
                                            ->required()
                                            ->helperText('Prefix untuk nomor deposit')
                                            ->live(),
                                    ])
                                    ->columns(2),
                                
                                Forms\Components\Section::make('File Uploads')
                                    ->schema([
                                        CloudinaryFileUpload::make('site_logo')
                                            ->label('Site Logo')
                                            ->image()
                                            ->imageEditor()
                                            ->imagePreviewHeight(200)
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth(800)
                                            ->imageResizeTargetHeight(450)
                                            ->directory('settings/logo')
                                            ->helperText('Logo website (PNG/JPG, max 2MB)')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                                            ->maxSize(2048)
                                            ->nullable()
                                            ->multiple(false)
                                            ->preserveFilenames(),
                                        
                                        CloudinaryFileUpload::make('site_favicon')
                                            ->label('Site Favicon')
                                            ->image()
                                            ->imageEditor()
                                            ->imagePreviewHeight(100)
                                            ->imageCropAspectRatio('1:1')
                                            ->imageResizeTargetWidth(32)
                                            ->imageResizeTargetHeight(32)
                                            ->directory('settings/favicon')
                                            ->helperText('Favicon website (ICO/PNG, 32x32px)')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/ico'])
                                            ->maxSize(1024)
                                            ->nullable()
                                            ->multiple(false)
                                            ->preserveFilenames(),
                                    ])
                                    ->columns(2),
                            ]),

                        // Tab Appearance
                        Forms\Components\Tabs\Tab::make('Appearance')
                            ->schema([
                                Forms\Components\Section::make('Color Scheme')
                                    ->schema([
                                        Forms\Components\ColorPicker::make('primary_color')
                                            ->label('Primary Color')
                                            ->default('#3B82F6')
                                            ->helperText('Warna utama website')
                                            ->live(),

                                        Forms\Components\ColorPicker::make('secondary_color')
                                            ->label('Secondary Color')
                                            ->default('#64748B')
                                            ->helperText('Warna sekunder website')
                                            ->live(),

                                        Forms\Components\ColorPicker::make('accent_color')
                                            ->label('Accent Color')
                                            ->default('#F59E0B')
                                            ->helperText('Warna aksen website')
                                            ->live(),

                                        Forms\Components\ColorPicker::make('success_color')
                                            ->label('Success Color')
                                            ->default('#10B981')
                                            ->helperText('Warna untuk status sukses')
                                            ->live(),
                                    ])
                                    ->columns(2),
                            ]),

                        // Tab SEO
                        Forms\Components\Tabs\Tab::make('SEO')
                            ->schema([
                                Forms\Components\Section::make('Meta Tags')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('Meta Title')
                                            ->helperText('Judul untuk SEO')
                                            ->live(),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->helperText('Deskripsi untuk SEO')
                                            ->live(),

                                        Forms\Components\Textarea::make('meta_keywords')
                                            ->label('Meta Keywords')
                                            ->rows(3)
                                            ->helperText('Keywords untuk SEO')
                                            ->live(),
                                    ])
                                    ->columns(1),
                            ]),

                        // Tab Social Media
                        Forms\Components\Tabs\Tab::make('Social Media')
                            ->schema([
                                Forms\Components\Section::make('Social Media Links')
                                    ->schema([
                                        Forms\Components\TextInput::make('instagram_url')
                                            ->label('Instagram URL')
                                            ->url()
                                            ->helperText('Link Instagram')
                                            ->live(),

                                        Forms\Components\TextInput::make('facebook_url')
                                            ->label('Facebook URL')
                                            ->url()
                                            ->helperText('Link Facebook')
                                            ->live(),

                                        Forms\Components\TextInput::make('youtube_url')
                                            ->label('YouTube URL')
                                            ->url()
                                            ->helperText('Link YouTube')
                                            ->live(),

                                        Forms\Components\TextInput::make('whatsapp_number')
                                            ->label('WhatsApp Number')
                                            ->helperText('Nomor WhatsApp (format: 6281234567890)')
                                            ->live(),
                                    ])
                                    ->columns(2),
                            ]),

                        // Tab Providers
                        Forms\Components\Tabs\Tab::make('Providers')
                            ->schema([
                                Forms\Components\Section::make('Digiflazz')
                                    ->schema([
                                        Forms\Components\TextInput::make('digiflazz_username')
                                            ->label('Digiflazz Username')
                                            ->helperText('Username Digiflazz')
                                            ->live(),

                                        Forms\Components\TextInput::make('digiflazz_api_key')
                                            ->label('Digiflazz API Key')
                                            ->password()
                                            ->helperText('API Key Digiflazz')
                                            ->live(),

                                        Forms\Components\TextInput::make('digiflazz_secret_key')
                                            ->label('Digiflazz Secret Key')
                                            ->password()
                                            ->helperText('Secret Key Digiflazz')
                                            ->live(),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Apigames')
                                    ->schema([
                                        Forms\Components\TextInput::make('apigames_username')
                                            ->label('Apigames Username')
                                            ->helperText('Username Apigames')
                                            ->live(),

                                        Forms\Components\TextInput::make('apigames_api_key')
                                            ->label('Apigames API Key')
                                            ->password()
                                            ->helperText('API Key Apigames')
                                            ->live(),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Bangjeff')
                                    ->schema([
                                        Forms\Components\TextInput::make('bangjeff_username')
                                            ->label('Bangjeff Username')
                                            ->helperText('Username Bangjeff')
                                            ->live(),

                                        Forms\Components\TextInput::make('bangjeff_api_key')
                                            ->label('Bangjeff API Key')
                                            ->password()
                                            ->helperText('API Key Bangjeff')
                                            ->live(),
                                    ])
                                    ->columns(2),
                            ]),

                        // Tab Payment Gateways
                        Forms\Components\Tabs\Tab::make('Payment Gateways')
                            ->schema([
                                Forms\Components\Section::make('Tokopay')
                                    ->schema([
                                        Forms\Components\TextInput::make('tokopay_merchant_id')
                                            ->label('Tokopay Merchant ID')
                                            ->helperText('Merchant ID Tokopay')
                                            ->live(),

                                        Forms\Components\TextInput::make('tokopay_secret_key')
                                            ->label('Tokopay Secret Key')
                                            ->password()
                                            ->helperText('Secret Key Tokopay')
                                            ->live(),

                                        Forms\Components\Toggle::make('tokopay_sandbox')
                                            ->label('Tokopay Sandbox Mode')
                                            ->helperText('Aktifkan mode sandbox')
                                            ->live(),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Duitku')
                                    ->schema([
                                        Forms\Components\TextInput::make('duitku_merchant_id')
                                            ->label('Duitku Merchant ID')
                                            ->helperText('Merchant ID Duitku')
                                            ->live(),

                                        Forms\Components\TextInput::make('duitku_api_key')
                                            ->label('Duitku API Key')
                                            ->password()
                                            ->helperText('API Key Duitku')
                                            ->live(),

                                        Forms\Components\Toggle::make('duitku_sandbox')
                                            ->label('Duitku Sandbox Mode')
                                            ->helperText('Aktifkan mode sandbox')
                                            ->live(),
                                    ])
                                    ->columns(3),
                            ]),

                        // Tab WhatsApp
                        Forms\Components\Tabs\Tab::make('WhatsApp')
                            ->schema([
                                Forms\Components\Section::make('Fonnte Configuration')
                                    ->schema([
                                        Forms\Components\TextInput::make('fonnte_token')
                                            ->label('Fonnte Token')
                                            ->password()
                                            ->helperText('Token Fonnte')
                                            ->live(),

                                        Forms\Components\TextInput::make('fonnte_device_id')
                                            ->label('Fonnte Device ID')
                                            ->helperText('Device ID Fonnte')
                                            ->live(),

                                        Forms\Components\Toggle::make('fonnte_enabled')
                                            ->label('Enable Fonnte')
                                            ->helperText('Aktifkan notifikasi WhatsApp')
                                            ->live(),
                                    ])
                                    ->columns(3),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            // Handle file uploads and convert to Cloudinary URLs if needed
            if (isset($data['site_logo']) && is_array($data['site_logo']) && !empty($data['site_logo'])) {
                $logoFile = $data['site_logo'][0];
                
                // If it's a local file path, try to get Cloudinary URL
                if (is_string($logoFile) && !filter_var($logoFile, FILTER_VALIDATE_URL)) {
                    $cloudinaryUrl = $this->getCloudinaryUrlForLocalFile($logoFile);
                    if ($cloudinaryUrl) {
                        $data['site_logo'] = $cloudinaryUrl;
                    } else {
                        $data['site_logo'] = $logoFile; // Keep local path if Cloudinary not ready
                    }
                } else {
                    $data['site_logo'] = $logoFile;
                }
            }
            
            if (isset($data['site_favicon']) && is_array($data['site_favicon']) && !empty($data['site_favicon'])) {
                $faviconFile = $data['site_favicon'][0];
                
                // If it's a local file path, try to get Cloudinary URL
                if (is_string($faviconFile) && !filter_var($faviconFile, FILTER_VALIDATE_URL)) {
                    $cloudinaryUrl = $this->getCloudinaryUrlForLocalFile($faviconFile);
                    if ($cloudinaryUrl) {
                        $data['site_favicon'] = $cloudinaryUrl;
                    } else {
                        $data['site_favicon'] = $faviconFile; // Keep local path if Cloudinary not ready
                    }
                } else {
                    $data['site_favicon'] = $faviconFile;
                }
            }
            
            $this->saveSettings($data);
            
            Notification::make()
                ->title('Settings saved successfully!')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Log::error('Settings save error: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error saving settings')
                ->body('Please try again.')
                ->danger()
                ->send();
        }
    }

    private function getSettingsData(): array
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        return [
            'site_name' => $settings['site_name'] ?? '',
            'site_description' => $settings['site_description'] ?? '',
            'prefix_pesanan' => $settings['prefix_pesanan'] ?? '',
            'prefix_deposit' => $settings['prefix_deposit'] ?? '',
            'site_logo' => $settings['site_logo'] ?? '',
            'site_favicon' => $settings['site_favicon'] ?? '',
            'primary_color' => $settings['primary_color'] ?? '#3B82F6',
            'secondary_color' => $settings['secondary_color'] ?? '#64748B',
            'accent_color' => $settings['accent_color'] ?? '#F59E0B',
            'success_color' => $settings['success_color'] ?? '#10B981',
            'meta_title' => $settings['meta_title'] ?? '',
            'meta_description' => $settings['meta_description'] ?? '',
            'meta_keywords' => $settings['meta_keywords'] ?? '',
            'instagram_url' => $settings['instagram_url'] ?? '',
            'facebook_url' => $settings['facebook_url'] ?? '',
            'whatsapp_number' => $settings['whatsapp_number'] ?? '',
            'youtube_url' => $settings['youtube_url'] ?? '',
            'digiflazz_username' => $settings['digiflazz_username'] ?? '',
            'digiflazz_api_key' => $settings['digiflazz_api_key'] ?? '',
            'digiflazz_secret_key' => $settings['digiflazz_secret_key'] ?? '',
            'apigames_username' => $settings['apigames_username'] ?? '',
            'apigames_api_key' => $settings['apigames_api_key'] ?? '',
            'bangjeff_username' => $settings['bangjeff_username'] ?? '',
            'bangjeff_api_key' => $settings['bangjeff_api_key'] ?? '',
            'tokopay_merchant_id' => $settings['tokopay_merchant_id'] ?? '',
            'tokopay_secret_key' => $settings['tokopay_secret_key'] ?? '',
            'tokopay_sandbox' => $settings['tokopay_sandbox'] ?? true,
            'duitku_merchant_id' => $settings['duitku_merchant_id'] ?? '',
            'duitku_api_key' => $settings['duitku_api_key'] ?? '',
            'duitku_sandbox' => $settings['duitku_sandbox'] ?? true,
            'fonnte_token' => $settings['fonnte_token'] ?? '',
            'fonnte_device_id' => $settings['fonnte_device_id'] ?? '',
            'fonnte_enabled' => $settings['fonnte_enabled'] ?? false,
        ];
    }

    private function saveSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }
    }

    private function getCloudinaryUrlForLocalFile($localPath)
    {
        // Check cache for Cloudinary URL mapping
        $cacheKey = 'cloudinary_mapping_' . md5($localPath);
        return Cache::get($cacheKey);
    }

} 