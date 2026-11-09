<?php
// controllers/LpdController.php - MODUL 4 (Maurah): CRUD Laporan Perubahan Dana (LPD)
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Lpd.php';

class LpdController extends Controller
{
    private Lpd $model;

    public function __construct()
    {
        $this->guard();
        $this->model = new Lpd();
    }

    private function ambilInput(): array
    {
        $d = [
            'periode_awal'  => trim($_POST['periode_awal'] ?? ''),
            'periode_akhir' => trim($_POST['periode_akhir'] ?? ''),
            'status'        => $_POST['status'] ?? 'draft',
            'catatan'       => trim($_POST['catatan'] ?? ''),
        ];
        $err = null;
        if (!tanggal_valid($d['periode_awal']) || !tanggal_valid($d['periode_akhir'])) {
            $err = 'Periode laporan tidak valid.';
        } elseif ($d['periode_awal'] > $d['periode_akhir']) {
            $err = 'Tanggal awal tidak boleh setelah tanggal akhir.';
        } elseif (!in_array($d['status'], ['draft', 'final'], true)) {
            $err = 'Status tidak valid.';
        } elseif (mb_strlen($d['catatan']) > 255) {
            $err = 'Catatan maksimal 255 karakter.';
        }
        return [$d, $err];
    }

    public function index(): void
    {
        $this->view('lpd/index', ['rows' => $this->model->getAll(), 'title' => 'Laporan Perubahan Dana']);
    }

    // Detail laporan (siap cetak)
    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $lpd = $this->model->getById($id);
        if (!$lpd) {
            http_response_code(404);
            die('Laporan tidak ditemukan.');
        }
        $this->view('lpd/show', ['lpd' => $lpd, 'detail' => $this->model->getDetail($id), 'title' => 'Detail LPD']);
    }

    public function create(): void
    {
        $err = null;
        $data = ['periode_awal' => date('Y-m-01'), 'periode_akhir' => date('Y-m-d'), 'status' => 'draft', 'catatan' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            [$d, $err] = $this->ambilInput();
            $data = $d;
            if (!$err) {
                try {
                    $id = $this->model->create($d);
                    flash('Laporan LPD berhasil dibuat dari data jurnal.');
                    redirect('index.php?page=lpd&action=show&id=' . $id);
                } catch (PDOException $ex) {
                    error_log($ex->getMessage());
                    $err = 'Gagal membuat laporan.';
                }
            }
        }
        $this->view('lpd/create', ['err' => $err, 'data' => $data, 'title' => 'Buat LPD']);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $lpd = $this->model->getById($id);
        if (!$lpd) {
            http_response_code(404);
            die('Laporan tidak ditemukan.');
        }
        if ($lpd['status'] === 'final') {
            flash('Laporan FINAL tidak dapat diubah.', 'err');
            redirect('index.php?page=lpd');
        }
        $err = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            [$d, $err] = $this->ambilInput();
            $lpd = array_merge($lpd, $d);
            if (!$err) {
                try {
                    $this->model->update($id, $d);
                    flash('Laporan diperbarui dan dihitung ulang dari jurnal.');
                    redirect('index.php?page=lpd&action=show&id=' . $id);
                } catch (RuntimeException $ex) {
                    $err = $ex->getMessage();
                } catch (PDOException $ex) {
                    error_log($ex->getMessage());
                    $err = 'Gagal memperbarui laporan.';
                }
            }
        }
        $this->view('lpd/edit', ['err' => $err, 'data' => $lpd, 'id' => $id, 'title' => 'Edit LPD']);
    }

    public function delete(): void
    {
        $this->requirePost();
        try {
            $this->model->delete((int)($_POST['id'] ?? 0));
            flash('Laporan dihapus.');
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'err');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            flash('Gagal menghapus laporan.', 'err');
        }
        redirect('index.php?page=lpd');
    }
}
