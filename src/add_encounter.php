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
    $encounter_name = $_POST['encounter_name'];
    $access_token = bin2hex(random_bytes(16)); // Generate unique token

    if (empty($encounter_name)) {
        http_response_code(400);
        echo "Encounter name is required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO encounters (name, user_id, access_token) VALUES (:name, :user_id, :access_token)");
        $stmt->execute(['name' => $encounter_name, 'user_id' => $user_id, 'access_token' => $access_token]);
        echo "Encounter added successfully!";
		header("Location: dashboard.php"); // Redirect to dashboard
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
