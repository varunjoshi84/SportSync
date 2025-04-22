<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../player.php';
require_once __DIR__ . '/../auth.php';

// Check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id']) || getUserById($_SESSION['user_id'])['account_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['match_id']) && isset($_GET['team'])) {
        $players = getPlayersByMatch($_GET['match_id'], $_GET['team']);
        $response = [
            'success' => true,
            'players' => $players
        ];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    switch ($data['action']) {
        case 'add_player':
            if (isset($data['name'], $data['team'], $data['sport'], $data['role'], $data['match_id'])) {
                $result = addPlayer($data['name'], $data['team'], $data['sport'], $data['role'], $data['match_id']);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Player added successfully'];
                }
            }
            break;
            
        case 'update_score':
            if (isset($data['player_id'], $data['score'])) {
                $result = updatePlayerScore($data['player_id'], $data['score']);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Score updated successfully'];
                }
            }
            break;
            
        case 'delete_player':
            if (isset($data['player_id'])) {
                $result = deletePlayer($data['player_id']);
                if ($result) {
                    $response = ['success' => true, 'message' => 'Player deleted successfully'];
                }
            }
            break;
    }
}

echo json_encode($response);
?> 