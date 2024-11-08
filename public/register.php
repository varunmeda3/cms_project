<!-- public/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8efd4; /* Warm color background */
            color: #563d7c; /* Warm color for text */
        }
        .form-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .btn-custom {
            background-color: #ff7f50;
            border-color: #ff7f50;
            color: white;
        }
        .btn-custom:hover {
            background-color: #ff6347;
            border-color: #ff6347;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center">Register</h2>
            <form action="register.php" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-custom btn-block">Register</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php">Back to Login</a>
            </div>
        </div>
    </div>

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
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($user->register($username, $email, $password)) {
            echo "User registration successful. Head back to Login!";
        } else {
            echo "User registration failed!";
        }
    }
?>

</body>
</html>