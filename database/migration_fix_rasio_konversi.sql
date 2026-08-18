-- ==============================================================================
-- FILE: database/migration_fix_rasio_konversi.sql
-- TUJUAN: Memastikan semua produk_variasi memiliki rasio_konversi yang valid (>= 1)
-- Jalankan script ini di phpMyAdmin jika E-Commerce menampilkan stok 0 padahal ada stok.
-- ==============================================================================

-- 1. Perbaiki rasio_konversi yang NULL atau 0 menjadi 1 (artinya 1 satuan besar = 1 satuan kecil)
UPDATE produk_variasi 
SET rasio_konversi = 1 
WHERE rasio_konversi IS NULL OR rasio_konversi = 0;

-- 2. Pastikan satuan_kecil & satuan_besar tidak kosong
UPDATE produk_variasi SET satuan_kecil = 'Pcs' WHERE satuan_kecil IS NULL OR satuan_kecil = '';
UPDATE produk_variasi SET satuan_besar = 'Box'  WHERE satuan_besar IS NULL OR satuan_besar = '';

-- 3. Pastikan harga_jual_kecil & harga_jual_besar tidak NULL
UPDATE produk_variasi SET harga_jual_kecil = 0 WHERE harga_jual_kecil IS NULL;
UPDATE produk_variasi SET harga_jual_besar = 0 WHERE harga_jual_besar IS NULL;

-- 4. Pastikan tampil_di_online = 1 untuk semua produk aktif (jika ingin semua tampil)
-- UPDATE produk_variasi SET tampil_di_online = 1 WHERE is_active = 1;
-- (Uncomment baris di atas jika semua produk tidak tampil di E-Commerce)

-- 5. Cek ringkasan kondisi data saat ini:
SELECT 
    v.id, 
    CONCAT(i.nama_produk, ' - ', v.nama_variasi) AS nama,
    v.satuan_kecil, v.satuan_besar, v.rasio_konversi,
    v.harga_jual_kecil, v.harga_jual_besar,
    v.tampil_di_online,
    COALESCE(sc.stok, 0) AS stok_pcs,
    FLOOR(COALESCE(sc.stok, 0) / GREATEST(v.rasio_konversi, 1)) AS stok_box
FROM produk_variasi v
JOIN produk_induk i ON i.id = v.id_produk_induk
LEFT JOIN stok_cabang sc ON sc.id_variasi = v.id AND sc.id_cabang = 1
WHERE v.is_active = 1 AND i.is_active = 1
ORDER BY i.nama_produk;
