-- SQL untuk membuat tabel 'users' yang kompatibel dengan Google Login
-- Silakan impor berkas ini ke phpMyAdmin Anda pada database 'laundry_db'

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) DEFAULT NULL, -- Kosong jika mendaftar menggunakan Google
  `google_id` VARCHAR(255) UNIQUE DEFAULT NULL, -- ID unik dari akun Google
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SQL untuk membuat tabel 'admins'
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `nama` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Menyisipkan data admin default (username: admin, password: admin123)
INSERT INTO `admins` (`username`, `nama`, `password`) VALUES
('admin', 'Administrator Utama', '$2y$10$6CA1WvuEc8Vjr0.WEMoSKOR7Nna5zmIwvGbUacApYqA4Rupn7BMF6')
ON DUPLICATE KEY UPDATE `username`=`username`;
