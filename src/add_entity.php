<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entity_name = $_POST['entity_name'];
    $group_id = $_POST['group_id'];

    if (empty($entity_name)) {
        http_response_code(400);
        echo "Entity name is required.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO entities (name, group_id) VALUES (:name, :group_id)");
        $stmt->execute([
            'name' => $entity_name,
            'group_id' => $group_id
        ]);
		if (isset($_POST["returnData"])) {
			$stmt = $pdo->prepare("Select id from entities where name=:name and group_id=:group_id");
			$stmt->execute([
				'name' => $entity_name,
				'group_id' => $group_id
			]);
			$entity = $stmt->fetch(PDO::FETCH_ASSOC);
			echo '{"id": '.$entity['id'].'}';
		}else{
			echo "Entity added successfully!";
			header("Location: dashboard.php"); // Redirect to dashboard
		}
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
