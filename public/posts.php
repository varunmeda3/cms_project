<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Nibun\CmsProject\Database;

    session_start();

    // Establish connection first
    $config = require __DIR__ . '/../config/database.php';
    $db = new Database($config);
    $pdo = $db->getConnection();

    // Initialize pagination
    $limit = 3; // Number of posts per page
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Get total number of posts
    $countStmt = $pdo->query('SELECT COUNT(*) FROM posts');
    $totalPosts = $countStmt->fetchColumn();
    $totalPages = ceil($totalPosts / $limit);

    // Search functionality
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

    // Check if the user is an Admin or the post author
    $is_admin = ($_SESSION['role'] === 'Admin');

    // Get the sorting criteria from the query string (or set default to 'date_desc')
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_desc';

    // Determine the ORDER BY clause based on the selected sorting criteria
    switch ($sort_by) {
        case 'date_asc':
            $order_by = 'posts.created_at ASC';
            break;
        case 'title_asc':
            $order_by = 'posts.title ASC';
            break;
        case 'title_desc':
            $order_by = 'posts.title DESC';
            break;
        case 'author_asc':
            $order_by = 'users.username ASC';
            break;
        case 'author_desc':
            $order_by = 'users.username DESC';
            break;
        case 'date_desc':
        default:
            $order_by = 'posts.created_at DESC';
            break;    
    }

    if ($is_admin) {
        // Admins can see all posts, or posts where they're the author
        $stmt = $pdo->prepare(
    "SELECT posts.*, users.username 
            FROM posts
            JOIN users ON posts.author_id = users.id
            WHERE (author_id = :author_id OR status = 'published')
            AND (title LIKE :searchTerm OR content LIKE :searchTerm)
            ORDER BY $order_by
            LIMIT :limit OFFSET :offset"
            );
        // Bind parameters
        $stmt->bindParam(':author_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Non-admins only see published posts
        $stmt = $pdo->prepare(
    "SELECT posts.*, users.username 
            FROM posts 
            JOIN users ON posts.author_id = users.id
            WHERE status = 'published'
            AND (title LIKE :searchTerm OR content LIKE :searchTerm) 
            ORDER BY $order_by
            LIMIT :limit OFFSET :offset"
        );
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }

    $posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-dashboard {
            background-color: #f4a261; /* Muted warm orange */
            border-color: #f4a261;
            color: white;
        }
        .btn-dashboard:hover {
            background-color: #e76f51; /* Darker warm orange for hover */
            border-color: #e76f51;
            color: white;
        }
        .pagination .page-link {
            background-color: #ffffff;  /* White background */
            border-color: #cccccc;      /* Light gray border */
            color: #563d7c;             /* Dark warm purple text to keep the theme consistent */
        }
        .pagination .page-link:hover {
            background-color: #f0f0f0;  /* Slightly darker white for hover */
            border-color: #bcbcbc;      /* Neutral gray border on hover */
            color: #563d7c;             /* Keep text consistent */
        }
        .pagination .active .page-link {
            background-color: #e27d60;  /* Warm color for active page */
            border-color: #e27d60;
            color: #ffffff;             /* White text for contrast */
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Posts</h1>

        <!-- Back to Dashboard Button -->
        <div class="mb-3 text-start">
            <a href="dashboard.php" class="btn btn-secondary btn-md">Back to Dashboard</a>
        </div>

        <!-- Search Form -->
        <form action="posts.php" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search posts..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn btn-custom">Search</button>
            </div>
        </form>

        <!-- Sorting Form -->
        <form action="posts.php" method="GET" class="mb-3">
            <div class="input-group">
                <label for="sort_by" class="input-group-text">Sort by:</label>
                <select name="sort_by" id="sort_by" class="form-select" onchange="this.form.submit()">
                    <option value="date_desc" <?php if ($sort_by == 'date_desc') echo 'selected'; ?>>Date (Newest to Oldest)</option>
                    <option value="date_asc" <?php if ($sort_by == 'date_asc') echo 'selected'; ?>>Date (Oldest to Newest)</option>
                    <option value="title_asc" <?php if ($sort_by == 'title_asc') echo 'selected'; ?>>Title (A-Z)</option>
                    <option value="title_desc" <?php if ($sort_by == 'title_desc') echo 'selected'; ?>>Title (Z-A)</option>
                    <option value="author_asc" <?php if ($sort_by == 'author_asc') echo 'selected'; ?>>Author (A-Z)</option>
                    <option value="author_desc" <?php if ($sort_by == 'author_desc') echo 'selected'; ?>>Author (Z-A)</option>
                </select>
            </div>
        </form>

        <div class="row">
            <div class="col-md-12 mb-3 text-end">
                <a href="create_post.php" class="btn btn-custom">New Post</a>
            </div>

            <!-- Post Loop -->
            <?php foreach ($posts as $post): ?>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <?php if ($post['image']): ?>
                            <img src="/cms_project/<?php echo $post['image']; ?>" class="card-img-top" alt="Post Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $post['title']; ?></h5>
                            <p class="card-text">By: <?php echo $post['username']; ?> on <?php echo $post['created_at']; ?></p>
                            <p class="card-text"><?php echo substr($post['content'], 0, 100); ?>...</p>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav class="pagination-controls">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page > 1) { echo 'posts.php?page=' . ($page - 1); } else { echo '#'; } ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="posts.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="<?php if ($page < $totalPages) { echo 'posts.php?page=' . ($page + 1); } else { echo '#'; } ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</body>
</html>