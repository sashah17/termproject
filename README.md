# VoltMarket – Electronics Marketplace

> **Web IT Term Project** | Full-Stack PHP + MySQL

---

## Description

VoltMarket is a second-hand electronics marketplace where users can buy and sell pre-owned tech gear including phones, laptops, audio equipment, gaming devices, cameras, wearables, and components.

**Design Theme:** Dark industrial-tech aesthetic — near-black background, electric teal (`#00e5a0`) accents, Space Mono + Syne fonts.

---

## Technologies

| Technology | Purpose |
|---|---|
| HTML5 | Structure and semantic markup |
| CSS3 (custom) | Dark tech design, CSS variables, animations |
| Bootstrap Icons | Icon library |
| JavaScript (ES6) | Client-side validation and interactivity |
| PHP 8+ | Server-side logic |
| MySQL | Database backend |
| PDO | Secure database access |
| XAMPP / LAMP | Local development environment |

---

## Features

### User Features
- ✅ Register, login, logout
- ✅ Browse electronics with search, category, condition & price filters
- ✅ Product detail with add-to-cart and reviews
- ✅ Shopping cart with quantity update
- ✅ Checkout with shipping info
- ✅ Order history with status tracking
- ✅ List items for sale
- ✅ Edit and delete own listings
- ✅ Star ratings & reviews (bonus)

### Admin Features
- ✅ Dashboard with stats (products, users, orders, revenue)
- ✅ Full product CRUD
- ✅ Order status management
- ✅ User role management

### Security
- ✅ `password_hash()` bcrypt
- ✅ PDO prepared statements (no SQL injection)
- ✅ Client-side + server-side validation
- ✅ Session fixation prevention
- ✅ `htmlspecialchars()` on all output

### Electronics Categories
- 📱 Phones & Tablets
- 💻 Laptops & PCs
- 🎧 Audio
- 🎮 Gaming
- 📷 Cameras
- ⌚ Wearables
- 🔧 Components & Parts

---

## Setup Instructions

### Step 1 — Copy Files
Place the `voltmarket/` folder in your web root:
- **Windows XAMPP:** `C:\xampp\htdocs\voltmarket\`
- **Mac XAMPP:** `/Applications/XAMPP/htdocs/voltmarket/`

### Step 2 — Import Database
1. Go to `http://localhost/phpmyadmin`
2. Click **Import** → choose `marketplace.sql` → **Go**

### Step 3 — Configure DB (if needed)
Edit `includes/db.php` — update `DB_USER` and `DB_PASS` if your MySQL credentials differ from the defaults.

### Step 4 — Set Passwords
Visit: `http://localhost/voltmarket/setup.php`

### Step 5 — Launch
Visit: `http://localhost/voltmarket/`

---

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@voltmarket.com | Admin1234! |
| Seller | seller@voltmarket.com | Seller123! |
| User | john@example.com | Password1! |

---

## Student Information
- **Project:** VoltMarket — Electronics Marketplace
- **Course:** Web IT Term Project
- **Stack:** HTML5, CSS3, JavaScript, PHP 8, MySQL
