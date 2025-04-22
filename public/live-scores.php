<?php
require_once __DIR__ . '/init.php';

$page = 'live-scores';
include __DIR__ . '/header.php';

// Get the active filter from URL parameter or set default to 'all'
$activeFilter = $_GET['sport'] ?? 'all';
?>

<div class="flex-grow">
    <div class="max-w-7xl mx-auto mt-32 px-4 pb-16">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-white">All Live Scores</h2>
            
            <!-- Sport Filter Tabs -->
            <div class="flex space-x-2">
                <a href="?page=live-scores&sport=all" 
                   class="px-4 py-2 rounded-lg <?php echo $activeFilter === 'all' ? 'bg-red-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'; ?>">
                    All
                </a>
                <a href="?page=live-scores&sport=football" 
                   class="px-4 py-2 rounded-lg <?php echo $activeFilter === 'football' ? 'bg-red-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'; ?>">
                    Football
                </a>
                <a href="?page=live-scores&sport=cricket" 
                   class="px-4 py-2 rounded-lg <?php echo $activeFilter === 'cricket' ? 'bg-red-500 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700'; ?>">
                    Cricket
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="match-cards">
            <?php
            // Get matches based on the active filter
            $matches = $activeFilter === 'all' ? getLiveMatches() : getLiveMatchesBySport($activeFilter);
            
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                $statusClass = $match['status'] === 'live' ? 'bg-red-700' : 'bg-gray-700';
                $isFavorite = isset($_SESSION['user_id']) ? isMatchFavorited($_SESSION['user_id'], $match['id']) : false;
                $favoriteClass = $isFavorite ? 'text-red-500' : 'text-gray-500';
                $favoriteIcon = $isFavorite ? '♥' : '♡';
                echo '<div class="border border-gray-700 rounded-lg p-6 bg-black text-white shadow-lg">';
                echo '<div class="flex justify-between items-center text-sm text-gray-400 mb-3">';
                echo '<span>' . htmlspecialchars($match['sport'] ?? '') . ' · ' . date('d M, H:i', strtotime($match['match_time'])) . '</span>';
                echo '<span class="' . $statusClass . ' px-2 py-0.5 rounded-full text-xs">' . strtoupper($match['status']) . '</span>';
                echo '</div>';
                echo '<div class="flex justify-between mb-3">';
                echo '<div class="flex items-center space-x-2">';
                echo '<img src="https://flagcdn.com/w40/' . strtolower($match['team1_country'] ?? ($match['sport'] === 'cricket' ? 'in' : 'gb')) . '.svg" alt="' . htmlspecialchars($match['team1'] ?? '') . ' flag" class="w-6 h-6">';
                echo '<span class="text-lg">' . htmlspecialchars($match['team1'] ?? '') . '</span>';
                echo '</div>';
                echo '<span class="font-bold text-lg">' . htmlspecialchars($match['team1_score'] ?? '0') . '</span>';
                echo '</div>';
                echo '<div class="flex justify-between mb-3">';
                echo '<div class="flex items-center space-x-2">';
                echo '<img src="https://flagcdn.com/w40/' . strtolower($match['team2_country'] ?? ($match['sport'] === 'cricket' ? 'in' : 'gb')) . '.svg" alt="' . htmlspecialchars($match['team2'] ?? '') . ' flag" class="w-6 h-6">';
                echo '<span class="text-lg">' . htmlspecialchars($match['team2'] ?? '') . '</span>';
                echo '</div>';
                echo '<span class="text-lg">' . htmlspecialchars($match['team2_score'] ?? ($match['status'] === 'upcoming' ? '-' : '0')) . '</span>';
                echo '</div>';
                echo '<div class="flex justify-between text-sm text-gray-500 mt-3">';
                echo '<span>' . htmlspecialchars($match['location'] ?? $match['venue'] ?? '') . '</span>';
                if (isset($_SESSION['user_id'])) {
                    echo '<button onclick="toggleFavorite(' . $match['id'] . ', ' . ($isFavorite ? 'true' : 'false') . ')" class="' . $favoriteClass . ' hover:text-red-400 cursor-pointer">' . $favoriteIcon . ' ' . ($isFavorite ? 'Remove' : 'Favorite') . '</button>';
                } else {
                    echo '<a href="?page=login" class="text-gray-500 hover:text-red-400 cursor-pointer">♡ Login to Favorite</a>';
                }
                echo '</div>';
                echo '</div>';
                }
            } else {
                echo '<div class="col-span-3 text-center text-gray-500 py-8">No live matches at the moment.</div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
function toggleFavorite(matchId, isFavorite) {
    const formData = new FormData();
    formData.append('toggle_favorite', '1');
    formData.append('user_id', '<?php echo $_SESSION['user_id'] ?? ''; ?>');
    formData.append('match_id', matchId);
    formData.append('is_favorite', isFavorite);

    fetch('../backend/match.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Failed to update favorite status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating favorite status');
    });
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
