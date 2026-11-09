<section class="card">
    <div class="card-head">
        <div>
            <h2>Laporan Perubahan Dana (LPD)</h2>
            <p class="muted">Saldo awal + penerimaan - penyaluran = saldo akhir, per pos dana. Dihitung dari Jurnal Kas.</p>
        </div>
        <a href="index.php?page=lpd&action=create" class="btn btn-primary">+ Buat LPD</a>
    </div>
    <div class="table-wrap">
    <table>
        <thead><tr><th>No</th><th>Periode</th><th>Status</th><th class="num">Total Saldo Akhir</th><th>Catatan</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="empty">Belum ada laporan. Klik "Buat LPD".</td></tr>
        <?php else: $no = 1; foreach ($rows as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e(tgl($r['periode_awal'])) ?> s.d. <?= e(tgl($r['periode_akhir'])) ?></td>
                <td><span class="badge <?= $r['status'] === 'final' ? 'badge-in' : '' ?>"><?= e(strtoupper($r['status'])) ?></span></td>
                <td class="num"><?= rupiah($r['total_saldo_akhir']) ?></td>
                <td><?= e($r['catatan']) ?></td>
                <td class="actions">
                    <a class="btn btn-sm" href="index.php?page=lpd&action=show&id=<?= (int)$r['id'] ?>">Lihat</a>
                <?php if ($r['status'] === 'draft'): ?>
                    <a class="btn btn-sm btn-warn" href="index.php?page=lpd&action=edit&id=<?= (int)$r['id'] ?>">Edit</a>
                    <form method="POST" action="index.php?page=lpd&action=delete" data-confirm="Hapus laporan periode ini?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                    </form>
                <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</section>
