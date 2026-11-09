<?php
// controllers/AuthController.php - login/logout (BCRYPT + session_regenerate_id)
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    public function login(): void
    {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            $user = (new User())->findByUsername(trim($_POST['username'] ?? ''));
            if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
                session_regenerate_id(true); // cegah session fixation
                $_SESSION['user'] = [
                    'id' => (int)$user['id'], 'username' => $user['username'],
                    'nama' => $user['nama_lengkap'], 'role' => $user['role'],
                ];
                redirect('index.php?page=jurnal');
            }
            $error = 'Username atau password salah.'; // pesan sama agar tidak membocorkan akun
        }
        require __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void
    {
        $this->requirePost();
        $_SESSION = [];
        session_destroy();
        redirect('index.php?page=auth&action=login');
    }
}
