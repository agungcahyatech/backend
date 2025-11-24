<?php

namespace App\Filament\Resources\PaymentMethodResource\Pages;

use App\Filament\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('getFromTokopay')
                ->label('Get Payment Channel')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('provider')
                        ->label('Pilih Provider')
                        ->required()
                        ->options(function () {
                            $providers = [];
                            
                            // Check configured payment gateways from settings
                            if (!empty(Setting::getValue('tokopay_merchant_id')) && !empty(Setting::getValue('tokopay_secret_key'))) {
                                $providers['tokopay'] = 'Tokopay';
                            }
                            if (!empty(Setting::getValue('duitku_merchant_id')) && !empty(Setting::getValue('duitku_api_key'))) {
                                $providers['duitku'] = 'Duitku';
                            }
                            
                            return $providers;
                        })
                        ->helperText('Select the payment gateway to import payment methods from.')
                        ->placeholder('Select an option'),
                ])
                ->modalHeading('Get Payment Channel')
                ->modalIcon('heroicon-o-exclamation-triangle')
                ->modalDescription('Are you sure you would like to do this?')
                ->modalWidth('sm')
                ->modalAlignment('center')
                ->modalSubmitActionLabel('Confirm')
                ->modalCancelActionLabel('Cancel')
                ->action(function (array $data) {
                    $this->getPaymentMethodsFromProvider($data);
                }),

            Actions\CreateAction::make()
                ->modalHeading('Create New Payment Method')
                ->modalDescription('Add a new payment method with logo, settings, and fee configuration.')
                ->modalSubmitActionLabel('Create Payment Method'),
        ];
    }

    public function getPaymentMethodsFromProvider(array $data): void
    {
        try {
            $selectedProvider = $data['provider'] ?? null;
            
            if (!$selectedProvider) {
                Notification::make()
                    ->title('No provider selected')
                    ->body('Please select a payment gateway provider.')
                    ->danger()
                    ->send();
                return;
            }

            // Handle different providers
            switch ($selectedProvider) {
                case 'tokopay':
                    $this->importTokopayPaymentMethods();
                    break;
                case 'duitku':
                    $this->importDuitkuPaymentMethods();
                    break;
                default:
                    Notification::make()
                        ->title('Unsupported provider')
                        ->body('The selected provider is not supported.')
                        ->danger()
                        ->send();
                    return;
            }

        } catch (\Exception $e) {
            Log::error('Error importing payment methods', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error importing payment methods')
                ->body('An error occurred while importing payment methods: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function importTokopayPaymentMethods(): void
    {
        // Check if Tokopay is configured
        $merchantId = Setting::getValue('tokopay_merchant_id');
        $secretKey = Setting::getValue('tokopay_secret_key');

        if (empty($merchantId) || empty($secretKey)) {
            Notification::make()
                ->title('Tokopay not configured')
                ->body('Please configure Tokopay settings first.')
                ->danger()
                ->send();
            return;
        }

        // Tokopay only has production API
        $endpoint = 'https://api.tokopay.id/v1/merchant';

        // Generate signature using md5(merchant_id:secret)
        $signature = md5($merchantId . ':' . $secretKey);

        // Make API request to Tokopay to get merchant info
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'merchant_id' => $merchantId,
            'signature' => $signature,
        ]);

        if (!$response->successful()) {
            Log::error('Tokopay API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
                'endpoint' => $endpoint,
                'merchant_id' => $merchantId
            ]);

            Notification::make()
                ->title('Failed to fetch payment methods')
                ->body('Error: ' . $response->status() . ' - ' . $response->body())
                ->danger()
                ->send();
            return;
        }

        $responseData = $response->json();
        
        // Log the response for debugging
        Log::info('Tokopay API Response', [
            'response' => $responseData,
            'status' => $response->status()
        ]);
        
        // Check if response is successful (Tokopay uses status: 1 for success)
        if (!isset($responseData['status']) || $responseData['status'] !== 1) {
            Notification::make()
                ->title('API Response Error')
                ->body('Tokopay API returned an error: ' . ($responseData['message'] ?? 'Unknown error'))
                ->danger()
                ->send();
            return;
        }
        
        // Since the merchant endpoint only returns account info, we'll create the standard Tokopay payment methods
        // based on the official documentation
        $tokopayPaymentMethods = [
            // QRIS
            ['code' => 'QRIS', 'name' => 'QRIS', 'type' => 'qris', 'group' => 'QRIS'],
            ['code' => 'QRISREALTIME', 'name' => 'QRIS Realtime', 'type' => 'qris', 'group' => 'QRIS'],
            ['code' => 'QRIS_REALTIME_NOBU', 'name' => 'QRIS Realtime Nobu', 'type' => 'qris', 'group' => 'QRIS'],
            
            // Virtual Account
            ['code' => 'BRIVA', 'name' => 'BRI Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'BCAVA', 'name' => 'BCA Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'BNIVA', 'name' => 'BNI Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'MANDIRIVA', 'name' => 'MANDIRI Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'PERMATAVA', 'name' => 'PERMATA Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'PERMATAVAA', 'name' => 'PERMATA Virtual Account 2', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'CIMBVA', 'name' => 'CIMB Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'DANAMONVA', 'name' => 'DANAMON Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'BSIVA', 'name' => 'BSI Virtual Account', 'type' => 'va', 'group' => 'Virtual Account'],
            ['code' => 'BNCVA', 'name' => 'BNC Virtual Account (NEO)', 'type' => 'va', 'group' => 'Virtual Account'],
            
            // E-Money
            ['code' => 'SHOPEEPAY', 'name' => 'Shopee Pay', 'type' => 'e-wallet', 'group' => 'E-Wallet'],
            ['code' => 'GOPAY', 'name' => 'Gopay', 'type' => 'e-wallet', 'group' => 'E-Wallet'],
            ['code' => 'DANA', 'name' => 'DANA', 'type' => 'e-wallet', 'group' => 'E-Wallet'],
            ['code' => 'LINKAJA', 'name' => 'LINK AJA', 'type' => 'e-wallet', 'group' => 'E-Wallet'],
            
            // Retail/Convenience Store
            ['code' => 'ALFAMART', 'name' => 'Alfamart', 'type' => 'convenience_store', 'group' => 'Convenience Store'],
            ['code' => 'INDOMARET', 'name' => 'Indomaret', 'type' => 'convenience_store', 'group' => 'Convenience Store'],
            
            // Pulsa
            ['code' => 'TELKOMSEL', 'name' => 'Telkomsel', 'type' => 'pulsa', 'group' => 'Pulsa'],
            ['code' => 'AXIS', 'name' => 'AXIS', 'type' => 'pulsa', 'group' => 'Pulsa'],
            ['code' => 'XL', 'name' => 'XL', 'type' => 'pulsa', 'group' => 'Pulsa'],
            ['code' => 'TRI', 'name' => 'Tri', 'type' => 'pulsa', 'group' => 'Pulsa'],
        ];
        
        $paymentMethods = $tokopayPaymentMethods;
        $importedCount = 0;

        // Define the order priority for payment types
        $typeOrder = [
            'qris' => 1,
            'e-wallet' => 2,
            'va' => 3,
            'convenience_store' => 4,
            'pulsa' => 5,
            'other' => 6
        ];

        // Sort payment methods by type order
        usort($paymentMethods, function ($a, $b) use ($typeOrder) {
            $aOrder = $typeOrder[$a['type'] ?? 'other'] ?? 6;
            $bOrder = $typeOrder[$b['type'] ?? 'other'] ?? 6;
            return $aOrder - $bOrder;
        });

        foreach ($paymentMethods as $method) {
            $code = $method['code'];
            $name = $method['name'];
            $type = $method['type'];
            $group = $method['group'];
            
            // Skip if already exists
            if (PaymentMethod::where('code', $code)->exists()) {
                continue;
            }

            // Create payment method
            PaymentMethod::create([
                'name' => $name,
                'provider' => 'tokopay',
                'code' => $code,
                'group' => $group,
                'type' => $type,
                'fee_flat' => 0, // Default fee, can be updated manually
                'fee_percent' => 0, // Default fee, can be updated manually
                'min_amount' => 0, // Default minimum, can be updated manually
                'max_amount' => 0, // Default maximum, can be updated manually
                'is_active' => true,
            ]);

            $importedCount++;
        }

        Notification::make()
            ->title('Payment methods imported successfully')
            ->body("Imported {$importedCount} payment methods from Tokopay")
            ->success()
            ->send();
    }

    private function importDuitkuPaymentMethods(): void
    {
        // Check if Duitku is configured
        $merchantId = Setting::getValue('duitku_merchant_id');
        $apiKey = Setting::getValue('duitku_api_key');

        if (empty($merchantId) || empty($apiKey)) {
            Notification::make()
                ->title('Duitku not configured')
                ->body('Please configure Duitku settings first.')
                ->danger()
                ->send();
            return;
        }

        // For now, we'll create a placeholder for Duitku
        // You can implement the actual Duitku API integration later
        Notification::make()
            ->title('Duitku integration')
            ->body('Duitku payment method import is not yet implemented.')
            ->warning()
            ->send();
    }
} 