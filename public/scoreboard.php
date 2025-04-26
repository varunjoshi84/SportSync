<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Get match ID from URL
$match_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle player score updates
$scoreUpdateMessage = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_player_score'])) {
    require_once __DIR__ . '/../backend/player.php';
    
    $player_id = isset($_POST['player_id']) ? intval($_POST['player_id']) : 0;
    $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
    
    if ($player_id > 0 && updatePlayerScore($player_id, $score)) {
        $scoreUpdateMessage = '<div class="bg-green-700 text-white p-2 rounded mb-4">Player score updated successfully!</div>';
    } else {
        $scoreUpdateMessage = '<div class="bg-red-700 text-white p-2 rounded mb-4">Failed to update player score.</div>';
    }
}

// Get match details
$match = null;
if ($match_id > 0) {
    require_once __DIR__ . '/../backend/match.php';
    require_once __DIR__ . '/../backend/player.php';
    $match = getMatchById($match_id);
    
    // Fetch players for both teams
    $team1Players = getPlayersByMatch($match_id, $match['team1']);
    $team2Players = getPlayersByMatch($match_id, $match['team2']);
}

// Redirect if match not found
if (!$match) {
    header('Location: index.php');
    exit();
}

$page = 'scoreboard';
include __DIR__ . '/header.php';

