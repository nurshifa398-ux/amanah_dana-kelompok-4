<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Amanah Dana</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="login-body">
<section class="card login-card">
    <h1>Amanah Dana</h1>
    <p class="muted">Akuntansi ZIS sesuai PSAK 109. Masuk untuk melanjutkan.</p>
    <?php if ($error): ?><div class="alert alert-err"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="index.php?page=auth&action=login">
        <?= csrf_field() ?>
        <div class="form-group"><label for="username">Username</label>
            <input id="username" type="text" name="username" required autofocus></div>
        <div class="form-group"><label for="password">Password</label>
            <input id="password" type="password" name="password" required></div>
        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>
</section>
</body>
</html>
