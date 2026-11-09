<section class="stat-grid">
<?php foreach ($ringkasan as $k => $r): ?>
    <article class="stat stat-<?= e($k) ?>">
        <span class="stat-label"><?= e($r['nama']) ?></span>
        <strong class="stat-value"><?= rupiah($r['saldo']) ?></strong>
        <span class="stat-sub">Masuk <?= rupiah($r['masuk']) ?> &middot; Keluar <?= rupiah($r['keluar']) ?></span>
    </article>
<?php endforeach; ?>
</section>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Jurnal Kas Masuk &amp; Keluar</h2>
            <p class="muted">Setiap transaksi dicatat per pos dana (Zakat, Infak/Sedekah, Amil, Nonhalal).</p>
        </div>
    </div>

    <input type="text" class="live-search" data-live-filter="tblJurnal" placeholder="Cari cepat di tabel (tanpa reload)...">

    <div class="table-wrap">
    <table id="tblJurnal">
        <thead>
            <tr><th>No Jurnal</th><th>Tanggal</th><th>Pos Dana</th><th>Debit</th><th>Kredit</th>
                <th class="num">Nominal</th><th>Sumber</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="empty">Belum ada jurnal untuk filter ini.</td></tr>
        <?php else: foreach ($rows as $r):
            $akunDana = JurnalKas::POS[$r['pos_dana']];
            $debit  = $r['jenis'] === 'masuk' ? 'Kas & Bank' : $akunDana;
            $kredit = $r['jenis'] === 'masuk' ? $akunDana : 'Kas & Bank'; ?>
            <tr>
                <td><strong><?= e($r['no_jurnal']) ?></strong><br><small class="muted"><?= e($r['keterangan']) ?></small></td>
                <td><?= e(tgl($r['tanggal'])) ?></td>
                <td><span class="badge"><?= e($akunDana) ?></span></td>
                <td><span class="badge badge-in">D</span> <?= e($debit) ?></td>
                <td><span class="badge badge-out">K</span> <?= e($kredit) ?></td>
                <td class="num"><?= rupiah($r['nominal']) ?></td>
                <td><?php if ($r['sumber'] === 'penerimaan'): ?><span class="badge badge-in">Penerimaan</span><?php elseif ($r['sumber'] === 'penyaluran'): ?><span class="badge badge-out">Penyaluran</span><?php else: ?><span class="badge">Jurnal Manual</span><?php endif; ?></td>
                <td class="actions">
                <?php if ($r['sumber'] === 'penerimaan'): ?>
                    <a class="btn btn-sm btn-warn" href="index.php?page=penerimaan&action=edit&id=<?= (int)$r['ref_id'] ?>">Edit</a>
                    <form method="POST" action="index.php?page=penerimaan" data-confirm="Hapus penerimaan dan jurnal terkait?"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['ref_id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
                <?php elseif ($r['sumber'] === 'penyaluran'): ?>
                    <a class="btn btn-sm btn-warn" href="index.php?page=penyaluran&action=edit&id=<?= (int)$r['ref_id'] ?>">Edit</a>
                    <form method="POST" action="index.php?page=penyaluran" data-confirm="Hapus penyaluran dan jurnal terkait?"><?= csrf_field() ?><input type="hidden" name="form_action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['ref_id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
                <?php else: ?>
                    <a class="btn btn-sm btn-warn" href="index.php?page=jurnal&action=edit&id=<?= (int)$r['id'] ?>">Edit</a>
                    <form method="POST" action="index.php?page=jurnal&action=delete" data-confirm="Hapus jurnal kas ini?">
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
