<?php
require_once __DIR__ . '/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$page = 'favorite-matches';
include __DIR__ . '/header.php';

// Get filter parameters
$sport_filter = isset($_GET['sport']) ? $_GET['sport'] : null;
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;

// Get favorite matches with filters
$favorite_matches = getFavoriteMatches($_SESSION['user_id'], $sport_filter, $status_filter);
?>

<div class="flex-grow">
    <div class="max-w-7xl mx-auto mt-32 px-4 pb-16">
        <h2 class="text-3xl font-bold text-white mb-6">My Favorite Matches</h2>
        
        <!-- Filter controls -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-gray-400">Filter by:</span>
            
            <!-- Sport filter buttons -->
            <div class="flex flex-wrap gap-2 mr-4">
                <a href="?page=favorite-matches" 
                   class="<?php echo !isset($_GET['sport']) ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    All Sports
                </a>
                <a href="?page=favorite-matches&sport=cricket<?php echo $status_filter ? '&status='.$status_filter : ''; ?>" 
                   class="<?php echo isset($_GET['sport']) && $_GET['sport'] === 'cricket' ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    Cricket
                </a>
                <a href="?page=favorite-matches&sport=football<?php echo $status_filter ? '&status='.$status_filter : ''; ?>" 
                   class="<?php echo isset($_GET['sport']) && $_GET['sport'] === 'football' ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    Football
                </a>
            </div>
            
            <!-- Status filter buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="?page=favorite-matches<?php echo $sport_filter ? '&sport='.$sport_filter : ''; ?>" 
                   class="<?php echo !isset($_GET['status']) ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    All Status
                </a>
                <a href="?page=favorite-matches<?php echo $sport_filter ? '&sport='.$sport_filter : ''; ?>&status=live" 
                   class="<?php echo isset($_GET['status']) && $_GET['status'] === 'live' ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    Live
                </a>
                <a href="?page=favorite-matches<?php echo $sport_filter ? '&sport='.$sport_filter : ''; ?>&status=upcoming" 
                   class="<?php echo isset($_GET['status']) && $_GET['status'] === 'upcoming' ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    Upcoming
                </a>
                <a href="?page=favorite-matches<?php echo $sport_filter ? '&sport='.$sport_filter : ''; ?>&status=completed" 
                   class="<?php echo isset($_GET['status']) && $_GET['status'] === 'completed' ? 'bg-red-600' : 'bg-gray-800'; ?> text-white px-3 py-1 rounded-full text-sm">
                    Completed
                </a>
            </div>
        </div>
        
        <div class="grid md:grid-cols-3 gap-4" id="match-cards">
            <?php if (count($favorite_matches) > 0): ?>
                <?php foreach ($favorite_matches as $match): ?>
                    <?php
                    $statusClass = $match['status'] === 'live' ? 'bg-red-700' : 'bg-gray-700';
                    ?>
                    <div class="border border-gray-700 rounded-lg p-4 bg-black text-white">
                        <div class="flex justify-between items-center text-sm text-gray-400 mb-2">
                            <span><?php echo htmlspecialchars($match['sport']); ?> · <?php echo date('d M, H:i', strtotime($match['match_time'])); ?></span>
                            <span class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs">
                                <?php echo strtoupper($match['status']); ?>
                            </span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <div class="flex items-center">
                                <span><?php echo htmlspecialchars($match['team1']); ?></span>
                            </div>
                            <span class="font-bold"><?php echo $match['team1_score'] ?? '0'; ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <div class="flex items-center">
                                <span><?php echo htmlspecialchars($match['team2']); ?></span>
                            </div>
                            <span><?php echo $match['team2_score'] ?? ($match['status'] === 'upcoming' ? '-' : '0'); ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span><?php echo htmlspecialchars($match['venue']); ?></span>
                            <button onclick="toggleFavorite(<?php echo $match['id']; ?>, true, <?php echo $_SESSION['user_id']; ?>)" 
                                    class="text-red-500 hover:text-red-400 cursor-pointer">
                                ♡ Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center text-gray-500 py-8">
                    No favorite matches found with the selected filters.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Include the toggle-favorite.js file instead of redefining the function -->
<script src="js/toggle-favorite.js"></script>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>