<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
</head>
<body>
<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<h1>Registreren</h1>
<form method="post">
    <label for="name">Naam:</label>
    <input type="text" id="name" name="name" required>
    <br><br>
    <label for="email">E-mailadres:</label>
    <input type="email" id="email" name="email" required>
    <br><br>
    <label for="password">Wachtwoord:</label>
    <input type="password" id="password" name="password" required>
    <br><br>
    <button type="submit">Registreren</button>
</form>
<p>
    Already have an account?
    <a href="login.php">Log in</a>
</p>
</body>
</html>
