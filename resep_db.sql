-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2025 at 07:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resep_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ingredients` text NOT NULL,
  `steps` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) NOT NULL DEFAULT 'images/uploads/default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `user_id`, `name`, `ingredients`, `steps`, `created_at`, `image`) VALUES
(12, 3, 'CHOCO GEMBILI', '50 gram tepung gembili\r\n100 gram margarin\r\n100 gram gula pasir\r\n1 butir telur\r\n100 gram tepung terigu\r\n30 gram cokelat bubuk\r\n1/2 sendok teh baking powder\r\n100 gram coklat chips\r\nSejumput garam', '[\"Campurkan bahan kering: Dalam sebuah wadah, masukkan terigu, susu, baking powder, dan garam.\",\"Siapkan adonan basah: Di wadah terpisah, campurkan gula pasir dan margarin cair hingga larut. Setelah itu, masukkan telur dan kocok kembali hingga rata.\",\"Gabungkan adonan: Tuang campuran telur ke dalam campuran tepung sedikit demi sedikit. Aduk perlahan dengan teknik aduk balik, jangan terlalu lama.\",\"Masukkan choco chips ke dalam adonan, lalu aduk sebentar hingga tercampur rata.\",\"Ambilkan adonan, bentuk bulat-bulat, lalu letakkan di atas loyang yang sudah disiapkan. Panggang dalam oven dengan suhu 175\\u2070 C menggunakan api atas bawah selama 25 menit, atau hingga matang.\",\"Setelah matang, angkat dan dinginkan kue di atas cooling rack. Setelah dingin, segera masukkan ke dalam toples kedap udara agar\\u00a0tetap\\u00a0renyah.\"]', '2025-07-15 07:41:28', 'images/uploads/img_6876062883fc94.82407898.jpg'),
(13, 4, 'PUTRI SALJU (UMBI UNGU) ', '200 gram margarin \r\n100 gram gula halus \r\n2 butir kuning telur \r\n250 gram tepung terigu \r\n50 gram tepung ubi ungu \r\n50 gram susu bubuk \r\n 100 gram kacang kenari atau almond \r\n½ sendok teh vanili bubuk \r\nBahan taburan \r\n50 gram gula halus ', '[\"Kocok margarin dan gula selama 1 menit, kemudian tambahkan  kuning telur, kocok hingga rata.\",\"Masukkan tepung terigu, maizena, susu bubuk, dan tepung ubi ungu,  lalu aduk hingga semua bahan tercampur rata menggunakan spatula.\",\"Siapkan adonan, kemudian giling hingga ketebalan 1 cm, lalu cetak  menggunakan cookie cutter.\",\"Siapkan loyang yang telah diolesi margarin, dan letakkan adonan di  atas loyang tersebut\",\"Oven adonan selama 15 menit, kemudian oven kembali selama 15  menit dengan suhu 150 derajat Celsius hingg6a matang. Angkat dan  dinginkan.\",\"Setelah dingin, masukkan ke dalam toples, dan cookies siap disajikan.\"]', '2025-07-16 07:28:26', 'images/uploads/img_6877549a8d10d7.63032394.jpg'),
(14, 5, 'KERIPIK TALAS', '250 gram tepung terigu \r\n37 gram tepung talas \r\n1 butir kuning telur \r\n2 siung bawag putih \r\n2 siung bawang merah \r\n1 sdt garam \r\n1 sdt penyedap rasa \r\n3 batang daun seledri \r\n500 ml minyak goreng ', '[\"Siapkan bahan sesuai resep, Campurkan tepung terigu dengan  tepung talas dalam wadah besar.\",\"Tambahkan garam dan penyedap rasa sesuai selera. Aduk hingga  semua bahan kering tercampur rata.\",\"Haluskan bawang putih dan bawang merah hingga halus, Iris daun seledri dan masukkan ke dalam adonan bersama kuning  telur, tambahkan air sedikit demi sedikit ke dalam campuran adonan.  aduk hingga adonan menjadi kalis, yaitu tidak lengket di tangan dan  dapat dibentuk.\",\"Bentuk adonan menjadi bulat-bulat kecil, giling adonan hingga pipih  menggunakan rolling pin atau alat penggiling. Ketebalan adonan  dapat disesuaikan dengan selera.\",\"Potong lembaran adonan yang telah digiling menjadi potongan kecil  menggunakan pisau, Potongan ini bisa berbentuk persegi atau sesuai  selera.\",\"Panaskan minyak dalam wajan, kemudian goreng potongan adonan  hingga matang dan berwarna keemasan. aduk saat proses  penggorengan supaya adonan tidak lengket. Setelah itu, tiriskan  potongan yang telah digoreng untuk menghilangkan minyak  berlebih, masukkan keripik ke dalam toples, dan siap disajikan.\"]', '2025-07-16 07:40:16', 'images/uploads/img_6877576080c859.56538503.jpg'),
(15, 6, 'SEMPRIT CILEMBU', '40 gram tepung maizena \r\n¼ sendok teh Vanili bubuk  \r\n150 gram gula halus  \r\n2 butir kuning telur \r\n1 butir putih telur \r\n200 gram mentega  \r\n330 gram tepung terigu  \r\n50 gram tepung ubi cilmbu', '[\"Campurkan tepung maizena, tepung terigu, dan tepung ubi dalam  satu wadah. Aduk hingga semua bahan tercampur rata untuk  memastikan tidak ada gumpalan, Campurkan mentega, garam, gula halus, dan vanili dalam sebuah  wadah. Kocok semua bahan ini menggunakan mixer atau whisk  hingga adonan menjadi krim yang pucat dan lembut.\",\"Setelah adonan krim terbentuk, masukkan kuning telur dan keju  parut. aduk kembali hingga semua bahan tercampur rata,\",\"Masukkan bahan-bahan kering, yaitu tepung, susu bubuk, dan  maizena. Campurkan semua bahan ini hingga rata hingga  membentuk adonan yang dapat dipadatkan, Diamkan adonan selama 20 menit di dalam kulkas. Proses ini  bertujuan untuk mengeraskan adonan agar tidak lembek saat  dibentuk.\",\"Oleskan loyang yang akan kamu gunakan dengan sedikit margarin.  Siapkan plastik untuk membuat cetakan kue semprit. Masukkan  bahan ke dalam plastik dan keluarkan dalam bentuk bunga mawar  hingga semua loyang terisi adonan.\",\"Setelah itu, masukkan ke dalam oven dengan suhu sekitar 165 \\u2013 175  derajat Celsius, panggang sekitar 25 menit hingga matang dan  berwara keemasan.\",\"Dinginkan cookies, setelah dingin masukkan cookies ke dalam  toples dan cookies siap disajikan.\"]', '2025-07-16 07:48:22', 'images/uploads/img_6877594604d9a2.14722202.jpg'),
(16, 7, 'PALM CHEESE (BENGKOANG)', '150 gram mentega tawar  \r\n½ sdt garam \r\n25 gram gula halus vanilla \r\n1 butir kuning telur  \r\n150 gram tepung pro sedang  \r\n50 gram tepung bengkoang \r\n25 gram susu bubuk  \r\n20 gram maizena \r\n60 gram keju parut \r\n100 gram palm sugar', '[\"Campurkan mentega, garam, gula halus, dan vanili dalam sebuah  wadah. Kocok semua bahan ini menggunakan mixer atau whisk  hingga adonan menjadi krim yang pucat dan lembut.\",\"Setelah adonan krim terbentuk, masukkan kuning telur dan keju  parut. Aduk kembali hingga semua bahan tercampur rata,  masukkan bahan-bahan kering, yaitu tepung, susu bubuk, dan  maizena. campurkan semua bahan ini hingga rata,  membentuk adonan yang dapat dipadatkan.\",\"Diamkan adonan selama 20 menit di dalam kulkas. proses ini  bertujuan untuk mengeraskan adonan agar tidak lembek saat  dibentuk.\",\"Ambil adonan dan bentuk bulat dengan berat sekitar 8 gram setiap  bulatan. Pastikan ukuran bulatan seragam agar matang merata saat  dipanggang. Baluri setiap bulatan adonan dengan gula palem hingga seluruh  permukaan tertutup rata.\",\"Olesi loyang dengan mentega, Panaskan oven pada suhu 150 derajat celsius. Letakkan bulatan  adonan di atas loyang dan panggang selama 25-30 menit hingga kue  matang dan berwarna kecoklatan.\",\"Dinginkan cookies, setelah itu masukkan ke dalam toples dan siap  untuk disajikan.\"]', '2025-07-16 11:32:00', 'images/uploads/img_68778db0b3cb50.51189676.jpg'),
(17, 8, 'PRATIWI COOKIES (UMBI UWI)', '150 gram mentega tawar \r\n½ sdt garam\r\n25 gram gula halus \r\n½ sdt vanilla\r\n30 gram santan\r\n1 butir kuning telur\r\n40 gram tepung tapioka\r\n30 gram tepung umbi uwi\r\n150 gram tepung terigu\r\nBahan Pelengkap : \r\n1 batang coklat \r\nSecukupnya springkel\r\n', '[\"Sangrai tepung tapioka di atas wajan dengan api kecil hingga terasa ringan dan harum. Pastikan tepung tidak gosong. Setelah matang, angkat dan sisihkan. biarkan hingga benar-benar dingin pada suhu ruang.\",\"kocok mentega, margarin, dan gula halus menggunakan mixer dengan kecepatan rendah hingga sedang selama kurang lebih 3-5 menit, sampai adonan lembut dan berwarna pucat\",\"Masukkan kuning telur dan vanila ekstrak (jika menggunakan) ke dalam adonan mentega. Kocok kembali hingga tercampur rata. tuangkan santan instan sedikit demi sedikit sambil terus diaduk rata\",\"Masukkan bahan kering\\/tepung  yang sudah disangrai dan diayak secara bertahap ke dalam adonan basah. aduk perlahan dengan spatula hingga adonan dapat dipulung atau dicetak. 7.\\tMasukkan adonan ke dalam piping bag yang telah diberi spuit. Semprotkan adonan di atas loyang yang sudah dialasi kertas roti atau alas silikon dengan berbagai bentuk sesuai selera\",\"Panaskan oven pada suhu 130-140\\u00b0C. Panggang kue selama kurang lebih 25-35 menit, atau hingga kue matang sempurna dan berwarna kuning keemasan.\",\"Setelah matang, keluarkan loyang dari oven. Biarkan kue sagu dingin sepenuhnya di atas loyang sebelum dipindahkan ke wadah kedap udara\"]', '2025-07-16 11:45:40', 'images/uploads/img_687790e453ebe2.95851900.jpg'),
(18, 9, 'GARLIC CHEESE KIESS (KENTANG)', '75 gram keju parut\r\n250 gram tepung terigu\r\n50 gram kentang halus\r\n25 gram maizena\r\n1 butir kuning telur \r\n5 gram (1 ½ sdt) bawang putih bubuk \r\n1 sdm parsley bubuk \r\nBahan olesan dan taburan :\r\nSecukupnya Keju parut\r\nSecukupnya parsley kering \r\n1 butir kuning telur \r\n1 sdm minyak sayur, selera\r\n1 tetes pewarna kuning (selera)\r\n', '[\"Masukan margarin, aduk sampai warnanya agak pucat. masukkan kuning telur dan bawang putih bubuk, aduk sampai tercampur\",\"Masukkan keju parut dan parsley kering, aduk sampai tercampur\",\"Masukkan tepung maizena dan tepung terigu secara bertahap sambil diaduk sampai tercampur rata\",\"Pipihkan adonan dan bentuk sesuai selera. tata di loyang yang sudah dioles dengan margarin\",\"Panaskan oven pada suhu 130-140\\u00b0C. Panggang kue selama kurang lebih 25-35 menit, atau hingga kue matang sempurna dan berwarna kuning keemasan.\",\"Setelah matang, keluarkan loyang 4dari oven. biarkan cookies dingin sepenuhnya di atas loyang sebelum dipindahkan ke wadah kedap udara\"]', '2025-07-16 11:51:20', 'images/uploads/img_6877923838e7e8.50331468.jpg'),
(19, 10, 'CASSAVA COOKIES(SINGKONG)', '150 gram margarin\r\n1 butir kuning telur\r\n80 gram keju parut\r\n190 gram  tepung protein rendah \r\n10 gram  susu bubuk\r\nBahan taburan\r\n50 gram keju parut\r\n30 gram singkong parut\r\nBahan Olesan:\r\n1 butir kuning telur\r\nSecukupnya pewarna kuning\r\n', '[\"Kocok lemak hingga lembut. pastikan lemak (margarin\\/mentega) dikocok hingga mengembang dan bertekstur lembut.  masukkan kuning telur dan keju parut. setelah lemak lembut, tambahkan kuning telur dan keju parut. aduk hingga semua bahan tercampur sempurna dan tidak ada gumpalan\",\"Tambahkan tepung terigu, dan susu bubuk. secara bertahap, masukkan campuran tepung terigu, tepung mocaf, dan susu bubuk yang sudah diayak ke dalam adonan. Aduk perlahan hingga semua bahan kering tercampur rata dan adonan menjadi lembut.\",\"Ambil sebagian adonan dan bentuk sesuai selera, letakkan adonan yang sudah dicetak di atas loyang yang sudah dialasi kertas roti atau diolesi sedikit margarin\",\"Olesi permukaan adonan dengan kuning telur. Ini akan memberikan warna keemasan yang cantik setelah dipanggang. Kemudian, taburi dengan keju parut dan singkong parut kering sebagai hiasan dan penambah rasa.\",\"Panaskan oven pada suhu 130-140\\u00b0C. Panggang kue selama kurang lebih 25-35 menit, atau hingga kue matang sempurna dan berwarna kuning keemasan\",\"Setelah matang, keluarkan loyang dari oven. Biarkan cookies dingin sepenuhnya di atas loyang sebelum dipindahkan ke wadah kedap udara\"]', '2025-07-16 12:08:06', 'images/uploads/img_687796266460b1.19031340.jpg'),
(20, 11, 'JINTUBI COOKIES ', '•	175 gram tepung terigu \r\n•	90 gram margarin\r\n•	50 gram gula halus\r\n•	1 sdm susu bubuk \r\n•	1 butir kuning telur\r\n•	75 gram ubi kuning kukus\r\nBahan Taburan: \r\n•	100 gram gula halus\r\n\r\n', '[\"Kocok margarin, gula halus, dan kuning telur menggunakan mikser hingga adonan mengembang, berwarna pucat, dan bertekstur lembut.\",\"Masukkan tepung, ubi halus, dan susu bubuk. setelah adonan margarin mengembang, masukkan tepung terigu, ubi yang sudah dihaluskan, dan susu bubuk\",\"Campurkan semua bahan ini secara bertahap. Aduk perlahan dengan spatula atau mixer berkecepatan rendah hingga semua bahan kering tercampur rata dan adonan menjadi kalis serta mudah dibentuk\",\"Siapkan alas kerja yang bersih dan taburi sedikit tepung agar adonan tidak lengket. Giling adonan hingga ketebalan yang diinginkan, biasanya sekitar 0,5 cm\",\"Kemudian, cetak adonan menggunakan cetakan berbentuk bunga atau sesuai selera, letakkan adonan yang sudah dicetak di atas loyang yang sudah dialasi kertas roti atau diolesi sedikit margarin\",\"Panaskan oven pada suhu 140-150\\u00b0C. Panggang kue selama kurang lebih 25-35 menit, atau hingga kue matang sempurna dan berwarna kuning keemasan\"]', '2025-07-16 15:30:42', 'images/uploads/img_6877c5a2cbece9.15395801.jpg'),
(21, 12, 'KASTANGEL COOKIES ', '•	125 gram margarine\r\n•	2,5 gram margarin tawar\r\n•	125 gram tepung terigu\r\n•	30 gram tepung ganyong\r\n•	62,5 gram keju cheddar (parut)\r\n•	1,5 gram garam\r\n•	1 butir kuning telur\r\n\r\nBahan Olesan :\r\n•	½ Butir kuning telur untuk olesan\r\n•	50 gram keju parut, untuk taburan\r\n', '[\"Kocok lemak (margarin\\/mentega) dengan kuning telur hingga lembut dan tercampur rata\",\"Campurkan tepung terigu dengan keju parut, lalu masukkan ke dalam adonan lemak dan telur. Aduk hingga seluruh bahan menyatu dan adonan menjadi kalis\",\"Diamkan adonan selama 20 menit di dalam kulkas. Proses ini bertujuan untuk mengeraskan adonan agar tidak lembek saat dibentuk.\",\"Giling adonan hingga ketebalan yang diinginkan, kemudian potong sesuai bentuk kastengel.olesi Loyang dengan margarin, dan letakkan potongan adonan di atas loyang\",\"Olesi permukaan adonan dengan kuning telur, lalu taburi dengan keju parut sebagai hiasan\",\"Panaskan oven pada suhu 140-150\\u00b0C. Panggang kue selama kurang lebih 25-35 menit, atau hingga kue matang sempurna dan berwarna kuning keemasan\"]', '2025-07-16 15:38:32', 'images/uploads/img_6877c778bc03f5.48121416.jpg'),
(22, 13, 'CHOCO SUWEG', '185 gram margarin\r\n1 sdt essen vanilla\r\n65 gram gula kastor\r\n80 gram gula palm\r\n1 butir telur\r\n200gram tepung terigu protein sedang\r\n30 gram tepung umbi suweg\r\n200 gram dark chocholate, cincang agak halus\r\nSejumput garam', '[\"Masukkan margarin, garam, esens, gula pasir, dan gula palem ke dalam wadah mixer.\",\"Kocok adonan dengan mixer menggunakan kecepatan sedang hingga gula setengah hancur dan tercampur rata dengan margarin. Adonan akan terlihat lebih pucat dan mengembang\\u00a0ringan\",\"Secara bertahap, masukkan tepung umbi suweg, tepung terigu, dan cokelat\",\"Aduk menggunakan spatula dengan gerakan melipat atau aduk balik hingga semua bahan kering tercampur rata dan tidak ada lagi gumpalan tepung\",\"Letakkan adonan yang sudah dibulatkan di atas loyang yang sudah disiapkan (biasanya diolesi margarin tipis atau dialasi\\u00a0kertas\\u00a0roti).\",\"Ambil sekitar 1 sendok teh adonan. Bentuk bulat di antara telapak tangan, Pipihkan sedikit adonan dengan jari atau garpu agar tidak terlalu tebal\\u00a0dan\\u00a0matang\"]', '2025-07-16 15:50:55', 'images/uploads/img_6877ca5f672671.08232358.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ratings`
--

