# Migrasi Article Images ke Cloudinary

## Overview
Implementasi Cloudinary untuk file upload featured image di articles, termasuk migrasi data yang sudah ada.

## Implementasi yang Telah Dibuat

### 1. **Modifikasi ArticleResource**
File: `app/Filament/Resources/ArticleResource.php`
- ✅ Mengganti `FileUpload` dengan `CloudinaryFileUpload` untuk featured image
- ✅ Upload langsung ke Cloudinary saat form disimpan
- ✅ Menyimpan URL Cloudinary langsung ke database
- ✅ Menggunakan folder: `articles`

### 2. **Modifikasi Model Article**
File: `app/Models/Article.php`
- ✅ Menambahkan logic untuk mendeteksi URL Cloudinary di `getImageUrlAttribute()`
- ✅ Fallback ke local storage jika bukan URL Cloudinary

### 3. **Command untuk Migrasi Data Lama**
File: `app/Console/Commands/MigrateArticleImagesToCloudinary.php`
- ✅ Command untuk mengupload article images yang sudah ada ke Cloudinary
- ✅ Mengupdate database dengan URL Cloudinary
- ✅ Menangani featured images untuk semua articles

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
php artisan articles:migrate-images-to-cloudinary
```

### 3. **Verifikasi Hasil**
Setelah migrasi, endpoint articles akan mengembalikan:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Sample Article",
      "slug": "sample-article",
      "image_path": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/articles/unique_id.jpg",
      "image_url": "https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/articles/unique_id.jpg",
      "content": "Article content...",
      "is_published": true,
      "publish_date": "2025-01-01T00:00:00.000000Z",
      "view_count": 0,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

## Penggunaan untuk Upload Baru

### 1. **Di Filament Admin**
Gunakan `CloudinaryFileUpload` component (sudah diimplementasikan):
```php
use App\Filament\Forms\Components\CloudinaryFileUpload;

CloudinaryFileUpload::make('image_path')
    ->label('Featured Image')
    ->image()
    ->imageEditor()
    ->imageCropAspectRatio('16:9')
    ->imageResizeTargetWidth('1200')
    ->imageResizeTargetHeight('675')
    ->directory('articles')
    ->helperText('Upload a featured image for the article. Recommended size: 1200x675px.');
```

### 2. **Di Controller (Manual Upload)**
```php
use App\Traits\CloudinaryTrait;

class ArticleController extends Controller
{
    use CloudinaryTrait;
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5120', // 5MB max
        ]);
        
        $data = $request->all();
        
        if ($request->hasFile('featured_image')) {
            $cloudinaryUrl = $this->uploadToCloudinary(
                $request->file('featured_image'), 
                'articles'
            );
            $data['image_path'] = $cloudinaryUrl;
        }
        
        $article = Article::create($data);
        
        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }
    
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:5120',
        ]);
        
        $data = $request->all();
        
        if ($request->hasFile('featured_image')) {
            // Delete old image from Cloudinary if exists
            if (str_starts_with($article->image_path, 'https://res.cloudinary.com')) {
                $this->deleteFromCloudinary($article->image_path);
            }
            
            $cloudinaryUrl = $this->uploadToCloudinary(
                $request->file('featured_image'), 
                'articles'
            );
            $data['image_path'] = $cloudinaryUrl;
        }
        
        $article->update($data);
        
        return response()->json([
            'success' => true,
            'data' => $article
        ]);
    }
}
```

## Keuntungan Setelah Migrasi

1. **CDN Global**: Article images diakses dari CDN global yang cepat
2. **Image Optimization**: Otomatis optimize oleh Cloudinary
3. **Transformations**: Dapat menambah parameter di URL untuk resize, crop, dll
4. **Backup**: File tersimpan aman di cloud
5. **Scalability**: Tidak perlu khawatir storage server

## Transformations URL

Setelah migrasi, Anda dapat menambahkan transformations di URL:

### Featured Image Transformations
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_1200,h_675,c_fill,q_auto/articles/article_image.jpg
```

### Thumbnail Transformations
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_300,h_169,c_fill,q_auto/articles/article_image.jpg
```

### Responsive Images
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_auto,h_auto,c_fill,q_auto,f_auto/articles/article_image.jpg
```

Parameter yang tersedia:
- `w_1200`: width 1200px (featured image)
- `h_675`: height 675px (featured image)
- `w_300`: width 300px (thumbnail)
- `h_169`: height 169px (thumbnail)
- `c_fill`: crop mode fill
- `q_auto`: quality auto
- `f_auto`: format auto
- `w_auto,h_auto`: responsive sizing

## Struktur Folder di Cloudinary

Setelah migrasi, file akan tersimpan dengan struktur:
```
cloudinary.com/your_cloud_name/image/upload/
├── articles/
│   ├── unique_id1.jpg
│   ├── unique_id2.png
│   ├── unique_id3.webp
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
# Cek jumlah articles yang sudah diupload ke Cloudinary
php artisan tinker
>>> App\Models\Article::whereNotNull('image_path')->where('image_path', 'like', 'https://res.cloudinary.com%')->count();
>>> App\Models\Article::whereNotNull('image_path')->where('image_path', 'not like', 'https://res.cloudinary.com%')->count();
```

### 2. **Backup Data**
Sebelum menjalankan migrasi, backup database:
```bash
php artisan db:backup
```

### 3. **Rollback (Jika Perlu)**
Jika ada masalah, restore dari backup atau update manual:
```sql
UPDATE articles SET image_path = 'articles/old_path.jpg' WHERE image_path LIKE 'https://res.cloudinary.com%';
```

