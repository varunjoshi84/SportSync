<?php
ini_set('display_errors', 0); // Don't display errors directly to users
error_reporting(E_ALL); // Report all PHP errors

// Log errors to a file instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/player_api_errors.log');

// Set a higher memory limit and execution time
ini_set('memory_limit', '256M');
set_time_limit(60);

header('Content-Type: application/json');
require_once __DIR__ . '/../player.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../user.php'; // Added user.php to access getUserById function

// Ensure any PHP fatal errors don't break JSON output
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Fatal PHP error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']
        ]);
        exit();
    }
});

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
        if (isset($_GET['match_id']) && isset($_GET['team'])) {
            try {
                $players = getPlayersByMatch($_GET['match_id'], $_GET['team']);
                $response = [
                    'success' => true,
                    'players' => $players
                ];
            } catch (Exception $e) {
                $response = ['success' => false, 'error' => 'Failed to fetch players: ' . $e->getMessage()];
                error_log("Player API - Error fetching players: " . $e->getMessage());
            }
        } else {
            $response = ['success' => false, 'error' => 'Missing required parameters'];
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check for regular form data first
        if (isset($_POST['action'])) {
            // Process form data
            switch ($_POST['action']) {
                case 'add_player':
                    if (isset($_POST['name'], $_POST['team'], $_POST['match_id']) && $_POST['name'] && $_POST['team'] && $_POST['match_id']) {
                        try {
                            // Get sport from match
                            $db = getDB();
                            $stmt = $db->prepare("SELECT sport FROM matches WHERE id = ?");
                            $stmt->execute([$_POST['match_id']]);
                            $match = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if (!$match) {
                                $response = ['success' => false, 'error' => 'Match not found'];
                            } else {
                                $sport = $match['sport'];
                                $result = addPlayer($_POST['name'], $_POST['team'], $sport, $_POST['role'], $_POST['match_id']);
                                
                                if ($result) {
                                    $response = ['success' => true, 'message' => 'Player added successfully'];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to add player'];
                                }
                            }
                        } catch (Exception $e) {
                            $response = ['success' => false, 'error' => 'Error adding player: ' . $e->getMessage()];
                            error_log("Player API - Error adding player: " . $e->getMessage());
                        }
                    } else {
                        $response = ['success' => false, 'error' => 'Missing required fields for adding player'];
                        error_log("Player API - Missing fields: " . json_encode($_POST));
                    }
                    break;
                    
                case 'delete_player':
                    if (isset($_POST['player_id'])) {
                        try {
                            $result = deletePlayer($_POST['player_id']);
                            if ($result) {
                                $response = ['success' => true, 'message' => 'Player deleted successfully'];
                            } else {
                                $response = ['success' => false, 'error' => 'Failed to delete player'];
                            }
                        } catch (Exception $e) {
                            $response = ['success' => false, 'error' => 'Error deleting player: ' . $e->getMessage()];
                            error_log("Player API - Error deleting player: " . $e->getMessage());
                        }
                    } else {
                        $response = ['success' => false, 'error' => 'Missing player_id'];
                    }
                    break;
                    
                default:
                    $response = ['success' => false, 'error' => 'Invalid action specified'];
                    break;
            }
        }
        // If no form data or action not handled, try JSON input
        else {
            $json_input = file_get_contents('php://input');
            
            if (empty($json_input)) {
                $response = ['success' => false, 'error' => 'No data received'];
            } else {
                $data = json_decode($json_input, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $response = ['success' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()];
                    error_log("Player API - Invalid JSON received: " . $json_input);
                } else {
                    if (!isset($data['action'])) {
                        $response = ['success' => false, 'error' => 'No action specified'];
                    } else {
                        switch ($data['action']) {
                            case 'add_player':
                                if (isset($data['name'], $data['team'], $data['sport'], $data['role'], $data['match_id'])) {
                                    try {
                                        $result = addPlayer($data['name'], $data['team'], $data['sport'], $data['role'], $data['match_id']);
                                        if ($result) {
                                            $response = ['success' => true, 'message' => 'Player added successfully'];
                                        } else {
                                            $response = ['success' => false, 'error' => 'Failed to add player'];
                                        }
                                    } catch (Exception $e) {
                                        $response = ['success' => false, 'error' => 'Error adding player: ' . $e->getMessage()];
                                        error_log("Player API - Error adding player: " . $e->getMessage());
                                    }
                                } else {
                                    $response = ['success' => false, 'error' => 'Missing required fields for adding player'];
                                }
                                break;
                                
                            case 'update_score':
                                if (isset($data['player_id'], $data['score'])) {
                                    try {
                                        $result = updatePlayerScore($data['player_id'], $data['score']);
                                        if ($result) {
                                            $response = ['success' => true, 'message' => 'Score updated successfully'];
                                        } else {
                                            $response = ['success' => false, 'error' => 'Failed to update score'];
                                        }
                                    } catch (Exception $e) {
                                        $response = ['success' => false, 'error' => 'Error updating score: ' . $e->getMessage()];
                                        error_log("Player API - Error updating score: " . $e->getMessage());
                                    }
                                } else {
                                    $response = ['success' => false, 'error' => 'Missing player_id or score'];
                                }
                                break;
                                
                            case 'delete_player':
                                if (isset($data['player_id'])) {
                                    try {
                                        $result = deletePlayer($data['player_id']);
                                        if ($result) {
                                            $response = ['success' => true, 'message' => 'Player deleted successfully'];
                                        } else {
                                            $response = ['success' => false, 'error' => 'Failed to delete player'];
                                        }
                                    } catch (Exception $e) {
                                        $response = ['success' => false, 'error' => 'Error deleting player: ' . $e->getMessage()];
                                        error_log("Player API - Error deleting player: " . $e->getMessage());
                                    }
                                } else {
                                    $response = ['success' => false, 'error' => 'Missing player_id'];
                                }
                                break;
                                
                            default:
                                $response = ['success' => false, 'error' => 'Invalid action specified'];
                                break;
                        }
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    // Catch any unexpected exceptions
    $response = ['success' => false, 'error' => 'Server error: ' . $e->getMessage()];
    error_log("Player API - Unexpected error: " . $e->getMessage());
}

// Ensure we always return a valid JSON response
echo json_encode($response);
// Make sure nothing else is output after the JSON
exit();
?>