<?php /* Form dipakai create.php & edit.php. Variabel: $data, $err, $saldo, $editMode (bool) */ ?>
<?php if ($err): ?><div class="alert alert-err"><?= e($err) ?></div><?php endif; ?>
<?= csrf_field() ?>
<div class="grid-2">
    <div class="form-group"><label for="tanggal">Tanggal</label>
        <input id="tanggal" type="date" name="tanggal" required value="<?= e($data['tanggal'] ?? '') ?>"></div>
    <div class="form-group"><label for="jenis">Jenis</label>
    <?php if ($editMode): ?>
        <input id="jenis" type="text" value="<?= ($data['jenis'] ?? '') === 'masuk' ? 'Kas Masuk' : 'Kas Keluar' ?>" disabled>
        <input type="hidden" name="jenis" value="<?= e($data['jenis']) ?>">
    <?php else: ?>
        <select id="jenis" name="jenis" required>
            <option value="masuk"  <?= ($data['jenis'] ?? '') === 'masuk' ? 'selected' : '' ?>>Kas Masuk</option>
            <option value="keluar" <?= ($data['jenis'] ?? '') === 'keluar' ? 'selected' : '' ?>>Kas Keluar</option>
        </select>
    <?php endif; ?>
    </div>
</div>
<div class="form-group"><label for="pos_dana">Pos Dana</label>
    <select id="pos_dana" name="pos_dana" required>
        <?php foreach (JurnalKas::POS as $k => $nama): ?>
            <option value="<?= e($k) ?>" data-saldo="<?= e((string)$saldo[$k]['saldo']) ?>"
                <?= ($data['pos_dana'] ?? '') === $k ? 'selected' : '' ?>><?= e($nama) ?></option>
        <?php endforeach; ?>
    </select>
    <small class="muted" id="saldoInfo"></small>
</div>
<div class="form-group"><label for="nominal">Nominal (Rp)</label>
    <input id="nominal" type="text" name="nominal" inputmode="numeric" data-rupiah required
           value="<?= e(isset($data['nominal']) ? number_format((float)$data['nominal'], 0, ',', '.') : '') ?>"></div>
<div class="form-group"><label for="keterangan">Keterangan</label>
    <input id="keterangan" type="text" name="keterangan" maxlength="255" required value="<?= e($data['keterangan'] ?? '') ?>"></div>
<div class="preview" id="jurnalPreview" aria-live="polite"></div>
<button type="submit" class="btn btn-primary">Simpan</button>
<a href="index.php?page=jurnal" class="btn btn-ghost">Batal</a>
