<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entity_id = $_POST['entity_id'] ?? null;
    $encounter_id = $_POST['encounter_id'] ?? null;

    if (!$entity_id || !$encounter_id) {
        http_response_code(400);
        echo "Entity ID and Encounter ID are required.";
        exit();
    }

    try {
		//Get encounter id
        $stmt = $pdo->prepare("SELECT id FROM encounters WHERE access_token = :group_id");
        $stmt->execute(['group_id' => $encounter_id]);
        $encounter_db_id = $stmt->fetch(PDO::FETCH_ASSOC)["id"];
		
        $stmt = $pdo->prepare("
            DELETE FROM encounter_entities
            WHERE entity_id = :entity_id AND encounter_id = :encounter_id
        ");
        $stmt->execute([
            'entity_id' => $entity_id,
            'encounter_id' => $encounter_db_id
        ]);
        echo "Entity removed from encounter successfully.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
