// Global variables to track current state
let currentMatchId = null;
let currentTeam = 'team1';
let currentSport = 'football';
let team1Players = [];
let team2Players = [];

// Function to open the team management modal
function manageTeam(match) {
    currentMatchId = match.id;
    currentSport = match.sport;
    document.getElementById('team-match-id').value = match.id;
    document.getElementById('team-name').value = 'team1';
    currentTeam = 'team1';
    
    // Set active team button
    document.getElementById('team1-btn').classList.add('bg-red-500', 'hover:bg-red-600');
    document.getElementById('team1-btn').classList.remove('bg-gray-700', 'hover:bg-gray-600');
    document.getElementById('team2-btn').classList.remove('bg-red-500', 'hover:bg-red-600');
    document.getElementById('team2-btn').classList.add('bg-gray-700', 'hover:bg-gray-600');
    
    // Populate role options based on sport
    populateRoleOptions();
    
    // Load players for the team
    loadPlayers('team1');
    
    // Show modal
    document.getElementById('team-modal').classList.remove('hidden');
}

// Function to switch between teams
function switchTeam(team) {
    currentTeam = team;
    document.getElementById('team-name').value = team;
    
    if (team === 'team1') {
        document.getElementById('team1-btn').classList.add('bg-red-500', 'hover:bg-red-600');
        document.getElementById('team1-btn').classList.remove('bg-gray-700', 'hover:bg-gray-600');
        document.getElementById('team2-btn').classList.remove('bg-red-500', 'hover:bg-red-600');
        document.getElementById('team2-btn').classList.add('bg-gray-700', 'hover:bg-gray-600');
    } else {
        document.getElementById('team2-btn').classList.add('bg-red-500', 'hover:bg-red-600');
        document.getElementById('team2-btn').classList.remove('bg-gray-700', 'hover:bg-gray-600');
        document.getElementById('team1-btn').classList.remove('bg-red-500', 'hover:bg-red-600');
        document.getElementById('team1-btn').classList.add('bg-gray-700', 'hover:bg-gray-600');
    }
    
    // Load players for the selected team
    loadPlayers(team);
}

// Function to close the team management modal
function closeTeamModal() {
    document.getElementById('team-modal').classList.add('hidden');
}

// Function to populate role options based on selected sport and existing team composition
function populateRoleOptions() {
    const roleSelect = document.getElementById('player-role');
    if (!roleSelect) return; // Safety check
    
    roleSelect.innerHTML = '';
    
    // Get current team's players
    const currentPlayers = currentTeam === 'team1' ? team1Players : team2Players;
    
    // Check if captain and wicket-keeper (for cricket) already exist
    const hasCaptain = currentPlayers.some(player => player.role === 'captain');
    const hasWicketKeeper = currentSport === 'cricket' && currentPlayers.some(player => player.role === 'wicket-keeper');
    
    let options = [];
    if (currentSport === 'cricket') {
        options = [
            { value: 'batsman', text: 'Batsman' },
            { value: 'bowler', text: 'Bowler' },
            { value: 'all-rounder', text: 'All-Rounder' }
        ];
        
        // Add wicket-keeper option only if none exists
        if (!hasWicketKeeper) {
            options.push({ value: 'wicket-keeper', text: 'Wicket Keeper' });
        }
    } else {
        // Football roles
        options = [
            { value: 'goalkeeper', text: 'Goalkeeper' },
            { value: 'defender', text: 'Defender' },
            { value: 'midfielder', text: 'Midfielder' },
            { value: 'forward', text: 'Forward' }
        ];
    }
    
    // Add captain option only if no captain exists
    if (!hasCaptain) {
        options.push({ value: 'captain', text: 'Captain' });
    }
    
    options.forEach(option => {
        const opt = document.createElement('option');
        opt.value = option.value;
        opt.textContent = option.text;
        roleSelect.appendChild(opt);
    });
}

