# API JSON Response Examples

## ✅ Semua API Sudah Berbentuk JSON

Semua endpoint API di aplikasi ini sudah menggunakan format JSON response. Berikut adalah contoh response dari setiap endpoint:

## 1. **Payment Methods API** - `/api/v1/payment-methods`

### Request:
```bash
GET /api/v1/payment-methods
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "QRIS",
      "image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/qris-logo.jpg",
      "image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/qris-logo.jpg",
      "provider": "tokopay",
      "code": "QRIS",
      "group": "QRIS",
      "type": "qris",
      "is_active": true,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    },
    {
      "id": 2,
      "name": "BCA Virtual Account",
      "image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/bca-va-logo.png",
      "image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/payment-methods/bca-va-logo.png",
      "provider": "midtrans",
      "code": "BCA_VA",
      "group": "Bank Transfer",
      "type": "bank_transfer",
      "is_active": true,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 2. **Social Media Settings API** - `/api/v1/settings/social-media`

### Request:
```bash
GET /api/v1/settings/social-media
```

### Response:
```json
{
  "success": true,
  "message": "Social media settings retrieved successfully",
  "data": {
    "social_media": {
      "instagram_url": "https://instagram.com/yourcompany",
      "facebook_url": "https://facebook.com/yourcompany",
      "youtube_url": "https://youtube.com/yourcompany",
      "whatsapp_number": "+6281234567890"
    }
  }
}
```

## 3. **General Settings API** - `/api/v1/settings`

### Request:
```bash
GET /api/v1/settings
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key": "site_name",
      "value": "Your Game Store",
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    },
    {
      "id": 2,
      "key": "site_description",
      "value": "Best gaming platform in Indonesia",
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    },
    {
      "id": 3,
      "key": "contact_email",
      "value": "support@yourcompany.com",
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 4. **Games List API** - `/api/v1/games`

### Request:
```bash
GET /api/v1/games
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Mobile Legends",
      "slug": "mobile-legends",
      "developer": "Moonton",
      "description": "5v5 MOBA game",
      "image_thumbnail_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/games/mobile-legends-thumb.jpg",
      "image_banner_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/games/mobile-legends-banner.jpg",
      "is_popular": true,
      "display_order": 1,
      "is_active": true,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z",
      "product_categories": [
        {
          "id": 1,
          "name": "Diamonds",
          "slug": "diamonds",
          "icon_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/product-categories/diamonds-icon.png",
          "icon_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/product-categories/diamonds-icon.png",
          "display_order": 1,
          "is_active": true,
          "products": [
            {
              "id": 1,
              "name": "86 Diamonds",
              "description": "86 Diamonds for Mobile Legends",
              "base_price": 20000,
              "product_price": 20800,
              "user_role": {
                "id": 2,
                "name": "guest",
                "profit_percentage": 4.5
              },
              "flash_sale": null,
              "final_price": 20800,
              "icon_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/products/86-diamonds.png",
              "provider": "xendit",
              "provider_sku": "ML_86_DIAMONDS",
              "display_order": 1
            }
          ]
        }
      ]
    }
  ]
}
```

## 5. **Game Products API** - `/api/v1/games/{slug}/products`

### Request:
```bash
GET /api/v1/games/mobile-legends/products
```

### Response:
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
        "name": "Diamonds",
        "slug": "diamonds",
        "icon_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/product-categories/diamonds-icon.png",
        "icon_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/product-categories/diamonds-icon.png",
        "display_order": 1,
        "products": [
          {
            "id": 1,
            "name": "86 Diamonds",
            "description": "86 Diamonds for Mobile Legends",
            "base_price": 20000,
            "product_price": 20800,
            "user_role": {
              "id": 2,
              "name": "guest",
              "profit_percentage": 4.5
            },
            "flash_sale": {
              "id": 1,
              "name": "Flash Sale Diamonds",
              "discounted_price": 18720,
              "original_price": 20800,
              "discount_percentage": 10.0,
              "stock": 50,
              "start_date": "2025-01-13T00:00:00.000000Z",
              "end_date": "2025-01-14T23:59:59.000000Z",
              "is_active": true
            },
            "final_price": 18720,
            "icon_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/products/86-diamonds.png",
            "provider": "xendit",
            "provider_sku": "ML_86_DIAMONDS",
            "display_order": 1
          }
        ]
      }
    ],
    "total_products": 1,
    "pricing_info": {
      "user_authenticated": false,
      "user_role": "guest",
      "profit_percentage": 4.5,
      "price_calculation": "Base price + 4.5% profit margin",
      "is_guest_default": true
    }
  }
}
```

## 6. **Categories API** - `/api/v1/categories`

### Request:
```bash
GET /api/v1/categories
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "MOBA Games",
      "slug": "moba-games",
      "description": "Multiplayer Online Battle Arena games",
      "icon_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/categories/moba-icon.png",
      "icon_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/categories/moba-icon.png",
      "is_active": true,
      "display_order": 1,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 7. **Flash Sales API** - `/api/v1/flash-sales`

