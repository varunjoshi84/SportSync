<?php
// Include required files
require_once __DIR__ . '/backend/db.php';
require_once __DIR__ . '/backend/match.php';

// Set timezone
date_default_timezone_set('UTC');

// Function to format date
function formatDate($days, $hours = 0, $minutes = 0) {
    return date('Y-m-d H:i:s', strtotime("$days days $hours hours $minutes minutes"));
}

// Clear output buffering
if (ob_get_level()) ob_end_clean();

echo "<h1>Adding Sample Football and Cricket Matches</h1>";
echo "<pre>";

// Add Football Matches - Upcoming Matches
$footballUpcoming = [
    [
        'team1' => 'Manchester United', 
        'team2' => 'Liverpool', 
        'venue' => 'Old Trafford, Manchester', 
        'match_time' => formatDate('+2', 15, 30), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'Barcelona', 
        'team2' => 'Real Madrid', 
        'venue' => 'Camp Nou, Barcelona', 
        'match_time' => formatDate('+3', 19, 45), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'Bayern Munich', 
        'team2' => 'Borussia Dortmund', 
        'venue' => 'Allianz Arena, Munich', 
        'match_time' => formatDate('+5', 18, 0), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'Paris Saint-Germain', 
        'team2' => 'Marseille', 
        'venue' => 'Parc des Princes, Paris', 
        'match_time' => formatDate('+7', 20, 0), 
        'status' => 'upcoming'
    ]
];

// Add Football Matches - Live Matches
$footballLive = [
    [
        'team1' => 'Chelsea', 
        'team2' => 'Arsenal', 
        'venue' => 'Stamford Bridge, London', 
        'match_time' => formatDate(0, -1), 
        'status' => 'live',
        'team1_score' => 2,
        'team2_score' => 1,
        'team1_shots' => 12,
        'team2_shots' => 8,
        'team1_possession' => 55,
        'team2_possession' => 45
    ],
    [
        'team1' => 'Juventus', 
        'team2' => 'AC Milan', 
        'venue' => 'Allianz Stadium, Turin', 
        'match_time' => formatDate(0, -1, -30), 
        'status' => 'live',
        'team1_score' => 0,
        'team2_score' => 0,
        'team1_shots' => 7,
        'team2_shots' => 5,
        'team1_possession' => 48,
        'team2_possession' => 52
    ]
];

// Add Football Matches - Completed Matches
$footballCompleted = [
    [
        'team1' => 'Manchester City', 
        'team2' => 'Tottenham', 
        'venue' => 'Etihad Stadium, Manchester', 
        'match_time' => formatDate('-1', 18, 30), 
        'status' => 'completed',
        'team1_score' => 3,
        'team2_score' => 1,
        'team1_shots' => 18,
        'team2_shots' => 7,
        'team1_possession' => 65,
        'team2_possession' => 35
    ],
    [
        'team1' => 'Atletico Madrid', 
        'team2' => 'Sevilla', 
        'venue' => 'Wanda Metropolitano, Madrid', 
        'match_time' => formatDate('-2', 20, 0), 
        'status' => 'completed',
        'team1_score' => 2,
        'team2_score' => 2,
        'team1_shots' => 14,
        'team2_shots' => 11,
        'team1_possession' => 52,
        'team2_possession' => 48
    ],
    [
        'team1' => 'Inter Milan', 
        'team2' => 'Napoli', 
        'venue' => 'San Siro, Milan', 
        'match_time' => formatDate('-3', 19, 45), 
        'status' => 'completed',
        'team1_score' => 0,
        'team2_score' => 2,
        'team1_shots' => 9,
        'team2_shots' => 12,
        'team1_possession' => 45,
        'team2_possession' => 55
    ]
];

// Add Cricket Matches - Upcoming Matches
$cricketUpcoming = [
    [
        'team1' => 'India', 
        'team2' => 'Australia', 
        'venue' => 'Melbourne Cricket Ground, Melbourne', 
        'match_time' => formatDate('+4', 9, 30), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'England', 
        'team2' => 'South Africa', 
        'venue' => 'Lord\'s Cricket Ground, London', 
        'match_time' => formatDate('+6', 11, 0), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'New Zealand', 
        'team2' => 'Pakistan', 
        'venue' => 'Eden Park, Auckland', 
        'match_time' => formatDate('+8', 10, 30), 
        'status' => 'upcoming'
    ],
    [
        'team1' => 'Sri Lanka', 
        'team2' => 'West Indies', 
        'venue' => 'R.Premadasa Stadium, Colombo', 
        'match_time' => formatDate('+10', 14, 0), 
        'status' => 'upcoming'
    ]
];

