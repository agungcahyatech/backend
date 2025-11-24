# Migrasi Game Images ke Cloudinary

## Overview
Implementasi Cloudinary untuk semua image upload di game (thumbnail dan banner), termasuk migrasi data yang sudah ada.

## Implementasi yang Telah Dibuat

### 1. **Modifikasi GameResource**
File: `app/Filament/Resources/GameResource.php`
- ✅ Mengganti `FileUpload` dengan `CloudinaryFileUpload` untuk thumbnail
- ✅ Mengganti `FileUpload` dengan `CloudinaryFileUpload` untuk banner
- ✅ Upload langsung ke Cloudinary saat form disimpan
- ✅ Menyimpan URL Cloudinary langsung ke database

### 2. **Modifikasi Model Game**
File: `app/Models/Game.php`
- ✅ Menambahkan deteksi URL Cloudinary di method `getImageThumbnailUrlAttribute()`
- ✅ Menambahkan deteksi URL Cloudinary di method `getImageBannerUrlAttribute()`
- ✅ Jika image path sudah berupa URL Cloudinary, kembalikan langsung
- ✅ Jika masih path lokal, kembalikan URL storage

### 3. **Command untuk Migrasi Data Lama**
File: `app/Console/Commands/MigrateGameImagesToCloudinary.php`
- ✅ Command untuk mengupload game thumbnail dan banner yang sudah ada ke Cloudinary
- ✅ Mengupdate database dengan URL Cloudinary
- ✅ Menangani kedua jenis image (thumbnail dan banner)

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
php artisan games:migrate-images-to-cloudinary
```

### 3. **Verifikasi Hasil**
Setelah migrasi, endpoint game akan mengembalikan:
```json
{
  "id": 1,
  "name": "Mobile Legends",
  "slug": "mobile-legends",
  "image_thumbnail_path": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/games/thumbnails/unique_id.png",
  "image_banner_path": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/games/banners/unique_id.jpg",
  "image_thumbnail_url": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/games/thumbnails/unique_id.png",
  "image_banner_url": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/games/banners/unique_id.jpg"
}
```

## Penggunaan untuk Upload Baru

### 1. **Di Filament Admin**
Gunakan `CloudinaryFileUpload` component (sudah diimplementasikan):
```php
use App\Filament\Forms\Components\CloudinaryFileUpload;

// Thumbnail Image
CloudinaryFileUpload::make('image_thumbnail_path')
    ->label('Thumbnail Image')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('1:1')
    ->imageResizeTargetWidth('300')
    ->imageResizeTargetHeight('300')
    ->directory('games/thumbnails')
    ->required();

// Banner Image
CloudinaryFileUpload::make('image_banner_path')
    ->label('Banner Image')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('16:9')
    ->imageResizeTargetWidth('1200')
    ->imageResizeTargetHeight('675')
    ->directory('games/banners')
    ->required();
```

### 2. **Di Controller (Manual Upload)**
```php
use App\Traits\CloudinaryTrait;

class GameController extends Controller
{
    use CloudinaryTrait;
    
    public function store(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|max:2048',
            'banner' => 'required|image|max:5120',
        ]);
        
        $thumbnailUrl = $this->uploadToCloudinary(
            $request->file('thumbnail'), 
            'games/thumbnails'
        );
        
        $bannerUrl = $this->uploadToCloudinary(
            $request->file('banner'), 
            'games/banners'
        );
        
        Game::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'image_thumbnail_path' => $thumbnailUrl,
            'image_banner_path' => $bannerUrl,
            'description' => $request->description,
            'long_description' => $request->long_description,
            'developer' => $request->developer,
            'brand' => $request->brand,
            'allowed_region' => $request->allowed_region,
            'category_id' => $request->category_id,
            'game_configuration_id' => $request->game_configuration_id,
            'display_order' => $request->display_order,
            'is_active' => $request->is_active,
            'is_popular' => $request->is_popular,
        ]);
        
        return response()->json(['success' => true]);
    }
}
```

## Keuntungan Setelah Migrasi

1. **CDN Global**: Game images diakses dari CDN global yang cepat
2. **Image Optimization**: Otomatis optimize oleh Cloudinary
3. **Transformations**: Dapat menambah parameter di URL untuk resize, crop, dll
4. **Backup**: File tersimpan aman di cloud
5. **Scalability**: Tidak perlu khawatir storage server

## Transformations URL

Setelah migrasi, Anda dapat menambahkan transformations di URL:

### Thumbnail Transformations
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_300,h_300,c_fill,q_auto/games/thumbnails/thumbnail.png
```

