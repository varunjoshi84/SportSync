// filepath: /Applications/XAMPP/xamppfiles/htdocs/sportsync/public/favorite-matches.php
/**
 * Favorite Matches Page
 * 
 * This page displays all matches favorited by the currently logged-in user.
 * Features include:
 * - Display of favorite matches with their details (teams, scores, status, time)
 * - Ability to remove matches from favorites with real-time updates
 * - Requires user authentication
 */

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
    // Create a spinner or loading indicator
    const targetButton = event.target;
    const originalText = targetButton.innerHTML;
    targetButton.innerHTML = '⟳ Processing...';
    targetButton.disabled = true;

    const formData = new FormData();
    formData.append('toggle_favorite', '1');
    formData.append('user_id', '<?php echo $_SESSION['user_id']; ?>');
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

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>