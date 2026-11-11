<?php
$lines = file(__DIR__ . '/../scratch/run_full_opname_simulation.php');
echo implode('', array_slice($lines, 140, 80));
