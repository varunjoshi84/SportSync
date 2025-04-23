<?php
require_once __DIR__ . '/init.php';

$page = 'football';
include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - Football</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="js/toggle-favorite.js"></script>
</head>
<body class="bg-black min-h-screen flex flex-col">
    <div class="flex-grow">
        <div class="max-w-7xl mx-auto mt-32 px-4 pb-16">
            <h2 class="text-3xl font-bold text-white mb-6">Football</h2>
            <p class="text-gray-400 mb-8">Latest matches, news, and updates for football fans</p>

            <!-- Match Filter Tabs -->
            <div class="flex space-x-4 mb-8">
                <button class="px-6 py-2 rounded-full bg-red-500 text-white filter-btn active" data-filter="all">
                    All
                </button>
                <button class="px-6 py-2 rounded-full bg-gray-800 text-white hover:bg-red-500 filter-btn" data-filter="live">
                    Live <span class="ml-2 px-2 py-0.5 bg-white text-red-500 rounded-full text-xs live-count">0</span>
                </button>
                <button class="px-6 py-2 rounded-full bg-gray-800 text-white hover:bg-red-500 filter-btn" data-filter="upcoming">
                    Upcoming
                </button>
                <button class="px-6 py-2 rounded-full bg-gray-800 text-white hover:bg-red-500 filter-btn" data-filter="completed">
                    Completed
                </button>
            </div>

            <!-- Match Cards Container -->
            <div id="match-cards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php 
                $activeFilter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
                $matches = $activeFilter === 'all' ? getAllFootballMatches() : getFootballMatches($activeFilter);
                $liveMatches = getFootballMatches('live');
                $liveCount = count($liveMatches);
                
                if (empty($matches)) {
                    echo '<div class="col-span-3 text-center text-gray-500 py-8">No ' . htmlspecialchars($activeFilter) . ' football matches at the moment.</div>';
                } else {
                    foreach ($matches as $match): 
                        $statusClass = $match['status'] === 'live' ? 'bg-red-700' : 'bg-gray-700';
                        $isFavorite = isset($_SESSION['user_id']) ? isMatchFavorited($_SESSION['user_id'], $match['id']) : false;
                        $favoriteClass = $isFavorite ? 'text-red-500' : 'text-gray-500';
                        $favoriteIcon = $isFavorite ? '♥' : '♡';
                ?>
                <div class="border border-gray-700 rounded-lg p-6 bg-black text-white shadow-lg">
                    <div class="flex justify-between items-center text-sm text-gray-400 mb-3">
                        <span>Football · <?php echo date('d M, H:i', strtotime($match['match_time'])); ?></span>
                        <span class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs"><?php echo strtoupper($match['status']); ?></span>
                    </div>
                    
                    <div class="flex justify-between mb-3">
                        <div class="flex items-center">
                            <span class="text-lg"><?php echo htmlspecialchars($match['team1']); ?></span>
                        </div>
                        <span class="font-bold text-lg">
                            <?php 
                            if ($match['status'] === 'upcoming') {
                                echo '-';
                            } else {
                                echo htmlspecialchars($match['team1_score']);
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between mb-3">
                        <div class="flex items-center">
                            <span class="text-lg"><?php echo htmlspecialchars($match['team2']); ?></span>
                        </div>
                        <span class="text-lg">
                            <?php 
                            if ($match['status'] === 'upcoming') {
                                echo '-';
                            } else {
                                echo htmlspecialchars($match['team2_score']);
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between text-sm text-gray-500 mt-3">
                        <span><?php echo htmlspecialchars($match['venue']); ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <button onclick="toggleFavorite(<?php echo $match['id']; ?>, <?php echo $isFavorite ? 'true' : 'false'; ?>, <?php echo $_SESSION['user_id']; ?>)" 
                                    class="<?php echo $favoriteClass; ?> hover:text-red-400 cursor-pointer">
                                <?php echo $favoriteIcon; ?> <?php echo $isFavorite ? 'Remove' : 'Favorite'; ?>
                            </button>
                        <?php else: ?>
                            <a href="login.php" class="text-gray-500 hover:text-red-400 cursor-pointer">♡ Login to Favorite</a>
                        <?php endif; ?>
                    </div>
                    
                    <a href="scoreboard.php?id=<?php echo $match['id']; ?>" 
                       class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors mt-4">
                        View Detailed Scoreboard
                    </a>
                </div>
                <?php 
                    endforeach;
                }
                ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let activeFilter = '<?php echo $activeFilter; ?>';
        const filterButtons = document.querySelectorAll('.filter-btn');
        const liveCountElement = document.querySelector('.live-count');
        
        // Set initial active button
        filterButtons.forEach(btn => {
            if (btn.dataset.filter === activeFilter) {
                btn.classList.remove('bg-gray-800');
                btn.classList.add('bg-red-500');
            } else {
                btn.classList.remove('bg-red-500');
                btn.classList.add('bg-gray-800');
            }
        });

        // Set initial live count
        liveCountElement.textContent = '<?php echo $liveCount; ?>';
        
        function updateMatches() {
            const url = activeFilter === 'all' 
                ? '../backend/match.php?action=fetch_matches&sport=football&all=true'
                : '../backend/match.php?action=fetch_matches&sport=football&status=' + activeFilter;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    const matches = Array.isArray(data) ? data : [];
                    const container = document.getElementById('match-cards');
                    
                    if (matches.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-3 text-center text-gray-500 py-8">
                                No ${activeFilter} football matches at the moment.
                            </div>`;
                        return;
                    }

                    container.innerHTML = matches.map(match => {
                        const statusClass = match.status === 'live' ? 'bg-red-700' : 'bg-gray-700';
                        const isFavorite = false; // This should be handled by the backend
                        const favoriteClass = isFavorite ? 'text-red-500' : 'text-gray-500';
                        const favoriteIcon = isFavorite ? '♥' : '♡';
                        
                        return `
                            <div class="border border-gray-700 rounded-lg p-6 bg-black text-white shadow-lg">
                                <div class="flex justify-between items-center text-sm text-gray-400 mb-3">
                                    <span>Football · ${new Date(match.match_time).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'})}</span>
                                    <span class="${statusClass} px-2 py-0.5 rounded-full text-xs">${match.status.toUpperCase()}</span>
                                </div>
                                
                                <div class="flex justify-between mb-3">
                                    <div class="flex items-center">
                                        <span class="text-lg">${match.team1}</span>
                                    </div>
                                    <span class="font-bold text-lg">
                                        ${match.status === 'upcoming' ? '-' : match.team1_score}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between mb-3">
                                    <div class="flex items-center">
                                        <span class="text-lg">${match.team2}</span>
                                    </div>
                                    <span class="text-lg">
                                        ${match.status === 'upcoming' ? '-' : match.team2_score}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between text-sm text-gray-500 mt-3">
                                    <span>${match.venue}</span>
                                    <button onclick="toggleFavorite(${match.id}, ${isFavorite})" 
                                            class="${favoriteClass} hover:text-red-400 cursor-pointer">
                                        ${favoriteIcon} ${isFavorite ? 'Remove' : 'Favorite'}
                                    </button>
                                </div>
                                
                                <a href="scoreboard.php?id=${match.id}" 
                                   class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors mt-4">
                                    View Detailed Scoreboard
                                </a>
                            </div>
                        `;
                    }).join('');
                })
                .catch(error => {
                    console.error('Error fetching matches:', error);
                });
        }

        // Update matches every 30 seconds
        setInterval(updateMatches, 30000);
        
        // Handle filter button clicks
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                activeFilter = filter;
                
                // Update button styles
                filterButtons.forEach(b => {
                    if (b === this) {
                        b.classList.remove('bg-gray-800');
                        b.classList.add('bg-red-500');
                    } else {
                        b.classList.remove('bg-red-500');
                        b.classList.add('bg-gray-800');
                    }
                });
                
                // Update matches
                updateMatches();
            });
        });
    });

    function toggleFavorite(matchId, isFavorite) {
        // Create a spinner or loading indicator
        const targetButton = event.target;
        const originalText = targetButton.innerHTML;
        targetButton.innerHTML = '⟳ Processing...';
        targetButton.disabled = true;

        const formData = new FormData();
        formData.append('toggle_favorite', '1');
        formData.append('user_id', '<?php echo $_SESSION['user_id'] ?? ''; ?>');
        formData.append('match_id', matchId);
        formData.append('is_favorite', isFavorite ? 'true' : 'false');

        // Add a timestamp to prevent caching
        const timestamp = new Date().getTime();
        
        fetch(`../backend/match.php?_=${timestamp}`, {
            method: 'POST',
            body: formData,
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network error: ${response.status} ${response.statusText}`);
            }
            return response.text().then(text => {
                if (!text || text.trim() === '') {
                    console.error('Empty response from server');
                    return { success: false, error: 'Empty response from server' };
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            if (data.success) {
                console.log('Favorite toggled successfully:', data);
                window.location.reload();
            } else {
                targetButton.innerHTML = originalText;
                targetButton.disabled = false;
                alert('Failed to update favorite status: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            targetButton.innerHTML = originalText;
            targetButton.disabled = false;
            alert('An error occurred while updating favorite status: ' + error.message);
        });
    }
    </script>
</body>
</html>
