# FlashSale API Endpoints

## Overview
Endpoint untuk mengelola flash sale yang sedang berlangsung dan yang akan datang.

## Endpoints

### 1. GET /api/v1/flash-sales
Mengembalikan daftar flash sale yang sedang berlangsung (active).

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Flash Sale Mobile Legends",
      "start_date": "2025-01-01T10:00:00.000000Z",
      "end_date": "2025-01-01T18:00:00.000000Z",
      "remaining_seconds": 28800,
      "is_active": true,
      "products": [
        {
          "id": 1,
          "name": "86 Diamonds",
          "description": "86 Diamonds for Mobile Legends",
          "base_price": 20000,
          "discounted_price": 15000,
          "discount_percentage": 25,
          "stock": 100,
          "icon_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/products/86-diamonds.png",
          "provider": "digiflazz",
          "provider_sku": "ML86",
          "game": {
            "id": 1,
            "name": "Mobile Legends",
            "slug": "mobile-legends"
          },
          "product_category": {
            "id": 1,
            "name": "Diamonds",
            "slug": "diamonds"
          }
        }
      ],
      "total_products": 1
    }
  ],
  "meta": {
    "current_time": "2025-01-01T10:00:00.000000Z",
    "total_active_flashsales": 1
  }
}
```

### 2. GET /api/v1/flash-sales/{id}
Mengembalikan detail flash sale berdasarkan ID.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Flash Sale Mobile Legends",
    "start_date": "2025-01-01T10:00:00.000000Z",
    "end_date": "2025-01-01T18:00:00.000000Z",
    "remaining_seconds": 28800,
    "is_active": true,
    "products": [
      {
        "id": 1,
        "name": "86 Diamonds",
        "description": "86 Diamonds for Mobile Legends",
        "base_price": 20000,
        "discounted_price": 15000,
        "discount_percentage": 25,
        "stock": 100,
        "icon_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/products/86-diamonds.png",
        "provider": "digiflazz",
        "provider_sku": "ML86",
        "game": {
          "id": 1,
          "name": "Mobile Legends",
          "slug": "mobile-legends"
        },
        "product_category": {
          "id": 1,
          "name": "Diamonds",
          "slug": "diamonds"
        }
      }
    ],
    "total_products": 1
  },
  "meta": {
    "current_time": "2025-01-01T10:00:00.000000Z"
  }
}
```

### 3. GET /api/v1/flash-sales/upcoming
Mengembalikan daftar flash sale yang akan datang.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Upcoming Flash Sale Free Fire",
      "start_date": "2025-01-02T10:00:00.000000Z",
      "end_date": "2025-01-02T18:00:00.000000Z",
      "time_until_start": 86400,
      "is_active": true,
      "products": [
        {
          "id": 2,
          "name": "100 Diamonds",
          "description": "100 Diamonds for Free Fire",
          "base_price": 25000,
          "discounted_price": 20000,
          "discount_percentage": 20,
          "stock": 50,
          "icon_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/products/100-diamonds.png",
          "provider": "digiflazz",
          "provider_sku": "FF100",
          "game": {
            "id": 2,
            "name": "Free Fire",
            "slug": "free-fire"
          },
          "product_category": {
            "id": 1,
            "name": "Diamonds",
            "slug": "diamonds"
          }
        }
      ],
      "total_products": 1
    }
  ],
  "meta": {
    "current_time": "2025-01-01T10:00:00.000000Z",
    "total_upcoming_flashsales": 1
  }
}
```

## Field Descriptions

### FlashSale Fields
- `id`: ID unik flash sale
- `name`: Nama flash sale
- `start_date`: Waktu mulai flash sale (ISO 8601 format)
- `end_date`: Waktu berakhir flash sale (ISO 8601 format)
- `remaining_seconds`: Sisa waktu dalam detik (hanya untuk flash sale aktif)
- `time_until_start`: Waktu sampai mulai dalam detik (hanya untuk upcoming)
- `is_active`: Status aktif flash sale
- `products`: Array produk yang ada di flash sale
- `total_products`: Jumlah total produk dalam flash sale

### Product Fields
- `id`: ID unik produk
- `name`: Nama produk
- `description`: Deskripsi produk
- `base_price`: Harga asli produk
- `discounted_price`: Harga setelah diskon
- `discount_percentage`: Persentase diskon
- `stock`: Stok tersedia
- `icon_url`: URL icon produk
- `provider`: Provider produk (digiflazz, apigames, dll)
- `provider_sku`: SKU dari provider
- `game`: Informasi game terkait
- `product_category`: Informasi kategori produk

### Meta Fields
- `current_time`: Waktu saat ini (ISO 8601 format)
- `total_active_flashsales`: Jumlah flash sale aktif
- `total_upcoming_flashsales`: Jumlah flash sale yang akan datang

## Filtering Logic

### Active Flash Sales
- `is_active = true`
- `start_date <= current_time`
- `end_date >= current_time`
- Diurutkan berdasarkan `end_date` (ascending)

### Upcoming Flash Sales
- `is_active = true`
- `start_date > current_time`
- Diurutkan berdasarkan `start_date` (ascending)

## Time Calculations

### Remaining Time (Active Flash Sales)
```php
$remainingTime = $now->diffInSeconds($endDate, false);
```

### Time Until Start (Upcoming Flash Sales)
```php
$timeUntilStart = $now->diffInSeconds($startDate, false);
```

## Discount Calculation

```php
$discountPercentage = round((($basePrice - $discountedPrice) / $basePrice) * 100);
```

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "message": "Flash sale not found"
}
```

### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid flash sale ID"
}
```

## Usage Examples

### Frontend Integration
```javascript
// Get active flash sales
fetch('/api/v1/flash-sales')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(flashSale => {
      console.log(`Flash Sale: ${flashSale.name}`);
      console.log(`Remaining time: ${flashSale.remaining_seconds} seconds`);
      console.log(`Products: ${flashSale.total_products}`);
    });
  });

// Get specific flash sale
fetch('/api/v1/flash-sales/1')
  .then(response => response.json())
  .then(data => {
    const flashSale = data.data;
    console.log(`Flash Sale: ${flashSale.name}`);
    flashSale.products.forEach(product => {
      console.log(`Product: ${product.name} - ${product.discount_percentage}% off`);
    });
  });

// Get upcoming flash sales
fetch('/api/v1/flash-sales/upcoming')
  .then(response => response.json())
  .then(data => {
    data.data.forEach(flashSale => {
      console.log(`Upcoming: ${flashSale.name}`);
      console.log(`Starts in: ${flashSale.time_until_start} seconds`);
    });
  });
``` 