/* =====================================================
   DATABASE
   ===================================================== */
CREATE DATABASE IF NOT EXISTS manajemengudang;
USE manajemengudang;

/* =====================================================
   TABEL MASTER
   ===================================================== */

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);

INSERT INTO users (username, password) VALUES
('admin', '$2y$10$y0PwCPJf4uXjW6dkqJdIieDz.XgChB4G5HgM0WsmhOrJ3eEFvS5bC');

CREATE TABLE master_administrasi (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    kode_admin VARCHAR(20) UNIQUE,
    nama_admin VARCHAR(100)
);

CREATE TABLE master_vendor (
    id_vendor INT AUTO_INCREMENT PRIMARY KEY,
    kode_vendor VARCHAR(20) UNIQUE,
    nama_vendor VARCHAR(100),
    alamat TEXT,
    telepon VARCHAR(20)
);

INSERT INTO master_vendor (nama_vendor, alamat, telepon)
VALUES ('Asus','-','-'), ('HP','-','-'), ('Lenovo','-','-');

CREATE TABLE master_barang_elektronik (
    id_barang INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20) UNIQUE,
    nama_barang VARCHAR(150),
    spesifikasi VARCHAR(50),
    kategori VARCHAR(50),
    satuan VARCHAR(30) DEFAULT 'Unit',
    stok INT DEFAULT 0
);

CREATE TABLE master_driver (
    id_driver INT AUTO_INCREMENT PRIMARY KEY,
    kode_driver VARCHAR(20) UNIQUE,
    nama_driver VARCHAR(100),
    no_hp VARCHAR(30),
    alamat TEXT
);

CREATE TABLE master_gudang (
    id_gudang INT AUTO_INCREMENT PRIMARY KEY,
    kode_gudang VARCHAR(20) UNIQUE,
    nama_gudang VARCHAR(100),
    lokasi TEXT
);

/* =====================================================
   TABEL TRANSAKSI
   ===================================================== */

CREATE TABLE trx_barang_masuk (
    id_masuk INT AUTO_INCREMENT PRIMARY KEY,
    kode_masuk VARCHAR(20),
    tanggal DATE,
    id_admin INT,
    id_barang INT,
    id_vendor INT,
    jumlah INT,
    FOREIGN KEY (id_admin) REFERENCES master_administrasi(id_admin),
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang),
    FOREIGN KEY (id_vendor) REFERENCES master_vendor(id_vendor)
);

CREATE TABLE trx_barang_pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30),
    tanggal DATE,
    id_admin INT,
    id_barang INT,
    id_gudang INT,
    jumlah INT,
    serial_number TEXT,
    FOREIGN KEY (id_admin) REFERENCES master_administrasi(id_admin),
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang),
    FOREIGN KEY (id_gudang) REFERENCES master_gudang(id_gudang)
);

CREATE TABLE trx_surat_jalan (
    id_surat INT AUTO_INCREMENT PRIMARY KEY,
    kode_surat VARCHAR(30),
    tanggal DATE,
    id_driver INT,
    id_gudang INT,
    keterangan TEXT,
    FOREIGN KEY (id_driver) REFERENCES master_driver(id_driver),
    FOREIGN KEY (id_gudang) REFERENCES master_gudang(id_gudang)
);

CREATE TABLE trx_retur (
    id_retur INT AUTO_INCREMENT PRIMARY KEY,
    kode_retur VARCHAR(30),
    tanggal DATE,
    id_barang INT,
    jumlah INT,
    alasan TEXT,
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang)
);

CREATE TABLE trx_berita_serah_terima (
    id_serah INT AUTO_INCREMENT PRIMARY KEY,
    kode_basterima VARCHAR(30),
    tanggal DATE,
    penerima VARCHAR(100),
    id_barang INT,
    jumlah INT,
    sn_perangkat TEXT,
    FOREIGN KEY (id_barang) REFERENCES master_barang_elektronik(id_barang)
);

/* =====================================================
   VIEW LAPORAN
   ===================================================== */

CREATE OR REPLACE VIEW v_barang_masuk_final AS
SELECT
    m.id_masuk,
    m.kode_masuk,
    m.tanggal,
    m.id_barang,
    m.id_vendor,
    m.id_admin,
    m.jumlah AS jumlah_masuk,
    IFNULL(SUM(r.jumlah),0) AS jumlah_retur,
    (m.jumlah - IFNULL(SUM(r.jumlah),0)) AS sisa_barang
FROM trx_barang_masuk m
LEFT JOIN trx_retur r ON m.id_barang = r.id_barang
GROUP BY m.id_masuk;

/* =====================================================
   TRIGGER
   ===================================================== */


DELIMITER //

CREATE TRIGGER trg_kode_admin
BEFORE INSERT ON master_administrasi
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE nomor INT;
    SELECT kode_admin INTO lastKode FROM master_administrasi ORDER BY id_admin DESC LIMIT 1;
    IF lastKode IS NULL THEN
        SET NEW.kode_admin = 'ADM0000001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET NEW.kode_admin = CONCAT('ADM', LPAD(nomor,7,'0'));
    END IF;
END//

CREATE TRIGGER trg_kode_barang
BEFORE INSERT ON master_barang_elektronik
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE nomor INT;
    SELECT kode_barang INTO lastKode FROM master_barang_elektronik ORDER BY id_barang DESC LIMIT 1;
    IF lastKode IS NULL THEN
        SET NEW.kode_barang = 'BRG0000001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,4) AS UNSIGNED) + 1;
        SET NEW.kode_barang = CONCAT('BRG', LPAD(nomor,7,'0'));
    END IF;
END//

CREATE TRIGGER trg_kode_barang_masuk
BEFORE INSERT ON trx_barang_masuk
FOR EACH ROW
BEGIN
    DECLARE lastKode VARCHAR(20);
    DECLARE nomor INT;
    SELECT kode_masuk INTO lastKode FROM trx_barang_masuk ORDER BY id_masuk DESC LIMIT 1;
    IF lastKode IS NULL THEN
        SET NEW.kode_masuk = 'EN0000001';
    ELSE
        SET nomor = CAST(SUBSTRING(lastKode,3) AS UNSIGNED) + 1;
        SET NEW.kode_masuk = CONCAT('EN', LPAD(nomor,7,'0'));
    END IF;
END//

CREATE TRIGGER trg_stok_barang_masuk
AFTER INSERT ON trx_barang_masuk
FOR EACH ROW
BEGIN
    UPDATE master_barang_elektronik
    SET stok = stok + NEW.jumlah
    WHERE id_barang = NEW.id_barang;
END//

CREATE TRIGGER trg_retur_validasi
BEFORE INSERT ON trx_retur
FOR EACH ROW
BEGIN
    DECLARE sisa INT;
    SELECT stok INTO sisa FROM master_barang_elektronik WHERE id_barang = NEW.id_barang;
    IF NEW.jumlah > sisa THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Jumlah retur melebihi stok tersedia';
    END IF;
END//

CREATE TRIGGER trg_retur_kurangi_stok
AFTER INSERT ON trx_retur
FOR EACH ROW
BEGIN
    UPDATE master_barang_elektronik
    SET stok = stok - NEW.jumlah
    WHERE id_barang = NEW.id_barang;
END//

DELIMITER ;
