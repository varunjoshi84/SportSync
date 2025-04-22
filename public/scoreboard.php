<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Get match ID from URL
$match_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get match details
$match = null;
if ($match_id > 0) {
    require_once __DIR__ . '/../backend/match.php';
    $match = getMatchById($match_id);
}

// Redirect if match not found
if (!$match) {
    header('Location: index.php');
    exit();
}

$page = 'scoreboard';
include __DIR__ . '/header.php';
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
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">Virat Kohli</td>
                                    <td class="py-2 px-4">85</td>
                                    <td class="py-2 px-4">76</td>
                                    <td class="py-2 px-4">8</td>
                                    <td class="py-2 px-4">2</td>
                                    <td class="py-2 px-4">111.84</td>
                                    <td class="py-2 px-4">c Smith b Starc</td>
                                </tr>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">Rohit Sharma</td>
                                    <td class="py-2 px-4">62</td>
                                    <td class="py-2 px-4">45</td>
                                    <td class="py-2 px-4">7</td>
                                    <td class="py-2 px-4">1</td>
                                    <td class="py-2 px-4">137.78</td>
                                    <td class="py-2 px-4">lbw b Cummins</td>
                                </tr>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">KL Rahul</td>
                                    <td class="py-2 px-4">45</td>
                                    <td class="py-2 px-4">38</td>
                                    <td class="py-2 px-4">5</td>
                                    <td class="py-2 px-4">0</td>
                                    <td class="py-2 px-4">118.42</td>
                                    <td class="py-2 px-4">not out</td>
                                </tr>
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
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">Mitchell Starc</td>
                                    <td class="py-2 px-4">10</td>
                                    <td class="py-2 px-4">1</td>
                                    <td class="py-2 px-4">65</td>
                                    <td class="py-2 px-4">2</td>
                                    <td class="py-2 px-4">6.50</td>
                                </tr>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">Pat Cummins</td>
                                    <td class="py-2 px-4">10</td>
                                    <td class="py-2 px-4">0</td>
                                    <td class="py-2 px-4">72</td>
                                    <td class="py-2 px-4">1</td>
                                    <td class="py-2 px-4">7.20</td>
                                </tr>
                                <tr class="border-t border-gray-700 text-white">
                                    <td class="py-2 px-4">Adam Zampa</td>
                                    <td class="py-2 px-4">10</td>
                                    <td class="py-2 px-4">0</td>
                                    <td class="py-2 px-4">58</td>
                                    <td class="py-2 px-4">1</td>
                                    <td class="py-2 px-4">5.80</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Match Summary -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-2">Match Summary</h4>
                    <div class="bg-gray-800 rounded-lg p-4 text-white">
                        <p class="mb-2"><strong>Result:</strong> <?php echo htmlspecialchars($match['team1']); ?> won by 5 wickets</p>
                        <p class="mb-2"><strong>Player of the Match:</strong> Virat Kohli (<?php echo htmlspecialchars($match['team1']); ?>)</p>
                        <p><strong>Match Highlights:</strong> <?php echo htmlspecialchars($match['team1']); ?> chased down the target of <?php echo $match['team2_score']; ?> with 2 overs to spare. Virat Kohli's 85 off 76 balls was the highlight of the innings.</p>
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
                            <div class="text-white font-bold">55%</div>
                            <div class="text-white"><?php echo htmlspecialchars($match['team2']); ?></div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Shots on Target</h4>
                        <div class="flex justify-between items-center">
                            <div class="text-white"><?php echo htmlspecialchars($match['team1']); ?></div>
                            <div class="text-white font-bold">8</div>
                            <div class="text-white"><?php echo htmlspecialchars($match['team2']); ?></div>
                        </div>
                    </div>
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Corners</h4>
                        <div class="flex justify-between items-center">
                            <div class="text-white"><?php echo htmlspecialchars($match['team1']); ?></div>
                            <div class="text-white font-bold">6</div>
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
                                <li>1. Alisson (GK)</li>
                                <li>2. Trent Alexander-Arnold</li>
                                <li>3. Virgil van Dijk (C)</li>
                                <li>4. Joe Gomez</li>
                                <li>5. Andy Robertson</li>
                                <li>6. Fabinho</li>
                                <li>7. Jordan Henderson</li>
                                <li>8. Thiago</li>
                                <li>9. Mohamed Salah</li>
                                <li>10. Roberto Firmino</li>
                                <li>11. Sadio Mane</li>
                            </ul>
                            
                            <h5 class="text-white font-semibold mt-4 mb-2">Substitutes</h5>
                            <ul class="text-white space-y-1">
                                <li>12. Naby Keita</li>
                                <li>13. Diogo Jota</li>
                                <li>14. James Milner</li>
                                <li>15. Alex Oxlade-Chamberlain</li>
                            </ul>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($match['team2']); ?> Lineup</h4>
                        <div class="bg-gray-800 rounded-lg p-4">
                            <h5 class="text-white font-semibold mb-2">Starting XI</h5>
                            <ul class="text-white space-y-1">
                                <li>1. Ederson (GK)</li>
                                <li>2. Kyle Walker</li>
                                <li>3. Ruben Dias</li>
                                <li>4. John Stones</li>
                                <li>5. Joao Cancelo</li>
                                <li>6. Rodri</li>
                                <li>7. Kevin De Bruyne</li>
                                <li>8. Bernardo Silva</li>
                                <li>9. Raheem Sterling</li>
                                <li>10. Phil Foden</li>
                                <li>11. Gabriel Jesus</li>
                            </ul>
                            
                            <h5 class="text-white font-semibold mt-4 mb-2">Substitutes</h5>
                            <ul class="text-white space-y-1">
                                <li>12. Riyad Mahrez</li>
                                <li>13. Ilkay Gundogan</li>
                                <li>14. Fernandinho</li>
                                <li>15. Jack Grealish</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Match Events -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-2">Match Events</h4>
                    <div class="bg-gray-800 rounded-lg p-4">
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">12'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-red-500">⚽ GOAL</span> - Mohamed Salah (<?php echo htmlspecialchars($match['team1']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">28'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-red-500">⚽ GOAL</span> - Kevin De Bruyne (<?php echo htmlspecialchars($match['team2']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">45'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-yellow-500">🟨 YELLOW CARD</span> - Fabinho (<?php echo htmlspecialchars($match['team1']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">67'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-red-500">⚽ GOAL</span> - Sadio Mane (<?php echo htmlspecialchars($match['team1']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">72'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-blue-500">🔄 SUBSTITUTION</span> - Naby Keita replaces Jordan Henderson (<?php echo htmlspecialchars($match['team1']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">78'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-blue-500">🔄 SUBSTITUTION</span> - Riyad Mahrez replaces Phil Foden (<?php echo htmlspecialchars($match['team2']); ?>)
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-16 text-gray-400 text-sm">89'</div>
                                <div class="flex-1 text-white">
                                    <span class="text-red-500">⚽ GOAL</span> - Raheem Sterling (<?php echo htmlspecialchars($match['team2']); ?>)
                                </div>
                            </div>
                        </div>
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