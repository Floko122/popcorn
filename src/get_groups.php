<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id, name FROM groups WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($groups);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
