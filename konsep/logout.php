<?php
// File: logout.php
session_start();
$isPelanggan = isset($_SESSION['role']) && $_SESSION['role'] === 'pelanggan';

session_unset();
session_destroy();

if ($isPelanggan) {
    header('Location: zencare_store.php');
} else {
    header('Location: login.php');
}
exit;
?>

