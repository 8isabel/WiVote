<?php
session_start();

require_once 'config/con.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int) $_SESSION['user_id'] : 0;

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$filterCategory = (int) ($_GET['category'] ?? 0);

$categories = [];
try {
    $categories = $conn->query('SELECT id, name FROM categories ORDER BY id ASC')->fetchAll();
} catch (PDOException $e) {

}

function redirectBack(int $category, string $anchor = ''): void
{
    header('Location: polls.php' . ($category > 0 ? '?category=' . $category : '') . $anchor);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'vote') {
    if (!$isLoggedIn) {
        header('Location: login.php');
        exit;
    }

    $pollId   = (int) ($_POST['poll_id'] ?? 0);
    $optionId = (int) ($_POST['option_id'] ?? 0);

    if ($pollId > 0 && $optionId > 0) {
        try {
            $stmt = $conn->prepare('SELECT id FROM poll_options WHERE id = :oid AND poll_id = :pid');
            $stmt->execute([':oid' => $optionId, ':pid' => $pollId]);

            if ($stmt->fetch()) {
                $stmt = $conn->prepare('INSERT INTO votes (poll_id, poll_option_id, user_id) VALUES (:pid, :oid, :uid)');
                $stmt->execute([':pid' => $pollId, ':oid' => $optionId, ':uid' => $userId]);
                $_SESSION['flash'] = 'Vote counted!';
            }
        } catch (PDOException $e) {

            $_SESSION['flash'] = 'You already voted on this poll.';
        }
    }

    redirectBack($filterCategory, '#poll-' . $pollId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'comment') {
    if (!$isLoggedIn) {
        header('Location: login.php');
        exit;
    }

    $targetType = ($_POST['target_type'] ?? '') === 'post' ? 'post' : 'poll';
    $targetId   = (int) ($_POST['target_id'] ?? 0);
    $parentId   = (int) ($_POST['parent_id'] ?? 0);
    $content    = trim($_POST['content'] ?? '');

    if ($targetId > 0 && $content !== '' && mb_strlen($content) <= 500) {
        try {
            $stmt = $conn->prepare('INSERT INTO comments (target_type, target_id, user_id, content, parent_id) VALUES (:type, :id, :uid, :content, :parent)');
            $stmt->execute([
                ':type'    => $targetType,
                ':id'      => $targetId,
                ':uid'     => $userId,
                ':content' => $content,
                ':parent'  => $parentId > 0 ? $parentId : null,
            ]);
        } catch (PDOException $e) {
            $_SESSION['flash'] = 'Could not post your comment. Did you run the database setup in phpMyAdmin?';
        }
    }

    redirectBack($filterCategory, '#' . $targetType . '-' . $targetId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_item') {
    if (!$isLoggedIn) {
        header('Location: login.php');
        exit;
    }

    $kind   = ($_POST['kind'] ?? '') === 'post' ? 'post' : 'poll';
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $table  = $kind === 'post' ? 'posts' : 'polls';
    $isAdmin = ($_SESSION['role'] ?? '') === 'admin';

    if ($itemId > 0) {
        try {
            $stmt = $conn->prepare("SELECT user_id, image_url FROM $table WHERE id = :id");
            $stmt->execute([':id' => $itemId]);
            $row = $stmt->fetch();

            if ($row && ((int) $row['user_id'] === $userId || $isAdmin)) {
                try {
                    $conn->prepare("DELETE FROM comment_likes WHERE comment_id IN (SELECT id FROM comments WHERE target_type = :t AND target_id = :id)")
                        ->execute([':t' => $kind, ':id' => $itemId]);
                } catch (PDOException $ignore) {
                }
                try {
                    $conn->prepare("DELETE FROM comments WHERE target_type = :t AND target_id = :id AND parent_id IS NOT NULL")
                        ->execute([':t' => $kind, ':id' => $itemId]);
                } catch (PDOException $ignore) {
                }
                $conn->prepare("DELETE FROM comments WHERE target_type = :t AND target_id = :id")
                    ->execute([':t' => $kind, ':id' => $itemId]);
                $conn->prepare("DELETE FROM $table WHERE id = :id")->execute([':id' => $itemId]);

                $img = (string) ($row['image_url'] ?? '');
                if (strpos($img, 'uploads/') === 0 && strpos($img, '..') === false) {
                    $file = __DIR__ . '/' . $img;
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
                $_SESSION['flash'] = ucfirst($kind) . ' deleted.';
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = 'Could not delete this ' . $kind . '.';
        }
    }

    redirectBack($filterCategory);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like_comment') {
    if (!$isLoggedIn) {
        header('Location: login.php');
        exit;
    }

    $commentId = (int) ($_POST['comment_id'] ?? 0);

    if ($commentId > 0) {
        try {
            $stmt = $conn->prepare('SELECT id FROM comments WHERE id = :cid');
            $stmt->execute([':cid' => $commentId]);

            if ($stmt->fetch()) {
                $stmt = $conn->prepare('SELECT comment_id FROM comment_likes WHERE comment_id = :cid AND user_id = :uid');
                $stmt->execute([':cid' => $commentId, ':uid' => $userId]);

                if ($stmt->fetch()) {
                    $stmt = $conn->prepare('DELETE FROM comment_likes WHERE comment_id = :cid AND user_id = :uid');
                } else {
                    $stmt = $conn->prepare('INSERT INTO comment_likes (comment_id, user_id) VALUES (:cid, :uid)');
                }
                $stmt->execute([':cid' => $commentId, ':uid' => $userId]);

                $stmt = $conn->prepare('SELECT target_type, target_id FROM comments WHERE id = :cid');
                $stmt->execute([':cid' => $commentId]);
                $target = $stmt->fetch();

                if ($target) {
                    redirectBack($filterCategory, '#' . $target['target_type'] . '-' . $target['target_id']);
                }
            }
        } catch (PDOException $e) {
            $_SESSION['flash'] = 'Could not process your like. Did you run the database setup in phpMyAdmin?';
        }
    }

    redirectBack($filterCategory);
}

$topics = [];
try {
    if ($filterCategory > 0) {
        $stmt = $conn->prepare("
            SELECT p.id, p.title, p.image_url,
                   (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id) AS total_votes
            FROM polls p
            WHERE p.category_id = :cat
            ORDER BY total_votes DESC, p.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([':cat' => $filterCategory]);
        $topics = $stmt->fetchAll();
    } else {
        $topics = $conn->query("
            SELECT p.id, p.title, p.image_url,
                   (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id) AS total_votes
            FROM polls p
            ORDER BY total_votes DESC, p.created_at DESC
            LIMIT 10
        ")->fetchAll();
    }
} catch (PDOException $e) {

}

$polls = [];
$optionsByPoll = [];
$myVotes = [];

try {
    $sql = "
        SELECT p.id, p.user_id, p.title, p.image_url, p.created_at,
               u.username,
               c.name AS category_name,
               m.face_shape, m.hair, m.hair_color, m.eyebrows, m.eyes, m.eye_color, m.skin_color,
               m.nose, m.mouth, m.facial_hair, m.glasses, m.shirt, m.shirt_color,
               (SELECT COUNT(*) FROM votes v WHERE v.poll_id = p.id) AS total_votes
        FROM polls p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN miis m ON m.user_id = u.id
    ";
    $params = [];
    if ($filterCategory > 0) {
        $sql .= ' WHERE p.category_id = :cid';
        $params[':cid'] = $filterCategory;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $polls = $stmt->fetchAll();

    if ($polls) {
        $stmt = $conn->query("
            SELECT po.id, po.poll_id, po.name,
                   (SELECT COUNT(*) FROM votes v WHERE v.poll_option_id = po.id) AS option_votes
            FROM poll_options po
            ORDER BY po.id ASC
        ");
        foreach ($stmt->fetchAll() as $opt) {
            $optionsByPoll[(int) $opt['poll_id']][] = $opt;
        }

        if ($isLoggedIn) {
            $stmt = $conn->prepare('SELECT poll_id, poll_option_id FROM votes WHERE user_id = :uid');
            $stmt->execute([':uid' => $userId]);
            foreach ($stmt->fetchAll() as $v) {
                $myVotes[(int) $v['poll_id']] = (int) $v['poll_option_id'];
            }
        }
    }
} catch (PDOException $e) {
    $polls = [];
}

$posts = [];
try {
    $sql = "
        SELECT p.id, p.user_id, p.title, p.content, p.image_url, p.created_at,
               u.username,
               c.name AS category_name,
               m.face_shape, m.hair, m.hair_color, m.eyebrows, m.eyes, m.eye_color, m.skin_color,
               m.nose, m.mouth, m.facial_hair, m.glasses, m.shirt, m.shirt_color
        FROM posts p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN miis m ON m.user_id = u.id
    ";
    $params = [];
    if ($filterCategory > 0) {
        $sql .= ' WHERE p.category_id = :cid';
        $params[':cid'] = $filterCategory;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

$commentsByTarget = ['poll' => [], 'post' => []];
try {
    $stmt = $conn->prepare("
        SELECT c.id, c.target_type, c.target_id, c.parent_id, c.content, c.created_at, u.username,
               m.face_shape, m.hair, m.hair_color, m.eyebrows, m.eyes, m.eye_color, m.skin_color,
               m.nose, m.mouth, m.facial_hair, m.glasses, m.shirt, m.shirt_color,
               (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count,
               EXISTS(SELECT 1 FROM comment_likes cl2 WHERE cl2.comment_id = c.id AND cl2.user_id = :me) AS liked_by_me
        FROM comments c
        JOIN users u ON u.id = c.user_id
        LEFT JOIN miis m ON m.user_id = u.id
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([':me' => $userId]);
    foreach ($stmt->fetchAll() as $c) {
        $commentsByTarget[$c['target_type']][(int) $c['target_id']][] = $c;
    }
} catch (PDOException $e) {

    try {
        $stmt = $conn->query("
            SELECT c.id, c.target_type, c.target_id, c.content, c.created_at, u.username,
                   m.face_shape, m.hair, m.hair_color, m.eyebrows, m.eyes, m.eye_color, m.skin_color,
                   m.nose, m.mouth, m.facial_hair, m.glasses, m.shirt, m.shirt_color
            FROM comments c
            JOIN users u ON u.id = c.user_id
            LEFT JOIN miis m ON m.user_id = u.id
            ORDER BY c.created_at ASC
        ");
        foreach ($stmt->fetchAll() as $c) {
            $commentsByTarget[$c['target_type']][(int) $c['target_id']][] = $c;
        }
    } catch (PDOException $e2) {

    }
}

$feed = [];
foreach ($polls as $poll) {
    $feed[] = ['kind' => 'poll', 'data' => $poll, 'created_at' => $poll['created_at']];
}
foreach ($posts as $post) {
    $feed[] = ['kind' => 'post', 'data' => $post, 'created_at' => $post['created_at']];
}
usort($feed, function ($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});

include 'views/polls_view.php';
