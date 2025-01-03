<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DM Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link href="css/style.css" rel="stylesheet">
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<h1 class="display-2">DM Dashboard</h1>
			</div>
		</div>
		<div class="row">
			<div class="col col-lg-4">
				<!-- Groups Management Section -->
				<div class="section">
					<h2>Manage Groups</h2>
					<form id="add-group-form" method="POST" action="add_group.php">
						<div class="d-flex align-items-center mt-3">
							<input type="text" name="group_name" placeholder="Group Name" required>
							<button type="submit">
									<i class="material-icons">add_circle</i>
							</button>
						</div>
					</form>
					<h3>Existing Groups</h3>
					<div id="groups-table">
						<!-- Groups will be dynamically loaded here -->
					</div>
				</div>
			</div>
			<div class="col col-lg-4">
				    <!-- Entities Management Section -->
					<div class="section">
						<h2>Manage Entities</h2>
						<form id="add-entity-form" method="POST" action="add_entity.php">
							<div class="d-flex align-items-center mt-3">
								<input type="text" name="entity_name" placeholder="Entity Name" required>
								<select class="form-select" name="group_id" id="group-select" required>
									<!-- Options dynamically populated from groups -->
								</select>
								<button type="submit">
									<i class="material-icons">add_circle</i>
								</button>
							</div>
						</form>
						<h3>Entities in Selected Group</h3>
						<div id="entities-table">
							<!-- Entities will be dynamically loaded here -->
						</div>
					</div>
			</div>
			<div class="col col-lg-4">
				<!-- Encounters Management Section -->
				<div class="section">
					<h2>Manage Encounters</h2>
					<form id="add-encounter-form" method="POST" action="add_encounter.php">
						<div class="d-flex align-items-center mt-3">
							<input type="text" name="encounter_name" placeholder="Encounter Name" required>
							<button type="submit">
									<i class="material-icons">add_circle</i>
							</button>
						</div>
					</form>
					<h3>Existing Encounters</h3>
					<div id="encounters-table">
						<!-- Encounters will be dynamically loaded here -->
					</div>
				</div>
			</div>
		</div>
	</div>
	
    <script>
        // JavaScript for loading data dynamically and managing actions
        async function loadGroups() {
            const response = await fetch('get_groups.php');
            const groups = await response.json();
            const groupSelect = document.getElementById('group-select');
            const groupsTable = document.getElementById('groups-table');
            groupSelect.innerHTML = '';
            groupsTable.innerHTML = '';
            groups.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                groupSelect.appendChild(option);

                const row = document.createElement('div');
                row.innerHTML = `
                    <div class="entity">
                        ${group.name} 
                        <button onclick="deleteGroup(${group.id})">Delete</button>
                    </div>`;
                groupsTable.appendChild(row);
            });
        }

		async function getGroupOptions(selectedGroupId) {
			const response = await fetch('get_groups.php');
			const groups = await response.json();
			return groups
				.map(group => 
					`<option value="${group.id}" ${group.id === selectedGroupId ? 'selected' : ''}>
						${group.name}
					</option>`
				)
				.join('');
		}

		async function updateEntityGroup(entityId, groupId) {
			const response = await fetch('update_entity_group.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `entity_id=${entityId}&group_id=${groupId}`
			});
			if (response.ok) {
				loadEntities();
			} else {
				console.error('Failed to update entity group.');
			}
		}
        async function deleteGroup(groupId) {
            const response = await fetch(`delete_group.php?id=${groupId}`, { method: 'GET' });
            if (response.ok) loadGroups();
        }

		async function loadEntities() {
			const response = await fetch('get_entities.php');
			const entities = await response.json();
			const entitiesTable = document.getElementById('entities-table');
			entitiesTable.innerHTML = '';

			// Group entities by group
			const groupedEntities = {};
			entities.forEach(entity => {
				const groupName = entity.group_name || 'No Group';
				if (!groupedEntities[groupName]) {
					groupedEntities[groupName] = [];
				}
				groupedEntities[groupName].push(entity);
			});

			// Render grouped entities
			for (const groupName in groupedEntities) {
				const groupDiv = document.createElement('div');
  				groupDiv.classList.add("entityGroup");
				groupDiv.innerHTML = `<h4>${groupName}</h4>`;
				groupedEntities[groupName].forEach(async entity => {
					const entityRow = document.createElement('div');
					entityRow.innerHTML = `
						<div class="d-flex align-items-center mt-3 entity">
							<span class="me-2">${entity.name}</span>
							<select class="form-select me-2" onchange="updateEntityGroup(${entity.id}, this.value)">
								${await getGroupOptions(entity.group_id)}
							</select>
							<button onclick="deleteEntity(${entity.id})">
								<i class="material-icons">delete</i>
							</button>
						</div>
					`;
					groupDiv.appendChild(entityRow);
				});
				entitiesTable.appendChild(groupDiv);
			}
		}

        async function deleteEntity(entityId) {
            const response = await fetch(`delete_entity.php?id=${entityId}`, { method: 'GET' });
            if (response.ok) loadEntities(document.getElementById('group-select').value);
        }
		async function loadEncounters() {
			const response = await fetch('get_encounters.php');
			const encounters = await response.json();
			const encountersTable = document.getElementById('encounters-table');
			encountersTable.innerHTML = '';

			encounters.forEach(encounter => {
				const row = document.createElement('div');
				actionButton = encounter.status=='closed'?
							`<button onclick="openEncounter(${encounter.id}, '${encounter.access_token}')">
								<i class="material-icons">play_arrow</i>
							</button>`:
							`<button onclick="closeEncounter(${encounter.id})">
								<i class="material-icons">block</i>
							</button>
							<button onclick="location.href='encounter_page.php?token=${encounter.access_token}'">
								<i class="material-icons">visibility</i>
							</button>`;
							
				row.innerHTML = `
					<div class="entity">
						<strong>${encounter.name}</strong> (${encounter.status})
						<div>
							${actionButton}
							<button onclick="deleteEncounter(${encounter.id})">
								<i class="material-icons">delete</i>
							</button>
						</div>
					</div>
				`;
				encountersTable.appendChild(row);
			});
		}
		async function openEncounter(encounterId, accessToken) {
			const response = await fetch('open_encounter.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `encounter_id=${encounterId}`
			});
			if (response.ok) {
				alert(`Encounter opened! Share this URL: ${getParentPath()}/encounter_page.php?token=${accessToken}`);
				loadEncounters();
			} else {
				console.error('Failed to open encounter.');
			}
		}
		function getParentPath() {
			var currentPath = window.location.pathname; // Get the current URL path
			var pathParts = currentPath.split('/'); // Split the path into parts based on '/'

			// Remove the last part (the file name)
			pathParts.pop(); 

			// Join the remaining parts back together to form the parent path
			var parentPath = pathParts.join('/');

			// Return the full URL
			return window.location.origin + parentPath;
		}
	    
		async function closeEncounter(encounterId) {
			const response = await fetch('close_encounter.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: `encounter_id=${encounterId}`
			});
			if (response.ok) {
				alert('Encounter closed!');
				loadEncounters();
			} else {
				console.error('Failed to close encounter.');
			}
		}

        async function deleteEncounter(encounterId) {
            const response = await fetch(`delete_encounter.php?id=${encounterId}`, { method: 'GET' });
            if (response.ok) loadEncounters();
        }

        // Load data on page load
        window.onload = function() {
            loadGroups();
    		loadEntities();
            loadEncounters();
        };
    </script>
</body>
</html>
