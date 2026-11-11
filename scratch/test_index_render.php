<?php
session_start();
$_SESSION["user_id"] = 1;
$_SESSION["role"] = "superadmin";
$_SESSION["nama_lengkap"] = "Superadmin IT";
ob_start();
include "index.php";
$html = ob_get_clean();
$pos = strpos($html, "</aside>");
echo substr($html, $pos, 400);
