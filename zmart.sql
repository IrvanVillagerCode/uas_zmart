-- Z-MART Database Dump
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- --------------------------------------------------------
-- Table structure for `cart`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `cart`

-- --------------------------------------------------------
-- Table structure for `chat_messages`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `chat_messages`;
CREATE TABLE `chat_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chat_room_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chat_room_id` (`chat_room_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_rooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `chat_messages`

-- --------------------------------------------------------
-- Table structure for `chat_rooms`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `chat_rooms`;
CREATE TABLE `chat_rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `closed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `chat_rooms_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_rooms_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `chat_rooms`

INSERT INTO `chat_rooms` (`id`, `customer_id`, `admin_id`, `status`, `created_at`, `updated_at`, `closed_at`) VALUES
('1', '3', NULL, 'open', '2026-06-14 16:21:20', '2026-06-14 16:21:20', NULL);

-- --------------------------------------------------------
-- Table structure for `migrations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for `migrations`

-- --------------------------------------------------------
-- Table structure for `order_items`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `order_items`

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `subtotal`, `created_at`) VALUES
('1', '1', '1', 'Kaos Polos Cotton Combed 30s', '1', '45000.00', '45000.00', '2026-06-20 06:37:14'),
('2', '2', '6', 'Celana Cargo Corduroy Brown', '1', '175000.00', '175000.00', '2026-06-20 06:55:09'),
('3', '3', '1', 'Kaos Polos Cotton Combed 30s', '3', '45000.00', '135000.00', '2026-06-20 07:14:48'),
('4', '4', '3', 'Kemeja Flanel Slim Fit Red-Black', '10', '120000.00', '1200000.00', '2026-06-20 10:24:02'),
('5', '5', '2', 'Jaket Denim Klasik Indigo', '3', '185000.00', '555000.00', '2026-06-20 20:32:15'),
('6', '6', '23', 'Pelembab Wajah SPF 30 50ml', '1', '45000.00', '45000.00', '2026-06-21 08:08:40'),
('7', '6', '14', 'Susu UHT Full Cream 1L', '23', '18500.00', '425500.00', '2026-06-21 08:08:40');

-- --------------------------------------------------------
-- Table structure for `orders`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `shipping_proof_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `orders`

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `payment_method`, `shipping_address`, `notes`, `shipping_proof_path`, `created_at`, `updated_at`) VALUES
('1', '2', 'ORD-178191223417', '45000.00', 'pending', 'Transfer Bank', 'Jl. Melati No. 12, Kebayoran Baru, Jakarta Selatan, 12130', NULL, NULL, '2026-06-19 23:37:14', '2026-06-19 23:37:14'),
('2', '1', 'ORD-178191330917', '175000.00', 'pending', 'Transfer Bank', 'jalan malaka 3 HB no 12 J', NULL, NULL, '2026-06-19 23:55:09', '2026-06-19 23:55:09'),
('3', '3', 'ORD-178191448872', '135000.00', 'pending', 'Transfer Bank', 'Jl. Raya Testing No. 123, Jakarta Selatan', NULL, NULL, '2026-06-20 00:14:48', '2026-06-20 00:14:48'),
('4', '6', 'ORD-178192584274', '1200000.00', 'success', 'Transfer Bank', 'jalan bekasi rt 12 rw 6', NULL, NULL, '2026-06-20 03:24:02', '2026-06-20 03:27:55'),
('5', '5', 'ORD-178196233540', '555000.00', 'pending', 'Transfer Bank', 'jln babelan rt01', NULL, NULL, '2026-06-20 13:32:15', '2026-06-20 13:32:15'),
('6', '3', 'ORD-178200412058', '470500.00', 'pending', 'Transfer Bank', 'jlaman malaka 3 hb no 12 j', NULL, NULL, '2026-06-21 01:08:40', '2026-06-21 01:08:40');

