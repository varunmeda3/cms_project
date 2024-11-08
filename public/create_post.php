<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Nibun\CmsProject\Database;

    session_start();
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Editor') {
        echo "You do NOT have permission to create this post...";
        exit;
    }

    // Establish database connection first
    $config = require __DIR__ . '/../config/database.php';
    $db = new Database($config);
    $pdo = $db->getConnection();

    // Fetch categories after the connection is established
    $categoryStmt = $pdo->query('SELECT * FROM categories');
    $categories = $categoryStmt->fetchAll();

    // Fetch tags after the connection is established
    $tagStmt = $pdo->query('SELECT * FROM tags');
    $tags = $tagStmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Capture the data
        $title = $_POST['title'];
        $content = $_POST['content'];
        $author_id = $_SESSION['user_id'];
        $status = $_POST['status'];
        // Default category if not selected
        $category_id = isset($_POST['category_id']) && !empty($_POST['category_id']) ? $_POST['category_id'] : 1; // Use default category ID

        // Handle the file upload
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            // Use the relative path to store in the database
            $imagePath = 'uploads/' . basename($_FILES["image"]["name"]);
            // Use the absolute path for moving the file
            $absolutePath = '/Applications/XAMPP/xamppfiles/htdocs/cms_project/' . $imagePath;

            // Move the uploaded files
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $absolutePath)) {
                echo "File uploaded successfully: " . $imagePath;
            } else {
                echo "Failed to move uploaded file.";
                echo "Image upload error: " . $_FILES['image']['error'];
                echo "Absolute path: " . $absolutePath;
            }
        }

        // Insert the post with the selected category or default category
        $stmt = $pdo->prepare('INSERT INTO posts (title, content, author_id, category_id, status, image) VALUES (?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$title, $content, $author_id, $category_id, $status, $imagePath])) {
            // Get the ID of the insert post
            $post_id = $pdo->lastInsertId();

            // Handle tags (if any tags are selected)
            if (isset($_POST['tags'])) {
                $tags = $_POST['tags'];
                foreach ($tags as $tag_id) {
                    // Insert each tag associated with the post into the post_tags table
                    $tagStmt = $pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)');
                    $tagStmt->execute([$post_id, $tag_id]);
                }
            }

            // Redirect to posts page after success
            header('Location: posts.php');
        } else {
            echo "Failed to create post.";
        }
    }
?>

<!-- HTML Form with Bootstrap -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8e5d3;
            color: #563d7c;
        }
        .btn-primary {
            background-color: #e27d60;
            border-color: #e27d60;
            color: white;
        }
        .btn-primary:hover {
            background-color: #c4563c;
            border-color: #c4563c;
            color: white;
        }
        .btn-link {
            color: #563d7c;
        }
    </style>
    <!-- Include CKEditor -->
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
</head>
<body class="container my-5">
    <h1 class="text-center mb-4">Create a New Post</h1>

    <form action="create_post.php" method="post" enctype="multipart/form-data" class="shadow p-4 rounded bg-light">
        <!-- Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter title" required>
        </div>

        <!-- Content -->
        <div class="mb-3">
            <label for="content" class="form-label">Content</label>
            <textarea name="content" class="form-control" placeholder="Write your content here" required></textarea>
        </div>

        <!-- Category Dropdown -->
        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tags (Checkboxes) -->
        <div class="mb-3">
            <label for="tags" class="form-label">Tags</label><br>
            <?php foreach ($tags as $tag): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                    <label class="form-check-label"><?php echo $tag['name']; ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Image Upload -->
        <div class="mb-3">
            <label for="image" class="form-label">Upload an Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <!-- Post Status -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="draft">Save as Draft</option>
                <option value="published">Publish Now</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100">Create Post</button>
    </form>

    <div class="text-center mt-3">
        <a href="posts.php" class="btn btn-link">Back to Posts</a>
    </div>

    <!-- Initialize CKEditor -->
    <script>
        CKEDITOR.replace('content');
    </script>
</body>
</html>