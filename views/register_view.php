<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Register - WiVote</title>
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href="https://fonts.cdnfonts.com/css/porter-sans-block" rel="stylesheet">
    <link rel="stylesheet" href="style/auth.css">
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">WiVote</div>
        <h1>Register</h1>

        <?php if ($error !== ''): ?>
            <div class="auth-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="16.5" r="0.8" fill="currentColor" stroke="none"/>
                </svg>
                <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="auth-field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required
                       class="<?= $error !== '' ? 'field-error' : '' ?>">
            </div>
            <div class="auth-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required
                       class="<?= $error !== '' ? 'field-error' : '' ?>">
            </div>
            <div class="auth-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       class="<?= $error !== '' ? 'field-error' : '' ?>">
                <span class="auth-hint">At least 8 characters.</span>
            </div>
            <div class="auth-field">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                       class="<?= $error !== '' ? 'field-error' : '' ?>">
            </div>
            <button type="submit" class="auth-submit">Register</button>
        </form>

        <p class="auth-footer">Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
