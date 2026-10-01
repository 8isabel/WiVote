<?php
session_start();
require_once 'config/con.php';

$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = null;
$profileColumnsExist = true;

if ($isLoggedIn) {
    try {
        $stmt = $conn->prepare('SELECT username, email, gender, birthday, favorite_color, is_single FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    } catch (PDOException $e) {
        // The gender/birthday/favorite_color/is_single columns don't exist yet
        // (sql/users_profile_fields.sql hasn't been run). Fall back gracefully.
        $profileColumnsExist = false;
        $stmt = $conn->prepare('SELECT username, email FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $currentUser = $stmt->fetch();
    }
}

$avatarOptions = require 'includes/avatar_options.php';

include 'views/profile_view.php';
