let currentMatch = null;
let currentTeam = 'team1';
let currentSport = '';

function manageTeam(match) {
    currentMatch = match;
    currentSport = match.sport;
    document.getElementById('team-modal').classList.remove('hidden');
    document.getElementById('team-match-id').value = match.id;
    switchTeam('team1'); // Default to team1
    updateRoleOptions();
    loadPlayers();
}

function closeTeamModal() {
    document.getElementById('team-modal').classList.add('hidden');
}

function switchTeam(team) {
    currentTeam = team;
    document.getElementById('team-name').value = currentMatch[team];
    document.getElementById('team1-tab').classList.toggle('border-red-500', team === 'team1');
    document.getElementById('team2-tab').classList.toggle('border-red-500', team === 'team2');
    loadPlayers();
}

function updateRoleOptions() {
    const roleSelect = document.getElementById('player-role');
    roleSelect.innerHTML = '';
    
    const roles = currentSport === 'cricket' 
        ? ['Batsman', 'Bowler', 'All-rounder', 'Wicket Keeper', 'Captain']
        : ['Goalkeeper', 'Defender', 'Midfielder', 'Forward', 'Captain'];
    
    roles.forEach(role => {
        const option = document.createElement('option');
        option.value = role.toLowerCase();
        option.textContent = role;
        roleSelect.appendChild(option);
    });
}

async function loadPlayers() {
    try {
        const response = await fetch(`/backend/api/player_management.php?match_id=${currentMatch.id}&team=${currentMatch[currentTeam]}`);
        const data = await response.json();
        
        if (data.success) {
            const playersList = document.getElementById('players-list');
            playersList.innerHTML = '';
            
            data.players.forEach(player => {
                const playerCard = document.createElement('div');
                playerCard.className = 'bg-gray-900 p-4 rounded-lg flex items-center justify-between';
                playerCard.innerHTML = `
                    <div>
                        <h4 class="font-semibold">${player.name}</h4>
                        <p class="text-gray-400">${player.role}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <input type="number" value="${player.score}" 
                               class="w-20 p-1 bg-gray-800 border border-gray-700 rounded text-center"
                               onchange="updateScore(${player.id}, this.value)">
                        <button onclick="deletePlayer(${player.id})" class="text-red-400 hover:text-red-300">
                            <i data-feather="trash-2"></i>
                        </button>
                    </div>
                `;
                playersList.appendChild(playerCard);
            });
            
            // Refresh Feather icons
            feather.replace();
        }
    } catch (error) {
        console.error('Error loading players:', error);
    }
}

async function addPlayer(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/backend/api/player_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_player',
                name: formData.get('player_name'),
                team: formData.get('team'),
                sport: currentSport,
                role: formData.get('role'),
                match_id: formData.get('match_id')
            })
        });
        
        const data = await response.json();
        if (data.success) {
            form.reset();
            loadPlayers();
        }
    } catch (error) {
        console.error('Error adding player:', error);
    }
}

async function updateScore(playerId, score) {
    try {
        const response = await fetch('/backend/api/player_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_score',
                player_id: playerId,
                score: score
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('Failed to update score');
        }
    } catch (error) {
        console.error('Error updating score:', error);
    }
}

async function deletePlayer(playerId) {
    if (!confirm('Are you sure you want to delete this player?')) {
        return;
    }
    
    try {
        const response = await fetch('/backend/api/player_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_player',
                player_id: playerId
            })
        });
        
        const data = await response.json();
        if (data.success) {
            loadPlayers();
        }
    } catch (error) {
        console.error('Error deleting player:', error);
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    const addPlayerForm = document.querySelector('#team-modal form');
    if (addPlayerForm) {
        addPlayerForm.addEventListener('submit', addPlayer);
    }
}); 