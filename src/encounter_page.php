<?php
session_start();
require 'config.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    die("Invalid access token.");
}

$myEncounter = False;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
	try {
		$stmt = $pdo->prepare("SELECT user_id FROM encounters WHERE access_token = :token");
		$stmt->execute(['token' => $token]);
		$encounter = $stmt->fetch(PDO::FETCH_ASSOC);
		if($user_id == $encounter["user_id"]){
			$myEncounter=True;
		}
	} catch (PDOException $e) {
		//Do nothing just do not show the add panel
	}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="css/style.css" rel="stylesheet">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=skull" />
    <title>Encounter</title>
</head>
<body>
    <div class="container fixed-top mt-2">
		<?php
		if($myEncounter){
			echo '<div class="state-section">
				<h3>Add Entities to Encounter</h3>
				<form id="add-entity-form">
					<div class="d-flex align-items-center mt-3">
						<input type="text" id="entity-name" placeholder="Entity Name">
						<button type="button" onclick="addEntityToEncounter()">
								<i class="material-icons">add_circle</i>
						</button>
					</div>
				</form>

				<form id="add-group-form">
					<div class="d-flex align-items-center mt-3">
						<select class="form-select" id="group-select"></select>
						<button type="button" onclick="addGroupToEncounter()">
								<i class="material-icons">add_circle</i>
						</button>
					</div>
				</form>
			</div>';
		}
		?>
		<div class="row">
			
			<div class="col col-lg-11" id="encounter-details"></div>
			<div class="col col-lg-1">
				<button onclick="endTurn()">
					<i class="material-icons">skip_next</i>
				</button>
			</div>
		</div>
			
		<div class="row">
			<div class="col col-lg-4">

				<div class="state-section" id="ready-section">
					<h3>Ready</h3>
					<div id="ready-entities"></div>
				</div>
			</div>
			
			<div class="col col-lg-4">
				<div class="state-section" id="played-section">
					<h3>Played</h3>
					<div id="played-entities"></div>
				</div>
			</div>
			
			<div class="col col-lg-4">
				<div class="state-section" id="dead-section">
					<h3>Dead</h3>
					<div id="dead-entities"></div>
				</div>
			</div>
		</div>
    </div>

    <script>
        const token = "<?= htmlspecialchars($token) ?>";

        async function loadEncounter() {
            const response = await fetch(`get_encounter_data.php?token=${token}`);
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            const { encounter, entities } = data;

            document.getElementById('encounter-details').innerHTML = `<h2>${encounter.name}</h2>`;

            const ready = document.getElementById('ready-entities');
            const played = document.getElementById('played-entities');
            const dead = document.getElementById('dead-entities');

            ready.innerHTML = '';
            played.innerHTML = '';
            dead.innerHTML = '';

            entities.forEach(entity => {
				var deadButton = `<button onclick="removeEntityFromEncounter(${entity.id})">${getStateIcon("remove")}</button>`;
				if(entity.state != 'dead'){
					deadButton=`<button onclick="updateEntityState(${entity.id}, 'dead')">${getStateIcon("dead")}
						</button>`;
				}
                const div = document.createElement('div');
                div.className = 'entity';
                div.innerHTML = `
                    <span>${entity.name}</span>
					<div>
						${deadButton}
						<button onclick="updateEntityState(${entity.id}, '${getNextState(entity.state)}')">
							${getStateIcon(getNextState(entity.state),entity.state)}
						</button>
					</div>
                `;

                if (entity.state === 'ready') ready.appendChild(div);
                else if (entity.state === 'played') played.appendChild(div);
                else if (entity.state === 'dead') dead.appendChild(div);
            });
        }

		async function removeEntityFromEncounter(entityId) {
			const response = await fetch('remove_entity_from_encounter.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `entity_id=${entityId}&encounter_id=${token}`
			});
			if (response.ok) {
				alert('Entity removed from encounter.');
				loadEncounter();
			} else {
				console.error('Failed to remove entity from encounter.');
			}
		}
		
        function getStateIcon(currentState,lastState="") {
			console.log(currentState);
            if (currentState === 'played') return '<i class="material-icons">play_arrow</i>';
            if (currentState === 'ready'&&lastState=== 'dead') return '<i class="material-icons">health_and_safety</i>';
            if (currentState === 'ready') return '<i class="material-icons">replay</i>';
            if (currentState === 'dead') return '<span class="material-symbols-outlined">skull</span>';
            if (currentState === 'revive') return '<i class="material-icons">health_and_safety</i>';
            if (currentState === 'remove') return '<i class="material-icons">delete</i>';
        }
        function getNextState(currentState) {
            if (currentState === 'ready') return 'played';
            if (currentState === 'played') return 'ready';
            if (currentState === 'dead') return 'ready';
        }

        async function updateEntityState(entityId, newState) {
            await fetch('update_entity_state.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `entity_id=${entityId}&new_state=${newState}`
            });
            loadEncounter();
        }

        async function endTurn() {
            await fetch('end_turn.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `encounter_id=${token}`
            });
            loadEncounter();
        }
		async function loadGroupsForSelect() {
			const response = await fetch('get_groups.php');
			const groups = await response.json();
			const groupSelect = document.getElementById('group-select');
			groupSelect.innerHTML = '<option value="">Select Group</option>';
			groups.forEach(group => {
				const option = document.createElement('option');
				option.value = group.id;
				option.textContent = group.name;
				groupSelect.appendChild(option);
			});
		}

		async function addEntityToEncounter() {
			const entityName = document.getElementById('entity-name').value;
			if (!entityName) {
				alert('Please enter an entity name.');
				return;
			}

			const response = await fetch('add_entity.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `entity_name=${encodeURIComponent(entityName)}&group_id=1&returnData=1`
			});
			if (response.ok) {
				const newEntity = await response.json();
				await fetch('add_entity_to_encounter.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: `encounter_id=${token}&entity_id=${newEntity.id}`
				});
				loadEncounter();
				document.getElementById('entity-name').value = '';
			} else {
				console.error('Failed to add entity.');
			}
		}

		async function addGroupToEncounter() {
			const groupId = document.getElementById('group-select').value;
			if (!groupId) {
				alert('Please select a group.');
				return;
			}

			const response = await fetch('add_group_to_encounter.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `encounter_id=${token}&group_id=${groupId}`
			});
			if (response.ok) {
				loadEncounter();
			} else {
				console.error('Failed to add group.');
			}
		}

		window.onload = function() {
			loadEncounter();
			loadGroupsForSelect();
			setInterval(loadEncounter, 1000);
		};
    </script>
</body>
</html>
