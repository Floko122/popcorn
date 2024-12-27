<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if (isset($_GET['id'])) {
    $entity_id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM entities WHERE id = :id");
        $stmt->execute(['id' => $entity_id]);
        echo "Entity deleted successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