CREATE TABLE `recipe_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `profile_image`, `role`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'admin@gmail.com', '2025-07-14 05:21:26', NULL, 'admin'),
(2, 'rezy', '202cb962ac59075b964b07152d234b70', 'sayang@gmail.com', '2025-07-14 05:36:34', NULL, 'user'),
(3, 'Kuni Fauziah (2023)', '202cb962ac59075b964b07152d234b70', 'kunifauziah@gmail.com', '2025-07-15 07:21:37', NULL, 'user'),
(4, 'Paluphi Nawangsasi (2023)', '202cb962ac59075b964b07152d234b70', 'palupi@gmail.com', '2025-07-16 07:23:41', NULL, 'user'),
(5, 'Nabilah Hazmi (2024) ', '202cb962ac59075b964b07152d234b70', 'nabila@gmail.com', '2025-07-16 07:33:44', NULL, 'user'),
(6, 'Nur Haliza Akhsa (2022)', '202cb962ac59075b964b07152d234b70', 'nur@gmail.com', '2025-07-16 07:42:59', NULL, 'user'),
(7, 'Annisa Farahanun (2022)', '202cb962ac59075b964b07152d234b70', 'nisa@gmail.com', '2025-07-16 11:23:24', NULL, 'user'),
(8, 'Dyah Nur Alfianti (2023)', '202cb962ac59075b964b07152d234b70', 'dia@gmail.com', '2025-07-16 11:33:42', NULL, 'user'),
(9, 'Rahmadenia (2023)', '202cb962ac59075b964b07152d234b70', 'denia@gmail.com', '2025-07-16 11:46:48', NULL, 'user'),
(10, 'Nisvia Muzaizana (2023)', '202cb962ac59075b964b07152d234b70', 'zana@gmail', '2025-07-16 12:02:40', NULL, 'user'),
(11, 'putri (2024)', '202cb962ac59075b964b07152d234b70', 'putri@gmail.com', '2025-07-16 15:22:19', NULL, 'user'),
(12, 'Murni Pratiwi (2023)', '202cb962ac59075b964b07152d234b70', 'Murnipratiwi@gmail.com', '2025-07-16 15:33:51', NULL, 'user'),
(13, 'Ranggi Lewu (2023)', '202cb962ac59075b964b07152d234b70', 'ranggilewu@gmail.com', '2025-07-16 15:43:28', NULL, 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recipe_ratings`
--
ALTER TABLE `recipe_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_recipe_unique` (`user_id`,`recipe_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `recipe_ratings`
--
ALTER TABLE `recipe_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
