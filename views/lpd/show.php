<section class="card print-area">
    <div class="card-head">
        <div>
            <h2>Laporan Perubahan Dana</h2>
            <p class="muted">Periode <?= e(tgl($lpd['periode_awal'])) ?> s.d. <?= e(tgl($lpd['periode_akhir'])) ?>
                &middot; Status <strong><?= e(strtoupper($lpd['status'])) ?></strong></p>
        </div>
        <div class="actions no-print">
            <button type="button" class="btn btn-primary" data-print>Cetak</button>
            <a href="index.php?page=lpd" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Pos Dana</th><th class="num">Saldo Awal</th><th class="num">Penerimaan</th>
            <th class="num">Penyaluran</th><th class="num">Saldo Akhir</th></tr></thead>
        <tbody>
        <?php $t = ['awal' => 0, 'in' => 0, 'out' => 0, 'akhir' => 0];
        foreach ($detail as $d):
            $t['awal'] += $d['saldo_awal']; $t['in'] += $d['penerimaan'];
            $t['out'] += $d['penyaluran'];  $t['akhir'] += $d['saldo_akhir']; ?>
            <tr>
                <td><?= e(JurnalKas::POS[$d['pos_dana']]) ?></td>
                <td class="num"><?= rupiah($d['saldo_awal']) ?></td>
                <td class="num"><?= rupiah($d['penerimaan']) ?></td>
                <td class="num"><?= rupiah($d['penyaluran']) ?></td>
                <td class="num"><?= rupiah($d['saldo_akhir']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th>Total</th><th class="num"><?= rupiah($t['awal']) ?></th><th class="num"><?= rupiah($t['in']) ?></th>
                <th class="num"><?= rupiah($t['out']) ?></th><th class="num"><?= rupiah($t['akhir']) ?></th></tr>
        </tfoot>
    </table>
    </div>
    <?php if ($lpd['catatan']): ?><p><strong>Catatan:</strong> <?= e($lpd['catatan']) ?></p><?php endif; ?>
</section>
