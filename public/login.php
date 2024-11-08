<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Nibun\CmsProject\Database;
    use Nibun\CmsProject\User;

    $config = require __DIR__ . '/../config/database.php';
    $db = new Database($config);
    $pdo = $db->getConnection();
    $user = new User($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        // Check if the user is logged in
        if ($user->login($username,$password)) {
            // Fetch the user's details from the database, including their role
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $userData = $stmt->fetch();

            if ($userData) {
                session_start();
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['role'] = $userData['role']; // Store the role in the session
                header('Location: dashboard.php');
                exit;
            } else {
                echo '<div class="alert alert-danger mt-3">User not found...</div>';
            }
        } else {
            echo '<div class="alert alert-danger mt-3">Invalid username or password!</div>';
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8e5d3;
        }
        .login-container {
            max-width: 480px;
            margin: 0 auto;
            margin-top: 100px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .login-btn {
            background-color: #e27d60;
            border-color: #e27d60;
            color: white;
        }
        .login-btn:hover {
            background-color: #c4563c;
            border-color: #c4563c;
            color: white;
        }
        .form-control {
            border-radius: 4px;
        }
        .register-link {
            color: #d99e73;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Login</h2>
            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn login-btn w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="register-link">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>