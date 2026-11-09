<?php
// controllers/KategoriController.php - MODUL 1 (Nazwa): Kategori Dana ZIS
// STATUS: index() jadi. TODO Nazwa: create/edit/delete. Tiru pola JurnalController.
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Kategori.php';

class KategoriController extends Controller
{
    public function __construct()
    {
        $this->guard();
    }

    public function index(): void
    {
        $this->view('kategori/index', ['rows' => (new Kategori())->getAll(), 'title' => 'Kategori Dana']);
    }
}
