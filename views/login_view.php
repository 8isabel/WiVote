<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <?php if ($error !== ''): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <h1>Login</h1>
    <form method="post">
        Email:<input type="email" name="email" required>
        Password:<input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>

    No account?
    <a href="register.php">Register</a>
</body>
</html>