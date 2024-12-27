<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entity_id = $_POST['entity_id'];
    $group_id = $_POST['group_id'];

    if (empty($entity_id)) {
        http_response_code(400);
        echo "Entity ID is required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE entities SET group_id = :group_id WHERE id = :entity_id");
        $stmt->execute([
            'group_id' => $group_id ?: null, // Set group_id to NULL if not provided
            'entity_id' => $entity_id
        ]);
        echo "Entity group updated successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
