<?php
// index.php - Front Controller / Router (pintu masuk aplikasi)
session_start();
require_once __DIR__ . '/config/Helper.php';

$routes = [
    'auth'       => ['AuthController', 'login'],
    'kategori'   => ['KategoriController', 'index'],
    'donatur'    => ['DonaturController', 'index'],
    'penerimaan' => ['PenerimaanController', 'index'],
    'penyaluran' => ['PenyaluranController', 'index'],
    'jurnal'     => ['JurnalController', 'index'],
    'lpd'        => ['LpdController', 'index'],
];

$page = $_GET['page'] ?? 'jurnal';
if (!is_string($page) || !isset($routes[$page])) {
    http_response_code(404);
    die('Halaman tidak ditemukan.');
}

[$class, $defaultAction] = $routes[$page];
require_once __DIR__ . '/controllers/' . $class . '.php';

$action = $_GET['action'] ?? $defaultAction;
// Whitelist: hanya method public milik controller itu sendiri yang boleh dipanggil dari URL
$allowed = array_diff(get_class_methods($class), get_class_methods('Controller'), ['__construct']);
if (!is_string($action) || !in_array($action, $allowed, true)) {
    http_response_code(404);
    die('Aksi tidak ditemukan.');
}
(new $class())->$action();
