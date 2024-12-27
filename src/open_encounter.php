<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $encounter_id = $_POST['encounter_id'];

    if (empty($encounter_id)) {
        http_response_code(400);
        echo "Encounter ID is required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE encounters SET status = 'open' WHERE id = :encounter_id");
        $stmt->execute(['encounter_id' => $encounter_id]);
        echo "Encounter opened successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
