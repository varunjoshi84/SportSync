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
       
        function addPlayer($name, $team, $sport, $role, $match_id, $score = 0) {
            try {
                $db = getDB();
                $sql = "INSERT INTO players (name, team, sport, role, match_id, score) 
                        VALUES (:name, :team, :sport, :role, :match_id, :score)";
                        
                $stmt = $db->prepare($sql);
                $params = [
                    ':name' => $name,
                    ':team' => $team,
                    ':sport' => $sport,
                    ':role' => $role,
                    ':match_id' => $match_id,
                    ':score' => $score
                ];
                
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Return the ID of the newly created player
                    $playerId = $db->lastInsertId();
                    
                    // If score is greater than 0, update the match score
                    if ($score > 0) {
                        $matchSql = "SELECT id, team1, team2, team1_score, team2_score FROM matches WHERE id = :match_id";
                        $matchStmt = $db->prepare($matchSql);
                        $matchStmt->execute([':match_id' => $match_id]);
                        $match = $matchStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($match) {
                            // Determine which team's score to update
                            if ($team == $match['team1']) {
                                $newScore = $match['team1_score'] + $score;
                                $updateMatchSql = "UPDATE matches SET team1_score = :score WHERE id = :match_id";
                            } else if ($team == $match['team2']) {
                                $newScore = $match['team2_score'] + $score;
                                $updateMatchSql = "UPDATE matches SET team2_score = :score WHERE id = :match_id";
                            } else {
                                // Team doesn't match either team1 or team2
                                return $playerId;
                            }
                            
                            // Execute the match score update
                            $updateMatchStmt = $db->prepare($updateMatchSql);
                            $updateMatchStmt->execute([
                                ':score' => $newScore,
                                ':match_id' => $match_id
                            ]);
                        }
                    }
                    
                    return $playerId;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
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
            try {
                $db = getDB();
                // First get the current player info to determine the team and match_id
                $playerSql = "SELECT id, match_id, team, score FROM players WHERE id = :id";
                $playerStmt = $db->prepare($playerSql);
                $playerStmt->execute([':id' => $player_id]);
                $player = $playerStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$player) {
                    return false;
                }
                
                // Calculate score difference to update match total
                $scoreDifference = $score - $player['score'];
                
                // Update the player's score
                $sql = "UPDATE players SET score = :score WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $params = [
                    ':id' => $player_id,
                    ':score' => $score
                ];
                
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Check if any rows were actually affected
                    $rowsAffected = $stmt->rowCount();
                    
                    // If player score was updated successfully, update the match score
                    if ($rowsAffected > 0 && $scoreDifference != 0) {
                        // Get the match details
                        $matchSql = "SELECT id, team1, team2, team1_score, team2_score, sport FROM matches WHERE id = :match_id";
                        $matchStmt = $db->prepare($matchSql);
                        $matchStmt->execute([':match_id' => $player['match_id']]);
                        $match = $matchStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($match) {
                            // Determine which team's score to update
                            if ($player['team'] == $match['team1']) {
                                $newScore = max(0, $match['team1_score'] + $scoreDifference);
                                $updateMatchSql = "UPDATE matches SET team1_score = :score WHERE id = :match_id";
                            } else if ($player['team'] == $match['team2']) {
                                $newScore = max(0, $match['team2_score'] + $scoreDifference);
                                $updateMatchSql = "UPDATE matches SET team2_score = :score WHERE id = :match_id";
                            } else {
                                // Team doesn't match either team1 or team2
                                return $rowsAffected > 0;
                            }
                            
                            // Execute the match score update
                            $updateMatchStmt = $db->prepare($updateMatchSql);
                            $updateMatchStmt->execute([
                                ':score' => $newScore,
                                ':match_id' => $player['match_id']
                            ]);
                        }
                    }
                    
                    return $rowsAffected > 0;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
    }

    if (!function_exists('deletePlayer')) {
       
        function deletePlayer($player_id) {
            try {
                $db = getDB();
                
                // First get the player info to subtract their score from the match total
                $playerSql = "SELECT id, match_id, team, score FROM players WHERE id = :id";
                $playerStmt = $db->prepare($playerSql);
                $playerStmt->execute([':id' => $player_id]);
                $player = $playerStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$player) {
                    return false;
                }
                
                // Delete the player
                $sql = "DELETE FROM players WHERE id = :id";
                $stmt = $db->prepare($sql);
                $params = [':id' => $player_id];
                $result = $stmt->execute($params);
                
                if ($result) {
                    // Check if any rows were actually deleted
                    $rowsAffected = $stmt->rowCount();
                    
                    // If player was deleted successfully and had a score, update the match score
                    if ($rowsAffected > 0 && $player['score'] > 0) {
                        // Get the match details
                        $matchSql = "SELECT id, team1, team2, team1_score, team2_score FROM matches WHERE id = :match_id";
                        $matchStmt = $db->prepare($matchSql);
                        $matchStmt->execute([':match_id' => $player['match_id']]);
                        $match = $matchStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($match) {
                            // Determine which team's score to update
                            if ($player['team'] == $match['team1']) {
                                $newScore = max(0, $match['team1_score'] - $player['score']);
                                $updateMatchSql = "UPDATE matches SET team1_score = :score WHERE id = :match_id";
                            } else if ($player['team'] == $match['team2']) {
                                $newScore = max(0, $match['team2_score'] - $player['score']);
                                $updateMatchSql = "UPDATE matches SET team2_score = :score WHERE id = :match_id";
                            } else {
                                // Team doesn't match either team1 or team2
                                return $rowsAffected > 0;
                            }
                            
                            // Execute the match score update
                            $updateMatchStmt = $db->prepare($updateMatchSql);
                            $updateMatchStmt->execute([
                                ':score' => $newScore,
                                ':match_id' => $player['match_id']
                            ]);
                        }
                    }
                    
                    return $rowsAffected > 0;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        }
    }

    // Initialize table
    createPlayerTable();
}
?>