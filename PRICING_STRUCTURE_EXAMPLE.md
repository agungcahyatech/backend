# Contoh Struktur Harga Baru

## Struktur Harga yang Direkomendasikan

### 1. **Base Price (Harga Modal)**
- Harga asli produk tanpa profit margin
- Digunakan untuk perhitungan internal
- Tidak ditampilkan ke user

### 2. **Product Price (Harga Frontend)**
- Harga yang ditampilkan ke user
- Sudah dihitung berdasarkan role + profit margin
- Harga yang user lihat di aplikasi

### 3. **Final Price (Harga Akhir)**
- Harga yang harus dibayar user
- Mengutamakan flash sale price jika ada
- Jika tidak ada flash sale, menggunakan product price

## Contoh Perhitungan

### Scenario 1: User Silver (4.00% profit margin)
```json
{
  "base_price": 20000,        // Harga modal
  "product_price": 20800,     // 20000 + (20000 * 4%) = 20800 (dibulatkan)
  "final_price": 20800        // Tidak ada flash sale
}
```

### Scenario 2: User Gold (3.00% profit margin) dengan Flash Sale
```json
{
  "base_price": 20000,        // Harga modal
  "product_price": 20600,     // 20000 + (20000 * 3%) = 20600 (dibulatkan)
  "flash_sale": {
    "discounted_price": 18000,    // Harga flash sale (dibulatkan)
    "original_price": 20600,      // Menggunakan product_price (dibulatkan)
    "discount_percentage": 12.62  // (20600-18000)/20600 * 100
  },
  "final_price": 18000        // Menggunakan flash sale price (dibulatkan)
}
```

### Scenario 3: User Admin (1.00% profit margin)
```json
{
  "base_price": 20000,        // Harga modal
  "product_price": 20200,     // 20000 + (20000 * 1%) = 20200 (dibulatkan)
  "final_price": 20200        // Tidak ada flash sale
}
```

### Scenario 4: User Tidak Login (Guest - 4.50% profit margin)
```json
{
  "base_price": 20000,        // Harga modal
  "product_price": 20900,     // 20000 + (20000 * 4.5%) = 20900 (dibulatkan)
  "final_price": 20900,       // Tidak ada flash sale
  "user_role": "guest",
  "is_guest_default": true
}
```

### Scenario 5: User Terautentikasi Tanpa Role (Guest - 4.50% profit margin)
```json
{
  "base_price": 20000,        // Harga modal
  "product_price": 20900,     // 20000 + (20000 * 4.5%) = 20900 (dibulatkan)
  "final_price": 20900,       // Tidak ada flash sale
  "user_role": "guest",
  "is_guest_default": true
}
```

### Contoh Perhitungan dengan Desimal

#### Base Price: 15,750
- **Guest Role (4.50%)**: 15,750 + (15,750 × 4.50%) = 16,458.75 → **16,459** (dibulatkan)
- **Silver Role (4.00%)**: 15,750 + (15,750 × 4.00%) = 16,380.00 → **16,380** (dibulatkan)
- **Gold Role (3.00%)**: 15,750 + (15,750 × 3.00%) = 16,222.50 → **16,223** (dibulatkan)

## Keuntungan Struktur Ini

1. **Jelas**: Frontend langsung tahu harga yang harus ditampilkan (`product_price`)
2. **Transparan**: Admin bisa melihat harga modal (`base_price`) untuk perhitungan profit
3. **Fleksibel**: Mudah menambah profit margin tanpa mengubah base_price
4. **Konsisten**: Flash sale menggunakan product_price sebagai referensi
5. **User-friendly**: User tidak perlu tahu tentang perhitungan profit margin

## Implementasi di Frontend

```javascript
// Frontend hanya perlu menggunakan product_price untuk display
const displayPrice = product.product_price;

// Jika ada flash sale, gunakan final_price
const finalPrice = product.final_price;

// Untuk perbandingan harga (jika ada flash sale)
const originalPrice = product.flash_sale ? product.flash_sale.original_price : product.product_price;
const discountedPrice = product.final_price;
``` 