### Request:
```bash
GET /api/v1/flash-sales
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Flash Sale Diamonds",
      "description": "Special discount on all diamond packages",
      "start_date": "2025-01-13T00:00:00.000000Z",
      "end_date": "2025-01-14T23:59:59.000000Z",
      "is_active": true,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z",
      "products": [
        {
          "id": 1,
          "name": "86 Diamonds",
          "pivot": {
            "discounted_price": 18720,
            "stock": 50
          }
        }
      ]
    }
  ]
}
```

## 8. **Articles API** - `/api/v1/articles`

### Request:
```bash
GET /api/v1/articles
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "How to Play Mobile Legends",
      "slug": "how-to-play-mobile-legends",
      "content": "Complete guide for beginners...",
      "excerpt": "Learn the basics of Mobile Legends...",
      "featured_image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/articles/mobile-legends-guide.jpg",
      "featured_image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/articles/mobile-legends-guide.jpg",
      "is_published": true,
      "published_at": "2025-01-13T10:30:00.000000Z",
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 9. **Sliders API** - `/api/v1/sliders`

### Request:
```bash
GET /api/v1/sliders
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Welcome to Our Game Store",
      "subtitle": "Best gaming platform in Indonesia",
      "description": "Get the best deals on your favorite games",
      "image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/sliders/welcome-banner.jpg",
      "image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/sliders/welcome-banner.jpg",
      "button_text": "Shop Now",
      "button_url": "/games",
      "is_active": true,
      "display_order": 1,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 10. **Pages API** - `/api/v1/pages`

### Request:
```bash
GET /api/v1/pages
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "About Us",
      "slug": "about-us",
      "content": "We are the leading gaming platform...",
      "meta_title": "About Us - Your Game Store",
      "meta_description": "Learn more about our gaming platform",
      "is_published": true,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 11. **Popups API** - `/api/v1/popups`

### Request:
```bash
GET /api/v1/popups
```

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Special Offer!",
      "content": "Get 20% off on your first purchase",
      "image_path": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/popups/special-offer.jpg",
      "image_url": "https://res.cloudinary.com/your-cloud/image/upload/v1234567890/popups/special-offer.jpg",
      "button_text": "Claim Now",
      "button_url": "/offers",
      "is_active": true,
      "display_order": 1,
      "created_at": "2025-01-13T10:30:00.000000Z",
      "updated_at": "2025-01-13T10:30:00.000000Z"
    }
  ]
}
```

## 📋 **Kesimpulan**

✅ **Semua API sudah berbentuk JSON** dengan struktur yang konsisten:

### **Struktur Response Standar:**
```json
{
  "success": true,
  "message": "Optional message",
  "data": {
    // Response data here
  }
}
```

### **Fitur JSON Response:**
- ✅ **Consistent Structure**: Semua response menggunakan format yang sama
- ✅ **Success Flag**: Field `success` untuk indikator status
- ✅ **Message Field**: Optional message untuk informasi tambahan
- ✅ **Data Field**: Berisi data utama response
- ✅ **Proper Headers**: Content-Type: application/json
- ✅ **Error Handling**: Error response juga dalam format JSON

### **Content-Type Headers:**
Semua response otomatis menggunakan header:
```
Content-Type: application/json
```

### **Error Response Example:**
```json
{
  "success": false,
  "message": "Game not found",
  "data": null
}
```

**Status: ✅ SEMUA API SUDAH BERBENTUK JSON** 