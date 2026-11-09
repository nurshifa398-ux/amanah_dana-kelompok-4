<section class="card narrow">
    <h2>Edit Laporan Perubahan Dana</h2>
    <form method="POST" action="index.php?page=lpd&action=edit&id=<?= (int)$id ?>">
        <?php require __DIR__ . '/_form.php'; ?>
    </form>
</section>
