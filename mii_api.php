<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'config/con.php';
$avatarOptions = require 'includes/avatar_options.php';

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    respond(['success' => false, 'error' => 'You need to be logged in.'], 401);
}

$userId = (int) $_SESSION['user_id'];
$rawInput = file_get_contents('php://input');
$jsonBody = json_decode($rawInput, true);
$jsonBody = is_array($jsonBody) ? $jsonBody : [];

$action = $_SERVER['REQUEST_METHOD'] === 'GET'
    ? ($_GET['action'] ?? '')
    : (($_POST['action'] ?? '') ?: ($jsonBody['action'] ?? ''));

function isValidColor(string $value): bool
{
    return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $value) && strlen($value) <= 30;
}

switch ($action) {
    case 'load': {
        try {
            $stmt = $conn->prepare('SELECT * FROM miis WHERE user_id = :uid LIMIT 1');
            $stmt->execute([':uid' => $userId]);
            $mii = $stmt->fetch();

            if (!$mii) {
                respond(['success' => true, 'isNew' => true, 'mii' => $avatarOptions['defaults']]);
            }

            unset($mii['id'], $mii['user_id'], $mii['created_at'], $mii['updated_at']);
            foreach (['hair', 'eyes', 'eyebrows', 'nose', 'mouth', 'face_shape', 'shirt'] as $f) {
                $mii[$f] = (int) $mii[$f];
            }
            foreach (['facial_hair', 'glasses'] as $f) {
                $mii[$f] = $mii[$f] === null ? null : (int) $mii[$f];
            }

            respond(['success' => true, 'isNew' => false, 'mii' => $mii]);
        } catch (PDOException $e) {
            respond(['success' => false, 'error' => 'Could not load your Mii. Please try again.'], 500);
        }
        break;
    }
    case 'save': {
        $body = $jsonBody;
        $cats = $avatarOptions['categories'];
        $clean = [];

        foreach ($cats as $field => $meta) {
            $value = $body[$field] ?? null;

            if ($value === null || $value === '') {
                if (!$meta['nullable']) {
                    respond(['success' => false, 'error' => "Missing value for {$meta['label']}."], 422);
                }
                $clean[$field] = null;
            } else {
                if (!is_numeric($value) || (int) $value < 0 || (int) $value >= $meta['count']) {
                    respond(['success' => false, 'error' => "Invalid option for {$meta['label']}."], 422);
                }
                $clean[$field] = (int) $value;
            }

            if ($meta['colorField']) {
                $colorField = $meta['colorField'];
                $color = $body[$colorField] ?? null;
                if (!is_string($color) || !isValidColor($color)) {
                    respond(['success' => false, 'error' => "Please pick a valid color for {$meta['label']}."], 422);
                }
                $clean[$colorField] = $color;
            }
        }

        try {
            $stmt = $conn->prepare('SELECT id FROM miis WHERE user_id = :uid LIMIT 1');
            $stmt->execute([':uid' => $userId]);
            $existing = $stmt->fetch();

            $columns = array_keys($clean);

            if ($existing) {
                $setSql = implode(', ', array_map(fn($c) => "`$c` = :$c", $columns));
                $stmt = $conn->prepare("UPDATE miis SET $setSql WHERE user_id = :uid");
                $clean['uid'] = $userId;
                $stmt->execute($clean);
            } else {
                $colSql = implode(', ', array_map(fn($c) => "`$c`", $columns));
                $placeholders = implode(', ', array_map(fn($c) => ":$c", $columns));
                $stmt = $conn->prepare("INSERT INTO miis (user_id, $colSql) VALUES (:uid, $placeholders)");
                $clean['uid'] = $userId;
                $stmt->execute($clean);
            }

            respond(['success' => true, 'message' => 'Mii saved!']);
        } catch (PDOException $e) {
            respond(['success' => false, 'error' => 'Could not save your Mii. Please try again.'], 500);
        }
        break;
    }

    case 'save_profile': {
        $body = $jsonBody;
        $name = trim((string) ($body['name'] ?? ''));
        $email = trim((string) ($body['email'] ?? ''));

        if ($name === '' || $email === '') {
            respond(['success' => false, 'error' => 'Name and email are required.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(['success' => false, 'error' => 'Please fill in a valid email address.'], 422);
        }
        $optionalFields = ['gender', 'birthday', 'favorite_color', 'is_single'];
        $availableFields = [];
        try {
            $cols = $conn->query('SHOW COLUMNS FROM users')->fetchAll();
            $allColumns = array_column($cols, 'Field');
            foreach ($optionalFields as $field) {
                if (in_array($field, $allColumns, true)) {
                    $availableFields[] = $field;
                }
            }
        } catch (PDOException $e) {
            $availableFields = [];
        }

        $values = [':name' => $name, ':email' => $email];
        $sets = ['username = :name', 'email = :email'];

        if (in_array('gender', $availableFields, true)) {
            $gender = trim((string) ($body['gender'] ?? ''));
            if ($gender !== '' && !in_array($gender, ['female', 'male', 'other'], true)) {
                respond(['success' => false, 'error' => 'Please pick a valid gender.'], 422);
            }
            $sets[] = 'gender = :gender';
            $values[':gender'] = $gender !== '' ? $gender : null;
        }

        if (in_array('birthday', $availableFields, true)) {
            $birthday = trim((string) ($body['birthday'] ?? ''));
            if ($birthday !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
                respond(['success' => false, 'error' => 'Please fill in a valid birthday.'], 422);
            }
            $sets[] = 'birthday = :birthday';
            $values[':birthday'] = $birthday !== '' ? $birthday : null;
        }

        if (in_array('favorite_color', $availableFields, true)) {
            $favoriteColor = trim((string) ($body['favorite_color'] ?? ''));
            if ($favoriteColor !== '' && !isValidColor($favoriteColor)) {
                respond(['success' => false, 'error' => 'Please pick a valid favourite color.'], 422);
            }
            $sets[] = 'favorite_color = :favorite_color';
            $values[':favorite_color'] = $favoriteColor !== '' ? $favoriteColor : null;
        }

        if (in_array('is_single', $availableFields, true)) {
            $sets[] = 'is_single = :is_single';
            $values[':is_single'] = !empty($body['is_single']) ? 1 : 0;
        }

        try {
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
            $stmt->execute([':email' => $email, ':id' => $userId]);
            if ($stmt->fetch()) {
                respond(['success' => false, 'error' => 'This email is already taken.'], 422);
            }

            $setSql = implode(', ', $sets);
            $values[':id'] = $userId;
            $stmt = $conn->prepare("UPDATE users SET $setSql WHERE id = :id");
            $stmt->execute($values);
            $_SESSION['username'] = $name;

            respond(['success' => true, 'message' => 'Profile saved!']);
        } catch (PDOException $e) {
            respond(['success' => false, 'error' => 'Could not save your profile. Please try again.'], 500);
        }
        break;
    }

    case 'change_password': {
        $body = $jsonBody;
        $current = (string) ($body['current_password'] ?? '');
        $new = (string) ($body['new_password'] ?? '');
        $confirm = (string) ($body['confirm_password'] ?? '');

        if ($current === '' || $new === '' || $confirm === '') {
            respond(['success' => false, 'error' => 'Fill in your current password and a new one.'], 422);
        }
        if (strlen($new) < 8) {
            respond(['success' => false, 'error' => 'Your new password must be at least 8 characters long.'], 422);
        }
        if ($new !== $confirm) {
            respond(['success' => false, 'error' => 'The passwords do not match.'], 422);
        }

        try {
            $stmt = $conn->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($current, $user['password'])) {
                respond(['success' => false, 'error' => 'Your current password is incorrect.'], 422);
            }

            $stmt = $conn->prepare('UPDATE users SET password = :password WHERE id = :id');
            $stmt->execute([
                ':password' => password_hash($new, PASSWORD_DEFAULT),
                ':id' => $userId,
            ]);

            respond(['success' => true, 'message' => 'Password changed!']);
        } catch (PDOException $e) {
            respond(['success' => false, 'error' => 'Could not change your password. Please try again.'], 500);
        }
        break;
    }

    default:
        respond(['success' => false, 'error' => 'Unknown action.'], 400);
}
