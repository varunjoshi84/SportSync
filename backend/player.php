<?php
// Prevent multiple inclusions
if (!defined('PLAYER_PHP_INCLUDED')) {
    define('PLAYER_PHP_INCLUDED', true);
    
    require_once __DIR__ . '/db.php';
    
    if (!function_exists('createPlayerTable')) {
        function createPlayerTable() {
            $sql = "CREATE TABLE IF NOT EXISTS players (
                id INT AUTO_INCREMENT PRIMARY KEY,
                match_id INT NOT NULL,
                team VARCHAR(100) NOT NULL,
                name VARCHAR(100) NOT NULL,
                role VARCHAR(50) NOT NULL,
                score INT DEFAULT 0,
                sport VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
            )";
            return executeQuery($sql);
        }
    }

    if (!function_exists('addPlayer')) {
        function addPlayer($name, $team, $sport, $role, $match_id) {
            $sql = "INSERT INTO players (name, team, sport, role, match_id) 
                    VALUES (:name, :team, :sport, :role, :match_id)";
            return executeQuery($sql, [
                ':name' => $name,
                ':team' => $team,
                ':sport' => $sport,
                ':role' => $role,
                ':match_id' => $match_id
            ]);
        }
    }

    if (!function_exists('getPlayersByMatch')) {
        function getPlayersByMatch($match_id, $team = null) {
            $sql = "SELECT * FROM players WHERE match_id = :match_id";
            $params = [':match_id' => $match_id];
            
            if ($team) {
                $sql .= " AND team = :team";
                $params[':team'] = $team;
            }
            
            return executeQuery($sql, $params);
        }
    }

    if (!function_exists('updatePlayerScore')) {
        function updatePlayerScore($player_id, $score) {
            $sql = "UPDATE players SET score = :score WHERE id = :id";
            return executeQuery($sql, [
                ':id' => $player_id,
                ':score' => $score
            ]);
        }
    }

    if (!function_exists('deletePlayer')) {
        function deletePlayer($player_id) {
            $sql = "DELETE FROM players WHERE id = :id";
            return executeQuery($sql, [':id' => $player_id]);
        }
    }

    // Initialize table
    createPlayerTable();
}
?> 