### Banner Transformations
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_1200,h_675,c_fill,q_auto/games/banners/banner.jpg
```

Parameter yang tersedia:
- `w_300`: width 300px (thumbnail)
- `h_300`: height 300px (thumbnail)
- `w_1200`: width 1200px (banner)
- `h_675`: height 675px (banner)
- `c_fill`: crop mode fill
- `q_auto`: quality auto
- `f_auto`: format auto

## Struktur Folder di Cloudinary

Setelah migrasi, file akan tersimpan dengan struktur:
```
cloudinary.com/your_cloud_name/image/upload/
├── games/
│   ├── thumbnails/
│   │   ├── unique_id1.png
│   │   ├── unique_id2.png
│   │   └── ...
│   └── banners/
│       ├── unique_id1.jpg
│       ├── unique_id2.jpg
│       └── ...
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
# Cek jumlah game yang sudah diupload ke Cloudinary
php artisan tinker
>>> App\Models\Game::where('image_thumbnail_path', 'like', 'https://res.cloudinary.com%')->count();
>>> App\Models\Game::where('image_banner_path', 'like', 'https://res.cloudinary.com%')->count();
```

### 2. **Backup Data**
Sebelum menjalankan migrasi, backup database:
```bash
php artisan db:backup
```

### 3. **Rollback (Jika Perlu)**
Jika ada masalah, restore dari backup atau update manual:
```sql
UPDATE games SET 
    image_thumbnail_path = 'games/thumbnails/old_path.png',
    image_banner_path = 'games/banners/old_path.jpg' 
WHERE image_thumbnail_path LIKE 'https://res.cloudinary.com%' 
   OR image_banner_path LIKE 'https://res.cloudinary.com%';
```

## Integration dengan Frontend

### 1. **Display Game Images**
```javascript
// Frontend akan otomatis mendapatkan URL Cloudinary
fetch('/api/v1/games')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(game => {
      console.log(`Game: ${game.name}`);
      console.log(`Thumbnail: ${game.image_thumbnail_url}`); // Cloudinary URL
      console.log(`Banner: ${game.image_banner_url}`); // Cloudinary URL
    });
  });
```

### 2. **Image Optimization**
```javascript
// Tambahkan transformations untuk optimize
const optimizedThumbnail = game.image_thumbnail_url.replace('/upload/', '/upload/w_300,h_300,c_fill,q_auto/');
const optimizedBanner = game.image_banner_url.replace('/upload/', '/upload/w_1200,h_675,c_fill,q_auto/');
```

### 3. **Responsive Images**
```html
<!-- Thumbnail -->
<img src="{{ game.image_thumbnail_url }}" 
     alt="{{ game.name }} thumbnail"
     class="game-thumbnail">

<!-- Banner -->
<img src="{{ game.image_banner_url }}" 
     alt="{{ game.name }} banner"
     class="game-banner">
```

### 4. **Lazy Loading**
```html
<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" 
     data-src="{{ game.image_thumbnail_url }}" 
     class="lazy game-thumbnail" 
     alt="{{ game.name }}">
```

## Perbedaan dengan Product Icons

| Aspect | Product Icons | Game Images |
|--------|---------------|-------------|
| **Size** | 64x64px | Thumbnail: 300x300px, Banner: 1200x675px |
| **Aspect Ratio** | 1:1 (Square) | Thumbnail: 1:1, Banner: 16:9 |
| **Folder** | `products/icons` | `games/thumbnails`, `games/banners` |
| **Quantity** | 1 image per product | 2 images per game (thumbnail + banner) |
| **Usage** | Product listing, cart | Game showcase, detail pages | 