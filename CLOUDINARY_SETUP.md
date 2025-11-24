# Cloudinary Setup untuk Upload File

## Konfigurasi Environment Variables

Tambahkan variabel berikut ke file `.env`:

```env
# Cloudinary Configuration
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name

# Set default filesystem disk ke cloudinary (opsional)
FILESYSTEM_DISK=cloudinary
```

## Cara Mendapatkan Credentials Cloudinary

1. Daftar akun di [Cloudinary](https://cloudinary.com/)
2. Login ke dashboard Cloudinary
3. Copy credentials dari halaman Dashboard:
   - Cloud Name
   - API Key
   - API Secret

## Implementasi yang Telah Dibuat

### 1. CloudinaryServiceProvider
- File: `app/Providers/CloudinaryServiceProvider.php`
- Mendaftarkan Cloudinary SDK ke Laravel
- Menambahkan disk 'cloudinary' ke filesystem

### 2. CloudinaryTrait
- File: `app/Traits/CloudinaryTrait.php`
- Berisi method untuk upload, delete, dan get URL dari Cloudinary
- Dapat digunakan di model atau controller

### 3. CloudinaryFileUpload Component
- File: `app/Filament/Forms/Components/CloudinaryFileUpload.php`
- Custom Filament component untuk upload langsung ke Cloudinary
- Otomatis mengupload file ke Cloudinary saat form disimpan

## Penggunaan di Filament Resources

### Menggunakan CloudinaryFileUpload Component

```php
use App\Filament\Forms\Components\CloudinaryFileUpload;

// Di dalam form schema
CloudinaryFileUpload::make('image_path')
    ->label('Image')
    ->image()
    ->imageEditor()
    ->directory('sliders') // Folder di Cloudinary
    ->required()
```

### Menggunakan CloudinaryTrait di Model

```php
use App\Traits\CloudinaryTrait;

class Slider extends Model
{
    use CloudinaryTrait;
    
    // Method untuk upload
    public function uploadImage($file)
    {
        return $this->uploadToCloudinary($file, 'sliders');
    }
    
    // Method untuk delete
    public function deleteImage($publicId)
    {
        return $this->deleteFromCloudinary($publicId);
    }
}
```

## Keuntungan Menggunakan Cloudinary

1. **CDN Global**: File tersimpan di CDN global yang cepat
2. **Image Optimization**: Otomatis optimize gambar
3. **Transformations**: Dapat mengubah ukuran, crop, filter secara real-time
4. **Backup**: File tersimpan aman di cloud
5. **Scalability**: Tidak perlu khawatir storage server

## Contoh URL Cloudinary

Setelah upload, file akan tersimpan dengan format:
```
https://res.cloudinary.com/your_cloud_name/image/upload/v1234567890/folder/filename.jpg
```

## Transformations URL

Anda dapat menambahkan transformations di URL:
```
https://res.cloudinary.com/your_cloud_name/image/upload/w_300,h_200,c_fill/sliders/image.jpg
```

- `w_300`: width 300px
- `h_200`: height 200px
- `c_fill`: crop mode fill
- `q_auto`: quality auto
- `f_auto`: format auto

## Troubleshooting

### Error: "Cloudinary credentials not found"
- Pastikan semua environment variables sudah diset dengan benar
- Restart server setelah mengubah .env

### Error: "Upload failed"
- Periksa koneksi internet
- Pastikan API key dan secret benar
- Periksa quota Cloudinary

### File tidak terupload
- Pastikan file tidak terlalu besar (max 100MB untuk free tier)
- Periksa format file yang didukung
- Pastikan folder di Cloudinary ada atau dapat dibuat 