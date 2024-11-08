<?php
    namespace Nibun\CmsProject;

    class User {
        // user properties and methods for authentication
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }

        public function register($username, $email, $password) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            return $stmt->execute([$username, $email, $passwordHash]);
        }
        
        public function login($username, $password) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                return true;
            }

            return false;
        }

        public function logout() {
            session_start();
            session_destroy();
        }
    }
?>