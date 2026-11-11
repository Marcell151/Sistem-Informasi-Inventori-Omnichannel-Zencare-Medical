<?php
$content = file_get_contents(__DIR__ . '/../inventori/dokumentasi_testing.php');
preg_match_all('/(<div[^>]*class="[^"]*(?:browser|window|chrome|mockup)[^"]*"[^>]*>)/i', $content, $m);
print_r($m[0]);

preg_match_all('/(localhost[^\n<]+)/i', $content, $m2);
print_r($m2[0]);

