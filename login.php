<?php

session_start();

require_once 'config/con.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'fill in your email and password';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'fill in an valid email adres.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, username, email, password, role
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([
            ':email' => $email
        ]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'email or password is incorrect';
        }
    }
}

require_once 'views/login_view.php';