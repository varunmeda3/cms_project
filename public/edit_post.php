<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Nibun\CmsProject\Database;

    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Editor') {
        echo "You do NOT have permission to edit this post...";
        exit;
    }

    // Establish database connection
    $config = require __DIR__ . '/../config/database.php';
    $db = new Database($config);
    $pdo = $db->getConnection();

    // Check if the post ID is provided in the URL and is valid
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $postId = (int)$_GET['id'];

        // Fetch the post details from the database
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$postId]);
        $post = $stmt->fetch();

        // Check if the post exists
        if ($post) {
            // Fetch categories
            $categoryStmt = $pdo->query('SELECT * FROM categories');
            $categories = $categoryStmt->fetchAll();

            //Fatch all tags
            $tagStmt = $pdo->query('SELECT * FROM tags');
            $tags = $tagStmt->fetchAll();

            // Fetch current tags for post
            $currentTagsStmt = $pdo->prepare('SELECT tag_id FROM post_tags WHERE post_id = ?');
            $currentTagsStmt->execute([$postId]);
            $currentTags = $currentTagsStmt->fetchAll(PDO::FETCH_COLUMN); // Get current tag IDs

            // Check if the form is submitted to update the post
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = $_POST['title'];
                $content = $_POST['content'];
                $category_id = $_POST['category_id'];
                $status = $_POST['status'];

                // Update the post in the database
                $updateStmt = $pdo->prepare('UPDATE posts SET title = ?, content = ?, category_id = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
                if ($updateStmt->execute([$title, $content, $category_id, $status, $postId])) {

                    // Update tags
                    $deleteTagsStmt = $pdo->prepare('DELETE FROM post_tags WHERE post_id = ?');
                    $deleteTagsStmt->execute([$postId]);

                    if (isset($_POST['tags'])) {
                        $newTags = $_POST['tags'];
                        foreach ($newTags as $tag_id) {
                            $insertTagStmt = $pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)');
                            $insertTagStmt->execute([$postId, $tag_id]);
                        }
                    }

                    header('Location: posts.php');
                } else {
                    echo "Failed to update post...";
                }
            }
        } else {
            echo "Post not found...";
            exit;
        }
    } else {
        echo "Invalid request...";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body {
            background-color: #f8e5d3;
            color: #563d7c;
        }
        .btn-custom {
            background-color: #e27d60;
            border-color: #e27d60;
            color: white;
        }
        .btn-custom:hover {
            background-color: #c4563c;
            border-color: #c4563c;
            color: white;
        }
        .btn-link {
            color: #563d7c;
        }
    </style>
</head>
<body class="container my-5">
    <h1 class="text-center mb-4">Edit Post</h1>

    <form action="edit_post.php?id=<?php echo $postId; ?>" method="post" enctype="multipart/form-data" class="shadow p-4 rounded bg-light">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea class="form-control" name="content" id="content" required><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
        </div>

        <!-- Category Dropdown -->
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" name="category_id" id="category" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tags Checkbox -->
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label><br>
            <?php foreach ($tags as $tag): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $currentTags) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="tags"><?php echo $tag['name']; ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Image Upload -->
        <div class="mb-3">
            <label for="image" class="form-label">Upload an Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <!-- Status of Post -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="status">
                <option value="draft" <?php if ($post['status'] === 'draft') echo 'selected'; ?>>Save as Draft</option>
                <option value="published" <?php if ($post['status'] === 'published') echo 'selected'; ?>>Publish Now</option>
            </select>
        </div>

        <button type="submit" class="btn btn-custom btn-lg w-100">Update Post</button>
    </form>

    <div class="text-center mt-3">
        <a href="posts.php" class="btn btn-link">Back to Posts</a>
    </div>

    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>