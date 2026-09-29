<?php
session_start();
require_once 'config/con.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $name = trim($name);
    $email = trim($email);

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Fill in all the required fields.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vul een geldig e-mailadres in.';
    }
    elseif (strlen($password) < 8) {
        $error = 'Your password must be at least 8 characters long.';
    }

    else {
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
            header('Location: login.php');
            exit;
        }
    }

    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
}

include_once("views/register_view.php");