<section class="card narrow">
    <h2>Edit Jurnal <?= e($data['no_jurnal']) ?></h2>
    <form method="POST" action="index.php?page=jurnal&action=edit&id=<?= (int)$data['id'] ?>" data-jurnal-form data-edit="1">
        <?php $editMode = true; require __DIR__ . '/_form.php'; ?>
    </form>
</section>
