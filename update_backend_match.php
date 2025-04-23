<?php
// Script to update match.php by removing country fields references
$file = './backend/match.php';
$content = file_get_contents($file);

// Replace the problematic update match section
$pattern = '/elseif \(isset\(\$_POST\[\'update_match_details\'\]\)\) \{(.*?)if \(updateMatch\(\$match_id, \$data\)\) \{/s';
$replacement = 'elseif (isset($_POST[\'update_match_details\'])) {
                    // Handle update match
                    try {
                        $match_id = $_POST[\'match_id\'];
                        $data = [
                            \'team1\' => $_POST[\'team1\'],
                            \'team2\' => $_POST[\'team2\'],
                            \'venue\' => $_POST[\'venue\'],
                            \'match_time\' => $_POST[\'match_time\'],
                            \'sport\' => $_POST[\'sport\'],
                            \'status\' => $_POST[\'status\'],
                            \'team1_score\' => $_POST[\'team1_score\'],
                            \'team2_score\' => $_POST[\'team2_score\']
                        ];

                        // Add sport-specific fields
                        if ($_POST[\'sport\'] === \'cricket\') {
                            $data[\'team1_wickets\'] = $_POST[\'team1_wickets\'];
                            $data[\'team2_wickets\'] = $_POST[\'team2_wickets\'];
                            $data[\'team1_overs\'] = $_POST[\'team1_overs\'];
                            $data[\'team2_overs\'] = $_POST[\'team2_overs\'];
                        }
                        // Football-specific fields removed to fix DB error

                        if (updateMatch($match_id, $data)) {';

$updated_content = preg_replace($pattern, $replacement, $content);

// Save the updated content back to the file
file_put_contents($file, $updated_content);
echo "File updated successfully!";
?>
