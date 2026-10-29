<?php
$f = fopen('C:/Users/Marcell/.gemini/antigravity-ide/brain/13d0fe16-dde0-4c4c-837c-637a5d24e784/.system_generated/logs/transcript_full.jsonl', 'r');
while (($line = fgets($f)) !== false) {
    if (strpos($line, 'transkrip rekaman bimbingan') !== false) {
        $json = json_decode($line, true);
        file_put_contents('scratch/full_bimbingan_prompt.txt', $json['content']);
        echo "Found and written!\n";
        break;
    }
}
fclose($f);
