<?php
require_once __DIR__ . '/init.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ?page=login");
    exit();
}

$page = 'favorite-matches';
include __DIR__ . '/header.php';

$favorite_matches = getFavoriteMatches($_SESSION['user_id']);
?>

<div class="flex-grow">
    <div class="max-w-7xl mx-auto mt-32 px-4 pb-16">
        <h2 class="text-3xl font-bold text-white mb-6">My Favorite Matches</h2>
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
                            <div class="flex items-center space-x-2">
                                <img src="https://flagcdn.com/w40/<?php echo strtolower($match['team1_country'] ?? ($match['sport'] === 'cricket' ? 'in' : 'gb')); ?>.svg" 
                                     alt="<?php echo htmlspecialchars($match['team1']); ?> flag" class="w-5 h-5">
                                <span><?php echo htmlspecialchars($match['team1']); ?></span>
                            </div>
                            <span class="font-bold"><?php echo $match['team1_score'] ?? '0'; ?></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <img src="https://flagcdn.com/w40/<?php echo strtolower($match['team2_country'] ?? ($match['sport'] === 'cricket' ? 'in' : 'gb')); ?>.svg" 
                                     alt="<?php echo htmlspecialchars($match['team2']); ?> flag" class="w-5 h-5">
                                <span><?php echo htmlspecialchars($match['team2']); ?></span>
                            </div>
                            <span><?php echo $match['team2_score'] ?? ($match['status'] === 'upcoming' ? '-' : '0'); ?></span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span><?php echo htmlspecialchars($match['venue']); ?></span>
                            <button onclick="toggleFavorite(<?php echo $match['id']; ?>, true)" 
                                    class="text-red-500 hover:text-red-400 cursor-pointer">
                                ♡ Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center text-gray-500 py-8">
                    You haven't added any matches to your favorites yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleFavorite(matchId, isFavorite) {
    const formData = new FormData();
    formData.append('toggle_favorite', '1');
    formData.append('user_id', '<?php echo $_SESSION['user_id']; ?>');
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
</body>
</html> 