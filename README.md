# Amanah Dana - pembaruan transaksi penerimaan, penyaluran, dan jurnal

## Fitur pembaruan
- Penerimaan dapat ditambah, diedit, dan dihapus. Jurnal kas terkait ikut disinkronkan otomatis.
- Penyaluran menggunakan input nama mustahik/penerima (bukan dropdown). Nama yang belum terdaftar akan dibuat otomatis dengan asnaf awal Fakir; data dapat diedit melalui transaksi berikutnya.
- Penyaluran dapat ditambah, diedit, dan dihapus. Jurnal kas terkait ikut disinkronkan otomatis.
- Halaman Jurnal Kas tidak lagi menyediakan tombol pencatatan manual baru. Transaksi dibuat dari menu Penerimaan atau Penyaluran.
- Jurnal Kas menampilkan saldo dana Zakat, Infak/Sedekah, Amil, dan Nonhalal yang dihitung dari transaksi. Ringkasan berubah otomatis setelah transaksi ditambah, diedit, atau dihapus.
- Tabel Jurnal Kas menyediakan Edit/Hapus yang diarahkan ke transaksi asal Penerimaan atau Penyaluran.
- Sistem mencegah perubahan penerimaan yang menyebabkan saldo pos dana menjadi minus dan menolak penyaluran yang membuat saldo tidak mencukupi.

## Cara menjalankan
1. Ekstrak folder `amanah_dana` ke `htdocs` XAMPP.
2. Aktifkan Apache dan MySQL.
3. Jalankan `setup.php` jika database belum dibuat.
4. Buka `http://localhost/amanah_dana/`.

Catatan: sintaks seluruh file PHP sudah diperiksa. Pengujian transaksi langsung memerlukan database MySQL aktif.
