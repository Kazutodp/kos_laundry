CREATE DATABASE IF NOT EXISTS `kos_laundry`;
USE `kos_laundry`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NULL,
    `google_id` VARCHAR(255) NULL UNIQUE,
    `foto_profil` VARCHAR(255) NULL,
    `no_telp` VARCHAR(20) NULL,
    `jenis_kelamin` VARCHAR(20) NULL,
    `alamat` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mitra_laundry` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_mitra` VARCHAR(100) NOT NULL,
    `foto_toko` VARCHAR(255) NULL,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `alamat` TEXT NOT NULL,
    `no_telp` VARCHAR(20) NULL,
    `rating` DECIMAL(2, 1) DEFAULT 0.0,
    `harga_per_kg` INT NOT NULL,
    `jam_buka` VARCHAR(50) NULL,
    `status_buka` TINYINT(1) DEFAULT 1,
    `icon_type` VARCHAR(50) DEFAULT 'kiloan',
    `is_rekomendasi` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data for Mataram
INSERT IGNORE INTO `mitra_laundry` (`id`, `nama_mitra`, `foto_toko`, `latitude`, `longitude`, `alamat`, `no_telp`, `rating`, `harga_per_kg`, `jam_buka`, `status_buka`, `icon_type`, `is_rekomendasi`) VALUES
(1, 'KosanFresh Laundry', 'uploads/mitra_1.png', -8.58260000, 116.10750000, 'Jl. Pejanggik No. 12, Mataram (Pusat Kota)', '081234567890', 4.9, 7000, 'Open until 21:00', 1, 'kiloan', 1),
(2, 'Express Shine', 'uploads/mitra_2.png', -8.59020000, 116.11540000, 'Jl. Panca Usaha No. 45, Cilinaya, Mataram', '081234567891', 4.7, 9500, 'Open 24 Hours', 1, 'express', 1),
(3, 'Sahabat Kos Laundry', 'uploads/mitra_1.png', -8.58300000, 116.09500000, 'Jl. Airlangga No. 8, Mataram', '081234567892', 4.5, 6000, 'Open until 20:00', 1, 'kiloan', 1),
(4, 'EcoWash Pure', 'uploads/mitra_2.png', -8.60150000, 116.11300000, 'Jl. Bung Karno, Pagutan, Mataram', '081234567893', 4.8, 8500, 'Open until 22:00', 1, 'eco', 1),
(5, 'FreshClean Laundry', 'uploads/mitra_1.png', -8.56200000, 116.07700000, 'Jl. Saleh Sungkar, Ampenan, Mataram', '081234567894', 4.6, 7500, 'Open until 21:30', 1, 'satuan', 1),
(6, 'WashTra Laundry Ekspress', 'uploads/mitra_washtra.png', -8.59059420, 116.09259180, 'Jl. Majapahit No.88C, Kekalik Jaya, Kec. Sekarbela, Kota Mataram, Nusa Tenggara Bar. 83127', '082341961954', 4.8, 15000, 'Senin - Minggu 07:00 - 22:00', 1, 'express', 1),
(7, 'LAUNDRY LOMBOK', 'uploads/laundry_lombok.png', -8.59210790, 116.08926740, 'Jl. Swasembada No.26, Kekalik Jaya, Kec. Sekarbela, Kota Mataram', '085941306413', 5.0, 7000, 'Senin - Minggu 07:00 - 21:00', 1, 'kiloan', 1),
(8, 'MAULaundry Mataram', 'uploads/mitra_maulaundry_storefront.png', -8.59282220, 116.08937300, 'Jl. Swasembada No.37, Kekalik Jaya, Kec. Sekarbela, Kota Mataram', '087736861615', 4.8, 25000, 'Senin - Minggu 08:00 - 22:00', 1, 'express', 1),
(9, 'Mate Shoes Care', 'uploads/mitra_mate_shoes_care.png', -8.58840500, 116.08540920, 'Jl. Swakarya Raya, Kekalik Jaya, Kec. Sekarbela, Kota Mataram. dekat Kudeta Barbershop Kekalik', '087898824993', 5.0, 25000, 'Senin - Minggu 09:00 - 21:00', 1, 'sepatu', 1);

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `mitra_id` INT NOT NULL,
    `nama_pelanggan` VARCHAR(100) NOT NULL,
    `layanan` VARCHAR(100) NOT NULL,
    `berat_atau_qty` DECIMAL(5, 2) NOT NULL,
    `tarif_per_kg` INT NOT NULL,
    `biaya_antar_jemput` INT DEFAULT 1500,
    `total_harga` INT NOT NULL,
    `status_pembayaran` VARCHAR(20) DEFAULT 'success',
    `status_transfer` VARCHAR(20) DEFAULT 'Selesai',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`mitra_id`) REFERENCES `mitra_laundry`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


