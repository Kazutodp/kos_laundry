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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data for Mataram
INSERT IGNORE INTO `mitra_laundry` (`id`, `nama_mitra`, `foto_toko`, `latitude`, `longitude`, `alamat`, `no_telp`, `rating`, `harga_per_kg`, `jam_buka`, `status_buka`, `icon_type`) VALUES
(1, 'KosanFresh Laundry', 'uploads/mitra_1.png', -8.58260000, 116.10750000, 'Jl. Pejanggik No. 12, Mataram (Pusat Kota)', '081234567890', 4.9, 7000, 'Open until 21:00', 1, 'kiloan'),
(2, 'Express Shine', 'uploads/mitra_2.png', -8.59020000, 116.11540000, 'Jl. Panca Usaha No. 45, Cilinaya, Mataram', '081234567891', 4.7, 9500, 'Open 24 Hours', 1, 'express'),
(3, 'Sahabat Kos Laundry', 'uploads/mitra_1.png', -8.58300000, 116.09500000, 'Jl. Airlangga No. 8, Mataram', '081234567892', 4.5, 6000, 'Open until 20:00', 1, 'kiloan'),
(4, 'EcoWash Pure', 'uploads/mitra_2.png', -8.60150000, 116.11300000, 'Jl. Bung Karno, Pagutan, Mataram', '081234567893', 4.8, 8500, 'Open until 22:00', 1, 'eco'),
(5, 'FreshClean Laundry', 'uploads/mitra_1.png', -8.56200000, 116.07700000, 'Jl. Saleh Sungkar, Ampenan, Mataram', '081234567894', 4.6, 7500, 'Open until 21:30', 1, 'satuan');

