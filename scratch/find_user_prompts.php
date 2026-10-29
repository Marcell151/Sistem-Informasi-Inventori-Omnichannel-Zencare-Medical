<?php
$h = fopen('C:\Users\Marcell\.gemini\antigravity\brain\13d0fe16-dde0-4c4c-837c-637a5d24e784\.system_generated\logs\transcript.jsonl', 'r');
$lineNum = 0;
while (($l = fgets($h)) !== false) {
    $lineNum++;
    $d = json_decode($l, true);
    if (!$d) continue;
    if (($d['type'] ?? '') === 'USER_INPUT') {
        echo "Line $lineNum: " . substr($d['content'] ?? '', 0, 200) . "\n";
    }
}
fclose($h);

