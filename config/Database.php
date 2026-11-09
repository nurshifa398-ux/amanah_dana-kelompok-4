<?php
// config/Database.php - Koneksi PDO Strict Mode (singleton)
class Database
{
    private static string $host = 'localhost';
    private static string $port = '3306';
    private static string $db   = 'db_amanah_dana';
    private static string $user = 'root';
    private static string $pass = '';
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            try {
                $dsn = 'mysql:host=' . self::$host . ';port=' . self::$port . ';dbname=' . self::$db . ';charset=utf8mb4';
                self::$pdo = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement asli: tutup celah SQL Injection
                ]);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                die('Koneksi database gagal. Pastikan MySQL aktif dan setup.php sudah dijalankan.');
            }
        }
        return self::$pdo;
    }

    // Dipakai setup.php (koneksi tanpa memilih database)
    public static function config(): array
    {
        return ['host' => self::$host, 'port' => self::$port, 'user' => self::$user, 'pass' => self::$pass];
    }
}
