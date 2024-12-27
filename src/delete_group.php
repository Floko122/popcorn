<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if (isset($_GET['id'])) {
    $group_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM groups WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $group_id, 'user_id' => $user_id]);
        echo "Group deleted successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
