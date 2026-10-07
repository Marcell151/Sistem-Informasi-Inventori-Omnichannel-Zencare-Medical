<?php
$files = [
    'c:/xampp/htdocs/inventory_zencare/database/db_inventory.sql',
    'c:/xampp/htdocs/inventory_zencare/database/db_inventory (isi).sql',
    'c:/xampp/htdocs/inventory_zencare/database/db_inventory (lengkap).sql'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // This is a complex regex. Let's just find the produk_variasi insert block and replace the values for Alkes.
        // Actually, replacing in SQL string is hard. It's better to dump the DB!
        echo "Found: $file\n";
    }
}
