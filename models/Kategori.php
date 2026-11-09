<?php
// models/Kategori.php - MODUL 1 (Nazwa): Kategori Dana ZIS
// STATUS: getAll() jadi. TODO Nazwa: getById, create, update, delete (tolak hapus jika dipakai penerimaan).
require_once __DIR__ . '/../config/Database.php';

class Kategori
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(): array
    {
        return $this->db->query('SELECT * FROM kategori_dana ORDER BY pos_dana, nama')->fetchAll();
    }
}
