<?php
$categoryIcons = [
    'face_shape'  => '<circle cx="12" cy="12" r="8"/><circle cx="9" cy="10" r="0.8" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="0.8" fill="currentColor" stroke="none"/><path d="M9 15c1 1 5 1 6 0"/>',
    'hair'        => '<path d="M5 13c0-5 3-8 7-8s7 3 7 8" /><path d="M5 13l1-5M9 13l0.5-6M12 13V5M15 13l-0.5-6M19 13l-1-5"/>',
    'eyebrows'    => '<path d="M5 11c1.5-2 5-2 6 0"/><path d="M13 11c1.5-2 5-2 6 0"/>',
    'eyes'        => '<ellipse cx="8" cy="12" rx="3.2" ry="2.2"/><circle cx="8" cy="12" r="1" fill="currentColor" stroke="none"/><ellipse cx="16" cy="12" rx="3.2" ry="2.2"/><circle cx="16" cy="12" r="1" fill="currentColor" stroke="none"/>',
    'nose'        => '<path d="M11 6c-1 4-2 6-2 8a3 3 0 0 0 6 0"/>',
    'mouth'       => '<path d="M5 10c3 4 11 4 14 0"/>',
    'facial_hair' => '<path d="M6 9c0 5 2 9 6 9s6-4 6-9"/><path d="M9 9v3M15 9v3"/>',
    'glasses'     => '<circle cx="7" cy="12" r="4"/><circle cx="17" cy="12" r="4"/><path d="M11 12h2M3 12l1-3M21 12l-1-3"/>',
    'shirt'       => '<path d="M8 4l4 2 4-2 3 4-2 2v10H7V10l-2-2z"/>',
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile - WiVote</title>
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href="https://fonts.cdnfonts.com/css/porter-sans-block" rel="stylesheet">
    <link rel="stylesheet" href="style/profile_view.css">
</head>
<body>

    <?php if (!$isLoggedIn): ?>
        <div class="login-overlay">
            <div class="login-box">
                <h2>Welcome</h2>
                <p>You need an account to edit your profile.</p>
                <div class="login-buttons">
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <header class="top">
        <?php foreach ($avatarOptions['categories'] as $key => $meta): ?>
            <button type="button"
                    class="selectbox<?= $key === 'face_shape' ? ' selected' : '' ?>"
                    data-category="<?= htmlspecialchars($key, ENT_QUOTES) ?>"
                    title="<?= htmlspecialchars($meta['label'], ENT_QUOTES) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <?= $categoryIcons[$key] ?>
                </svg>
                <span class="selectbox-label"><?= htmlspecialchars($meta['label'], ENT_QUOTES) ?></span>
            </button>
        <?php endforeach; ?>
    </header>

    <main>
        <div id="main">
            <div id="mii">
                <svg id="mii-canvas" viewBox="0 0 300 380" class="mii-img"></svg>

                <div id="mii-colors" class="mii-colors" hidden></div>

                <div id="mii-options" class="mii-options"></div>

                <button id="confirm" type="button">Confirm</button>
                <p id="mii-status" class="form-message" aria-live="polite"></p>
            </div>

            <div id="box">
                <form id="profile-form" autocomplete="off">
                    <div class="rowb">
                        <div class="infob">
                            <p>Name:</p>
                            <input type="text" name="name" value="<?= htmlspecialchars($currentUser['username'] ?? '', ENT_QUOTES) ?>" required>
                        </div>
                        <div class="infob">
                            <p>Email:</p>
                            <input type="email" name="email" value="<?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES) ?>" required>
                        </div>
                    </div>
                    <?php if ($profileColumnsExist): ?>
                    <div class="rowb">
                        <div class="infob">
                            <p>Gender:</p>
                            <select name="gender">
                                <option value="" <?= empty($currentUser['gender']) ? 'selected' : '' ?>>Prefer not to say</option>
                                <option value="female" <?= ($currentUser['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="male" <?= ($currentUser['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="other" <?= ($currentUser['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="infob infob-birthday">
                            <p>Birthday:</p>
                            <input type="date" name="birthday" value="<?= htmlspecialchars($currentUser['birthday'] ?? '', ENT_QUOTES) ?>">
                        </div>
                    </div>
                    <div class="rowb">
                        <div class="infob">
                            <p>Favourite color:</p>
                            <input type="color" name="favorite_color" value="<?= htmlspecialchars($currentUser['favorite_color'] ?? '#81B6FF', ENT_QUOTES) ?>">
                        </div>
                        <div class="infob infob-checkbox">
                            <p>Single:</p>
                            <input type="checkbox" name="is_single" <?= !empty($currentUser['is_single']) ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="rowb">
                        <div class="infob">
                            <p>New password:</p>
                            <input type="password" name="new_password" placeholder="Leave empty to keep current" autocomplete="new-password">
                        </div>
                        <div class="infob">
                            <p>Confirm password:</p>
                            <input type="password" name="confirm_password" placeholder="Repeat new password" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="rowb profile-save-row">
                        <div class="infob">
                            <p>Current password:</p>
                            <input type="password" name="current_password" placeholder="Required to change password" autocomplete="current-password">
                        </div>
                        <div class="infob infob-save">
                            <button type="submit" id="save-profile">Save changes</button>
                        </div>
                    </div>
                    <p id="profile-status" class="form-message" aria-live="polite"></p>
                </form>
            </div>
        </div>
    </main>

    <footer class="bottom">
        <a class="bottom-button home" href="index.php">Home</a>
        <div class="username-block">
            <div class="username"><?= htmlspecialchars($currentUser['username'] ?? 'Username', ENT_QUOTES) ?></div>
            <?php if ($isLoggedIn): ?>
                <a class="logout-link" href="logout.php">Logout</a>
            <?php endif; ?>
        </div>
        <a class="bottom-button profile" href="profile.php">Profile</a>
    </footer>

    <button class="menu-button" type="button">Menu</button>

    <script>
        window.MII_OPTIONS = <?= json_encode($avatarOptions) ?>;
        window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
    </script>
    <script src="js/menu.js" defer></script>
    <script src="js/profile_view.js" defer></script>
    <script src="js/avatar.js" defer></script>
</body>
</html>
