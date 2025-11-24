# Migrasi Product Icons ke Cloudinary

## Overview
Implementasi Cloudinary untuk semua image upload di product, termasuk migrasi data yang sudah ada.

## Implementasi yang Telah Dibuat

### 1. **Modifikasi ProductResource**
File: `app/Filament/Resources/ProductResource.php`
- ✅ Mengganti `FileUpload` dengan `CloudinaryFileUpload`
- ✅ Upload langsung ke Cloudinary saat form disimpan
- ✅ Menyimpan URL Cloudinary langsung ke database

### 2. **Modifikasi Model Product**
File: `app/Models/Product.php`
- ✅ Menambahkan deteksi URL Cloudinary di method `getIconUrlAttribute()`
- ✅ Jika `icon_path` sudah berupa URL Cloudinary, kembalikan langsung
- ✅ Jika masih path lokal, kembalikan URL storage

### 3. **Command untuk Migrasi Data Lama**
File: `app/Console/Commands/MigrateProductIconsToCloudinary.php`
- ✅ Command untuk mengupload product icons yang sudah ada ke Cloudinary
- ✅ Mengupdate database dengan URL Cloudinary

## Cara Menjalankan Migrasi

### 1. **Setup Environment Variables**
Pastikan sudah menambahkan credentials Cloudinary di `.env`:
```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
```

### 2. **Jalankan Command Migrasi**
```bash
php artisan products:migrate-icons-to-cloudinary
```

### 3. **Verifikasi Hasil**
Setelah migrasi, endpoint product akan mengembalikan:
```json
{
  "id": 1,
  "name": "86 Diamonds",
  "icon_path": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/products/icons/unique_id.png",
  "icon_url": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/products/icons/unique_id.png"
}
```

## Penggunaan untuk Upload Baru

### 1. **Di Filament Admin**
Gunakan `CloudinaryFileUpload` component (sudah diimplementasikan):
```php
use App\Filament\Forms\Components\CloudinaryFileUpload;

CloudinaryFileUpload::make('icon_path')
    ->label('Product Icon')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('1:1')
    ->imageResizeTargetWidth('64')
    ->imageResizeTargetHeight('64')
    ->directory('products/icons')
    ->required()
```

### 2. **Di Controller (Manual Upload)**
```php
use App\Traits\CloudinaryTrait;

class ProductController extends Controller
{
    use CloudinaryTrait;
    
    public function store(Request $request)
    {
        $request->validate([
            'icon' => 'required|image|max:2048',
        ]);
        
        $cloudinaryUrl = $this->uploadToCloudinary(
            $request->file('icon'), 
            'products/icons'
        );
        
        Product::create([
            'name' => $request->name,
            'icon_path' => $cloudinaryUrl,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'provider_sku' => $request->provider_sku,
            'provider' => $request->provider,
            'game_id' => $request->game_id,
            'product_category_id' => $request->product_category_id,
            'display_order' => $request->display_order,
            'is_active' => $request->is_active,
        ]);
        
        return response()->json(['success' => true]);
    }
}
```

## Keuntungan Setelah Migrasi

1. **CDN Global**: Icon produk diakses dari CDN global yang cepat
2. **Image Optimization**: Otomatis optimize oleh Cloudinary
3. **Transformations**: Dapat menambah parameter di URL untuk resize, crop, dll
4. **Backup**: File tersimpan aman di cloud
5. **Scalability**: Tidak perlu khawatir storage server

## Transformations URL

Setelah migrasi, Anda dapat menambahkan transformations di URL:
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_64,h_64,c_fill,q_auto/products/icons/icon.png
```

Parameter yang tersedia:
- `w_64`: width 64px
- `h_64`: height 64px
- `c_fill`: crop mode fill
- `q_auto`: quality auto
- `f_auto`: format auto

## Struktur Folder di Cloudinary

Setelah migrasi, file akan tersimpan dengan struktur:
```
cloudinary.com/your_cloud_name/image/upload/
├── products/
│   ├── icons/
│   │   ├── unique_id1.png
│   │   ├── unique_id2.png
│   │   └── ...
│   └── ...
└── ...
```

## Troubleshooting

### Error: "Cloudinary credentials not found"
- Pastikan environment variables sudah diset dengan benar
- Restart server setelah mengubah .env

### Error: "Upload failed"
- Periksa koneksi internet
- Pastikan API key dan secret benar
- Periksa quota Cloudinary

### File tidak terupload
- Pastikan file tidak terlalu besar (max 100MB untuk free tier)
- Periksa format file yang didukung
- Pastikan folder di Cloudinary ada atau dapat dibuat

### Command tidak ditemukan
- Pastikan file command sudah dibuat di `app/Console/Commands/`
- Jalankan `php artisan list` untuk melihat semua commands yang tersedia

## Monitoring dan Maintenance

### 1. **Cek Status Upload**
```bash
# Cek jumlah product yang sudah diupload ke Cloudinary
php artisan tinker
>>> App\Models\Product::where('icon_path', 'like', 'https://res.cloudinary.com%')->count();
```

### 2. **Backup Data**
Sebelum menjalankan migrasi, backup database:
```bash
php artisan db:backup
```

### 3. **Rollback (Jika Perlu)**
Jika ada masalah, restore dari backup atau update manual:
```sql
UPDATE products SET icon_path = 'products/icons/old_path.png' WHERE icon_path LIKE 'https://res.cloudinary.com%';
```

## Integration dengan Frontend

### 1. **Display Product Icons**
```javascript
// Frontend akan otomatis mendapatkan URL Cloudinary
fetch('/api/v1/products')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(product => {
      console.log(`Product: ${product.name}`);
      console.log(`Icon URL: ${product.icon_url}`); // Cloudinary URL
    });
  });
```

### 2. **Image Optimization**
```javascript
// Tambahkan transformations untuk optimize
const optimizedUrl = product.icon_url.replace('/upload/', '/upload/w_64,h_64,c_fill,q_auto/');
```

### 3. **Lazy Loading**
```html
<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
     data-src="{{ product.icon_url }}" 
     class="lazy" 
     alt="{{ product.name }}">
``` 