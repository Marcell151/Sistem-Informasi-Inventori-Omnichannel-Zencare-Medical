@echo off
set MYSQL_DUMP="c:\xampp\mysql\bin\mysqldump.exe"
set DB_NAME=db_inventory
set DB_USER=root

echo Dumping schema (bersih)...
%MYSQL_DUMP% -u %DB_USER% -d %DB_NAME% > "database\db_inventory.sql"

echo Dumping lengkap...
%MYSQL_DUMP% -u %DB_USER% %DB_NAME% > "database\db_inventory (lengkap).sql"

echo Dumping isi (master data only)...
%MYSQL_DUMP% -u %DB_USER% %DB_NAME% --ignore-table=%DB_NAME%.penjualan --ignore-table=%DB_NAME%.detail_penjualan --ignore-table=%DB_NAME%.penerimaan_barang --ignore-table=%DB_NAME%.detail_penerimaan --ignore-table=%DB_NAME%.kartu_stok > "database\db_inventory (isi).sql"

echo Selesai.
