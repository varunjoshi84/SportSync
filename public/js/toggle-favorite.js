/**
 * Function to toggle a match's favorite status
 * 
 * @param {number} matchId - The ID of the match to toggle
 * @param {boolean} isFavorite - Whether the match is currently a favorite
 * @param {number} userId - The ID of the current user
 */
function toggleFavorite(matchId, isFavorite, userId) {
    // Validate required parameters
    if (!matchId || !userId) {
        alert('Error: Missing match ID or user ID');
        return;
    }
    
    // Create a spinner or loading indicator
    const targetButton = event.target;
    const originalText = targetButton.innerHTML;
    targetButton.innerHTML = '⟳ Processing...';
    targetButton.disabled = true;

    const formData = new FormData();
    formData.append('toggle_favorite', '1');
    formData.append('user_id', userId);
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
            // Update the button without reloading the page
            const newIsFavorite = !isFavorite;
            const favoriteIcon = newIsFavorite ? '♥' : '♡';
            const favoriteText = newIsFavorite ? 'Remove' : 'Favorite';
            targetButton.classList.toggle('text-red-500', newIsFavorite);
            targetButton.classList.toggle('text-gray-500', !newIsFavorite);
            targetButton.innerHTML = `${favoriteIcon} ${favoriteText}`;
            targetButton.disabled = false;
            
            // Update the onclick attribute for the next click
            targetButton.setAttribute('onclick', `toggleFavorite(${matchId}, ${newIsFavorite}, ${userId})`);
            
            console.log('Favorite toggled successfully:', data);
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