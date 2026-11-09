<?php
// models/JurnalKas.php - MODUL 4 (Maurah): Jurnal Kas Masuk/Keluar PSAK 109
// Jurnal otomatis dari modul lain: panggil catat() dengan sumber 'penerimaan' / 'penyaluran'.
require_once __DIR__ . '/../config/Database.php';

class JurnalKas
{
    private PDO $db;

    public const POS = [
        'zakat'    => 'Dana Zakat',
        'infak'    => 'Dana Infak/Sedekah',
        'amil'     => 'Dana Amil',
        'nonhalal' => 'Dana Nonhalal',
    ];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Jalankan $fn dalam transaksi; aman dipanggil di dalam transaksi modul lain (tidak nested)
    private function inTx(callable $fn)
    {
        $own = !$this->db->inTransaction();
        if ($own) {
            $this->db->beginTransaction();
        }
        try {
            $r = $fn();
            if ($own) {
                $this->db->commit();
            }
            return $r;
        } catch (Throwable $e) {
            if ($own && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // READ ALL dengan filter periode, pos dana, jenis, dan kata kunci
    public function getAll(array $f = []): array
    {
        $sql = 'SELECT * FROM jurnal_kas WHERE 1=1';
        $p = [];
        if (!empty($f['dari']))   { $sql .= ' AND tanggal >= :dari';   $p[':dari'] = $f['dari']; }
        if (!empty($f['sampai'])) { $sql .= ' AND tanggal <= :sampai'; $p[':sampai'] = $f['sampai']; }
        if (!empty($f['pos']))    { $sql .= ' AND pos_dana = :pos';    $p[':pos'] = $f['pos']; }
        if (!empty($f['jenis']))  { $sql .= ' AND jenis = :jenis';     $p[':jenis'] = $f['jenis']; }
        if (!empty($f['q'])) {
            // placeholder harus unik: PDO native prepare tidak boleh memakai nama yang sama dua kali
            $sql .= ' AND (keterangan LIKE :q1 OR no_jurnal LIKE :q2)';
            $p[':q1'] = '%' . $f['q'] . '%';
            $p[':q2'] = '%' . $f['q'] . '%';
        }
        $sql .= ' ORDER BY tanggal DESC, id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    // READ ONE
    public function getById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM jurnal_kas WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Saldo kas satu pos dana = total masuk - total keluar
    public function saldoPos(string $pos): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN jenis='masuk' THEN nominal ELSE -nominal END),0)
             FROM jurnal_kas WHERE pos_dana = :pos"
        );
        $stmt->execute([':pos' => $pos]);
        return (float)$stmt->fetchColumn();
    }

    // Untuk dashboard: saldo, total masuk, total keluar semua pos dana
    public function ringkasanPos(): array
    {
        $out = [];
        foreach (self::POS as $k => $nama) {
            $out[$k] = ['nama' => $nama, 'masuk' => 0.0, 'keluar' => 0.0, 'saldo' => 0.0];
        }
        $rows = $this->db->query(
            "SELECT pos_dana,
                    COALESCE(SUM(CASE WHEN jenis='masuk'  THEN nominal ELSE 0 END),0) AS masuk,
                    COALESCE(SUM(CASE WHEN jenis='keluar' THEN nominal ELSE 0 END),0) AS keluar
             FROM jurnal_kas GROUP BY pos_dana"
        )->fetchAll();
        foreach ($rows as $r) {
            $out[$r['pos_dana']]['masuk']  = (float)$r['masuk'];
            $out[$r['pos_dana']]['keluar'] = (float)$r['keluar'];
            $out[$r['pos_dana']]['saldo']  = (float)$r['masuk'] - (float)$r['keluar'];
        }
        return $out;
    }

    // Nomor jurnal: JKM-YYYYMM-0001 (masuk) / JKK-YYYYMM-0001 (keluar)
    private function nomor(string $jenis, string $tanggal): string
    {
        $prefix = ($jenis === 'masuk' ? 'JKM' : 'JKK') . '-' . date('Ym', strtotime($tanggal)) . '-';
        $stmt = $this->db->prepare('SELECT MAX(no_jurnal) FROM jurnal_kas WHERE no_jurnal LIKE :p');
        $stmt->execute([':p' => $prefix . '%']);
        $max = $stmt->fetchColumn();
        $n = $max ? ((int)substr($max, -4)) + 1 : 1;
        return $prefix . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    // Saldo pos dana tidak boleh minus (validasi integritas PSAK 109 per pos dana)
    private function pastikanSaldo(array $posList): void
    {
        foreach (array_unique($posList) as $pos) {
            if ($this->saldoPos($pos) < 0) {
                throw new RuntimeException('Saldo ' . self::POS[$pos] . ' tidak mencukupi. Transaksi dibatalkan.');
            }
        }
    }

    // CREATE (juga dipakai modul Shabila/Nur Shifa untuk posting otomatis)
    public function catat(array $d): int
    {
        return $this->inTx(function () use ($d) {
            $sql = 'INSERT INTO jurnal_kas (no_jurnal, tanggal, jenis, pos_dana, nominal, keterangan, sumber, ref_id)
                    VALUES (:no, :tgl, :jenis, :pos, :nom, :ket, :sumber, :ref)';
            $this->db->prepare($sql)->execute([
                ':no'     => $this->nomor($d['jenis'], $d['tanggal']),
                ':tgl'    => $d['tanggal'],
                ':jenis'  => $d['jenis'],
                ':pos'    => $d['pos_dana'],
                ':nom'    => $d['nominal'],
                ':ket'    => $d['keterangan'],
                ':sumber' => $d['sumber'] ?? 'manual',
                ':ref'    => $d['ref_id'] ?? null,
            ]);
            $id = (int)$this->db->lastInsertId();
            if ($d['jenis'] === 'keluar') {
                $this->pastikanSaldo([$d['pos_dana']]);
            }
            return $id;
        });
    }

    // UPDATE (hanya jurnal manual; jenis tidak boleh diubah)
    public function update(int $id, array $d): void
    {
        $this->inTx(function () use ($id, $d) {
            $old = $this->getById($id);
            if (!$old) {
                throw new RuntimeException('Jurnal tidak ditemukan.');
            }
            if ($old['sumber'] !== 'manual') {
                throw new RuntimeException('Jurnal otomatis hanya bisa diubah dari modul asalnya.');
            }
            $this->db->prepare(
                'UPDATE jurnal_kas SET tanggal=:tgl, pos_dana=:pos, nominal=:nom, keterangan=:ket WHERE id=:id'
            )->execute([
                ':tgl' => $d['tanggal'], ':pos' => $d['pos_dana'],
                ':nom' => $d['nominal'], ':ket' => $d['keterangan'], ':id' => $id,
            ]);
            $this->pastikanSaldo([$old['pos_dana'], $d['pos_dana']]);
        });
    }

    // DELETE (hanya jurnal manual; saldo tidak boleh jadi minus setelah dihapus)
    public function delete(int $id): void
    {
        $this->inTx(function () use ($id) {
            $old = $this->getById($id);
            if (!$old) {
                throw new RuntimeException('Jurnal tidak ditemukan.');
            }
            if ($old['sumber'] !== 'manual') {
                throw new RuntimeException('Jurnal otomatis hanya bisa dihapus dari modul asalnya.');
            }
            $this->db->prepare('DELETE FROM jurnal_kas WHERE id = :id')->execute([':id' => $id]);
            $this->pastikanSaldo([$old['pos_dana']]);
        });
    }
}
