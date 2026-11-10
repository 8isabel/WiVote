<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
$presetCategory = (int) ($_GET['category'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/create.css">
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href="https://fonts.cdnfonts.com/css/porter-sans-block" rel="stylesheet">
    <title>Create - WiVote</title>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <div class="login-overlay">
        <div class="login-box">
            <h2>Welcome</h2>
            <p>You need an account to post on WiVote.</p>
            <div class="login-buttons">
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
<?php else: ?>

<div class="title">
    <h1>Create</h1>
</div>

<div class="type-switch">
    <a class="type-tab<?= $type === 'poll' ? ' active' : '' ?>" href="create.php?type=poll<?= $presetCategory > 0 ? '&category=' . $presetCategory : '' ?>">
        Poll
        <span class="type-hint">people vote &amp; comment</span>
    </a>
    <a class="type-tab<?= $type === 'post' ? ' active' : '' ?>" href="create.php?type=post<?= $presetCategory > 0 ? '&category=' . $presetCategory : '' ?>">
        Post
        <span class="type-hint">people only comment</span>
    </a>
</div>

<?php if ($flash !== ''): ?>
    <p class="flash"><?= e($flash) ?></p>
<?php endif; ?>

<?php if ($type === 'poll'): ?>
    <form class="create-form" method="post" action="create.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_poll">

        <label class="field">
            <span>Question / title</span>
            <input type="text" name="title" maxlength="150" placeholder="What should everyone vote on?" required>
        </label>

        <div class="field-row">
            <label class="field">
                <span>Category</span>
                <select name="category_id">
                    <option value="0">No category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $presetCategory === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Image (optional, max 5 MB)</span>
                <input class="file-input" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
            </label>
        </div>

        <label class="field">
            <span>Options (fill in at least 2)</span>
            <input type="text" name="options[]" maxlength="100" placeholder="Option 1">
            <input type="text" name="options[]" maxlength="100" placeholder="Option 2">
            <input type="text" name="options[]" maxlength="100" placeholder="Option 3 (optional)">
            <input type="text" name="options[]" maxlength="100" placeholder="Option 4 (optional)">
        </label>

        <button class="submit-button" type="submit">Post poll</button>
    </form>
<?php else: ?>
    <form class="create-form" method="post" action="create.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_post">

        <label class="field">
            <span>Title</span>
            <input type="text" name="title" maxlength="150" placeholder="What's on your mind?" required>
        </label>

        <div class="field-row">
            <label class="field">
                <span>Category</span>
                <select name="category_id">
                    <option value="0">No category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $presetCategory === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="field">
                <span>Image (optional, max 5 MB)</span>
                <input class="file-input" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
            </label>
        </div>

        <label class="field">
            <span>Content (optional)</span>
            <textarea name="content" maxlength="2000" rows="4" placeholder="Say more about it..."></textarea>
        </label>

        <button class="submit-button" type="submit">Post</button>
    </form>
<?php endif; ?>

<footer class="bottom">
    <a class="bottom-button home" href="index.php">Home</a>
    <div class="username-block">
        <div class="username"><?= e($username) ?></div>
        <a class="logout-link" href="logout.php">Logout</a>
    </div>
    <a class="bottom-button profile" href="profile.php">Profile</a>
</footer>
<button class="menu-button" type="button">Menu</button>

<script src="js/menu.js" defer></script>
<?php endif; ?>
</body>
</html>
