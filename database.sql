-- database.sql - Amanah Dana: SIA Lembaga Amil Zakat (ZIS) PSAK 109 - Kelompok 4
-- Bisa diimpor lewat phpMyAdmin atau dijalankan otomatis oleh setup.php
CREATE DATABASE IF NOT EXISTS `db_amanah_dana` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_amanah_dana`;

-- Akun login (password di-hash BCRYPT oleh setup.php)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `role` ENUM('admin','bendahara') NOT NULL DEFAULT 'bendahara'
) ENGINE=InnoDB;

-- MODUL 1 (Nazwa): Kategori Dana ZIS & Donatur
-- pos_dana = kelompok dana menurut PSAK 109
CREATE TABLE IF NOT EXISTS `kategori_dana` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `pos_dana` ENUM('zakat','infak','amil','nonhalal') NOT NULL,
  `keterangan` VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `donatur` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `jenis` ENUM('individu','lembaga') NOT NULL DEFAULT 'individu',
  `telepon` VARCHAR(20) NULL,
  `alamat` VARCHAR(255) NULL
) ENGINE=InnoDB;

-- MODUL 2 (Shabila): Penerimaan donasi & kuitansi
CREATE TABLE IF NOT EXISTS `penerimaan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_kuitansi` VARCHAR(30) NOT NULL UNIQUE,
  `tanggal` DATE NOT NULL,
  `donatur_id` INT NOT NULL,
  `kategori_id` INT NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `keterangan` VARCHAR(255) NULL,
  FOREIGN KEY (`donatur_id`)  REFERENCES `donatur`(`id`)       ON DELETE RESTRICT,
  FOREIGN KEY (`kategori_id`) REFERENCES `kategori_dana`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- MODUL 3 (Nur Shifa): Penyaluran kepada 8 asnaf / mustahik
CREATE TABLE IF NOT EXISTS `asnaf` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `mustahik` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(20) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `asnaf_id` INT NOT NULL,
  `alamat` VARCHAR(255) NULL,
  FOREIGN KEY (`asnaf_id`) REFERENCES `asnaf`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `penyaluran` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_bukti` VARCHAR(30) NOT NULL UNIQUE,
  `tanggal` DATE NOT NULL,
  `mustahik_id` INT NOT NULL,
  `pos_dana` ENUM('zakat','infak','amil','nonhalal') NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `keterangan` VARCHAR(255) NULL,
  FOREIGN KEY (`mustahik_id`) REFERENCES `mustahik`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- MODUL 4 (Maurah): Jurnal kas masuk/keluar per pos dana
