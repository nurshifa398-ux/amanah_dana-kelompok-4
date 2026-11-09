<?php
// controllers/Controller.php - Base controller (OOP inheritance) + Session Guard
require_once __DIR__ . '/../config/Helper.php';

abstract class Controller
{
    // Session Guard: halaman terproteksi wajib login
    protected function guard(): void
    {
        if (empty($_SESSION['user'])) {
            redirect('index.php?page=auth&action=login');
        }
    }

    protected function view(string $path, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/' . $path . '.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Metode tidak diizinkan.');
        }
        csrf_check();
    }
}
