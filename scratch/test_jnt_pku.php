<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_GET['action'] = 'cost';
$_POST['origin'] = '256';
$_POST['destination'] = '201';
$_POST['city_name'] = 'KOTA PEKANBARU';
$_POST['weight'] = 1000;
$_POST['courier'] = 'jnt';
include 'api/rajaongkir.php';

