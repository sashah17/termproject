-- ============================================================
-- VoltMarket – Electronics-Only Database Schema & Sample Data
-- ============================================================
CREATE DATABASE IF NOT EXISTS voltmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voltmarket;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(200) NOT NULL, description TEXT,
  price DECIMAL(10,2) NOT NULL, image_url VARCHAR(500) DEFAULT NULL,
  category VARCHAR(80) NOT NULL, `condition` VARCHAR(50) NOT NULL DEFAULT 'Good',
  stock INT NOT NULL DEFAULT 1, seller_id INT NOT NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_cart (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, total_price DECIMAL(10,2) NOT NULL,
  order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, status VARCHAR(50) NOT NULL DEFAULT 'Pending',
  ship_name VARCHAR(150), ship_email VARCHAR(150), ship_address TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, product_id INT NOT NULL,
  quantity INT NOT NULL, price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY, product_id INT NOT NULL, user_id INT NOT NULL,
  rating TINYINT NOT NULL, comment TEXT, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_review (product_id, user_id),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users (passwords set by setup.php)
INSERT INTO users (name, email, password, is_admin) VALUES
('Admin User',   'admin@voltmarket.com', 'SETUP_PENDING', 1),
('TechSeller',   'seller@voltmarket.com','SETUP_PENDING', 0),
('John Doe',     'john@example.com',     'SETUP_PENDING', 0);

-- Electronics Products
INSERT INTO products (name, description, price, image_url, category, `condition`, stock, seller_id) VALUES

-- Phones & Tablets
('iPhone 15 Pro – 256GB Natural Titanium',
 'Apple iPhone 15 Pro in Natural Titanium. 256GB storage. A17 Pro chip. ProMotion 120Hz display. USB-C connector. Battery health at 97%. Original box, cable and documentation included. Minor fingerprint marks on titanium frame, screen is flawless.',
 879.00,'https://images.unsplash.com/photo-1697567782252-b9f5de68ae48?w=600&q=80','Phones & Tablets','Like New',1,2),

('Samsung Galaxy S24 Ultra – 512GB',
 'Galaxy S24 Ultra in Titanium Black. 512GB. 200MP camera. Integrated S Pen. Exynos 2400 chip. Excellent condition — used with case and screen protector since day one. Both included. Battery 100%.',
 799.00,'https://images.unsplash.com/photo-1707343843437-caacff5cfa74?w=600&q=80','Phones & Tablets','Good',1,2),

('iPad Pro 12.9" M2 – 256GB WiFi',
 'iPad Pro 12.9-inch with M2 chip, 256GB, Space Grey. Mini-LED Liquid Retina XDR display. Apple Pencil 2nd gen compatible. Light scratches on back. Screen perfect. Smart Folio case included.',
 749.00,'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&q=80','Phones & Tablets','Good',1,2),

('Google Pixel 8 Pro – 128GB',
 'Pixel 8 Pro in Obsidian. 128GB. Google Tensor G3 chip. 50MP main camera with 5x telephoto. 7 years of OS updates. Purchased 6 months ago, used with a case. No scratches. Original box.',
 549.00,'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&q=80','Phones & Tablets','Like New',2,2),

-- Laptops & PCs
('MacBook Air 15" M3 – 16GB/512GB',
 'MacBook Air 15-inch with M3 chip. 16GB unified memory, 512GB SSD. Midnight colour. 18-hour battery life. Purchased 4 months ago for university. No scratches or dents. Charger included.',
 1149.00,'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&q=80','Laptops & PCs','Like New',1,2),

('Dell XPS 15 9530 – i9/32GB/1TB',
 'Dell XPS 15 with Intel Core i9-13900H, 32GB DDR5, 1TB NVMe SSD, Nvidia RTX 4060. 3.5K OLED display. Perfect for creative work and development. Some keyboard wear, display flawless.',
 1299.00,'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&q=80','Laptops & PCs','Good',1,2),

('Lenovo ThinkPad X1 Carbon Gen 11',
 'ThinkPad X1 Carbon 11th Gen, Intel Core i7-1365U, 16GB LPDDR5, 512GB SSD. 14" 2.8K OLED display. Business-class reliability. Light corporate use. Cleaned and ready. Charger and sleeve included.',
 899.00,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&q=80','Laptops & PCs','Good',1,2),

-- Audio
('Sony WH-1000XM5 Wireless Headphones',
 'Sony WH-1000XM5 in Midnight Black. Industry-leading noise cancellation. 30-hour battery. LDAC Hi-Res Audio. Purchased 8 months ago. Ear cushions like new. Carrying case and cables included.',
 249.00,'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=600&q=80','Audio','Like New',2,2),

('Apple AirPods Pro 2nd Gen (USB-C)',
 'AirPods Pro 2nd gen with USB-C charging case. Active noise cancellation and Transparency mode. Adaptive Audio. H2 chip. Used 3 months. Both ear tips sizes included. Pristine condition.',
 179.00,'https://images.unsplash.com/photo-1606741965326-cb990ae01bb2?w=600&q=80','Audio','Like New',1,2),

('Sonos Era 300 Spatial Audio Speaker',
 'Sonos Era 300 in Black. Dolby Atmos spatial audio with 6 drivers. WiFi 6 and Bluetooth 5.0. Works perfectly, selling because upgrading to a soundbar. Power cable included.',
 299.00,'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&q=80','Audio','Good',1,2),

-- Gaming
('PlayStation 5 Slim – Disc Edition',
 'PS5 Slim disc edition in white. Barely used — purchased 5 months ago. DualSense controller in excellent condition. All cables included. No scratches. 1 game included (Spiderman 2).',
 449.00,'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=600&q=80','Gaming','Like New',1,2),

('Nintendo Switch OLED – White',
 'Nintendo Switch OLED model in white. 7" vibrant OLED screen. Wide adjustable stand. 64GB internal storage. Dock, Joy-Cons, and all accessories included. Light use, no dead pixels.',
 289.00,'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=600&q=80','Gaming','Good',1,2),

('Nvidia RTX 4070 Super 12GB',
 'MSI Gaming X Slim RTX 4070 Super 12GB GDDR6X. Used 6 months for gaming. Runs cool and quiet. GPU-Z screenshots available. Never overclocked. Original box and accessories.',
 499.00,'https://images.unsplash.com/photo-1591488320449-011701bb6704?w=600&q=80','Gaming','Good',1,2),

-- Cameras
('Sony A7 IV Full-Frame Mirrorless',
 'Sony Alpha A7 IV body only. 33MP full-frame BSI CMOS. 4K 60p video. 759-point AF system. Under 8,000 shutter count. Sensor clean, no scratches. Battery, charger, strap, and box included.',
 2099.00,'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&q=80','Cameras','Like New',1,2),

('GoPro Hero 12 Black',
 'GoPro Hero 12 Black. 5.3K video, HyperSmooth 6.0 stabilisation. Waterproof to 10m. Includes 2 batteries, charging cable, adhesive mounts, and carrying case. Used for 3 hikes.',
 279.00,'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=600&q=80','Cameras','Good',2,2),

-- Wearables
('Apple Watch Series 9 – 45mm GPS',
 'Apple Watch Series 9 in Midnight Aluminium with Black Sport Band. 45mm GPS. S9 chip. Blood oxygen and ECG. Battery health 99%. Original box and extra bands included. No scratches.',
 319.00,'https://images.unsplash.com/photo-1434494878577-86c23bcb06b9?w=600&q=80','Wearables','Like New',1,2),

-- Components & Parts
('Samsung 990 Pro 2TB NVMe SSD',
 'Samsung 990 Pro 2TB PCIe 4.0 NVMe SSD. Up to 7,450 MB/s read speed. Used for 4 months, 22TB total host writes. Healthy SMART data. Upgrading to larger drive. Heatsink included.',
 149.00,'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=600&q=80','Components & Parts','Good',1,2),

('AMD Ryzen 9 7900X Processor',
 '12-core/24-thread desktop CPU for AM5 socket. Up to 5.6GHz boost. Stock cooler not included. Ran at stock speeds, never delidded. Tested and verified. Upgrading to X3D variant.',
 279.00,'https://images.unsplash.com/photo-1555617981-dac3772ef4b0?w=600&q=80','Components & Parts','Good',1,2);
