<?php
session_start();
require_once 'config/con.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$name = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $error = 'Please fill in all the required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please fill in a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE email = :email
        LIMIT 1
        ");
        $stmt->execute([
            ':email' => $email
        ]);
        $user = $stmt->fetch();
        if ($user) {
            $error = 'This email is already taken.';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (:name, :email, :password, :role)
            ");

            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => 'user'
            ]);
            header('Location: login.php?registered=1');
            exit;
        }
    }

    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
}

include_once("views/register_view.php");
