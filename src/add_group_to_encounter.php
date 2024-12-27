<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $encounter_id = $_POST['encounter_id'] ?? null;
    $group_id = $_POST['group_id'] ?? null;

    if (!$encounter_id || !$group_id) {
        http_response_code(400);
        echo "Encounter ID and Group ID are required.";
        exit();
    }

    try {
        // Fetch all entities in the group
        $stmt = $pdo->prepare("SELECT id FROM entities WHERE group_id = :group_id");
        $stmt->execute(['group_id' => $group_id]);
        $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//Get encounter id
        $stmt = $pdo->prepare("SELECT id FROM encounters WHERE access_token = :group_id");
        $stmt->execute(['group_id' => $encounter_id]);
        $encounter_db_id = $stmt->fetch(PDO::FETCH_ASSOC)["id"];
		
        // Insert each entity into the encounter
        $insertStmt = $pdo->prepare("
            INSERT INTO encounter_entities (encounter_id, entity_id, state)
            VALUES (:encounter_id, :entity_id, 'ready')
        ");
        foreach ($entities as $entity) {
            $insertStmt->execute(['encounter_id' => $encounter_db_id, 'entity_id' => $entity['id']]);
        }

        echo "Group added to encounter successfully!";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
