<?php
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8e5d3;
            color: #563d7c;
        }
        .dashboard-container {
            max-width: 480px;
            margin: 0 auto;
            margin-top: 100px;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .dashboard-container h1 {
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
    </style>
</head>
<body>
    <div class="container dashboard-container">
        <h1>Welcome to the Dashboard!</h1>
        <p>Hello, you have successfully logged in. Use the links below to navigate the system:</p>
        
        <div class="d-grid gap-2">
            <a href="posts.php" class="btn btn-custom btn-lg">Go to Posts</a>
            <a href="logout.php" class="btn btn-secondary btn-lg">Logout</a>
        </div>
    </div>
</body>
</html>