// Check if the user is an admin
$isAdmin = isset($_SESSION['user_id']) && getUserById($_SESSION['user_id'])['account_type'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - <?php echo htmlspecialchars($match['team1'] . ' vs ' . $match['team2']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex flex-col">
    <div class="flex-grow">
        <div class="max-w-7xl mx-auto mt-32 px-4 mb-16">
            <!-- Display score update message if any -->
            <?php echo $scoreUpdateMessage; ?>
            
            <div class="mb-6">
                <a href="index.php?page=<?php echo $match['sport']; ?>" class="text-red-500 hover:text-red-400 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to <?php echo ucfirst($match['sport']); ?> Matches
                </a>
            </div>

            <div class="bg-gray-900 rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-white">
                        <?php echo htmlspecialchars($match['team1'] . ' vs ' . $match['team2']); ?>
                    </h2>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold 
                        <?php echo $match['status'] === 'live' ? 'bg-red-700 text-white' : 
                            ($match['status'] === 'completed' ? 'bg-gray-700 text-white' : 'bg-blue-700 text-white'); ?>">
                        <?php echo strtoupper($match['status']); ?>
                    </span>
                </div>
                
                <div class="flex justify-between items-center mb-4">
                    <div class="text-gray-400">
                        <span class="mr-2">🏟️</span> <?php echo htmlspecialchars($match['venue']); ?>
                    </div>
                    <div class="text-gray-400">
                        <span class="mr-2">🕒</span> <?php echo date('F j, Y, g:i a', strtotime($match['match_time'])); ?>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($match['team1']); ?></h3>
                        <div class="text-4xl font-bold text-white"><?php echo $match['team1_score']; ?></div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($match['team2']); ?></h3>
                        <div class="text-4xl font-bold text-white"><?php echo $match['team2_score']; ?></div>
                    </div>
                </div>

                <?php if ($match['status'] === 'completed'): ?>
                <div class="bg-gray-800 rounded-lg p-4 text-center mb-4">
                    <h3 class="text-xl font-bold 
                        <?php echo $match['winner'] === 'Draw' ? 'text-yellow-500' : 'text-green-500'; ?>">
                        <?php if ($match['winner'] === 'Draw'): ?>
                            Match ended in a Draw
                        <?php else: ?>
                            <?php echo htmlspecialchars($match['winner']); ?> Won
                        <?php endif; ?>
                    </h3>
                </div>
                <?php endif; ?>

                <?php if ($isAdmin && ($match['status'] === 'live' || $match['status'] === 'completed')): ?>
                <div class="bg-gray-800 rounded-lg p-4 mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Update Player Scores</h3>
                    
                    <?php if (!empty($team1Players) || !empty($team2Players)): ?>
                    <form method="POST" class="space-y-4">
                        <div class="mb-4">
                            <label for="player_id" class="block text-white mb-2">Select Player</label>
                            <select id="player_id" name="player_id" class="w-full p-2 rounded bg-gray-700 text-white" required>
                                <option value="">-- Select Player --</option>
                                <?php if (!empty($team1Players)): ?>
                                <optgroup label="<?php echo htmlspecialchars($match['team1']); ?>">
                                    <?php foreach ($team1Players as $player): ?>
                                    <option value="<?php echo $player['id']; ?>"><?php echo htmlspecialchars($player['name'] . ' - ' . $player['role']); ?> (Current: <?php echo $player['score']; ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                                
                                <?php if (!empty($team2Players)): ?>
                                <optgroup label="<?php echo htmlspecialchars($match['team2']); ?>">
                                    <?php foreach ($team2Players as $player): ?>
                                    <option value="<?php echo $player['id']; ?>"><?php echo htmlspecialchars($player['name'] . ' - ' . $player['role']); ?> (Current: <?php echo $player['score']; ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="score" class="block text-white mb-2">Score</label>
                            <input type="number" id="score" name="score" min="0" class="w-full p-2 rounded bg-gray-700 text-white" required>
                        </div>
                        
                        <button type="submit" name="update_player_score" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Update Score</button>
                    </form>
                    <?php else: ?>
                    <p class="text-white">No players available for this match yet.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($match['sport'] === 'cricket'): ?>
            <!-- Cricket Scoreboard -->
            <div class="bg-gray-900 rounded-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-white mb-4">Cricket Scorecard</h3>
                
                <!-- Team 1 Batting -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($match['team1']); ?> Batting</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-800 rounded-lg">
                            <thead>
                                <tr class="text-gray-300 text-left">
                                    <th class="py-2 px-4">Batsman</th>
                                    <th class="py-2 px-4">R</th>
                                    <th class="py-2 px-4">B</th>
                                    <th class="py-2 px-4">4s</th>
                                    <th class="py-2 px-4">6s</th>
                                    <th class="py-2 px-4">SR</th>
                                    <th class="py-2 px-4">Dismissal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $batsmen = array_filter($team1Players ?? [], function($player) {
                                    return $player['role'] === 'Batsman' || $player['role'] === 'All-rounder';
                                });
                                
                                if (!empty($batsmen)): 
                                    foreach ($batsmen as $player):
                                ?>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($player['name']); ?></td>
                                    <td class="py-2 px-4"><?php echo $player['score']; ?></td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr class="border-t border-gray-700 text-white">
                                    <td colspan="7" class="py-2 px-4 text-center">No player data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Team 2 Bowling -->
                <div class="mb-6">
                    <h4 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($match['team2']); ?> Bowling</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-800 rounded-lg">
                            <thead>
                                <tr class="text-gray-300 text-left">
                                    <th class="py-2 px-4">Bowler</th>
                                    <th class="py-2 px-4">O</th>
                                    <th class="py-2 px-4">M</th>
                                    <th class="py-2 px-4">R</th>
                                    <th class="py-2 px-4">W</th>
                                    <th class="py-2 px-4">Econ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $bowlers = array_filter($team2Players ?? [], function($player) {
                                    return $player['role'] === 'Bowler' || $player['role'] === 'All-rounder';
                                });
                                
                                if (!empty($bowlers)): 
                                    foreach ($bowlers as $player):
                                ?>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4"><?php echo htmlspecialchars($player['name']); ?></td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                    <td class="py-2 px-4">-</td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr class="border-t border-gray-700 text-white">
                                    <td colspan="6" class="py-2 px-4 text-center">No player data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Match Summary -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-2">Match Summary</h4>
                    <div class="bg-gray-800 rounded-lg p-4 text-white">
                        <p class="mb-2"><strong>Result:</strong> 
                            <?php if ($match['status'] === 'completed'): ?>
                                <?php echo $match['team1_score'] > $match['team2_score'] ? 
                                    htmlspecialchars($match['team1']) . ' won' : 
                                    htmlspecialchars($match['team2']) . ' won'; ?>
                            <?php else: ?>
                                Match in progress
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($team1Players) || !empty($team2Players)): ?>
                            <?php 
                            $topScorer = null;
                            $maxScore = 0;
                            
                            foreach (array_merge($team1Players ?? [], $team2Players ?? []) as $player) {
                                if ($player['score'] > $maxScore) {
                                    $maxScore = $player['score'];
                                    $topScorer = $player;
                                }
                            }
                            
                            if ($topScorer): 
                            ?>
                            <p class="mb-2"><strong>Top Performer:</strong> 
                                <?php echo htmlspecialchars($topScorer['name'] . ' (' . $topScorer['team'] . ')'); ?>
                            </p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p><strong>Match Highlights:</strong> 
                            <?php echo $match['status'] === 'upcoming' ? 
                                'Match yet to start.' : 
                                'Match highlights will be updated soon.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php elseif ($match['sport'] === 'football'): ?>
            <!-- Football Scoreboard -->
            <div class="bg-gray-900 rounded-lg p-6 mb-8">
                <h3 class="text-xl font-bold text-white mb-4">Football Match Details</h3>
                
                <!-- Match Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Possession</h4>
                        <div class="flex justify-between items-center">
                            <div class="text-white"><?php echo htmlspecialchars($match['team1']); ?></div>
                            <div class="text-white font-bold"><?php echo $match['team1_possession'] ?? '50'; ?>%</div>
                            <div class="text-white"><?php echo htmlspecialchars($match['team2']); ?></div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Shots on Target</h4>
                        <div class="flex justify-between items-center">
                            <div class="text-white"><?php echo htmlspecialchars($match['team1']); ?></div>
                            <div class="text-white font-bold"><?php echo $match['team1_shots'] ?? '0'; ?> - <?php echo $match['team2_shots'] ?? '0'; ?></div>
                            <div class="text-white"><?php echo htmlspecialchars($match['team2']); ?></div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Corners</h4>
                        <div class="flex justify-between items-center">
                            <div class="text-white"><?php echo htmlspecialchars($match['team1']); ?></div>
                            <div class="text-white font-bold">-</div>
                            <div class="text-white"><?php echo htmlspecialchars($match['team2']); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Team Lineups -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($match['team1']); ?> Lineup</h4>
                        <div class="bg-gray-800 rounded-lg p-4">
                            <h5 class="text-white font-semibold mb-2">Starting XI</h5>
                            <ul class="text-white space-y-1">
                                <?php 
                                $starters = array_filter($team1Players ?? [], function($player) {
                                    return strpos($player['role'], 'Substitute') === false;
                                });
                                
                                if (!empty($starters)): 
                                    foreach ($starters as $index => $player):
                                ?>
                                <li><?php echo ($index + 1) . '. ' . htmlspecialchars($player['name']) . 
                                    ($player['role'] === 'Goalkeeper' ? ' (GK)' : '') . 
                                    (strpos($player['role'], 'Captain') !== false ? ' (C)' : ''); ?></li>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <li>No player data available</li>
                                <?php endif; ?>
                            </ul>
                            
                            <h5 class="text-white font-semibold mt-4 mb-2">Substitutes</h5>
                            <ul class="text-white space-y-1">
                                <?php 
                                $substitutes = array_filter($team1Players ?? [], function($player) {
                                    return strpos($player['role'], 'Substitute') !== false;
                                });
                                
                                if (!empty($substitutes)): 
                                    foreach ($substitutes as $index => $player):
                                ?>
                                <li><?php echo ($index + 1) . '. ' . htmlspecialchars($player['name']); ?></li>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <li>No substitute data available</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($match['team2']); ?> Lineup</h4>
                        <div class="bg-gray-800 rounded-lg p-4">
                            <h5 class="text-white font-semibold mb-2">Starting XI</h5>
                            <ul class="text-white space-y-1">
                                <?php 
                                $starters = array_filter($team2Players ?? [], function($player) {
                                    return strpos($player['role'], 'Substitute') === false;
                                });
                                
                                if (!empty($starters)): 
                                    foreach ($starters as $index => $player):
                                ?>
                                <li><?php echo ($index + 1) . '. ' . htmlspecialchars($player['name']) . 
                                    ($player['role'] === 'Goalkeeper' ? ' (GK)' : '') . 
                                    (strpos($player['role'], 'Captain') !== false ? ' (C)' : ''); ?></li>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <li>No player data available</li>
                                <?php endif; ?>
                            </ul>
                            
                            <h5 class="text-white font-semibold mt-4 mb-2">Substitutes</h5>
                            <ul class="text-white space-y-1">
                                <?php 
                                $substitutes = array_filter($team2Players ?? [], function($player) {
                                    return strpos($player['role'], 'Substitute') !== false;
                                });
                                
                                if (!empty($substitutes)): 
                                    foreach ($substitutes as $index => $player):
                                ?>
                                <li><?php echo ($index + 1) . '. ' . htmlspecialchars($player['name']); ?></li>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <li>No substitute data available</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Match Events -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-2">Match Events</h4>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <?php if ($match['status'] === 'upcoming'): ?>
                            <div class="text-white text-center">Match has not started yet. Events will be updated during the match.</div>
                        <?php elseif (empty($team1Players) && empty($team2Players)): ?>
                            <div class="text-white text-center">No match events available yet. Please check back later.</div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php if ($match['team1_score'] > 0 || $match['team2_score'] > 0): ?>
                                    <?php
                                    // Find goal scorers based on player scores
                                    $goalScorers = [];
                                    foreach (array_merge($team1Players ?? [], $team2Players ?? []) as $player) {
                                        if ($player['score'] > 0) {
                                            $goalScorers[] = [
                                                'name' => $player['name'],
                                                'team' => $player['team'],
                                                'time' => rand(1, 90) // Random minute for demonstration
                                            ];
                                        }
                                    }
                                    
                                    // Sort by minute
                                    usort($goalScorers, function($a, $b) {
                                        return $a['time'] - $b['time'];
                                    });
                                    
                                    foreach ($goalScorers as $scorer):
                                    ?>
                                    <div class="flex items-start">
                                        <div class="w-16 text-gray-400 text-sm"><?php echo $scorer['time']; ?>'</div>
                                        <div class="flex-1 text-white">
                                            <span class="text-red-500">⚽ GOAL</span> - <?php echo htmlspecialchars($scorer['name'] . ' (' . $scorer['team'] . ')'); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-white text-center">No goals scored yet.</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
<?php
?>