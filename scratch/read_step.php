<?php
$f = file('C:/Users/Marcell/.gemini/antigravity/brain/13d0fe16-dde0-4c4c-837c-637a5d24e784/.system_generated/logs/transcript.jsonl');
foreach ($f as $line) {
    $j = json_decode($line, true);
    if (isset($j['step_index']) && $j['step_index'] == 12394) {
        echo "=== STEP 12394 ===\n";
        echo $j['content'] . "\n";
    }
}

