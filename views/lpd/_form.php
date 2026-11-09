<?php /* Form dipakai create.php & edit.php. Variabel: $data, $err */ ?>
<?php if ($err): ?><div class="alert alert-err"><?= e($err) ?></div><?php endif; ?>
<?= csrf_field() ?>
<div class="grid-2">
    <div class="form-group"><label for="periode_awal">Periode Awal</label>
        <input id="periode_awal" type="date" name="periode_awal" required value="<?= e($data['periode_awal'] ?? '') ?>"></div>
    <div class="form-group"><label for="periode_akhir">Periode Akhir</label>
        <input id="periode_akhir" type="date" name="periode_akhir" required value="<?= e($data['periode_akhir'] ?? '') ?>"></div>
</div>
<div class="form-group"><label for="status">Status</label>
    <select id="status" name="status">
        <option value="draft" <?= ($data['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (masih bisa diubah)</option>
        <option value="final" <?= ($data['status'] ?? '') === 'final' ? 'selected' : '' ?>>Final (dikunci)</option>
    </select></div>
<div class="form-group"><label for="catatan">Catatan</label>
    <input id="catatan" type="text" name="catatan" maxlength="255" value="<?= e($data['catatan'] ?? '') ?>"></div>
<p class="muted">Angka laporan dihitung otomatis dari Jurnal Kas pada periode ini.</p>
<button type="submit" class="btn btn-primary">Simpan</button>
<a href="index.php?page=lpd" class="btn btn-ghost">Batal</a>
