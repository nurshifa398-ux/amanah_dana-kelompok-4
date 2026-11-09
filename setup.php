<?php
// setup.php - auto-migrator 1-klik: membuat database, tabel, data awal, dan akun login (BCRYPT).
// Jalankan dari terminal (php setup.php) atau buka lewat browser. HAPUS file ini setelah deploy ke hosting.
require_once __DIR__ . '/config/Database.php';

$cli = PHP_SAPI === 'cli';
$out = function (string $m) use ($cli) {
    echo $cli ? $m . PHP_EOL : '<p>' . htmlspecialchars($m, ENT_QUOTES, 'UTF-8') . '</p>';
};
if (!$cli) {
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Setup</title><body style="font-family:sans-serif;max-width:640px;margin:40px auto">';
}

try {
    $c = Database::config();
    $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};charset=utf8mb4", $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec(file_get_contents(__DIR__ . '/database.sql'));
    $out('Database dan tabel berhasil dibuat.');

    $db = Database::connect();
    $stmt = $db->prepare(
        'INSERT INTO users (username, password, nama_lengkap, role) VALUES (:u, :p, :n, :r)
         ON DUPLICATE KEY UPDATE password = VALUES(password)'
    );
    foreach ([
        ['admin', 'admin123', 'Administrator', 'admin'],
        ['bendahara', 'bendahara123', 'Bendahara Amanah Dana', 'bendahara'],
    ] as [$u, $pw, $n, $r]) {
        $stmt->execute([':u' => $u, ':p' => password_hash($pw, PASSWORD_BCRYPT), ':n' => $n, ':r' => $r]);
    }
    $out('Akun dibuat: admin / admin123 dan bendahara / bendahara123.');
    $out($cli ? 'Jalankan: php -S localhost:8000 lalu buka http://localhost:8000' : 'Selesai. Buka index.php untuk login, lalu hapus setup.php.');
} catch (Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    $out('Setup gagal. Pastikan MySQL aktif dan kredensial di config/Database.php benar.');
}
