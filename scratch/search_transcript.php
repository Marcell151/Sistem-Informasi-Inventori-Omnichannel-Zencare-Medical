<?php
$transcriptFile = 'C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784\.system_generated\logs\transcript.jsonl';
if (!file_exists($transcriptFile)) {
    echo "Transcript not found\n";
    exit;
}

$handle = fopen($transcriptFile, 'r');
$matches = [];
$lineNum = 0;
while (($line = fgets($handle)) !== false) {
    $lineNum++;
    $data = json_decode($line, true);
    if (!$data) continue;
    $content = $data['content'] ?? '';
    if (is_array($content)) $content = json_encode($content);
    if (stripos($content, 'skenario') !== false || stripos($content, 'needs_verification') !== false || stripos($content, 'satuan besar') !== false || stripos($content, 'konversi') !== false) {
        $matches[] = "Line $lineNum: " . substr(strip_tags($content), 0, 150);
    }
}
fclose($handle);
echo "Total matches: " . count($matches) . "\n";
foreach (array_slice($matches, -20) as $m) {
    echo $m . "\n";
}

