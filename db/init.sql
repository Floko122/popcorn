-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);

-- Groups Table
CREATE TABLE groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Entities Table
CREATE TABLE entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    group_id INT,
    FOREIGN KEY (group_id) REFERENCES groups(id)
);

-- Encounters Table
CREATE TABLE encounters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    access_token VARCHAR(255) UNIQUE NOT NULL
);

-- Encounter Entities Table
CREATE TABLE encounter_entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encounter_id INT,
    entity_id INT,
    state ENUM('ready', 'played', 'dead') DEFAULT 'ready',
    FOREIGN KEY (encounter_id) REFERENCES encounters(id),
    FOREIGN KEY (entity_id) REFERENCES entities(id)
);

ALTER TABLE encounters ADD COLUMN status ENUM('open', 'closed') DEFAULT 'closed';
ALTER TABLE encounter_entities ADD COLUMN last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

--Default group for entities added in the encounter
INSERT INTO `users`(`id`, `username`, `password_hash`) VALUES (1,'shadow','--------------------------------------------------');
INSERT INTO `groups`(`id`, `name`, `user_id`) VALUES ('0','DEFGROUP',1);

DELIMITER $$

CREATE PROCEDURE cleanup_unlinked_entities_proc()
BEGIN
    DELETE FROM entities
    WHERE id IN (
        SELECT e.id
        FROM entities e
        LEFT JOIN encounter_entities ee ON e.id = ee.entity_id
        WHERE e.group_id = 1 AND ee.entity_id IS NULL
    );
END$$

DELIMITER ;

DELIMITER $$

CREATE EVENT run_cleanup_unlinked_entities
ON SCHEDULE EVERY 1 DAY
DO
CALL cleanup_unlinked_entities_proc();$$

DELIMITER ;