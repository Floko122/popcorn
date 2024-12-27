<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $group_name = $_POST['group_name'];

    if (empty($group_name)) {
        http_response_code(400);
        echo "Group name is required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO groups (name, user_id) VALUES (:name, :user_id)");
        $stmt->execute(['name' => $group_name, 'user_id' => $user_id]);
        echo "Group added successfully!";
		header("Location: dashboard.php"); // Redirect to dashboard
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
