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
    $stmt = $pdo->prepare("
        SELECT entities.id, entities.name, groups.name AS group_name, entities.group_id
        FROM entities
        LEFT JOIN groups ON entities.group_id = groups.id
		where groups.user_id = :user_id
        ORDER BY groups.name, entities.name
    ");
    $stmt->execute(['user_id' => $user_id]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($entities);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>