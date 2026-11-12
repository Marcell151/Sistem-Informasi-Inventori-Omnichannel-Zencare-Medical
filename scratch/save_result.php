<?php
if (isset($_POST['data'])) {
    file_put_contents(__DIR__ . '/results.txt', $_POST['data']);
    echo "OK";
}
?>

