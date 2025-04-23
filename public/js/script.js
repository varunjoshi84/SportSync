document.addEventListener('DOMContentLoaded', () => {
    const matchCards = document.getElementById('match-cards');

    function updateScores() {
        // Dynamic path based on current location
        const baseUrl = window.location.pathname.includes('public') 
            ? window.location.pathname.split('public')[0] + 'backend/match.php'
            : '/backend/match.php';
        fetch(`${baseUrl}?action=fetch_live`, {
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                matchCards.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(match => {
                        const statusClass = match.status === 'live' ? 'bg-red-700' : 'bg-gray-700';
                        const card = document.createElement('div');
                        card.className = 'border border-gray-700 rounded-lg p-4 bg-black text-white';
                        card.innerHTML = `
                            <div class="flex justify-between items-center text-sm text-gray-400 mb-2">
                                <span>${match.sport} · ${new Date(match.match_time).toLocaleString()}</span>
                                <span class="${statusClass} px-2 py-0.5 rounded-full text-xs">${match.status.toUpperCase()}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <div class="flex items-center">
                                    <span>${match.team1}</span>
                                </div>
                                <span class="font-bold">${match.team1_score}</span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <div class="flex items-center">
                                    <span>${match.team2}</span>
                                </div>
                                <span>${match.team2_score ?? (match.status === 'upcoming' ? '-' : '0')}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>${match.location}</span>
                                <span>♡ Favorite</span>
                            </div>
                        `;
                        matchCards.appendChild(card);
                    });
                } else {
                    matchCards.innerHTML = '<div class="text-center text-gray-500 py-8 col-span-3">No live matches at the moment.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching live scores:', error);
                matchCards.innerHTML = '<div class="text-center text-red-500 py-8 col-span-3">Failed to load live scores. Please try again later.</div>';
            });
    }

    // Update scores every 10 seconds
    setInterval(updateScores, 10000);
    updateScores(); // Initial load
});