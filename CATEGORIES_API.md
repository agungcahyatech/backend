# Categories API Endpoints

## Overview
Endpoint untuk mengelola kategori game dengan fitur tab list di frontend. Setiap kategori berisi daftar game yang terkait beserta informasi developer.

## Endpoints

### 1. GET /api/v1/categories
Mengembalikan daftar semua kategori beserta game yang terkait untuk fitur tab list.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Top Up Game",
      "slug": "top-up-game",
      "display_order": 1,
      "is_active": true,
      "games": [
        {
          "id": 1,
          "name": "Mobile Legends",
          "slug": "mobile-legends",
          "developer": "Moonton",
          "image_thumbnail_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/thumbnails/mobile-legends.png",
          "image_banner_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/banners/mobile-legends.jpg",
          "description": "Mobile Legends is a mobile MOBA game...",
          "is_popular": true,
          "display_order": 1
        },
        {
          "id": 2,
          "name": "Free Fire",
          "slug": "free-fire",
          "developer": "Garena",
          "image_thumbnail_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/thumbnails/free-fire.png",
          "image_banner_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/banners/free-fire.jpg",
          "description": "Free Fire is a battle royale game...",
          "is_popular": false,
          "display_order": 2
        }
      ],
      "total_games": 2
    },
    {
      "id": 2,
      "name": "Voucher Game",
      "slug": "voucher-game",
      "display_order": 2,
      "is_active": true,
      "games": [
        {
          "id": 3,
          "name": "PUBG Mobile",
          "slug": "pubg-mobile",
          "developer": "PUBG Corporation",
          "image_thumbnail_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/thumbnails/pubg-mobile.png",
          "image_banner_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/banners/pubg-mobile.jpg",
          "description": "PUBG Mobile is a battle royale game...",
          "is_popular": true,
          "display_order": 1
        }
      ],
      "total_games": 1
    }
  ],
  "meta": {
    "total_categories": 2,
    "total_games": 3
  }
}
```

### 2. GET /api/v1/categories/{slug}
Mengembalikan detail kategori berdasarkan slug beserta game yang terkait.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Top Up Game",
    "slug": "top-up-game",
    "display_order": 1,
    "is_active": true,
    "games": [
      {
        "id": 1,
        "name": "Mobile Legends",
        "slug": "mobile-legends",
        "developer": "Moonton",
        "image_thumbnail_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/thumbnails/mobile-legends.png",
        "image_banner_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/banners/mobile-legends.jpg",
        "description": "Mobile Legends is a mobile MOBA game...",
        "is_popular": true,
        "display_order": 1
      },
      {
        "id": 2,
        "name": "Free Fire",
        "slug": "free-fire",
        "developer": "Garena",
        "image_thumbnail_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/thumbnails/free-fire.png",
        "image_banner_url": "https://res.cloudinary.com/your_cloud/image/upload/v1234567890/games/banners/free-fire.jpg",
        "description": "Free Fire is a battle royale game...",
        "is_popular": false,
        "display_order": 2
      }
    ],
    "total_games": 2
  },
  "meta": {
    "total_games": 2
  }
}
```

## Field Descriptions

### Category Fields
- `id`: ID unik kategori
- `name`: Nama kategori (akan ditampilkan sebagai tab)
- `slug`: Slug kategori untuk URL
- `display_order`: Urutan tampilan kategori
- `is_active`: Status aktif kategori
- `games`: Array game yang terkait dengan kategori
- `total_games`: Jumlah game dalam kategori

### Game Fields
- `id`: ID unik game
- `name`: Nama game
- `slug`: Slug game untuk URL
- `developer`: Developer/publisher game (contoh: Moonton, Garena, PUBG Corporation)
- `image_thumbnail_url`: URL thumbnail game (Cloudinary)
- `image_banner_url`: URL banner game (Cloudinary)
- `description`: Deskripsi game
- `is_popular`: Status popular game
- `display_order`: Urutan tampilan game

### Meta Fields
- `total_categories`: Jumlah total kategori
- `total_games`: Jumlah total game

## Frontend Integration untuk Tab List

### 1. **React/Vue.js Implementation**
```javascript
// Fetch categories data
const fetchCategories = async () => {
  try {
    const response = await fetch('/api/v1/categories');
    const data = await response.json();
    
    if (data.success) {
      setCategories(data.data);
      setActiveTab(data.data[0]?.slug || null);
    }
  } catch (error) {
    console.error('Error fetching categories:', error);
  }
};

// Tab component
const TabList = ({ categories, activeTab, onTabChange }) => {
  return (
    <div className="tab-container">
      {categories.map((category) => (
        <button
          key={category.slug}
          className={`tab ${activeTab === category.slug ? 'active' : ''}`}
          onClick={() => onTabChange(category.slug)}
        >
          {category.name}
          <span className="game-count">({category.total_games})</span>
        </button>
      ))}
    </div>
  );
};

// Game list component
const GameList = ({ categories, activeTab }) => {
  const activeCategory = categories.find(cat => cat.slug === activeTab);
  
  if (!activeCategory) return null;
  
  return (
    <div className="game-list">
      {activeCategory.games.map((game) => (
        <div key={game.id} className="game-card">
          <img src={game.image_thumbnail_url} alt={game.name} />
          <h3>{game.name}</h3>
          <p className="developer">{game.developer}</p>
          <p>{game.description}</p>
          {game.is_popular && <span className="popular-badge">Popular</span>}
        </div>
      ))}
    </div>
  );
};
```

