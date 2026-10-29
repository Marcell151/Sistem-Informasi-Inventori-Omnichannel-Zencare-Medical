<?php
$prov = json_decode(file_get_contents('cache/provinces_list.json'), true);
foreach ($prov['data'] as $p) {
    echo "ID: {$p['id']} => {$p['name']}\n";
}

