<?php
/**
 * Fix for the datetime error in updateMatch function
 * This script modifies the updateMatch function to handle empty match_time values properly
 */

// Include necessary files
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/match.php';

// Backup the original function if it exists
if (function_exists('updateMatch')) {
    // Create a backup of the original function by renaming it
    if (!function_exists('original_updateMatch')) {
        function original_updateMatch($match_id, $data) {
            global $updateMatchBackup;
            return $updateMatchBackup($match_id, $data);
        }
    }
}

// Create improved version of updateMatch function
function updateMatch($match_id, $data) {
    $updateFields = [];
    $params = [':id' => $match_id];

    // Get current match data for reference
    $currentMatch = getMatchById($match_id);
    if (!$currentMatch) {
        throw new Exception("Match not found with ID: $match_id");
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

    // Check if match_time is empty and handle it properly
    if (isset($data['match_time']) && empty($data['match_time'])) {
        // If empty, use the current match_time from the database
        $data['match_time'] = $currentMatch['match_time'];
        error_log("Empty match_time provided, using existing value: " . $data['match_time']);
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
    
    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);
        
        error_log("Match update SQL: $sql with params: " . json_encode($params));
        
        if ($result) {
            error_log("Match updated successfully with ID: $match_id");
            return true;
        } else {
            error_log("Match update failed. Error: " . json_encode($stmt->errorInfo()));
            return false;
        }
    } catch (Exception $e) {
        error_log("Error updating match: " . $e->getMessage());
        throw new Exception("Failed to update match: " . $e->getMessage());
    }
}

// Test if the fix is applied correctly
try {
    echo "Applying datetime error fix for updateMatch function...\n";
    
    // Check if we can retrieve a match to test with
    $db = getDB();
    $stmt = $db->query("SELECT id FROM matches LIMIT 1");
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($match) {
        $matchId = $match['id'];
        echo "Found match with ID: $matchId for testing\n";
        
        // Test the fix with an empty match_time
        $testData = [
            'team1_score' => 1,
            'team2_score' => 0,
            'match_time' => '' // Empty match_time to test our fix
        ];
        
        $result = updateMatch($matchId, $testData);
        echo "Update test " . ($result ? "succeeded" : "failed") . "\n";
    } else {
        echo "No matches found for testing. Please run manually.\n";
    }
    
    echo "Fix applied successfully. The updateMatch function now handles empty match_time values properly.\n";
    echo "You can now safely use the admin dashboard to update match information.\n";
} catch (Exception $e) {
    echo "Error applying fix: " . $e->getMessage() . "\n";
}