// Add Cricket Matches - Live Matches
$cricketLive = [
    [
        'team1' => 'Bangladesh', 
        'team2' => 'Afghanistan', 
        'venue' => 'Shere Bangla Stadium, Dhaka', 
        'match_time' => formatDate(0, -3), 
        'status' => 'live',
        'team1_score' => 187,
        'team2_score' => 92,
        'team1_wickets' => 10,
        'team2_wickets' => 3,
        'team1_overs' => 50.0,
        'team2_overs' => 23.4
    ],
    [
        'team1' => 'Zimbabwe', 
        'team2' => 'Ireland', 
        'venue' => 'Queens Sports Club, Bulawayo', 
        'match_time' => formatDate(0, -2), 
        'status' => 'live',
        'team1_score' => 245,
        'team2_score' => 0,
        'team1_wickets' => 8,
        'team2_wickets' => 0,
        'team1_overs' => 50.0,
        'team2_overs' => 0.0
    ]
];

// Add Cricket Matches - Completed Matches
$cricketCompleted = [
    [
        'team1' => 'Australia', 
        'team2' => 'England', 
        'venue' => 'Sydney Cricket Ground, Sydney', 
        'match_time' => formatDate('-2', 9, 30), 
        'status' => 'completed',
        'team1_score' => 285,
        'team2_score' => 263,
        'team1_wickets' => 8,
        'team2_wickets' => 10,
        'team1_overs' => 50.0,
        'team2_overs' => 48.3
    ],
    [
        'team1' => 'India', 
        'team2' => 'South Africa', 
        'venue' => 'Eden Gardens, Kolkata', 
        'match_time' => formatDate('-3', 14, 0), 
        'status' => 'completed',
        'team1_score' => 327,
        'team2_score' => 298,
        'team1_wickets' => 6,
        'team2_wickets' => 10,
        'team1_overs' => 50.0,
        'team2_overs' => 47.5
    ],
    [
        'team1' => 'New Zealand', 
        'team2' => 'Pakistan', 
        'venue' => 'Basin Reserve, Wellington', 
        'match_time' => formatDate('-4', 11, 0), 
        'status' => 'completed',
        'team1_score' => 256,
        'team2_score' => 259,
        'team1_wickets' => 9,
        'team2_wickets' => 7,
        'team1_overs' => 50.0,
        'team2_overs' => 48.2
    ]
];

// Function to add matches to the database
function addMatchesToDB($matches, $sport) {
    $count = 0;
    foreach ($matches as $match) {
        try {
            // Add match first
            $result = addMatch(
                $match['team1'], 
                $match['team2'], 
                $match['venue'], 
                $match['match_time'], 
                $sport, 
                $match['status']
            );
            
            if ($result) {
                // Get last inserted ID
                $db = getDB();
                $lastId = $db->lastInsertId();
                
                // If match status is live or completed, update match score
                if ($match['status'] == 'live' || $match['status'] == 'completed') {
                    $updateData = [
                        'team1_score' => $match['team1_score'],
                        'team2_score' => $match['team2_score'],
                        'status' => $match['status']
                    ];
                    
                    // Add cricket specific fields
                    if ($sport == 'cricket') {
                        $updateData['team1_wickets'] = $match['team1_wickets'];
                        $updateData['team2_wickets'] = $match['team2_wickets'];
                        $updateData['team1_overs'] = $match['team1_overs'];
                        $updateData['team2_overs'] = $match['team2_overs'];
                    }
                    
                    // Add football specific fields
                    if ($sport == 'football') {
                        $updateData['team1_shots'] = $match['team1_shots'] ?? 0;
                        $updateData['team2_shots'] = $match['team2_shots'] ?? 0;
                        $updateData['team1_possession'] = $match['team1_possession'] ?? 50;
                        $updateData['team2_possession'] = $match['team2_possession'] ?? 50;
                    }
                    
                    // Update with scores and additional data
                    updateMatch($lastId, $updateData);
                }
                
                echo "Added {$sport} match: {$match['team1']} vs {$match['team2']} ({$match['status']})\n";
                $count++;
            }
        } catch (Exception $e) {
            echo "Error adding match {$match['team1']} vs {$match['team2']}: " . $e->getMessage() . "\n";
        }
    }
    return $count;
}

// Add all the matches
$totalFootball = 0;
$totalCricket = 0;

echo "Adding Football Matches...\n";
$totalFootball += addMatchesToDB($footballUpcoming, 'football');
$totalFootball += addMatchesToDB($footballLive, 'football');
$totalFootball += addMatchesToDB($footballCompleted, 'football');

echo "\nAdding Cricket Matches...\n";
$totalCricket += addMatchesToDB($cricketUpcoming, 'cricket');
$totalCricket += addMatchesToDB($cricketLive, 'cricket');
$totalCricket += addMatchesToDB($cricketCompleted, 'cricket');

echo "\nSummary:\n";
echo "Added $totalFootball football matches\n";
echo "Added $totalCricket cricket matches\n";
echo "Total: " . ($totalFootball + $totalCricket) . " matches\n";
echo "</pre>";
echo "<p><a href='public/index.php'>Return to homepage</a></p>";
?>