# Games Products API Documentation

## Endpoint: `GET /api/v1/games/{slug}/products`

Endpoint ini menampilkan produk-produk dari game tertentu dengan informasi harga yang lengkap termasuk role-based pricing dan flash sale.

### Parameters

- `slug` (required): Slug dari game yang ingin ditampilkan produknya

### Response Structure

```json
{
  "success": true,
  "data": {
    "game": {
      "id": 1,
      "name": "Mobile Legends",
      "slug": "mobile-legends",
      "developer": "Moonton"
    },
    "product_categories": [
      {
        "id": 1,
        "name": "Diamond",
        "slug": "diamond",
        "icon_url": "https://example.com/diamond-icon.png",
        "display_order": 1,
        "products": [
          {
            "id": 1,
            "name": "86 💎 Diamond",
            "description": "86 Diamond Mobile Legends",
            "base_price": 20000,
            "product_price": 21000,
            "user_role": {
              "id": 3,
              "name": "guest",
              "profit_percentage": 4.50
            },
            "flash_sale": {
              "id": 1,
              "name": "Flash Sale Diamond",
              "discounted_price": 18000,
              "original_price": 21000,
              "discount_percentage": 14.29,
              "stock": 50,
              "start_date": "2025-01-01T00:00:00.000000Z",
              "end_date": "2025-01-31T23:59:59.000000Z",
              "is_active": true
            },
            "final_price": 18000,
            "icon_url": "https://example.com/86-diamond.png",
            "provider": "digiflazz",
            "provider_sku": "ML86",
            "display_order": 1
          }
        ]
      }
    ],
    "total_products": 1,
    "pricing_info": {
      "user_authenticated": false,
      "user_role": "guest",
      "profit_percentage": 4.50,
      "price_calculation": "Base price + 4.5% profit margin",
      "is_guest_default": true
    }
  }
}
```

### Field Explanations

#### Product Fields

- `base_price`: Harga modal/dasar produk (harga asli tanpa profit margin)
- `product_price`: Harga yang ditampilkan ke frontend (sudah dihitung berdasarkan role + profit margin)
- `user_role`: Informasi role user (jika terautentikasi)
  - `id`: ID role
  - `name`: Nama role (admin, silver, gold, pro)
  - `profit_percentage`: Persentase profit margin
- `flash_sale`: Informasi flash sale aktif (jika ada)
  - `id`: ID flash sale
  - `name`: Nama flash sale
  - `discounted_price`: Harga setelah diskon
  - `original_price`: Harga sebelum diskon (menggunakan product_price)
  - `discount_percentage`: Persentase diskon
  - `stock`: Stok tersedia
  - `start_date`: Tanggal mulai flash sale
  - `end_date`: Tanggal berakhir flash sale
  - `is_active`: Status aktif flash sale
- `final_price`: Harga final yang harus dibayar (flash sale price atau product_price)

#### Pricing Info

- `user_authenticated`: Apakah user sudah login
- `user_role`: Nama role user (jika terautentikasi)
- `profit_percentage`: Persentase profit margin role
- `price_calculation`: Penjelasan perhitungan harga

### Role-Based Pricing

Sistem mendukung role-based pricing dengan profit margin:

- **Admin**: 1.00% profit margin (product_price = base_price + 1%)
- **Silver**: 4.00% profit margin (product_price = base_price + 4%)
- **Gold**: 3.00% profit margin (product_price = base_price + 3%)
- **Pro**: 2.00% profit margin (product_price = base_price + 2%)
- **Guest**: 4.50% profit margin (product_price = base_price + 4.5%) - **Default untuk user tidak login**

### Flash Sale Integration

Produk yang sedang dalam flash sale akan menampilkan:

1. Informasi flash sale lengkap
2. Harga diskon sebagai `final_price`
3. Persentase diskon (dihitung dari product_price)
4. Stok tersedia
5. Periode flash sale

### Authentication

- **Tidak terautentikasi**: 
  - `base_price` = harga modal
  - `product_price` = base_price + 4.50% (menggunakan role guest)
  - `final_price` = product_price atau flash sale price
  - `user_role` = "guest"
  - `is_guest_default` = true
- **Terdautentikasi tanpa role**: 
  - `base_price` = harga modal
  - `product_price` = base_price + 4.50% (menggunakan role guest)
  - `final_price` = product_price atau flash sale price
  - `user_role` = "guest"
  - `is_guest_default` = true
- **Terdautentikasi dengan role**: 
  - `base_price` = harga modal
  - `product_price` = base_price + profit margin berdasarkan role
  - `final_price` = product_price atau flash sale price
  - `user_role` = nama role user
  - `is_guest_default` = false

### Example Usage

```bash
# Get products for Mobile Legends game
GET /api/v1/games/mobile-legends/products

# Response will include role-based pricing and flash sale info
```

### Error Responses

```json
{
  "success": false,
  "message": "Game not found"
}
```

### Notes

1. Flash sale hanya menampilkan yang sedang aktif (start_date <= now <= end_date)
2. Role-based pricing hanya berlaku untuk user yang terautentikasi
3. Final price mengutamakan flash sale price jika ada, jika tidak menggunakan product_price
4. Semua harga dalam format integer (sen/cent) dan sudah dibulatkan
5. `base_price` adalah harga modal yang tidak ditampilkan ke user
6. `product_price` adalah harga yang ditampilkan ke frontend (sudah termasuk profit margin dan dibulatkan)
7. `final_price` adalah harga akhir yang harus dibayar (sudah dibulatkan) 