-- --------------------------------------------------------
-- Table structure for `products`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `products`

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image`, `external_link`, `category`, `is_active`, `created_at`, `updated_at`) VALUES
('1', 'Kaos Polos Cotton Combed 30s', 'Bahan katun premium Combed 30s yang sangat lembut, adem, dan menyerap keringat. Cocok untuk bersantai sehari-hari.', '45000.00', '120', 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80', '', 'kaos', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('2', 'Jaket Denim Klasik Indigo', 'Jaket denim berkualitas tinggi dengan jahitan kuat dan detail washed retro yang modis.', '185000.00', '45', 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600&auto=format&fit=crop&q=80', '', 'jaket', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('3', 'Kemeja Flanel Slim Fit Red-Black', 'Kemeja flanel lengan panjang dengan motif kotak-kotak klasik. Sangat cocok dipadukan dengan kaos polos.', '120000.00', '60', 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&auto=format&fit=crop&q=80', '', 'kemeja', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('4', 'Celana Chino Stretch Slim Beige', 'Celana chino kasual stretch yang elastis dan nyaman untuk bergerak bebas sepanjang hari.', '150000.00', '75', 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=600&auto=format&fit=crop&q=80', '', 'celana', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('5', 'Hoodie Pullover Oversized Sage Green', 'Hoodie rajutan premium yang tebal dan hangat namun tetap sejuk dipakai di siang hari.', '195000.00', '35', 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop&q=80', '', 'hoodie', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('6', 'Celana Cargo Corduroy Brown', 'Celana cargo berbahan corduroy premium dengan saku samping fungsional untuk petualangan urban Anda.', '175000.00', '28', 'https://images.unsplash.com/photo-1517462964-21fdcec3f25b?w=600&auto=format&fit=crop&q=80', '', 'celana', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('7', 'Jaket Bomber Vintage Black', 'Jaket bomber dengan lapisan windbreaker tebal yang melindungi dari dingin malam namun tetap stylish.', '210000.00', '20', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&auto=format&fit=crop&q=80', '', 'jaket', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('8', 'Kaos Polo Premium Navy Blue', 'Kaos polo semi-formal berkancing dengan kerah rajut padat dan rajutan katun berpori yang breathable.', '85000.00', '80', 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=600&auto=format&fit=crop&q=80', '', 'kaos', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('9', 'Beras Premium Pandan Wangi 5kg', 'Beras putih pulen premium varietas Pandan Wangi pilihan petani terbaik. Aroma wangi alami dan tekstur nasi yang lembut.', '68000.00', '150', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80', '', 'sembako', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('10', 'Minyak Goreng Kemasan 1L', 'Minyak goreng sawit berkualitas tinggi, jernih, bebas kolesterol jahat. Cocok untuk menggoreng, menumis, dan memanggang.', '22000.00', '200', 'https://images.unsplash.com/photo-1620706857370-e1b9770e8bb1?w=600&auto=format&fit=crop&q=80', '', 'sembako', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('11', 'Gula Pasir Rafinasi 1kg', 'Gula pasir putih halus berkualitas tinggi, bersih tanpa kotoran. Ideal untuk memasak, kue, dan minuman.', '16500.00', '180', 'https://images.unsplash.com/photo-1571506165871-ee72a35bc9d4?w=600&auto=format&fit=crop&q=80', '', 'sembako', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('12', 'Telur Ayam Negeri Segar 1 Kg', 'Telur ayam negeri segar langsung dari peternak, protein tinggi untuk kebutuhan gizi keluarga sehari-hari.', '28000.00', '100', 'https://images.unsplash.com/photo-1506976785307-8732e854ad03?w=600&auto=format&fit=crop&q=80', '', 'sembako', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('13', 'Mie Instan Rasa Ayam Bawang (Box)', 'Isi 40 bungkus per karton. Mie instan favorit keluarga dengan bumbu khas ayam bawang yang gurih.', '95000.00', '80', 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=600&auto=format&fit=crop&q=80', '', 'makanan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('14', 'Susu UHT Full Cream 1L', 'Susu sapi segar Ultra High Temperature dengan lemak penuh. Sumber kalsium dan protein untuk pertumbuhan.', '18500.00', '97', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&auto=format&fit=crop&q=80', '', 'minuman', '1', '2026-06-21 00:51:45', '2026-06-21 01:08:40'),
('15', 'Air Mineral Botol 600ml (1 Krat)', 'Air mineral segar tersaring 100% alami dalam kemasan botol praktis. Segar, bersih, bebas kuman.', '24000.00', '60', 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=600&auto=format&fit=crop&q=80', '', 'minuman', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('16', 'Sabun Cuci Piring Busa Aktif 800ml', 'Formula busa tebal dengan aroma jeruk segar. Efektif membersihkan lemak membandel tanpa merusak kulit tangan.', '15000.00', '200', 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=600&auto=format&fit=crop&q=80', '', 'kebersihan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('17', 'Deterjen Cair Konsentrat 800ml', 'Deterjen cair konsentrat yang efisien. Formula Enzyme Power mengangkat noda membandel pada baju kesayangan Anda.', '32000.00', '150', 'https://images.unsplash.com/photo-1585670083947-7c3a5e2ebe02?w=600&auto=format&fit=crop&q=80', '', 'kebersihan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('18', 'Pembersih Lantai Anti-Kuman 1.8L', 'Formula anti-kuman 99.9% dengan wangi lavender tahan lama. Menjaga lantai bersih, wangi, dan bebas bakteri.', '28500.00', '90', 'https://images.unsplash.com/photo-1563453392212-326f5e854473?w=600&auto=format&fit=crop&q=80', '', 'kebersihan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('19', 'Tisu Wajah Lembut 200 Lembar', 'Tisu wajah lembut ekstra soft berbahan pulp premium. Tidak berbulu, aman untuk kulit wajah sensitif.', '18000.00', '300', 'https://images.unsplash.com/photo-1616628188859-7a11abb6fcc9?w=600&auto=format&fit=crop&q=80', '', 'kebersihan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('20', 'Shampo Perawatan Rambut 340ml', 'Formula keratin dan argan oil yang menutrisi rambut dari akar hingga ujung. Membuat rambut halus, berkilau, dan bebas kusut.', '34000.00', '110', 'https://images.unsplash.com/photo-1585751119414-ef2636f8aede?w=600&auto=format&fit=crop&q=80', '', 'perawatan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('21', 'Sabun Mandi Cair Moisturizing 400ml', 'Sabun mandi cair dengan pelembab shea butter dan vitamin E. Menjaga kelembaban kulit hingga 24 jam setelah mandi.', '28000.00', '250', 'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?w=600&auto=format&fit=crop&q=80', '', 'perawatan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('22', 'Pasta Gigi Whitening Charcoal 120g', 'Formula charcoal aktif yang memutihkan gigi secara alami sekaligus melawan bakteri penyebab bau mulut.', '18000.00', '175', 'https://images.unsplash.com/photo-1559591937-abc0d0c77c58?w=600&auto=format&fit=crop&q=80', '', 'perawatan', '1', '2026-06-21 00:51:45', '2026-06-21 00:51:45'),
('23', 'Pelembab Wajah SPF 30 50ml', 'Pelembab wajah ringan dengan perlindungan UV SPF 30. Cocok untuk pemakaian sehari-hari di luar dan dalam ruangan.', '45000.00', '84', 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=600&auto=format&fit=crop&q=80', '', 'perawatan', '1', '2026-06-21 00:51:45', '2026-06-21 01:08:40');

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for `users`

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `avatar`, `google_uid`, `created_at`, `updated_at`) VALUES
('1', 'admin', 'admin@zmart.id', 'admin123', 'System Admin', 'admin', NULL, NULL, '2026-06-21 07:51:45', '2026-06-21 07:51:45'),
('2', 'user1', 'user1@zmart.id', 'user123', 'Regular Customer', 'customer', NULL, NULL, '2026-06-21 07:51:45', '2026-06-21 07:51:45'),
('3', 'irvanagussaputra710', 'irvanagussaputra710@gmail.com', 'google_oauth', 'Irvan Agus Saputra', 'customer', 'https://lh3.googleusercontent.com/a/ACg8ocJcyXKQAhaDWUMprM9MY3PBGVt25_8noDkfWyvWegoPWmB19A=s96-c', 'cYGQ22WAJrau3dw4VT8I1u1ECXo1', '2026-06-21 08:08:08', '2026-06-21 08:08:08');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
