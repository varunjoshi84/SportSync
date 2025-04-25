<?php


ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Prevent multiple inclusions
if (!defined('MATCH_PHP_INCLUDED')) {
    define('MATCH_PHP_INCLUDED', true);
    
    // Include required files
    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/news.php';
    require_once __DIR__ . '/auth.php';
    require_once __DIR__ . '/user.php';
    
    // Create favorites table function
    if (!function_exists('createFavoritesTable')) {
        function createFavoritesTable() {
            $sql = "CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                match_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_favorite (user_id, match_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
            )";
            return executeQuery($sql);
        }
    }

    // Define functions only if they don't exist
    if (!function_exists('getLiveMatches')) {
        function getLiveMatches($sport = null) {
            $params = [];
            $sql = "SELECT * FROM matches WHERE status = 'live'";
            if ($sport) {
                $sql .= " AND sport = :sport";
                $params[':sport'] = $sport;
            }
            return executeQuery($sql, $params);
        }
    }
    
    if (!function_exists('getMatchesByStatus')) {
        function getMatchesByStatus($status, $sport = null) {
            $params = [':status' => $status];
            $sql = "SELECT * FROM matches WHERE status = :status";
            if ($sport) {
                $sql .= " AND sport = :sport";
                $params[':sport'] = $sport;
            }
            return executeQuery($sql, $params);
        }
    }
    
    if (!function_exists('getCricketMatches')) {
        function getCricketMatches($status = null) {
            $params = [':sport' => 'cricket'];
            $sql = "SELECT * FROM matches WHERE sport = :sport";
            if ($status) {
                $sql .= " AND status = :status";
                $params[':status'] = $status;
            }
            return executeQuery($sql, $params);
        }
    }
    
    if (!function_exists('getFootballMatches')) {
        function getFootballMatches($status = null) {
            $params = [':sport' => 'football'];
            $sql = "SELECT * FROM matches WHERE sport = :sport";
            if ($status) {
                $sql .= " AND status = :status";
                $params[':status'] = $status;
            }
            return executeQuery($sql, $params);
        }
    }
    
    if (!function_exists('getUpcomingMatches')) {
        function getUpcomingMatches() {
            return getMatchesByStatus('upcoming');
        }
    }
    
    if (!function_exists('updateMatchScore')) {
        function updateMatchScore($match_id, $team1_score, $team2_score, 
                                $team1_wickets = null, $team2_wickets = null,
                                $team1_overs = null, $team2_overs = null) {
            $sql = "UPDATE matches SET 
                    team1_score = ?, 
                    team2_score = ?";
            $params = [$team1_score, $team2_score];
            
            if ($team1_wickets !== null) {
                $sql .= ", team1_wickets = ?";
                $params[] = $team1_wickets;
            }
            if ($team2_wickets !== null) {
                $sql .= ", team2_wickets = ?";
                $params[] = $team2_wickets;
            }
            if ($team1_overs !== null) {
                $sql .= ", team1_overs = ?";
                $params[] = $team1_overs;
            }
            if ($team2_overs !== null) {
                $sql .= ", team2_overs = ?";
                $params[] = $team2_overs;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $match_id;
            
            return executeQuery($sql, $params);
        }
    }
    
    if (!function_exists('updateCricketMatch')) {
        function updateCricketMatch($match_id, $data) {
            $sql = "UPDATE matches SET 
                    team1_score = :score1,
                    team2_score = :score2,
                    team1_wickets = :wickets1,
                    team2_wickets = :wickets2,
                    team1_overs = :overs1,
                    team2_overs = :overs2,
                    status = :status
                    WHERE id = :id AND sport = 'cricket'";
            
            return executeQuery($sql, [
                ':id' => $match_id,
                ':score1' => $data['team1_score'] ?? 0,
                ':score2' => $data['team2_score'] ?? 0,
                ':wickets1' => $data['team1_wickets'] ?? 0,
                ':wickets2' => $data['team2_wickets'] ?? 0,
                ':overs1' => $data['team1_overs'] ?? 0.0,
                ':overs2' => $data['team2_overs'] ?? 0.0,
                ':status' => $data['status'] ?? 'live'
            ]);
        }
    }
    
    if (!function_exists('addMatch')) {
        function addMatch($team1, $team2, $venue, $match_time, $sport, $status = 'upcoming') {
            $sql = "INSERT INTO matches (team1, team2, venue, match_time, sport, status) 
                    VALUES (:team1, :team2, :venue, :match_time, :sport, :status)";
            
            try {
                $result = executeQuery($sql, [
                    ':team1' => $team1,
                    ':team2' => $team2,
                    ':venue' => $venue,
                    ':match_time' => $match_time,
                    ':sport' => $sport,
                    ':status' => $status
                ]);
                return $result !== false;
            } catch (Exception $e) {
                error_log("Error adding match: " . $e->getMessage());
                throw new Exception("Failed to add match: " . $e->getMessage());
            }
        }
    }
    
    if (!function_exists('createMatchTable')) {
        function createMatchTable() {
            $sql = "CREATE TABLE IF NOT EXISTS matches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                team1 VARCHAR(100) NOT NULL,
                team2 VARCHAR(100) NOT NULL,
                team1_score INT DEFAULT 0,
                team2_score INT DEFAULT 0,
                team1_wickets INT DEFAULT 0,
                team2_wickets INT DEFAULT 0,
                team1_overs DECIMAL(4,1) DEFAULT 0.0,
                team2_overs DECIMAL(4,1) DEFAULT 0.0,
                team1_shots INT DEFAULT 0,
                team2_shots INT DEFAULT 0,
                team1_possession INT DEFAULT 50,
                team2_possession INT DEFAULT 50,
                venue VARCHAR(200) NOT NULL,
                match_time DATETIME NOT NULL,
                sport ENUM('cricket', 'football') NOT NULL,
                status ENUM('upcoming', 'live', 'completed') DEFAULT 'upcoming',
                winner VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            return executeQuery($sql);
        }
    }
    
    if (!function_exists('getFilteredMatches')) {
        function getFilteredMatches($sport = null, $status = null, $search = null) {
            $params = [];
            $sql = "SELECT * FROM matches WHERE 1=1";
            
            if ($sport) {
                $sql .= " AND sport = :sport";
                $params[':sport'] = $sport;
            }
            
            if ($status) {
                $sql .= " AND status = :status";
                $params[':status'] = $status;
            }
            
            if ($search) {
                $sql .= " AND (team1 LIKE :search OR team2 LIKE :search OR venue LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            $sql .= " ORDER BY match_time DESC";
            return executeQuery($sql, $params);
        }
    }

    if (!function_exists('updateMatch')) {
        function updateMatch($match_id, $data) {
            $updateFields = [];
            $params = [':id' => $match_id];

            // Get the current match data to use as fallback values
            $currentMatch = getMatchById($match_id);
            if (!$currentMatch) {
                error_log("Match not found with ID: $match_id");
                throw new Exception("Match not found with ID: $match_id");
            }

            // Validate status field - ensure it's one of the allowed values
            if (isset($data['status'])) {
                $allowedStatuses = ['upcoming', 'live', 'completed'];
                if (!in_array($data['status'], $allowedStatuses)) {
                    error_log("Invalid status value: " . $data['status'] . ". Using current status: " . $currentMatch['status']);
                    $data['status'] = $currentMatch['status']; // Use existing value if invalid
                }
            }

            // If status is being updated to 'completed', determine the winner
            if (isset($data['status']) && $data['status'] === 'completed') {
                $team1 = $data['team1'] ?? $currentMatch['team1'];
                $team2 = $data['team2'] ?? $currentMatch['team2'];
                $team1_score = $data['team1_score'] ?? $currentMatch['team1_score'];
                $team2_score = $data['team2_score'] ?? $currentMatch['team2_score'];
                
                // Determine winner and add to data
                $data['winner'] = determineWinner($team1_score, $team2_score, $team1, $team2);
                error_log("Match completed, winner determined: " . $data['winner']);
            }

            // Handle empty match_time specially - use existing value if empty
            if (isset($data['match_time']) && empty($data['match_time'])) {
                error_log("Empty match_time detected - using existing value from database");
                $data['match_time'] = $currentMatch['match_time'];
            }

            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $updateFields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($updateFields)) {
                return false;
            }

            $sql = "UPDATE matches SET " . implode(', ', $updateFields) . " WHERE id = :id";
            error_log("SQL: $sql");
            error_log("Params: " . json_encode($params));
            
            try {
                $db = getDB();
                $stmt = $db->prepare($sql);
                $result = $stmt->execute($params);
                
                if ($result) {
                    error_log("Match updated successfully");
                    return true;
                } else {
                    error_log("Match update failed: " . json_encode($stmt->errorInfo()));
                    return false;
                }
            } catch (Exception $e) {
                error_log("Error updating match: " . $e->getMessage());
                throw new Exception("Failed to update match: " . $e->getMessage());
            }
        }
    }

    if (!function_exists('deleteMatch')) {
        function deleteMatch($match_id) {
            $sql = "DELETE FROM matches WHERE id = :id";
            return executeQuery($sql, [':id' => $match_id]);
        }
    }

    if (!function_exists('getMatchById')) {
        function getMatchById($match_id) {
            $sql = "SELECT * FROM matches WHERE id = :id";
            $result = executeQuery($sql, [':id' => $match_id]);
            return $result ? $result[0] : null;
        }
    }
    
    if (!function_exists('handleApiRequest')) {
        function handleApiRequest() {
            header('Content-Type: application/json');
            
            // Add error logging
            error_log("API Request received: " . $_SERVER['REQUEST_URI']);
            error_log("GET parameters: " . print_r($_GET, true));
            
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'fetch_matches':
                        $sport = $_GET['sport'] ?? null;
                        $status = $_GET['status'] ?? null;
                        $all = isset($_GET['all']) && $_GET['all'] === 'true';
                        
                        if ($all) {
                            if ($sport === 'football') {
                                $matches = getAllFootballMatches();
                            } elseif ($sport === 'cricket') {
                                $matches = getAllCricketMatches();
                            } else {
                                $matches = getFilteredMatches($sport);
                            }
                        } else {
                            if ($sport === 'cricket') {
                                $matches = getCricketMatches($status);
                            } elseif ($sport === 'football') {
                                $matches = getFootballMatches($status);
                            } else {
                                $matches = getMatchesByStatus($status, $sport);
                            }
                        }
                        
                        echo json_encode($matches);
                        break;
                        
                    case 'fetch_live':
                        $sport = $_GET['sport'] ?? null;
                        $matches = getLiveMatches($sport);
                        echo json_encode($matches);
                        break;
                        
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid action']);
                        break;
                }
                exit();
            }
        }
    }

    if (!function_exists('addToFavorites')) {
        function addToFavorites($user_id, $match_id) {
            $sql = "INSERT INTO favorites (user_id, match_id) VALUES (:user_id, :match_id)";
            try {
                return executeQuery($sql, [':user_id' => $user_id, ':match_id' => $match_id]);
            } catch (Exception $e) {
                error_log("Error adding to favorites: " . $e->getMessage());
                return false;
            }
        }
    }

    if (!function_exists('removeFromFavorites')) {
        function removeFromFavorites($user_id, $match_id) {
            $sql = "DELETE FROM favorites WHERE user_id = :user_id AND match_id = :match_id";
            try {
                return executeQuery($sql, [':user_id' => $user_id, ':match_id' => $match_id]);
            } catch (Exception $e) {
                error_log("Error removing from favorites: " . $e->getMessage());
                return false;
            }
        }
    }

    if (!function_exists('getFavoriteMatches')) {
        function getFavoriteMatches($user_id, $sport = null, $status = null) {
            $sql = "SELECT m.* FROM matches m 
                    INNER JOIN favorites f ON m.id = f.match_id 
                    WHERE f.user_id = :user_id";
            
            $params = [':user_id' => $user_id];
            
            // Add sport filter if provided
            if ($sport !== null) {
                $sql .= " AND m.sport = :sport";
                $params[':sport'] = $sport;
            }
            
            // Add status filter if provided
            if ($status !== null) {
                $sql .= " AND m.status = :status";
                $params[':status'] = $status;
            }
            
            $sql .= " ORDER BY m.match_time DESC";
            
            try {
                return executeQuery($sql, $params);
            } catch (Exception $e) {
                error_log("Error getting favorite matches: " . $e->getMessage());
                return [];
            }
        }
    }

    if (!function_exists('isMatchFavorited')) {
        function isMatchFavorited($user_id, $match_id) {
            $sql = "SELECT COUNT(*) as count FROM favorites 
                    WHERE user_id = :user_id AND match_id = :match_id";
            try {
                $result = executeQuery($sql, [':user_id' => $user_id, ':match_id' => $match_id]);
                return $result && $result[0]['count'] > 0;
            } catch (Exception $e) {
                error_log("Error checking favorite status: " . $e->getMessage());
                return false;
            }
        }
    }

    if (!function_exists('getAllFootballMatches')) {
        function getAllFootballMatches() {
            $params = [':sport' => 'football'];
            $sql = "SELECT * FROM matches WHERE sport = :sport ORDER BY match_time DESC";
            return executeQuery($sql, $params);
        }
    }

    if (!function_exists('getAllCricketMatches')) {
        function getAllCricketMatches() {
            $params = [':sport' => 'cricket'];
            $sql = "SELECT * FROM matches WHERE sport = :sport ORDER BY match_time DESC";
            return executeQuery($sql, $params);
        }
    }

    if (!function_exists('getLiveMatchesBySport')) {
        function getLiveMatchesBySport($sport) {
            $sql = "SELECT * FROM matches WHERE sport = :sport AND status IN ('live', 'upcoming') ORDER BY match_time DESC";
            $params = [':sport' => $sport];
            return executeQuery($sql, $params);
        }
    }

    if (!function_exists('determineWinner')) {
        /**
         * Determines the winner of a match based on scores
         * 
         * @param int $team1_score Score of team 1
         * @param int $team2_score Score of team 2
         * @param string $team1 Name of team 1
         * @param string $team2 Name of team 2
         * @return string|null Name of winning team or "Draw" or null if not determined
         */
        function determineWinner($team1_score, $team2_score, $team1, $team2) {
            if ($team1_score > $team2_score) {
                return $team1;
            } elseif ($team2_score > $team1_score) {
                return $team2;
            } elseif ($team1_score == $team2_score) {
                return "Draw";
            }
            return null; // No winner determined
        }
    }

    // Initialize tables when this file is included
    if (function_exists('initializeTables')) {
        initializeTables();
    } else {
        // Create necessary tables if initializeTables doesn't exist
        if (function_exists('createMatchTable')) {
            createMatchTable();
        }
        if (function_exists('createFavoritesTable')) {
            createFavoritesTable();
        }
    }

    // Handle API requests if this file is accessed directly
    if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
        handleApiRequest();
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Handle toggle_favorite separately since it's more commonly used
            if (isset($_POST['toggle_favorite'])) {
                header('Content-Type: application/json');
                if (ob_get_length()) ob_clean();
                
                // Log the request for debugging
                error_log('toggle_favorite POST: ' . print_r($_POST, true));
                
                // Get parameters
                $user_id = $_POST['user_id'] ?? null;
                $match_id = $_POST['match_id'] ?? null;
                $is_favorite = isset($_POST['is_favorite']) && $_POST['is_favorite'] === 'true';

                // Validate required parameters
                if (!$user_id || !$match_id) {
                    $response = ['success' => false, 'error' => 'Missing user_id or match_id'];
                    echo json_encode($response);
                    exit;
                }

                try {
                    if ($is_favorite) {
                        $result = removeFromFavorites($user_id, $match_id);
                        error_log("Removing from favorites: user_id=$user_id, match_id=$match_id, result=" . ($result ? 'success' : 'failed'));
                    } else {
                        $result = addToFavorites($user_id, $match_id);
                        error_log("Adding to favorites: user_id=$user_id, match_id=$match_id, result=" . ($result ? 'success' : 'failed'));
                    }
                    
                    // Always send a definitive response
                    $response = [
                        'success' => ($result !== false), 
                        'is_favorite' => !$is_favorite,
                        'action' => $is_favorite ? 'removed' : 'added'
                    ];
                    
                    // Make sure we have output, even if result is false
                    if ($result === false) {
                        $response['error'] = 'Database operation failed';
                    }
                } catch (Exception $e) {
                    error_log("Error in toggle_favorite: " . $e->getMessage());
                    $response = ['success' => false, 'error' => $e->getMessage()];
                }
                
                // Add additional error handling to guarantee output
                if (empty($response)) {
                    $response = ['success' => false, 'error' => 'Unknown server error occurred'];
                }
                
                // Ensure we're sending valid JSON with proper headers
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                echo json_encode($response);
                exit;
            }
            // Handle other form submissions
            else if (isset($_POST['add_match']) || isset($_POST['update_match']) || isset($_POST['delete_match']) || 
                isset($_POST['add_to_favorites']) || isset($_POST['remove_from_favorites'])) {
                
                header('Content-Type: application/json');
                $response = ['success' => false];

                if (isset($_POST['add_match'])) {
                    // Handle add match
                    $team1 = $_POST['team1'];
                    $team2 = $_POST['team2'];
                    $venue = $_POST['venue'];
                    $match_time = $_POST['match_time'];
                    $sport = $_POST['sport'];
                    $status = $_POST['status'] ?? 'upcoming';

                    try {
                        if (addMatch($team1, $team2, $venue, $match_time, $sport, $status)) {
                            $response = ['success' => true, 'message' => 'Match added successfully'];
                        } else {
                            $response = ['success' => false, 'error' => 'Failed to add match'];
                        }
                    } catch (Exception $e) {
                        $response = ['success' => false, 'error' => $e->getMessage()];
                    }
                }
                elseif (isset($_POST['update_match_details'])) {
                    // Handle update match
                    try {
                        $match_id = $_POST['match_id'];
                        $data = [
                            'team1' => $_POST['team1'],
                            'team2' => $_POST['team2'],
                            'venue' => $_POST['venue'],
                            'match_time' => $_POST['match_time'],
                            'sport' => $_POST['sport'],
                            'status' => $_POST['status'],
                            'team1_score' => $_POST['team1_score'],
                            'team2_score' => $_POST['team2_score']
                        ];

                        // Add sport-specific fields
                        if ($_POST['sport'] === 'cricket') {
                            $data['team1_wickets'] = $_POST['team1_wickets'];
                            $data['team2_wickets'] = $_POST['team2_wickets'];
                            $data['team1_overs'] = $_POST['team1_overs'];
                            $data['team2_overs'] = $_POST['team2_overs'];
                        }
                        // Add football-specific fields
                        if ($_POST['sport'] === 'football') {
                            $data['team1_shots'] = $_POST['team1_shots'];
                            $data['team2_shots'] = $_POST['team2_shots'];
                            $data['team1_possession'] = $_POST['team1_possession'];
                            $data['team2_possession'] = $_POST['team2_possession'];
                        }

                        if (updateMatch($match_id, $data)) {
                            $response = ['success' => true, 'message' => 'Match updated successfully'];
                        } else {
                            $response = ['success' => false, 'error' => 'Failed to update match'];
                        }
                    } catch (Exception $e) {
                        $response = ['success' => false, 'error' => $e->getMessage()];
                    }
                }
                elseif (isset($_POST['delete_match'])) {
                    // Handle delete match
                    try {
                        $match_id = $_POST['match_id'];
                        
                        if (!$match_id) {
                            $response = ['success' => false, 'error' => 'Match ID is required'];
                        } else {
                            // Check if the match exists first
                            $match = getMatchById($match_id);
                            
                            if (!$match) {
                                $response = ['success' => false, 'error' => 'Match not found'];
                            } else {
                                $result = deleteMatch($match_id);
                                
                                if ($result !== false) {
                                    $response = ['success' => true, 'message' => 'Match deleted successfully'];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to delete match'];
                                }
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error deleting match: " . $e->getMessage());
                        $response = ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
                    }
                }

                // Return the response
                if (!headers_sent()) {
                    header('Content-Type: application/json');
                }
                if (ob_get_length()) ob_clean();
                echo json_encode($response);
                exit;
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'An unexpected error occurred: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>