--   jenis = masuk  -> Debit Kas & Bank,  Kredit Dana [pos]
--   jenis = keluar -> Debit Dana [pos],  Kredit Kas & Bank
--   sumber 'penerimaan'/'penyaluran' = diposting otomatis oleh modul lain (ref_id = id transaksi asal)
CREATE TABLE IF NOT EXISTS `jurnal_kas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `no_jurnal` VARCHAR(30) NOT NULL UNIQUE,
  `tanggal` DATE NOT NULL,
  `jenis` ENUM('masuk','keluar') NOT NULL,
  `pos_dana` ENUM('zakat','infak','amil','nonhalal') NOT NULL,
  `nominal` DECIMAL(15,2) NOT NULL,
  `keterangan` VARCHAR(255) NOT NULL,
  `sumber` ENUM('manual','penerimaan','penyaluran') NOT NULL DEFAULT 'manual',
  `ref_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tanggal` (`tanggal`),
  INDEX `idx_pos` (`pos_dana`)
) ENGINE=InnoDB;

-- MODUL 4 (Maurah): Laporan Perubahan Dana (LPD) - snapshot per periode
CREATE TABLE IF NOT EXISTS `lpd` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `periode_awal` DATE NOT NULL,
  `periode_akhir` DATE NOT NULL,
  `status` ENUM('draft','final') NOT NULL DEFAULT 'draft',
  `catatan` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `lpd_detail` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `lpd_id` INT NOT NULL,
  `pos_dana` ENUM('zakat','infak','amil','nonhalal') NOT NULL,
  `saldo_awal` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `penerimaan` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `penyaluran` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `saldo_akhir` DECIMAL(15,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (`lpd_id`) REFERENCES `lpd`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== DATA AWAL (INSERT IGNORE agar aman dijalankan ulang) =====
INSERT IGNORE INTO `kategori_dana` (kode,nama,pos_dana,keterangan) VALUES
('KAT-ZM','Zakat Maal','zakat','Zakat harta'),
('KAT-ZF','Zakat Fitrah','zakat','Zakat fitrah Ramadan'),
('KAT-INF','Infak Umum','infak','Infak tidak terikat'),
('KAT-SDQ','Sedekah','infak','Sedekah umum'),
('KAT-NH','Dana Nonhalal','nonhalal','Mis. jasa giro bank');

INSERT IGNORE INTO `donatur` (kode,nama,jenis,telepon,alamat) VALUES
('DNT-001','Budi Santoso','individu','081234567801','Cirebon'),
('DNT-002','PT Maju Jaya','lembaga','0231555010','Kota Cirebon'),
('DNT-003','Hj. Aminah','individu','081234567803','Kuningan');

-- 8 asnaf (QS. At-Taubah: 60)
INSERT IGNORE INTO `asnaf` (nama) VALUES
('Fakir'),('Miskin'),('Amil'),('Muallaf'),('Riqab'),('Gharimin'),('Fisabilillah'),('Ibnu Sabil');

INSERT IGNORE INTO `mustahik` (kode,nama,asnaf_id,alamat) VALUES
('MST-001','Ibu Sari',(SELECT id FROM asnaf WHERE nama='Fakir'),'Cirebon'),
('MST-002','Pak Joko',(SELECT id FROM asnaf WHERE nama='Miskin'),'Indramayu');

INSERT IGNORE INTO `penerimaan` (no_kuitansi,tanggal,donatur_id,kategori_id,nominal,keterangan) VALUES
('KWT-2026-0001','2026-10-05',(SELECT id FROM donatur WHERE kode='DNT-001'),(SELECT id FROM kategori_dana WHERE kode='KAT-ZM'),5000000,'Zakat maal'),
('KWT-2026-0002','2026-10-12',(SELECT id FROM donatur WHERE kode='DNT-002'),(SELECT id FROM kategori_dana WHERE kode='KAT-INF'),10000000,'Infak perusahaan'),
('KWT-2026-0003','2026-10-20',(SELECT id FROM donatur WHERE kode='DNT-003'),(SELECT id FROM kategori_dana WHERE kode='KAT-ZM'),2500000,'Zakat maal'),
('KWT-2026-0004','2026-10-25',(SELECT id FROM donatur WHERE kode='DNT-001'),(SELECT id FROM kategori_dana WHERE kode='KAT-SDQ'),1000000,'Sedekah');

INSERT IGNORE INTO `penyaluran` (no_bukti,tanggal,mustahik_id,pos_dana,nominal,keterangan) VALUES
('BKK-2026-0001','2026-10-28',(SELECT id FROM mustahik WHERE kode='MST-001'),'zakat',1500000,'Bantuan kebutuhan pokok'),
('BKK-2026-0002','2026-11-02',(SELECT id FROM mustahik WHERE kode='MST-002'),'infak',750000,'Bantuan modal usaha kecil');

-- Jurnal kas: sebagian diposting dari penerimaan/penyaluran, satu entri manual
INSERT IGNORE INTO `jurnal_kas` (no_jurnal,tanggal,jenis,pos_dana,nominal,keterangan,sumber,ref_id) VALUES
('JKM-202610-0001','2026-10-05','masuk','zakat',5000000,'Penerimaan KWT-2026-0001','penerimaan',(SELECT id FROM penerimaan WHERE no_kuitansi='KWT-2026-0001')),
('JKM-202610-0002','2026-10-12','masuk','infak',10000000,'Penerimaan KWT-2026-0002','penerimaan',(SELECT id FROM penerimaan WHERE no_kuitansi='KWT-2026-0002')),
('JKM-202610-0003','2026-10-20','masuk','zakat',2500000,'Penerimaan KWT-2026-0003','penerimaan',(SELECT id FROM penerimaan WHERE no_kuitansi='KWT-2026-0003')),
('JKM-202610-0004','2026-10-25','masuk','infak',1000000,'Penerimaan KWT-2026-0004','penerimaan',(SELECT id FROM penerimaan WHERE no_kuitansi='KWT-2026-0004')),
('JKK-202610-0001','2026-10-28','keluar','zakat',1500000,'Penyaluran BKK-2026-0001','penyaluran',(SELECT id FROM penyaluran WHERE no_bukti='BKK-2026-0001')),
('JKK-202611-0001','2026-11-02','keluar','infak',750000,'Penyaluran BKK-2026-0002','penyaluran',(SELECT id FROM penyaluran WHERE no_bukti='BKK-2026-0002')),
('JKM-202611-0001','2026-11-01','masuk','nonhalal',150000,'Jasa giro bank bulan Oktober','manual',NULL);