// Function to load players for a specific team
function loadPlayers(team) {
    const matchId = currentMatchId;
    const playersList = document.getElementById('players-list');
    if (!playersList) return; // Safety check
    
    playersList.innerHTML = '<p class="text-gray-500">Loading players...</p>';
    
    // Fix the API path to point to the correct backend location
    fetch(`../backend/api/player_management.php?action=get_players&match_id=${matchId}&team=${team}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Store players data
                if (team === 'team1') {
                    team1Players = Array.isArray(data.players) ? data.players : [];
                } else {
                    team2Players = Array.isArray(data.players) ? data.players : [];
                }
                
                // Display players
                displayPlayers(team === 'team1' ? team1Players : team2Players);
                
                // Update role options based on current team composition
                populateRoleOptions();
                
                // Update player count display and check max players
                updatePlayerCountDisplay();
            } else {
                console.error('API returned error:', data.error || 'Unknown error');
                playersList.innerHTML = `<p class="text-red-500">Error loading players: ${data.error || 'Unknown error'}</p>`;
            }
        })
        .catch(error => {
            console.error('Error loading players:', error);
            playersList.innerHTML = `<p class="text-red-500">Error loading players. Please try again. (${error.message})</p>`;
            
            // Initialize empty arrays to prevent errors
            if (team === 'team1') {
                team1Players = [];
            } else {
                team2Players = [];
            }
            
            // Still update UI elements
            populateRoleOptions();
            updatePlayerCountDisplay();
        });
}

// Function to update player count display
function updatePlayerCountDisplay() {
    const currentPlayers = currentTeam === 'team1' ? team1Players : team2Players;
    const playerCount = Array.isArray(currentPlayers) ? currentPlayers.length : 0;
    
    // Create or update the player count element
    let countDisplay = document.getElementById('player-count-display');
    if (!countDisplay) {
        countDisplay = document.createElement('div');
        countDisplay.id = 'player-count-display';
        countDisplay.className = 'text-center mb-4';
        const form = document.getElementById('add-player-form');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(countDisplay, form);
        }
    }
    
    // Update the count display
    countDisplay.innerHTML = `<p class="text-gray-300">Players: <span class="${playerCount === 11 ? 'text-red-500 font-bold' : 'text-green-500'}">${playerCount} / 11</span></p>`;
    
    // Disable form if 11 players added
    const form = document.getElementById('add-player-form');
    if (!form) return; // Safety check
    
    const submitButton = form.querySelector('button[type="submit"]');
    if (!submitButton) return; // Safety check
    
    const nameInput = form.querySelector('input[name="name"]');
    const roleSelect = form.querySelector('select[name="role"]');
    
    if (playerCount >= 11) {
        submitButton.disabled = true;
        submitButton.classList.add('bg-gray-500', 'cursor-not-allowed');
        submitButton.classList.remove('bg-red-500', 'hover:bg-red-600');
        if (nameInput) nameInput.disabled = true;
        if (roleSelect) roleSelect.disabled = true;
        
        // Add message
        let maxMessage = document.getElementById('max-players-message');
        if (!maxMessage && form.parentNode) {
            maxMessage = document.createElement('div');
            maxMessage.id = 'max-players-message';
            maxMessage.className = 'text-red-500 text-center mb-4';
            maxMessage.textContent = 'Maximum team size (11) reached!';
            form.parentNode.insertBefore(maxMessage, form);
        }
    } else {
        if (submitButton.disabled) {
            submitButton.disabled = false;
            submitButton.classList.remove('bg-gray-500', 'cursor-not-allowed');
            submitButton.classList.add('bg-red-500', 'hover:bg-red-600');
            if (nameInput) nameInput.disabled = false;
            if (roleSelect) roleSelect.disabled = false;
            
            // Remove max message if exists
            const maxMessage = document.getElementById('max-players-message');
            if (maxMessage) maxMessage.remove();
        }
    }
}

// Function to display players
function displayPlayers(players) {
    const playersList = document.getElementById('players-list');
    if (!playersList) return; // Safety check
    
    playersList.innerHTML = '';
    
    if (!Array.isArray(players) || players.length === 0) {
        playersList.innerHTML = '<p class="text-gray-500">No players added yet.</p>';
        return;
    }
    
    players.forEach(player => {
        const playerCard = document.createElement('div');
        playerCard.className = 'bg-gray-700 p-3 rounded flex justify-between items-center mb-2';
        
        // Special styling for captain and wicket-keeper
        const specialClass = player.role === 'captain' ? 'text-yellow-400' : 
                           (player.role === 'wicket-keeper' ? 'text-blue-400' : '');
        
        const playerInfo = document.createElement('div');
        playerInfo.innerHTML = `
            <p class="font-semibold ${specialClass}">${player.name}${player.role === 'captain' ? ' (C)' : ''}</p>
            <p class="text-sm text-gray-400">${player.role}</p>
        `;
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'text-red-400 hover:text-red-300';
        deleteBtn.innerHTML = '<i data-feather="trash-2"></i>';
        deleteBtn.onclick = function() {
            deletePlayer(player.id);
        };
        
        playerCard.appendChild(playerInfo);
        playerCard.appendChild(deleteBtn);
        playersList.appendChild(playerCard);
    });
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Function to delete a player
function deletePlayer(playerId) {
    if (confirm('Are you sure you want to delete this player?')) {
        // Create form data for POST request
        const formData = new FormData();
        formData.append('action', 'delete_player');
        formData.append('player_id', playerId);
        
        // Use POST method instead of GET for delete operations
        fetch('../backend/api/player_management.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Reload the players list
                loadPlayers(currentTeam);
            } else {
                alert('Failed to delete player: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting player:', error);
            alert('An error occurred while deleting the player: ' + error.message);
        });
    }
}

// Set up the form submission for adding players
document.addEventListener('DOMContentLoaded', function() {
    const addPlayerForm = document.getElementById('add-player-form');
    if (addPlayerForm) {
        addPlayerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate against team composition rules
            const currentPlayers = currentTeam === 'team1' ? team1Players : team2Players;
            
            // Check if team already has 11 players
            if (Array.isArray(currentPlayers) && currentPlayers.length >= 11) {
                alert('Team already has the maximum of 11 players!');
                return;
            }
            
            const playerRole = document.getElementById('player-role');
            if (!playerRole) {
                alert('Error: Role field not found');
                return;
            }
            const selectedRole = playerRole.value;
            
            // Check captain rule
            const hasCaptain = Array.isArray(currentPlayers) && currentPlayers.some(player => player.role === 'captain');
            if (selectedRole === 'captain' && hasCaptain) {
                alert('Team already has a captain!');
                return;
            }
            
            // Check wicket-keeper rule for cricket
            if (currentSport === 'cricket' && selectedRole === 'wicket-keeper') {
                const hasWicketKeeper = Array.isArray(currentPlayers) && currentPlayers.some(player => player.role === 'wicket-keeper');
                if (hasWicketKeeper) {
                    alert('Team already has a wicket-keeper!');
                    return;
                }
            }
            
            const formData = new FormData(this);
            formData.append('action', 'add_player');
            
            // Show loading indicator
            const submitBtn = addPlayerForm.querySelector('button[type="submit"]');
            if (!submitBtn) return; // Safety check
            
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Adding...';
            submitBtn.disabled = true;
            
            // Fix the API path for adding players
            fetch('../backend/api/player_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Restore button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Clear form fields
                    addPlayerForm.reset();
                    
                    // Set hidden fields again
                    const teamMatchId = document.getElementById('team-match-id');
                    const teamName = document.getElementById('team-name');
                    if (teamMatchId) teamMatchId.value = currentMatchId;
                    if (teamName) teamName.value = currentTeam;
                    
                    // Reload players list
                    loadPlayers(currentTeam);
                } else {
                    alert('Failed to add player: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                // Restore button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                
                console.error('Error adding player:', error);
                alert('An error occurred while adding the player: ' + error.message);
            });
        });
    }
});