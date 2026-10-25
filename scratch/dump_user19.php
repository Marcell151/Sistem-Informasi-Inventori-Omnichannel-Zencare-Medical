<?php
$f = 'C:/Users/Marcell/.gemini/antigravity-ide/brain/13d0fe16-dde0-4c4c-837c-637a5d24e784/.system_generated/logs/transcript_full.jsonl';
$handle = fopen($f, 'r');
$i = 0;
while (($line = fgets($handle)) !== false) {
    if (strpos($line, '"type":"USER_INPUT"') !== false) {
        $i++;
        if ($i === 43) {
            $data = json_decode($line, true);
            file_put_contents('scratch/user43_full.txt', $data['content']);
            echo "Wrote user 43 full content (" . strlen($data['content']) . " bytes)\n";
            break;
        }
    }
}
fclose($handle);
