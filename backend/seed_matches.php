<?php
require_once __DIR__ . '/match.php';

// First, delete existing matches without truncating
$db = getDB();
$db->exec("DELETE FROM matches");

// Add sample cricket matches
$matches = [
    // Live cricket match
    [
        'team1' => 'India',
        'team2' => 'Australia',
        'venue' => 'Melbourne Cricket Ground',
        'match_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'sport' => 'cricket',
        'status' => 'live',
        'team1_score' => 245,
        'team2_score' => 189
    ],
    // Upcoming cricket match
    [
        'team1' => 'England',
        'team2' => 'Pakistan',
        'venue' => 'Lords Cricket Ground',
        'match_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        'sport' => 'cricket',
        'status' => 'upcoming'
    ],
    // Recent completed cricket match
    [
        'team1' => 'South Africa',
        'team2' => 'New Zealand',
        'venue' => 'Eden Park',
        'match_time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'sport' => 'cricket',
        'status' => 'completed',
        'team1_score' => 302,
        'team2_score' => 298
    ],
    // Champions Trophy 2025 Final
    [
        'team1' => 'India',
        'team2' => 'England',
        'venue' => 'The Oval, London',
        'match_time' => '2025-06-18 14:00:00',
        'sport' => 'cricket',
        'status' => 'completed',
        'team1_score' => 328,
        'team2_score' => 325
    ],
    // Champions Trophy 2025 Semi-Final
    [
        'team1' => 'Australia',
        'team2' => 'Pakistan',
        'venue' => 'Edgbaston, Birmingham',
        'match_time' => '2025-06-15 10:30:00',
        'sport' => 'cricket',
        'status' => 'completed',
        'team1_score' => 285,
        'team2_score' => 290
    ],
    // Champions Trophy 2025 Quarter-Final
    [
        'team1' => 'New Zealand',
        'team2' => 'South Africa',
        'venue' => 'Lord\'s, London',
        'match_time' => '2025-06-12 13:00:00',
        'sport' => 'cricket',
        'status' => 'completed',
        'team1_score' => 312,
        'team2_score' => 308
    ],
    // Live football match
    [
        'team1' => 'Manchester United',
        'team2' => 'Liverpool',
        'venue' => 'Old Trafford',
        'match_time' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
        'sport' => 'football',
        'status' => 'live',
        'team1_score' => 2,
        'team2_score' => 1
    ],
    // Upcoming football match
    [
        'team1' => 'Real Madrid',
        'team2' => 'Barcelona',
        'venue' => 'Santiago Bernabéu',
        'match_time' => date('Y-m-d H:i:s', strtotime('+3 hours')),
        'sport' => 'football',
        'status' => 'upcoming'
    ],
    // Recent completed football match
    [
        'team1' => 'Bayern Munich',
        'team2' => 'Paris Saint-Germain',
        'venue' => 'Allianz Arena',
        'match_time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'sport' => 'football',
        'status' => 'completed',
        'team1_score' => 3,
        'team2_score' => 2
    ],
    // FIFA 2023 World Cup Final
    [
        'team1' => 'Argentina',
        'team2' => 'France',
        'venue' => 'Lusail Iconic Stadium, Qatar',
        'match_time' => '2023-12-18 18:00:00',
        'sport' => 'football',
        'status' => 'completed',
        'team1_score' => 3,
        'team2_score' => 3
    ],
    // FIFA 2023 World Cup Semi-Final
    [
        'team1' => 'Croatia',
        'team2' => 'Argentina',
        'venue' => 'Lusail Iconic Stadium, Qatar',
        'match_time' => '2023-12-13 22:00:00',
        'sport' => 'football',
        'status' => 'completed',
        'team1_score' => 0,
        'team2_score' => 3
    ],
    // FIFA 2023 World Cup Semi-Final
    [
        'team1' => 'France',
        'team2' => 'Morocco',
        'venue' => 'Al Bayt Stadium, Qatar',
        'match_time' => '2023-12-14 22:00:00',
        'sport' => 'football',
        'status' => 'completed',
        'team1_score' => 2,
        'team2_score' => 0
    ],
    // FIFA 2023 World Cup Quarter-Final
    [
        'team1' => 'England',
        'team2' => 'France',
        'venue' => 'Al Bayt Stadium, Qatar',
        'match_time' => '2023-12-10 20:00:00',
        'sport' => 'football',
        'status' => 'completed',
        'team1_score' => 1,
        'team2_score' => 2
    ]
];

// Add matches to database
foreach ($matches as $match) {
    addMatch(
        $match['team1'],
        $match['team2'],
        $match['venue'],
        $match['match_time'],
        $match['sport'],
        $match['status']
    );
    
    // Update scores for live and completed matches
    if (isset($match['team1_score']) && isset($match['team2_score'])) {
        $lastId = $db->lastInsertId();
        updateMatchScore($lastId, $match['team1_score'], $match['team2_score']);
    }
}

echo "Sample matches added successfully!\n";
?> 