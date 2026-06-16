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
