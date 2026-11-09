// assets/js/app.js - Vanilla JS: konfirmasi hapus, format Rupiah, pencarian tabel, pratinjau jurnal, cetak

// Konfirmasi sebelum hapus
document.querySelectorAll('form[data-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (e) {
    if (!confirm(f.dataset.confirm)) e.preventDefault();
  });
});

// Format Rupiah saat mengetik: 1500000 -> 1.500.000
document.querySelectorAll('input[data-rupiah]').forEach(function (el) {
  el.addEventListener('input', function () {
    var digits = el.value.replace(/\D/g, '');
    el.value = digits ? Number(digits).toLocaleString('id-ID') : '';
  });
});

// Pencarian tabel tanpa reload
document.querySelectorAll('input[data-live-filter]').forEach(function (input) {
  var table = document.getElementById(input.dataset.liveFilter);
  if (!table) return;
  input.addEventListener('input', function () {
    var q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(function (tr) {
      tr.style.display = tr.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
    });
  });
});

// Form jurnal: tampilkan saldo pos dana + pratinjau debit/kredit + cegah kas keluar melebihi saldo
document.querySelectorAll('form[data-jurnal-form]').forEach(function (form) {
  var jenis = form.querySelector('[name=jenis]');
  var pos = form.querySelector('[name=pos_dana]');
  var nominal = form.querySelector('[name=nominal]');
  var info = form.querySelector('#saldoInfo');
  var preview = form.querySelector('#jurnalPreview');
  var rp = function (n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); };

  function update() {
    var opt = pos.options[pos.selectedIndex];
    var saldo = parseFloat(opt.dataset.saldo || '0');
    var nama = opt.text;
    var nom = parseInt((nominal.value || '0').replace(/\D/g, ''), 10) || 0;
    var masuk = jenis.value === 'masuk';
    info.textContent = 'Saldo saat ini: ' + rp(saldo);
    if (nom > 0) {
      preview.classList.add('show');
      preview.innerHTML = '<strong>Pratinjau jurnal</strong><br>Debit: ' + (masuk ? 'Kas & Bank' : nama) +
        ' ' + rp(nom) + '<br>Kredit: ' + (masuk ? nama : 'Kas & Bank') + ' ' + rp(nom);
    } else {
      preview.classList.remove('show');
    }
    form.dataset.kurang = (!masuk && nom > saldo) ? '1' : '';
    nominal.setCustomValidity(form.dataset.kurang && !form.dataset.edit ? 'Nominal melebihi saldo ' + nama : '');
  }
  [jenis, pos, nominal].forEach(function (el) { el.addEventListener('input', update); el.addEventListener('change', update); });
  update();
});

// Tombol cetak laporan
document.querySelectorAll('[data-print]').forEach(function (b) {
  b.addEventListener('click', function () { window.print(); });
});
