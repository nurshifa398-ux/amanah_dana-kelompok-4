<section class="card">
    <h2>Kategori Dana ZIS</h2>
    <p class="muted">Modul Nazwa. Tabel sudah tampil; form tambah/edit/hapus perlu dilengkapi (lihat TODO di KategoriController).</p>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Kode</th><th>Nama</th><th>Pos Dana</th><th>Keterangan</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr><td><strong><?= e($r['kode']) ?></strong></td><td><?= e($r['nama']) ?></td>
                <td><span class="badge"><?= e($r['pos_dana']) ?></span></td><td><?= e($r['keterangan']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</section>
