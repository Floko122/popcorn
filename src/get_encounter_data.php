<?php
require 'config.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid access token.']);
    exit();
}

try {
    // Fetch encounter details
    $stmt = $pdo->prepare("
        SELECT encounters.id, encounters.name, encounters.status
        FROM encounters
        WHERE access_token = :token
    ");
    $stmt->execute(['token' => $token]);
    $encounter = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$encounter || $encounter['status'] !== 'open') {
        http_response_code(403);
        echo json_encode(['error' => 'Encounter is not open or does not exist.']);
        exit();
    }

    // Fetch entities grouped by state
    $stmt = $pdo->prepare("
        SELECT entities.id, entities.name, encounter_entities.state
        FROM encounter_entities
        JOIN entities ON encounter_entities.entity_id = entities.id
        WHERE encounter_entities.encounter_id = :encounter_id
        ORDER BY encounter_entities.last_updated ASC
    ");
    $stmt->execute(['encounter_id' => $encounter['id']]);
    $entities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['encounter' => $encounter, 'entities' => $entities]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
