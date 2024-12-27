<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	echo "dsasd";
    $encounter_id = $_POST['encounter_id'] ?? null;

    if (!$encounter_id) {
        http_response_code(400);
        echo "Encounter ID is required.";
        exit();
	}
	
    try {
		//Get encounter id
        $stmt = $pdo->prepare("SELECT id FROM encounters WHERE access_token = :group_id");
        $stmt->execute(['group_id' => $encounter_id]);
        $encounter_db_id = $stmt->fetch(PDO::FETCH_ASSOC)["id"];
		
        $stmt = $pdo->prepare("
            UPDATE encounter_entities
            SET state = 'ready'
            WHERE state = 'played' AND encounter_id = :encounter_id
        ");
        $stmt->execute(['encounter_id' => $encounter_db_id]);
        echo "Turn ended successfully.";
    } catch (PDOException $e) {
        //http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
}
?>