## Integration dengan Frontend

### 1. **Display Article Images**
```javascript
// Frontend akan otomatis mendapatkan URL Cloudinary
fetch('/api/v1/articles')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(article => {
      console.log('Article Image URL:', article.image_url); // Cloudinary URL
    });
  });
```

### 2. **Image Optimization**
```javascript
// Tambahkan transformations untuk optimize
const optimizedImage = imageUrl.replace('/upload/', '/upload/w_1200,h_675,c_fill,q_auto/');
const thumbnailImage = imageUrl.replace('/upload/', '/upload/w_300,h_169,c_fill,q_auto/');
```

### 3. **HTML Integration**
```html
<!-- Featured Image -->
<img src="{{ $article->image_url }}" 
     alt="{{ $article->title }}"
     class="article-featured-image">

<!-- Thumbnail -->
<img src="{{ str_replace('/upload/', '/upload/w_300,h_169,c_fill,q_auto/', $article->image_url) }}" 
     alt="{{ $article->title }}"
     class="article-thumbnail">
```

### 4. **Responsive Images**
```html
<!-- Responsive Article Image -->
<img src="{{ $article->image_url }}" 
     srcset="{{ $article->image_url }} 1x, 
             {{ str_replace('/upload/', '/upload/w_2400,h_1350,c_fill,q_auto/', $article->image_url) }} 2x"
     alt="{{ $article->title }}"
     class="article-image">
```

### 5. **Lazy Loading**
```html
<!-- Lazy Loading Article Images -->
<img src="{{ $article->image_url }}" 
     alt="{{ $article->title }}"
     loading="lazy"
     class="article-image">
```

## Perbedaan dengan Game/Product/Settings Images

| Aspect | Article Images | Game/Product Images | Settings Images |
|--------|----------------|---------------------|-----------------|
| **Size** | 1200x675px (16:9) | Varied sizes | Logo: 200x100px, Favicon: 32x32px |
| **Usage** | Blog content | Game/product display | Brand identity |
| **Folder** | `articles/` | `games/`, `products/` | `settings/logo`, `settings/favicon` |
| **Frequency** | Per article | Multiple per game/product | Single upload per type |
| **Importance** | Content illustration | Product showcase | Brand recognition |

## Best Practices

### 1. **Article Image Guidelines**
- Gunakan format JPG/PNG/WebP
- Ukuran optimal: 1200x675px (16:9 aspect ratio)
- Pastikan image relevan dengan konten artikel
- Optimize untuk web (compress sebelum upload)

### 2. **Performance Optimization**
```javascript
// Preload critical article images
const preloadArticleImage = (src) => {
  const link = document.createElement('link');
  link.rel = 'preload';
  link.as = 'image';
  link.href = src;
  document.head.appendChild(link);
};

// Preload featured article images
document.querySelectorAll('.article-featured-image').forEach(img => {
  preloadArticleImage(img.src);
});
```

### 3. **Error Handling**
```javascript
// Fallback untuk image yang gagal load
const handleArticleImageError = (event) => {
  event.target.src = '/images/default-article-image.jpg';
  event.target.alt = 'Default Article Image';
};

// Apply error handler
document.querySelectorAll('.article-image').forEach(img => {
  img.addEventListener('error', handleArticleImageError);
});
```

### 4. **SEO Optimization**
```html
<!-- SEO optimized article images -->
<img src="{{ $article->image_url }}" 
     alt="{{ $article->title }} - {{ $article->excerpt }}"
     title="{{ $article->title }}"
     class="article-image">
```

### 5. **Social Media Optimization**
```html
<!-- Open Graph Image -->
<meta property="og:image" content="{{ $article->image_url }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="675">

<!-- Twitter Card Image -->
<meta name="twitter:image" content="{{ $article->image_url }}">
```

## Advanced Usage

### 1. **Multiple Image Sizes**
```php
// Generate multiple sizes for different use cases
public function getImageUrlsAttribute()
{
    $baseUrl = $this->image_path;
    
    return [
        'original' => $baseUrl,
        'featured' => str_replace('/upload/', '/upload/w_1200,h_675,c_fill,q_auto/', $baseUrl),
        'thumbnail' => str_replace('/upload/', '/upload/w_300,h_169,c_fill,q_auto/', $baseUrl),
        'small' => str_replace('/upload/', '/upload/w_150,h_84,c_fill,q_auto/', $baseUrl),
    ];
}
```

### 2. **Image Placeholder**
```html
<!-- Placeholder while loading -->
<div class="article-image-container">
  <div class="image-placeholder" style="aspect-ratio: 16/9; background: #f0f0f0;"></div>
  <img src="{{ $article->image_url }}" 
       alt="{{ $article->title }}"
       class="article-image"
       style="display: none;"
       onload="this.style.display='block'; this.previousElementSibling.style.display='none';">
</div>
```

### 3. **Progressive Loading**
```javascript
// Progressive image loading
const loadProgressiveImage = (lowResUrl, highResUrl, imgElement) => {
  // Load low res first
  const lowResImg = new Image();
  lowResImg.onload = () => {
    imgElement.src = lowResUrl;
    
    // Then load high res
    const highResImg = new Image();
    highResImg.onload = () => {
      imgElement.src = highResUrl;
    };
    highResImg.src = highResUrl;
  };
  lowResImg.src = lowResUrl;
};
``` 