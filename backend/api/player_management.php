<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../player.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../match.php';
require_once __DIR__ . '/../user.php';

try {
    // Check if user is logged in and is admin
    session_start();
    if (!isset($_SESSION['user_id']) || getUserById($_SESSION['user_id'])['account_type'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }

    $response = ['success' => false, 'error' => 'Invalid request'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get all players for a specific match
        if (isset($_GET['match_id'])) {
            $matchId = $_GET['match_id'];
            $match = getMatchById($matchId);
            
            if (!$match) {
                $response = ['success' => false, 'error' => 'Match not found'];
            } else {
                $team1 = $match['team1'];
                $team2 = $match['team2'];
                $team1Players = getPlayersByMatch($matchId, $team1);
                $team2Players = getPlayersByMatch($matchId, $team2);
                
                $response = [
                    'success' => true,
                    'match_id' => $matchId,
                    'team1' => $team1,
                    'team2' => $team2,
                    'players' => array_merge($team1Players, $team2Players)
                ];
            }
        } 
        // Get players for a specific team in a match
        else if (isset($_GET['match_id'], $_GET['team'])) {
            $players = getPlayersByMatch($_GET['match_id'], $_GET['team']);
            $response = [
                'success' => true,
                'players' => $players
            ];
        } 
        else {
            $response = ['success' => false, 'error' => 'Missing required parameters'];
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $json_input = file_get_contents('php://input');
        
        if (empty($json_input)) {
            // Check for regular form data
            if (isset($_POST['action'])) {
                // Process form data
                switch ($_POST['action']) {
                    case 'add_player':
                        if (isset($_POST['name'], $_POST['team'], $_POST['match_id']) && $_POST['name'] && $_POST['team'] && $_POST['match_id']) {
                            $match = getMatchById($_POST['match_id']);
                            if (!$match) {
                                $response = ['success' => false, 'error' => 'Match not found'];
                            } else {
                                $sport = $match['sport'];
                                $role = $_POST['role'] ?? 'Player';
                                $result = addPlayer($_POST['name'], $_POST['team'], $sport, $role, $_POST['match_id']);
                                
                                if ($result) {
                                    // If player was added successfully and score is provided, update the score
                                    if (isset($_POST['score']) && $_POST['score'] > 0) {
                                        updatePlayerScore($result, $_POST['score']);
                                    }
                                    $response = ['success' => true, 'message' => 'Player added successfully', 'player_id' => $result];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to add player'];
                                }
                            }
                        } else {
                            $response = ['success' => false, 'error' => 'Missing required fields for adding player'];
                        }
                        break;
                        
                    case 'delete_player':
                        if (isset($_POST['player_id'])) {
                            $result = deletePlayer($_POST['player_id']);
                            if ($result) {
                                $response = ['success' => true, 'message' => 'Player deleted successfully'];
                            } else {
                                $response = ['success' => false, 'error' => 'Failed to delete player'];
                            }
                        } else {
                            $response = ['success' => false, 'error' => 'Missing player_id'];
                        }
                        break;
                        
                    default:
                        $response = ['success' => false, 'error' => 'Invalid action specified'];
                        break;
                }
            } else {
                $response = ['success' => false, 'error' => 'No data received'];
            }
        } else {
            $data = json_decode($json_input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = ['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()];
            } else {
                // Check if we're adding players for a newly created match
                if (isset($data['match_id'], $data['team1'], $data['team2'], $data['sport'])) {
                    $matchId = $data['match_id'];
                    $sport = $data['sport'];
                    $successCount = 0;
                    $failCount = 0;
                    
                    // Add team 1 players
                    if (isset($data['team1']['players']) && is_array($data['team1']['players'])) {
                        foreach ($data['team1']['players'] as $player) {
                            if (isset($player['name'], $player['role'])) {
                                $playerId = addPlayer($player['name'], $data['team1']['name'], $sport, $player['role'], $matchId);
                                if ($playerId && isset($player['score']) && $player['score'] > 0) {
                                    updatePlayerScore($playerId, $player['score']);
                                }
                                
                                if ($playerId) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                }
                            }
                        }
                    }
                    
                    // Add team 2 players
                    if (isset($data['team2']['players']) && is_array($data['team2']['players'])) {
                        foreach ($data['team2']['players'] as $player) {
                            if (isset($player['name'], $player['role'])) {
                                $playerId = addPlayer($player['name'], $data['team2']['name'], $sport, $player['role'], $matchId);
                                if ($playerId && isset($player['score']) && $player['score'] > 0) {
                                    updatePlayerScore($playerId, $player['score']);
                                }
                                
                                if ($playerId) {
                                    $successCount++;
                                } else {
                                    $failCount++;
                                }
                            }
                        }
                    }
                    
                    if ($successCount > 0) {
                        $response = [
                            'success' => true, 
                            'message' => "$successCount players added successfully" . ($failCount > 0 ? ", $failCount failed" : "")
                        ];
                    } else if ($failCount > 0) {
                        $response = ['success' => false, 'error' => "Failed to add any players"];
                    } else {
                        $response = ['success' => true, 'message' => 'No players to add'];
                    }
                }
                // Check if we need to handle specific actions
                else if (isset($data['action'])) {
                    switch ($data['action']) {
                        case 'add_player':
                            if (isset($data['name'], $data['team'], $data['match_id'])) {
                                $match = getMatchById($data['match_id']);
                                if (!$match) {
                                    $response = ['success' => false, 'error' => 'Match not found'];
                                } else {
                                    $sport = $data['sport'] ?? $match['sport'];
                                    $role = $data['role'] ?? 'Player';
                                    $playerId = addPlayer($data['name'], $data['team'], $sport, $role, $data['match_id']);
                                    
                                    if ($playerId) {
                                        // If player was added successfully and score is provided, update the score
                                        if (isset($data['score']) && $data['score'] > 0) {
                                            updatePlayerScore($playerId, $data['score']);
                                        }
                                        $response = ['success' => true, 'message' => 'Player added successfully', 'player_id' => $playerId];
                                    } else {
                                        $response = ['success' => false, 'error' => 'Failed to add player'];
                                    }
                                }
                            } else {
                                $response = ['success' => false, 'error' => 'Missing required fields for adding player'];
                            }
                            break;
                            
                        case 'update_scores':
                            if (isset($data['players']) && is_array($data['players'])) {
                                $successCount = 0;
                                $failCount = 0;
                                
                                foreach ($data['players'] as $player) {
                                    if (isset($player['id'], $player['score'])) {
                                        $result = updatePlayerScore($player['id'], $player['score']);
                                        if ($result) {
                                            $successCount++;
                                        } else {
                                            $failCount++;
                                        }
                                    } else {
                                        $failCount++;
                                    }
                                }
                                
                                if ($successCount > 0) {
                                    $response = [
                                        'success' => true, 
                                        'message' => "$successCount player scores updated" . ($failCount > 0 ? ", $failCount failed" : "")
                                    ];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to update any player scores'];
                                }
                            } else {
                                $response = ['success' => false, 'error' => 'Missing players data'];
                            }
                            break;
                            
                        case 'update_score':
                            if (isset($data['player_id'], $data['score'])) {
                                $result = updatePlayerScore($data['player_id'], $data['score']);
                                if ($result) {
                                    $response = ['success' => true, 'message' => 'Score updated successfully'];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to update score'];
                                }
                            } else {
                                $response = ['success' => false, 'error' => 'Missing player_id or score'];
                            }
                            break;
                            
                        case 'delete_player':
                            if (isset($data['player_id'])) {
                                $result = deletePlayer($data['player_id']);
                                if ($result) {
                                    $response = ['success' => true, 'message' => 'Player deleted successfully'];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to delete player'];
                                }
                            } else {
                                $response = ['success' => false, 'error' => 'Missing player_id'];
                            }
                            break;
                            
                        default:
                            $response = ['success' => false, 'error' => 'Invalid action specified'];
                            break;
                    }
                } else {
                    $response = ['success' => false, 'error' => 'Missing action or match data'];
                }
            }
        }
    }
} catch (Exception $e) {
    $response = ['success' => false, 'error' => 'Server error: ' . $e->getMessage()];
}

// Ensure we always return a valid JSON response
echo json_encode($response);
exit();
?>