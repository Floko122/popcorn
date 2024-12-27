<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entity_id = $_POST['entity_id'] ?? null;
    $new_state = $_POST['new_state'] ?? null;

    if (!$entity_id || !$new_state || !in_array($new_state, ['ready', 'played', 'dead'])) {
        http_response_code(400);
        echo "Invalid data provided.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE encounter_entities SET state = :new_state, last_updated = CURRENT_TIMESTAMP WHERE entity_id = :entity_id");
        $stmt->execute(['new_state' => $new_state, 'entity_id' => $entity_id]);
        echo "Entity state updated successfully.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
