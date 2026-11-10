<?php

$isLoggedIn = isset($_SESSION['user_id']);
$username   = $_SESSION['username'] ?? 'Guest';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link href='https://fonts.googleapis.com/css?family=Turret Road' rel='stylesheet'>
    <link href='https://fonts.cdnfonts.com/css/porter-sans-block' rel='stylesheet'>
    <title>WiVote</title>
</head>
<body>
<div class="title">
    <h1>WiVote</h1>
</div>

<div class="choices">
    <?php foreach ($categories as $category): ?>
        <?php
            $pollCount   = (int) $category['poll_count'];
            $totalVotes  = (int) $category['total_votes'];
        ?>
        <a class="choice" href="polls.php?category=<?= (int) $category['id'] ?>">
            <img src="<?= e($category['image_url']) ?>" alt="<?= e($category['name']) ?>">
            <div class="choice-text">
                <p class="categorie"><?= e($category['name']) ?></p>
                <span class="vote-count">
                    <?= $pollCount ?> poll<?= $pollCount === 1 ? '' : 's' ?> &middot; <?= $totalVotes ?> vote<?= $totalVotes === 1 ? '' : 's' ?>
                </span>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<header></header>
<footer class="bottom">
    <a class="bottom-button home" href="index.php">Home</a>
    <div class="username-block">
        <div class="username"><?= e($isLoggedIn ? $username : 'Guest') ?></div>
        <?php if ($isLoggedIn): ?>
            <a class="logout-link" href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
    <a class="bottom-button profile" href="<?= $isLoggedIn ? 'profile.php' : 'login.php' ?>"><?= $isLoggedIn ? 'Profile' : 'Login' ?></a>
</footer>
<button class="menu-button" type="button">Menu</button>

<script src="js/menu.js" defer></script>
</body>
</html>
