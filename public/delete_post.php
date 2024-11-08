<!-- public/delete_post.php -->

<?php

require __DIR__ . '/../vendor/autoload.php';

use Nibun\CmsProject\Database;

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Editor') {
    echo "You do NOT have permission to delete this post...";
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = require __DIR__ . '/../config/database.php';
$db = new Database($config);
$pdo = $db->getConnection();

// Check if the post ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $postId = $_GET['id'];

    // Check if the post exists before deleting
    $checkPostStmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $checkPostStmt->execute([$postId]);
    $post = $checkPostStmt->fetch();

    if ($post) {
        // Delete related tags first from post_tags
        $deleteTagsStmt = $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?');
        $deleteTagsStmt->execute([$postId]);

        // Delete the post from the database
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        if ($stmt->execute([$postId])) {
            header('Location: posts.php');
            exit();
        } else {
            echo "Failed to delete post...";
        }
    } else {
        echo "Invalid request...";
        exit;
    }
}

?>