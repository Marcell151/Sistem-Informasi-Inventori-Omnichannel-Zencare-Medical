<?php
$f = 'C:/Users/Marcell/.gemini/antigravity-ide/brain/13d0fe16-dde0-4c4c-837c-637a5d24e784/.system_generated/logs/transcript.jsonl';
$handle = fopen($f, 'r');
$i = 0;
while (($line = fgets($handle)) !== false) {
    if (strpos($line, '"type":"USER_INPUT"') !== false) {
        $i++;
        $data = json_decode($line, true);
        if ($data && isset($data['content'])) {
            if (in_array($i, [18, 19, 24, 43])) {
                echo "============================== USER " . $i . " ==============================\n";
                echo $data['content'] . "\n\n";
            }
        }
    }
}
fclose($handle);
