<?php
session_start();

require_once 'config/con.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int) $_SESSION['user_id'] : 0;
$username   = $_SESSION['username'] ?? 'Guest';

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function handleImageUpload(?string &$flash): ?string
{
    if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $flash = 'Image upload failed, try a smaller file (max 5 MB).';
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $flash = 'That image is too big (max 5 MB).';
        return null;
    }

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) {
        $flash = 'Only JPG, PNG, GIF or WEBP images are allowed.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if ($mime !== false && !in_array($mime, $allowed, true)) {
        $flash = 'That file is not a real image.';
        return null;
    }

    if (!is_dir(__DIR__ . '/uploads') && !mkdir(__DIR__ . '/uploads', 0775, true)) {
        $flash = 'Could not create the uploads folder. Check the folder permissions.';
        return null;
    }

    $name = 'img_' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $name)) {
        $flash = 'Could not save the image. Check the permissions of the uploads folder.';
        return null;
    }

    return 'uploads/' . $name;
}

if (!$isLoggedIn) {
    include 'views/create_view.php';
    exit;
}

$categories = [];
try {
    $categories = $conn->query('SELECT id, name FROM categories ORDER BY id ASC')->fetchAll();
} catch (PDOException $e) {

}

$type = $_GET['type'] ?? 'poll';
$type = in_array($type, ['poll', 'post'], true) ? $type : 'poll';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_poll') {
    $type = 'poll';
    $title      = trim($_POST['title'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $imageUrl   = handleImageUpload($flash);
    $options    = array_values(array_filter(array_map('trim', $_POST['options'] ?? []), function ($o) { return $o !== ''; }));

    if ($title === '' || count($options) < 2) {
        $_SESSION['flash'] = 'A poll needs a title and at least two options.';
    } elseif (mb_strlen($title) > 150) {
        $_SESSION['flash'] = 'That title is too long (max 150 characters).';
    } else {
        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare('INSERT INTO polls (user_id, category_id, title, image_url) VALUES (:uid, :cid, :title, :img)');
            $stmt->execute([
                ':uid'   => $userId,
                ':cid'   => $categoryId > 0 ? $categoryId : null,
                ':title' => $title,
                ':img'   => $imageUrl,
            ]);
            $pollId = (int) $conn->lastInsertId();

            $optStmt = $conn->prepare('INSERT INTO poll_options (poll_id, name) VALUES (:pid, :name)');
            foreach ($options as $option) {
                $optStmt->execute([':pid' => $pollId, ':name' => $option]);
            }

            $conn->commit();
            $_SESSION['flash'] = 'Your poll is online!';
            header('Location: polls.php' . ($categoryId > 0 ? '?category=' . $categoryId : '') . '#poll-' . $pollId);
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $_SESSION['flash'] = 'Could not save your poll. Did you run the database setup in phpMyAdmin?';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
    $type = 'post';
    $title      = trim($_POST['title'] ?? '');
    $content    = trim($_POST['content'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $imageUrl   = handleImageUpload($flash);

    if ($title === '') {
        $_SESSION['flash'] = 'A post needs a title.';
    } elseif (mb_strlen($title) > 150) {
        $_SESSION['flash'] = 'That title is too long (max 150 characters).';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO posts (user_id, category_id, title, content, image_url) VALUES (:uid, :cid, :title, :content, :img)');
            $stmt->execute([
                ':uid'     => $userId,
                ':cid'     => $categoryId > 0 ? $categoryId : null,
                ':title'   => $title,
                ':content' => $content !== '' ? $content : null,
                ':img'     => $imageUrl,
            ]);
            $postId = (int) $conn->lastInsertId();

            $_SESSION['flash'] = 'Your post is online!';
            header('Location: polls.php' . ($categoryId > 0 ? '?category=' . $categoryId : '') . '#post-' . $postId);
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash'] = 'Could not save your post. Did you run the database setup in phpMyAdmin?';
        }
    }
}

include 'views/create_view.php';
