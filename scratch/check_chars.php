<?php
$c = file_get_contents('scratch/run_full_opname_simulation.php');
if (preg_match_all('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $c, $m)) {
    print_r($m[0]);
}

