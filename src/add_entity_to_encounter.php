<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $encounter_id = $_POST['encounter_id'] ?? null;
    $entity_id = $_POST['entity_id'] ?? null;

    if (!$encounter_id || !$entity_id) {
        http_response_code(400);
        echo "Encounter ID and Entity ID are required.";
        exit();
    }

    try {
		//Get encounter id
        $stmt = $pdo->prepare("SELECT id FROM encounters WHERE access_token = :group_id");
        $stmt->execute(['group_id' => $encounter_id]);
        $encounter_db_id = $stmt->fetch(PDO::FETCH_ASSOC)["id"];
		
        $stmt = $pdo->prepare("
            INSERT INTO encounter_entities (encounter_id, entity_id, state)
            VALUES (:encounter_id, :entity_id, 'ready')
        ");
        $stmt->execute(['encounter_id' => $encounter_db_id, 'entity_id' => $entity_id]);
        echo "Entity added to encounter successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
