<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        die("Username and password are required.");
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :passwordHash)");
        $stmt->execute(['username' => $username, 'passwordHash' => $passwordHash]);

        // Automatically log in the user after registration
        session_start();
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: dashboard.php"); // Redirect to dashboard
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            echo "Username already taken.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>
