# Payment Method Cloudinary Integration

## Overview

Payment Method sekarang menggunakan Cloudinary untuk menyimpan dan mengelola gambar logo payment method. Ini memberikan performa yang lebih baik dan skalabilitas untuk aplikasi.

## Perubahan yang Dilakukan

### 1. **PaymentMethod Model**
- Mengupdate `getImageUrlAttribute()` untuk mendukung Cloudinary URLs
- Mendeteksi URL Cloudinary dan local storage paths
- Fallback ke local storage jika diperlukan

### 2. **PaymentMethodResource**
- Mengganti `FileUpload` dengan `CloudinaryFileUpload`
- Menggunakan folder `payment-methods` di Cloudinary
- Mendukung image editing dan cropping

### 3. **Migration**
- Membuat migration untuk memindahkan gambar existing ke Cloudinary
- Otomatis menghapus file lokal setelah upload berhasil
- Logging untuk tracking progress

## Struktur Folder Cloudinary

```
payment-methods/
├── qris-logo.jpg
├── bca-va-logo.png
├── dana-logo.jpg
├── ovo-logo.png
└── ...
```

## Response Structure

### API Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "QRIS",
      "provider": "tokopay",
      "code": "QRIS",
      "group": "QRIS",
      "type": "qris",
      "image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/qris-logo.jpg",
      "image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/qris-logo.jpg",
      "fee_flat": 0,
      "fee_percent": 0,
      "min_amount": 1000,
      "max_amount": 10000000,
      "is_active": true
    }
  ]
}
```

## Implementasi di Frontend

### JavaScript
```javascript
// Menggunakan image_url untuk display
const paymentMethods = response.data;
paymentMethods.forEach(method => {
    const logoUrl = method.image_url;
    const logoElement = document.createElement('img');
    logoElement.src = logoUrl;
    logoElement.alt = method.name;
    // Append to DOM
});
```

### React/Vue
```jsx
// React Example
const PaymentMethodCard = ({ method }) => {
    return (
        <div className="payment-method-card">
            <img 
                src={method.image_url} 
                alt={method.name}
                className="payment-logo"
            />
            <h3>{method.name}</h3>
            <p>{method.group}</p>
        </div>
    );
};
```

## Migration Process

### Running Migration
```bash
php artisan migrate
```

### Migration Steps
1. **Scan**: Mencari payment methods dengan local image paths
2. **Upload**: Upload gambar ke Cloudinary dengan folder `payment-methods`
3. **Update**: Update database dengan Cloudinary URL
4. **Cleanup**: Hapus file lokal setelah upload berhasil
5. **Logging**: Record semua proses untuk monitoring

### Migration Logs
```
[INFO] Migrated payment method image: payment-methods/qris-logo.jpg -> https://res.cloudinary.com/...
[INFO] Migrated payment method image: payment-methods/bca-va.png -> https://res.cloudinary.com/...
[ERROR] Failed to migrate payment method image 5: File not found
```

## Keuntungan Cloudinary

### 1. **Performance**
- CDN global untuk loading cepat
- Image optimization otomatis
- Responsive images

### 2. **Scalability**
- Tidak ada batasan storage lokal
- Auto-scaling berdasarkan traffic
- Backup dan redundancy

### 3. **Features**
- Image transformations
- Format optimization
- Lazy loading support

### 4. **Cost Effective**
- Pay-per-use pricing
- Tidak perlu server storage
- Bandwidth optimization

## Error Handling

### Model Fallback
```php
public function getImageUrlAttribute(): ?string
{
    // Try Cloudinary URL first
    if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
        return $this->image_path;
    }

    // Try Cloudinary pattern
    if (str_starts_with($this->image_path, 'https://res.cloudinary.com')) {
        return $this->image_path;
    }

    // Fallback to local storage
    return $this->image_path ? Storage::url($this->image_path) : null;
}
```

### Migration Error Handling
```php
try {
    // Migration logic
} catch (\Exception $e) {
    Log::error("Failed to migrate payment method image {$paymentMethod->id}: " . $e->getMessage());
    // Continue with next item
}
```

## Configuration

### Cloudinary Config
```php
// config/services.php
'cloudinary' => [
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
],
```

### Environment Variables
```env
CLOUDINARY_CLOUD_NAME=your-cloud-name
CLOUDINARY_API_KEY=your-api-key
CLOUDINARY_API_SECRET=your-api-secret
```

## Testing

### Manual Test
1. Upload payment method logo melalui admin panel
2. Verify image tersimpan di Cloudinary
3. Check API response menggunakan `image_url`
4. Verify image loading di frontend

### Automated Test
```php
public function test_payment_method_cloudinary_integration()
{
    $paymentMethod = PaymentMethod::factory()->create([
        'image_path' => 'https://res.cloudinary.com/test/image/upload/test.jpg'
    ]);

    $this->assertEquals(
        'https://res.cloudinary.com/test/image/upload/test.jpg',
        $paymentMethod->image_url
    );
}
```

## Monitoring

### Logs to Monitor
- Migration progress
- Upload failures
- Cloudinary API errors
- Storage cleanup

### Metrics
- Upload success rate
- Average upload time
- Storage usage
- CDN performance 