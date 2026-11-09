<?php
// models/Lpd.php - MODUL 4 (Maurah): Laporan Perubahan Dana (LPD)
// LPD disimpan sebagai snapshot per periode: saldo awal + penerimaan - penyaluran = saldo akhir (per pos dana)
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/JurnalKas.php';

class Lpd
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Hitung angka LPD dari jurnal_kas untuk periode tertentu (belum disimpan)
    public function hitung(string $dari, string $sampai): array
    {
        $out = [];
        foreach (JurnalKas::POS as $k => $nama) {
            $out[$k] = ['pos_dana' => $k, 'saldo_awal' => 0.0, 'penerimaan' => 0.0, 'penyaluran' => 0.0, 'saldo_akhir' => 0.0];
        }

        $awal = $this->db->prepare(
            "SELECT pos_dana, COALESCE(SUM(CASE WHEN jenis='masuk' THEN nominal ELSE -nominal END),0) AS saldo
             FROM jurnal_kas WHERE tanggal < :dari GROUP BY pos_dana"
        );
        $awal->execute([':dari' => $dari]);
        foreach ($awal->fetchAll() as $r) {
            $out[$r['pos_dana']]['saldo_awal'] = (float)$r['saldo'];
        }

        $mut = $this->db->prepare(
            "SELECT pos_dana,
                    COALESCE(SUM(CASE WHEN jenis='masuk'  THEN nominal ELSE 0 END),0) AS masuk,
                    COALESCE(SUM(CASE WHEN jenis='keluar' THEN nominal ELSE 0 END),0) AS keluar
             FROM jurnal_kas WHERE tanggal BETWEEN :dari AND :sampai GROUP BY pos_dana"
        );
        $mut->execute([':dari' => $dari, ':sampai' => $sampai]);
        foreach ($mut->fetchAll() as $r) {
            $out[$r['pos_dana']]['penerimaan'] = (float)$r['masuk'];
            $out[$r['pos_dana']]['penyaluran'] = (float)$r['keluar'];
        }

        foreach ($out as $k => $row) {
            $out[$k]['saldo_akhir'] = $row['saldo_awal'] + $row['penerimaan'] - $row['penyaluran'];
        }
        return $out;
    }

    public function getAll(): array
    {
        return $this->db->query(
            'SELECT l.*, (SELECT COALESCE(SUM(saldo_akhir),0) FROM lpd_detail d WHERE d.lpd_id = l.id) AS total_saldo_akhir
             FROM lpd l ORDER BY l.periode_akhir DESC, l.id DESC'
        )->fetchAll();
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare('SELECT * FROM lpd WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function getDetail(int $lpdId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM lpd_detail WHERE lpd_id = :id
             ORDER BY FIELD(pos_dana,'zakat','infak','amil','nonhalal')"
        );
        $stmt->execute([':id' => $lpdId]);
        return $stmt->fetchAll();
    }

    // Simpan 4 baris detail (snapshot) untuk satu LPD
    private function simpanDetail(int $lpdId, string $dari, string $sampai): void
    {
        $ins = $this->db->prepare(
            'INSERT INTO lpd_detail (lpd_id, pos_dana, saldo_awal, penerimaan, penyaluran, saldo_akhir)
             VALUES (:lpd, :pos, :awal, :in, :out, :akhir)'
        );
        foreach ($this->hitung($dari, $sampai) as $r) {
            $ins->execute([
                ':lpd' => $lpdId, ':pos' => $r['pos_dana'], ':awal' => $r['saldo_awal'],
                ':in' => $r['penerimaan'], ':out' => $r['penyaluran'], ':akhir' => $r['saldo_akhir'],
            ]);
        }
    }

    // CREATE: header + snapshot detail dalam satu transaksi
    public function create(array $d): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'INSERT INTO lpd (periode_awal, periode_akhir, status, catatan) VALUES (:a, :b, :s, :c)'
            )->execute([':a' => $d['periode_awal'], ':b' => $d['periode_akhir'], ':s' => $d['status'], ':c' => $d['catatan']]);
            $id = (int)$this->db->lastInsertId();
            $this->simpanDetail($id, $d['periode_awal'], $d['periode_akhir']);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // UPDATE: hanya laporan draft. Snapshot dihitung ulang dari jurnal terbaru.
    public function update(int $id, array $d): void
    {
        $this->db->beginTransaction();
        try {
            $old = $this->getById($id);
            if (!$old) {
                throw new RuntimeException('Laporan tidak ditemukan.');
            }
            if ($old['status'] === 'final') {
                throw new RuntimeException('Laporan berstatus FINAL tidak dapat diubah.');
            }
            $this->db->prepare(
                'UPDATE lpd SET periode_awal=:a, periode_akhir=:b, status=:s, catatan=:c WHERE id=:id'
            )->execute([':a' => $d['periode_awal'], ':b' => $d['periode_akhir'], ':s' => $d['status'], ':c' => $d['catatan'], ':id' => $id]);
            $this->db->prepare('DELETE FROM lpd_detail WHERE lpd_id = :id')->execute([':id' => $id]);
            $this->simpanDetail($id, $d['periode_awal'], $d['periode_akhir']);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // DELETE: hanya draft (detail ikut terhapus lewat ON DELETE CASCADE)
    public function delete(int $id): void
    {
        $old = $this->getById($id);
        if (!$old) {
            throw new RuntimeException('Laporan tidak ditemukan.');
        }
        if ($old['status'] === 'final') {
            throw new RuntimeException('Laporan berstatus FINAL tidak dapat dihapus.');
        }
        $this->db->prepare('DELETE FROM lpd WHERE id = :id')->execute([':id' => $id]);
    }
}
