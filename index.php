<?php
session_start();

require_once 'config/con.php';

$fallbackCategories = [
    ['id' => 1, 'name' => 'Food',           'image_url' => 'media/cat_food.png',   'poll_count' => 0, 'total_votes' => 0],
    ['id' => 2, 'name' => 'Music',          'image_url' => 'media/cat_music.png',   'poll_count' => 0, 'total_votes' => 0],
    ['id' => 3, 'name' => 'Games',          'image_url' => 'media/cat_games.png',   'poll_count' => 0, 'total_votes' => 0],
    ['id' => 4, 'name' => 'Movies & Series', 'image_url' => 'media/cat_movies.png', 'poll_count' => 0, 'total_votes' => 0],
    ['id' => 5, 'name' => 'Fashion',        'image_url' => 'media/cat_fashion.png',  'poll_count' => 0, 'total_votes' => 0],
    ['id' => 6, 'name' => 'Sports',         'image_url' => 'media/cat_sports.png',  'poll_count' => 0, 'total_votes' => 0],
];

$categories = [];
$databaseReady = false;

try {
    $stmt = $conn->query("
        SELECT c.id,
               c.name,
               c.image_url,
               (SELECT COUNT(*) FROM polls p WHERE p.category_id = c.id) AS poll_count,
               (SELECT COUNT(*) FROM votes v JOIN polls p ON p.id = v.poll_id WHERE p.category_id = c.id) AS total_votes
        FROM categories c
        ORDER BY c.id ASC
    ");
    $categories = $stmt->fetchAll();
    $databaseReady = !empty($categories);
} catch (PDOException $e) {

}

if (!$databaseReady) {
    $categories = $fallbackCategories;
}

include 'views/index_view.php';
