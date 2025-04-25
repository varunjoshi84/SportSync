<?php
/**
 * Player Management Functions
 *
 * This file contains functions for managing player data including:
 * - Creating the players database table
 * - Adding new players to matches
 * - Retrieving players by match ID
 * - Updating player scores
 * - Deleting players from the system
 */

// Prevent multiple inclusions
if (!defined('PLAYER_PHP_INCLUDED')) {
    define('PLAYER_PHP_INCLUDED', true);
    
    require_once __DIR__ . '/db.php';
    
    if (!function_exists('createPlayerTable')) {
        /**
         * Creates the players table if it doesn't exist
         *
         * @return mixed PDOStatement if successful, false otherwise
         */
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
        /**
         * Adds a new player to a match
         *
         * @param string $name Player's name
         * @param string $team Team name
         * @param string $sport Sport type (e.g., 'cricket', 'football')
         * @param string $role Player's role (e.g., 'batsman', 'bowler', 'forward')
         * @param int $match_id ID of the match
         * @return int|bool Player ID if successful, false otherwise
         */
        function addPlayer($name, $team, $sport, $role, $match_id) {
            try {
                $db = getDB();
                $sql = "INSERT INTO players (name, team, sport, role, match_id) 
                        VALUES (:name, :team, :sport, :role, :match_id)";
                        
                $stmt = $db->prepare($sql);
                $params = [
                    ':name' => $name,
                    ':team' => $team,
                    ':sport' => $sport,
                    ':role' => $role,
                    ':match_id' => $match_id
                ];
                
                error_log("Adding player with params: " . json_encode($params));
                
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Return the ID of the newly created player
                    $playerId = $db->lastInsertId();
                    error_log("Player added successfully with ID: " . $playerId);
                    return $playerId;
                } else {
                    error_log("Failed to add player: " . json_encode($stmt->errorInfo()));
                    return false;
                }
            } catch (Exception $e) {
                error_log("Exception adding player: " . $e->getMessage());
                return false;
            }
        }
    }

    if (!function_exists('getPlayersByMatch')) {
        /**
         * Retrieves players for a specific match, optionally filtered by team
         *
         * @param int $match_id Match ID to retrieve players for
         * @param string|null $team Optional team name to filter by
         * @return array List of players for the match
         */
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
        /**
         * Updates a player's score
         *
         * @param int $player_id Player ID to update
         * @param int $score New score value
         * @return bool True if successful, false otherwise
         */
        function updatePlayerScore($player_id, $score) {
            try {
                $db = getDB();
                $sql = "UPDATE players SET score = :score WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $params = [
                    ':id' => $player_id,
                    ':score' => $score
                ];
                
                error_log("Updating player score with params: " . json_encode($params));
                
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Check if any rows were actually affected
                    $rowsAffected = $stmt->rowCount();
                    error_log("Player score updated. Rows affected: " . $rowsAffected);
                    return $rowsAffected > 0;
                } else {
                    error_log("Failed to update player score: " . json_encode($stmt->errorInfo()));
                    return false;
                }
            } catch (Exception $e) {
                error_log("Exception updating player score: " . $e->getMessage());
                return false;
            }
        }
    }

    if (!function_exists('deletePlayer')) {
        /**
         * Deletes a player from the system
         *
         * @param int $player_id Player ID to delete
         * @return bool True if successful, false otherwise
         */
        function deletePlayer($player_id) {
            try {
                $db = getDB();
                $sql = "DELETE FROM players WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $params = [':id' => $player_id];
                
                error_log("Deleting player with ID: " . $player_id);
                
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Check if any rows were actually deleted
                    $rowsAffected = $stmt->rowCount();
                    error_log("Player deleted. Rows affected: " . $rowsAffected);
                    return $rowsAffected > 0;
                } else {
                    error_log("Failed to delete player: " . json_encode($stmt->errorInfo()));
                    return false;
                }
            } catch (Exception $e) {
                error_log("Exception deleting player: " . $e->getMessage());
                return false;
            }
        }
    }

    // Initialize table
    createPlayerTable();
}
?>