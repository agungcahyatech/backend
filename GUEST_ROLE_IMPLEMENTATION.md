# Implementasi Role Guest sebagai Default

## Overview

Role "guest" sekarang digunakan sebagai default untuk user yang:
1. Belum melakukan login (tidak terautentikasi)
2. Sudah login tapi tidak memiliki role yang ditugaskan

## Konfigurasi Role Guest

```json
{
  "id": 5,
  "name": "guest",
  "profit_percentage": 4.50
}
```

## Perubahan di GameController

### Logic Baru:
```php
// Get user role for pricing calculation (if authenticated)
$userRole = null;
$userProfitPercentage = 0;

if (Auth::check()) {
    $user = Auth::user();
    $userRole = $user->role;
    $userProfitPercentage = $user->getProfitPercentage();
}

// If user is not authenticated or has no role, use guest role as default
if (!$userRole) {
    $guestRole = \App\Models\Role::where('name', 'guest')->first();
    if ($guestRole) {
        $userRole = $guestRole;
        $userProfitPercentage = $guestRole->profit_percentage;
    }
}
```

## Response Structure

### Untuk User Tidak Login:
```json
{
  "user_role": {
    "id": 5,
    "name": "guest",
    "profit_percentage": 4.50
  },
  "product_price": 20900,  // base_price + 4.50%
  "pricing_info": {
    "user_authenticated": false,
    "user_role": "guest",
    "profit_percentage": 4.50,
    "price_calculation": "Base price + 4.5% profit margin",
    "is_guest_default": true
  }
}
```

### Untuk User Login Tanpa Role:
```json
{
  "user_role": {
    "id": 5,
    "name": "guest",
    "profit_percentage": 4.50
  },
  "product_price": 20900,  // base_price + 4.50%
  "pricing_info": {
    "user_authenticated": true,
    "user_role": "guest",
    "profit_percentage": 4.50,
    "price_calculation": "Base price + 4.5% profit margin",
    "is_guest_default": true
  }
}
```

## Keuntungan Implementasi Ini

1. **Konsistensi**: Semua user (login atau tidak) akan mendapatkan harga yang konsisten
2. **Profit Margin**: Guest role memberikan profit margin 4.50% untuk semua transaksi
3. **Transparansi**: Frontend bisa mengetahui apakah user menggunakan role default atau role asli
4. **Fleksibilitas**: Admin bisa mengatur profit margin untuk guest role sesuai kebutuhan

## Field Baru di Response

- `is_guest_default`: Boolean yang menunjukkan apakah user menggunakan role guest sebagai default
- `user_role`: Selalu terisi (tidak null) karena menggunakan guest sebagai fallback

## Contoh Perhitungan

### Base Price: 20,000
- **Guest Role (4.50%)**: 20,000 + (20,000 × 4.50%) = 20,900 → **20,900** (dibulatkan)
- **Silver Role (4.00%)**: 20,000 + (20,000 × 4.00%) = 20,800 → **20,800** (dibulatkan)
- **Gold Role (3.00%)**: 20,000 + (20,000 × 3.00%) = 20,600 → **20,600** (dibulatkan)
- **Pro Role (2.00%)**: 20,000 + (20,000 × 2.00%) = 20,400 → **20,400** (dibulatkan)
- **Admin Role (1.00%)**: 20,000 + (20,000 × 1.00%) = 20,200 → **20,200** (dibulatkan)

### Base Price: 15,750 (dengan desimal)
- **Guest Role (4.50%)**: 15,750 + (15,750 × 4.50%) = 16,458.75 → **16,459** (dibulatkan)
- **Silver Role (4.00%)**: 15,750 + (15,750 × 4.00%) = 16,380.00 → **16,380** (dibulatkan)
- **Gold Role (3.00%)**: 15,750 + (15,750 × 3.00%) = 16,222.50 → **16,223** (dibulatkan)

## Implementasi Frontend

```javascript
// Frontend bisa menampilkan informasi role
const userRole = response.data.pricing_info.user_role;
const isGuestDefault = response.data.pricing_info.is_guest_default;

if (isGuestDefault) {
    console.log(`User menggunakan role ${userRole} sebagai default`);
} else {
    console.log(`User menggunakan role ${userRole} yang ditugaskan`);
}

// Harga yang ditampilkan
const displayPrice = product.product_price;
``` 