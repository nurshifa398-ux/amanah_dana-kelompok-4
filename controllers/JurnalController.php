<?php
// controllers/JurnalController.php - MODUL 4 (Maurah): CRUD Jurnal Kas Masuk/Keluar PSAK 109
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/JurnalKas.php';

class JurnalController extends Controller
{
    private JurnalKas $model;

    public function __construct()
    {
        $this->guard();
        $this->model = new JurnalKas();
    }

    // Validasi + sanitasi input; jenis dikunci saat edit ($lockJenis)
    private function ambilInput(?string $lockJenis = null): array
    {
        $d = [
            'tanggal'    => trim($_POST['tanggal'] ?? ''),
            'jenis'      => $lockJenis ?? ($_POST['jenis'] ?? ''),
            'pos_dana'   => $_POST['pos_dana'] ?? '',
            'nominal'    => (float)preg_replace('/\D/', '', $_POST['nominal'] ?? '0'),
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'sumber'     => 'manual',
            'ref_id'     => null,
        ];
        $err = null;
        if (!tanggal_valid($d['tanggal'])) {
            $err = 'Tanggal tidak valid.';
        } elseif (!in_array($d['jenis'], ['masuk', 'keluar'], true)) {
            $err = 'Jenis jurnal harus Kas Masuk atau Kas Keluar.';
        } elseif (!array_key_exists($d['pos_dana'], JurnalKas::POS)) {
            $err = 'Pos dana tidak valid.';
        } elseif ($d['nominal'] <= 0) {
            $err = 'Nominal harus lebih dari 0.';
        } elseif ($d['nominal'] >= 1e13) {
            $err = 'Nominal terlalu besar.';
        } elseif ($d['keterangan'] === '' || mb_strlen($d['keterangan']) > 255) {
            $err = 'Keterangan wajib diisi (maksimal 255 karakter).';
        }
        return [$d, $err];
    }

    public function index(): void
    {
        $f = ['dari'=>tanggal_valid($_GET['dari'] ?? '') ? $_GET['dari'] : '', 'sampai'=>tanggal_valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : '', 'pos'=>array_key_exists($_GET['pos'] ?? '', JurnalKas::POS) ? $_GET['pos'] : '', 'jenis'=>in_array($_GET['jenis'] ?? '', ['masuk','keluar'], true) ? $_GET['jenis'] : '', 'q'=>trim($_GET['q'] ?? '')];
        $this->view('jurnal/index',['rows'=>$this->model->getAll($f),'ringkasan'=>$this->model->ringkasanPos(),'f'=>$f,'title'=>'Jurnal Kas']);
    }

    public function create(): void
    {
        $err = null;
        $old = ['tanggal' => date('Y-m-d'), 'jenis' => $_GET['jenis'] ?? 'masuk'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            [$d, $err] = $this->ambilInput();
            $old = $d;
            if (!$err) {
                try {
                    $this->model->catat($d);
                    flash('Jurnal kas berhasil dicatat.');
                    redirect('index.php?page=jurnal');
                } catch (RuntimeException $ex) {
                    $err = $ex->getMessage();
                } catch (PDOException $ex) {
                    error_log($ex->getMessage());
                    $err = 'Gagal menyimpan jurnal.';
                }
            }
        }
        $this->view('jurnal/create', [
            'err' => $err, 'data' => $old, 'saldo' => $this->model->ringkasanPos(), 'title' => 'Catat Jurnal Kas',
        ]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $row = $this->model->getById($id);
        if (!$row) {
            http_response_code(404);
            die('Jurnal tidak ditemukan.');
        }
        if ($row['sumber'] !== 'manual') {
            flash('Jurnal otomatis hanya bisa diubah dari modul asalnya (' . $row['sumber'] . ').', 'err');
            redirect('index.php?page=jurnal');
        }
        $err = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_check();
            [$d, $err] = $this->ambilInput($row['jenis']);
            $row = array_merge($row, $d);
            if (!$err) {
                try {
                    $this->model->update($id, $d);
                    flash('Jurnal kas diperbarui.');
                    redirect('index.php?page=jurnal');
                } catch (RuntimeException $ex) {
                    $err = $ex->getMessage();
                } catch (PDOException $ex) {
                    error_log($ex->getMessage());
                    $err = 'Gagal memperbarui jurnal.';
                }
            }
        }
        $this->view('jurnal/edit', [
            'err' => $err, 'data' => $row, 'saldo' => $this->model->ringkasanPos(), 'title' => 'Edit Jurnal Kas',
        ]);
    }

    // Hapus via POST + CSRF
    public function delete(): void
    {
        $this->requirePost();
        try {
            $this->model->delete((int)($_POST['id'] ?? 0));
            flash('Jurnal dihapus.');
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'err');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            flash('Gagal menghapus jurnal.', 'err');
        }
        redirect('index.php?page=jurnal');
    }
}
