<?php
    require __DIR__ . '/../vendor/autoload.php';

    use Nibun\CmsProject\User;

    // Start the session
    session_start();
    
    // Destroy all session data
    session_destroy();

    // Unset all session variables
    $_SESSION = [];

    // Optionally, destroy the session cookie (if you use cookies for sessions)
    if (ini_get("session.use_cookies")) {
        $param = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Redirect to the login page
    header('Location: login.php');
    exit;
?>