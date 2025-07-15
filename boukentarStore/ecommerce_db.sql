SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `ecommerce_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ecommerce_db`;




CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'habibAllah', '$2y$10$FdPX0RDJJsIytMmn5eihhOwAevzuaTj/CVCqg9la1RVi69AtRnB42'),
(2, 'ali', '$2y$10$YNE152B.bM/6OPgqoSTYROOhm3btqxHPVHleamkq/Q/iQFeFH1ZSu'),
(3, 'mohammed', '$2y$10$eTa7QOZXs4Zr31/GY/zXN.flEydMtTXI8HQ7M5IYGTfY.fhtf29Y6');




CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, '🏀 Ballon en cuir', 'Ballon artisanal fabriqué à la main en cuir véritable. Idéal comme objet décoratif ou pour les amateurs de football vintage marocain. Chaque pièce est unique et rend hommage au savoir-faire traditionnel.'),
(2, '👡 Babouches femme', 'Élégantes babouches marocaines pour femme, confectionnées à la main avec des matériaux de qualité. Parfaites pour allier confort et style oriental, à la maison comme à l’extérieur.'),
(3, '👜 Sac en cuir', 'Sac en cuir 100% naturel, cousu à la main par des artisans marocains. Un accessoire robuste et raffiné, idéal pour le quotidien ou pour apporter une touche ethnique à votre look.'),
(4, '🪑 Bouffât artisanal', 'Bouffât marocain traditionnel, fabriqué en cuir et entièrement cousu à la main. Ce pouf artisanal peut servir d’assise, de repose-pieds ou d’élément décoratif pour une ambiance authentique.'),
(5, '🧕 Djellaba marocaine', 'Djellaba traditionnelle pour femme, avec broderies fines et capuche. Confectionnée avec soin pour refléter l’élégance et la richesse de la culture marocaine.'),
(6, '🎁 Offre Spéciale', 'Découvrez nos offres limitées sur une sélection de produits artisanaux marocains. Profitez de prix réduits sans compromis sur la qualité et l’authenticité.');




CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT 'default_product_image.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `image_url`, `created_at`) VALUES
(1, 6, '🪑 Ensemble Salon Extérieur', 'Deux fauteuils tressés noirs et une petite table ronde, parfaits pour un balcon ou jardin.', 499.00, 10, 'WhatsApp Image 2025-07-03 at 15.21.17(1).jpeg', '2025-07-03 16:26:35'),
(2, 6, '🧊 Mini-Frigo Vitré', 'Un petit réfrigérateur noir avec une porte vitrée, parfait pour les boissons et snacks.', 899.00, 5, 'WhatsApp Image 2025-07-03 at 15.21.25(1).jpeg', '2025-07-03 16:28:09'),
(3, 5, '👗 Kaftan Lilas Brodé', 'Un kaftan lilas doux orné de broderies florales bleues claires, élégant et féminin.', 89.00, 30, 'WhatsApp Image 2025-07-03 at 15.17.43(1).jpeg', '2025-07-03 16:30:38'),
(4, 5, '👗 Kaftan Carreaux & Kaki', 'Un kaftan à carreaux noir et blanc avec des accents et broderies vert kaki.', 79.00, 14, 'WhatsApp Image 2025-07-03 at 15.17.51.jpeg', '2025-07-03 16:31:55'),
(5, 3, '💼 Sac Polochon Cuir Cognac', 'Un grand sac de voyage en cuir cognac avec de nombreuses poches zippées, idéal pour les escapades.', 99.00, 13, 'WhatsApp Image 2025-07-03 at 15.11.13.jpeg', '2025-07-03 16:33:05'),
(6, 1, '🏈 Ballon de Rugby Ancien', 'Un ballon de rugby en cuir marron foncé avec un laçage visible, rappelant les modèles d\'époque.', 79.00, 40, 'WhatsApp Image 2025-07-03 at 15.07.48(1).jpeg', '2025-07-03 16:35:36'),
(7, 2, '🥿 Babouches Rouge Classique', 'Babouches traditionnelles marocaines en cuir rouge vif à bout pointu.', 39.00, 40, 'WhatsApp Image 2025-07-03 at 15.13.11.jpeg', '2025-07-03 16:36:24'),
(8, 1, '⚽ Ballon de Foot Rétro Lacé', 'Un ballon de football en cuir marron vintage avec un système de laçage traditionnel.', 59.00, 10, 'WhatsApp Image 2025-07-03 at 15.07.49(1).jpeg', '2025-07-03 16:37:40'),
(9, 3, '🎒 Sac à Dos Classique Marron', 'Un sac à dos en cuir marron avec des boucles dorées et de multiples poches à rabat.', 69.00, 77, 'WhatsApp Image 2025-07-03 at 15.11.13(2).jpeg', '2025-07-03 16:39:01'),
(10, 1, '🏀 Ballon Vintage de Basket', 'Un ballon de basketball en cuir marron foncé au design classique et empiécé.', 49.00, 55, 'WhatsApp Image 2025-07-03 at 15.07.49.jpeg', '2025-07-03 16:40:14'),
(11, 2, '👟 Mules Tressées Beige', 'Mules légères et pointues de couleur beige, caractérisées par une texture tressée raffinée.', 49.00, 33, 'WhatsApp Image 2025-07-03 at 15.13.12(2).jpeg', '2025-07-03 16:41:55'),
(12, 3, '👜 Sac Polochon Marine', 'Un élégant sac de voyage en cuir bicolore bleu marine et marron, avec des poignées solides.', 69.00, 22, 'WhatsApp Image 2025-07-03 at 15.11.13(1).jpeg', '2025-07-03 16:43:02'),
(13, 2, '🥿 Babouches Florales Noires', 'Babouches en cuir noir avec des motifs floraux blancs délicatement brodés.', 49.00, 44, 'WhatsApp Image 2025-07-03 at 15.13.21(1).jpeg', '2025-07-03 16:44:01'),
(14, 2, '🥿 Babouches Florales Noires', 'Babouches en cuir noir avec des motifs floraux blancs délicatement brodés.', 49.00, 33, 'WhatsApp Image 2025-07-03 at 15.13.12(1).jpeg', '2025-07-03 16:44:45'),
(15, 2, '🥿 Mules Ethniques Colorées', 'Mules ouvertes très colorées avec des motifs ethniques brodés, offrant un style unique et confortable', 49.00, 33, 'WhatsApp Image 2025-07-03 at 15.13.12(2).jpeg', '2025-07-03 16:46:10'),
(16, 4, '🛋️ Pouf Cuir Marron', 'Un pouf marocain classique en cuir marron foncé avec des coutures contrastantes.', 119.00, 5, 'WhatsApp Image 2025-07-03 at 15.14.29(2).jpeg', '2025-07-03 16:47:45'),
(17, 4, '🛋️ Pouf Doré Scintillant', 'Un pouf en cuir doré brillant avec des coutures et des motifs brodés, apportant une touche luxueuse.', 119.00, 30, 'WhatsApp Image 2025-07-03 at 15.14.35.jpeg', '2025-07-03 16:49:03'),
(18, 5, '👗 Kaftan Rouge Vif', 'Une robe longue kaftan rouge éclatant avec des broderies discrètes, pour une présence audacieuse.', 59.00, 5, 'WhatsApp Image 2025-07-03 at 15.17.32.jpeg', '2025-07-03 16:49:43'),
(19, 4, '🛋️ Pouf Artisanal Patchwork', 'Un pouf en cuir patchwork marron et crème orné de motifs géométriques dorés.', 119.00, 7, 'WhatsApp Image 2025-07-03 at 15.14.29.jpeg', '2025-07-03 16:51:10'),
(20, 5, '👗 Kaftan Émeraude Élégant', 'Une robe longue kaftan vert émeraude avec des broderies ton sur ton, parfaite pour une allure raffinée.', 79.00, 44, 'WhatsApp Image 2025-07-03 at 15.17.32(1).jpeg', '2025-07-03 16:51:56'),
(21, 4, '🛋️ Pouf Azur', 'Un pouf en cuir bleu vif avec des coutures décoratives, ajoutant une touche de couleur et de confort.', 119.00, 3, 'WhatsApp Image 2025-07-03 at 15.14.29(1).jpeg', '2025-07-03 16:53:01'),
(22, 5, '👗 Kaftan Royal Noir', 'Un somptueux kaftan noir et doré avec des broderies élaborées, idéal pour les grandes occasions.', 69.00, 65, 'WhatsApp Image 2025-07-03 at 15.17.43.jpeg', '2025-07-03 16:55:17'),
(23, 2, '🥿 Babouches Brodés Vibrants', 'Chaussures traditionnelles colorées avec des broderies géométriques vives et une semelle robuste.', 69.00, 42, 'WhatsApp Image 2025-07-03 at 15.13.12.jpeg', '2025-07-03 16:56:52'),
(24, 1, '⚽ Ballon de Foot Héritage Lacé', 'Un ballon de football en cuir marron foncé avec une fermeture à lacets visible et des coutures traditionnelles.', 49.00, 9, 'WhatsApp Image 2025-07-03 at 15.07.48(1).jpeg', '2025-07-03 17:03:52'),
(25, 6, 'tissues Boîte à Mouchoirs Chromée', 'Un distributeur de mouchoirs moderne en métal chromé brillant, parfait pour un intérieur contemporain.', 9.00, 130, 'WhatsApp Image 2025-07-03 at 15.21.17.jpeg', '2025-07-03 17:05:41');




CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `shipping_address` text NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;





ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);


ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`);


ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_product` (`product_id`);


ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_product_category` (`category_id`);


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);





ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;


ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;


ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;





ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;


ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