### 2. **CSS Styling dengan Developer Info**
```css
.tab-container {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.tab {
  padding: 0.75rem 1.5rem;
  border: none;
  background: none;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}

.tab.active {
  border-bottom-color: #3b82f6;
  color: #3b82f6;
  font-weight: 600;
}

.game-count {
  margin-left: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.game-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.game-card {
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all 0.3s ease;
}

.game-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
}

.game-card h3 {
  margin: 0.5rem 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.developer {
  color: #6b7280;
  font-size: 0.875rem;
  margin: 0 0 0.5rem 0;
  font-style: italic;
}

.popular-badge {
  background: #f59e0b;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}
```

### 3. **Complete React Component dengan Developer**
```jsx
import React, { useState, useEffect } from 'react';

const GameCategories = () => {
  const [categories, setCategories] = useState([]);
  const [activeTab, setActiveTab] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/v1/categories');
      const data = await response.json();
      
      if (data.success) {
        setCategories(data.data);
        setActiveTab(data.data[0]?.slug || null);
      }
    } catch (error) {
      console.error('Error fetching categories:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleTabChange = (slug) => {
    setActiveTab(slug);
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <div className="game-categories">
      <h2>Game Categories</h2>
      
      <TabList 
        categories={categories}
        activeTab={activeTab}
        onTabChange={handleTabChange}
      />
      
      <GameList 
        categories={categories}
        activeTab={activeTab}
      />
    </div>
  );
};

export default GameCategories;
```

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "message": "Category not found"
}
```

### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid category slug"
}
```

## Performance Optimization

### 1. **Eager Loading**
- Menggunakan `with()` untuk eager loading game data
- Menggunakan `select()` untuk memilih field yang diperlukan saja termasuk `developer`

### 2. **Caching**
```php
// Di CategoryController
public function index()
{
    $categories = Cache::remember('categories_with_games', 3600, function () {
        return Category::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->with(['games' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('display_order', 'asc')
                      ->select('id', 'name', 'slug', 'developer', 'image_thumbnail_path', 'image_banner_path', 'description', 'is_popular', 'category_id', 'display_order');
            }])
            ->get();
    });
    
    // ... rest of the code
}
```

### 3. **Pagination (Jika Perlu)**
```php
// Untuk kategori dengan banyak game
$games = $category->games()->paginate(12);
```

## Usage Examples

### 1. **Get All Categories with Games**
```javascript
fetch('/api/v1/categories')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Categories:', data.data);
      console.log('Total categories:', data.meta.total_categories);
      console.log('Total games:', data.meta.total_games);
      
      // Access developer info
      data.data.forEach(category => {
        category.games.forEach(game => {
          console.log(`${game.name} by ${game.developer}`);
        });
      });
    }
  });
```

### 2. **Get Specific Category**
```javascript
fetch('/api/v1/categories/top-up-game')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Category:', data.data.name);
      console.log('Games:', data.data.games);
      
      // Display developer info
      data.data.games.forEach(game => {
        console.log(`${game.name} - Developer: ${game.developer}`);
      });
    }
  });
```

### 3. **Dynamic Tab Switching dengan Developer Info**
```javascript
const switchTab = (categorySlug) => {
  // Update active tab
  setActiveTab(categorySlug);
  
  // Optional: Fetch specific category data
  fetch(`/api/v1/categories/${categorySlug}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update games list for the selected category
        setGames(data.data.games);
        
        // Log developer info
        data.data.games.forEach(game => {
          console.log(`${game.name} by ${game.developer}`);
        });
      }
    });
};
```

## Developer Information Benefits

### 1. **User Trust**
- Menampilkan developer membantu user mengenali game yang familiar
- Meningkatkan kepercayaan user terhadap platform

### 2. **Game Discovery**
- User dapat mencari game berdasarkan developer favorit
- Memudahkan user menemukan game serupa dari developer yang sama

### 3. **Marketing Value**
- Developer terkenal dapat menarik lebih banyak user
- Meningkatkan nilai branding platform

### 4. **Filtering & Search**
```javascript
// Filter games by developer
const filterByDeveloper = (developer) => {
  return games.filter(game => game.developer === developer);
};

// Search games by developer
const searchByDeveloper = (searchTerm) => {
  return games.filter(game => 
    game.developer.toLowerCase().includes(searchTerm.toLowerCase())
  );
};
``` 