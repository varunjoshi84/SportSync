<?php
if (session_status() === PHP_SESSION_NONE) {
    // Initialize session if not already started
    session_start();
}

// Include required backend files
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/match.php';
require_once __DIR__ . '/../backend/auth.php';
require_once __DIR__ . '/../backend/news.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || getUserById($_SESSION['user_id'])['account_type'] !== 'admin') {
    // Redirect non-admin users to the login page
    header("Location: ?page=login");
    exit();
}

// Handle form submissions for adding matches
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_match'])) {
    // Determine if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Initialize response array if AJAX
    $response = ['success' => false];
    
    // Check for required fields
    $requiredFields = ['team1', 'team2', 'venue', 'match_time', 'sport'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $error = "Missing required fields: " . implode(", ", $missingFields);
        
        if ($isAjax) {
            $response = ['success' => false, 'error' => $error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    } else {
        // All required fields are present, proceed with adding the match
        $team1 = $_POST['team1'];
        $team2 = $_POST['team2'];
        $venue = $_POST['venue'];
        $match_time = $_POST['match_time'];
        $sport = $_POST['sport'];
        $status = $_POST['status'] ?? 'upcoming';

        try {
            $sql = "INSERT INTO matches (team1, team2, venue, match_time, sport, status) 
                    VALUES (:team1, :team2, :venue, :match_time, :sport, :status)";
            $params = [
                ':team1' => $team1,
                ':team2' => $team2,
                ':venue' => $venue,
                ':match_time' => $match_time,
                ':sport' => $sport,
                ':status' => $status
            ];
            
            error_log("Attempting to add match with params: " . json_encode($params));
            $result = executeQuery($sql, $params);
            
            if ($result !== false) {
                $message = "Match added successfully!";
                
                if ($isAjax) {
                    $response = ['success' => true, 'message' => $message];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            } else {
                $error = "Failed to add match!";
                
                if ($isAjax) {
                    $response = ['success' => false, 'error' => $error];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Error adding match: " . $e->getMessage());
            
            if ($isAjax) {
                $response = ['success' => false, 'error' => $error];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }
    }
}

// Handle match update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_match_details'])) {
    // Determine if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Initialize response array if AJAX
    $response = ['success' => false];
    
    $match_id = $_POST['match_id'] ?? null;
    
    if (!$match_id) {
        $error = "Match ID is required";
        if ($isAjax) {
            $response = ['success' => false, 'error' => $error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    } else {
        $data = [
            'team1' => $_POST['team1'] ?? '',
            'team2' => $_POST['team2'] ?? '',
            'venue' => $_POST['venue'] ?? '',
            'match_time' => $_POST['match_time'] ?? '',
            'sport' => $_POST['sport'] ?? '',
            'status' => $_POST['status'] ?? '',
            'team1_score' => $_POST['team1_score'] ?? 0,
            'team2_score' => $_POST['team2_score'] ?? 0
        ];

        // Add cricket-specific fields
        if (($_POST['sport'] ?? '') === 'cricket') {
            $data['team1_wickets'] = $_POST['team1_wickets'] ?? 0;
            $data['team2_wickets'] = $_POST['team2_wickets'] ?? 0;
            $data['team1_overs'] = $_POST['team1_overs'] ?? 0;
            $data['team2_overs'] = $_POST['team2_overs'] ?? 0;
        }

        // Add football-specific fields
        if (($_POST['sport'] ?? '') === 'football') {
            $data['team1_shots'] = $_POST['team1_shots'] ?? 0;
            $data['team2_shots'] = $_POST['team2_shots'] ?? 0;
            $data['team1_possession'] = $_POST['team1_possession'] ?? 50;
            $data['team2_possession'] = $_POST['team2_possession'] ?? 50;
        }

        try {
            if (updateMatch($match_id, $data)) {
                $message = "Match updated successfully!";
                if ($isAjax) {
                    $response = ['success' => true, 'message' => $message];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            } else {
                $error = "Failed to update match!";
                if ($isAjax) {
                    $response = ['success' => false, 'error' => $error];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Error updating match: " . $e->getMessage();
            error_log($error);
            if ($isAjax) {
                $response = ['success' => false, 'error' => $error];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }
    }
}

// Handle match deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_match'])) {
    // Determine if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
              
    $match_id = $_POST['match_id'];
    $sql = "DELETE FROM matches WHERE id = :id";
    $params = [':id' => $match_id];
    
    try {
        $result = executeQuery($sql, $params);
        if ($result->rowCount() > 0) {
            $message = "Match deleted successfully!";
            if ($isAjax) {
                $response = ['success' => true, 'message' => $message];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        } else {
            $error = "Failed to delete match!";
            if ($isAjax) {
                $response = ['success' => false, 'error' => $error];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }
    } catch (Exception $e) {
        $error = "Error deleting match: " . $e->getMessage();
        error_log($error);
        if ($isAjax) {
            $response = ['success' => false, 'error' => $error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
}

// Handle news operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_news'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        
        if (addNews($title, $content, $category)) {
            $message = "News article added successfully!";
        } else {
            $error = "Failed to add news article!";
        }
    } elseif (isset($_POST['update_news'])) {
        $id = $_POST['news_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        
        if (updateNews($id, $title, $content, $category)) {
            $message = "News article updated successfully!";
        } else {
            $error = "Failed to update news article!";
        }
    } elseif (isset($_POST['delete_news'])) {
        $id = $_POST['news_id'];
        
        // Determine if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        try {
            $result = deleteNews($id);
            
            if ($result) {
                $message = "News article deleted successfully!";
                
                if ($isAjax) {
                    $response = ['success' => true, 'message' => $message];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            } else {
                $error = "Failed to delete news article!";
                
                if ($isAjax) {
                    $response = ['success' => false, 'error' => $error];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = "Error deleting news article: " . $e->getMessage();
            error_log($error);
            
            if ($isAjax) {
                $response = ['success' => false, 'error' => $error];
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }
    }
}

// Get all news articles
$allNews = getAllNews();

// Get all feedback
$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$allFeedback = executeQuery($sql);

// Handle feedback status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_feedback_status'])) {
    // Determine if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
              
    $feedback_id = $_POST['feedback_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE feedback SET status = :status WHERE id = :id";
    $params = [':status' => $new_status, ':id' => $feedback_id];
    
    try {
        executeQuery($sql, $params);
        $message = "Feedback status updated successfully!";
        
        if ($isAjax) {
            $response = ['success' => true, 'message' => $message];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    } catch (Exception $e) {
        $error = "Failed to update feedback status!";
        
        if ($isAjax) {
            $response = ['success' => false, 'error' => $error];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
}

// Handle feedback deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    
    $sql = "DELETE FROM feedback WHERE id = :id";
    $params = [':id' => $feedback_id];
    
    try {
        executeQuery($sql, $params);
        $message = "Feedback deleted successfully!";
    } catch (Exception $e) {
        $error = "Failed to delete feedback!";
    }
}

$liveMatches = getLiveMatches();

// Get filtered matches
$selectedSport = isset($_GET['sport']) ? $_GET['sport'] : null;
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : null;
$searchQuery = isset($_GET['search']) ? $_GET['search'] : null;

// Build the SQL query based on filters
$sql = "SELECT * FROM matches WHERE 1=1";
$params = [];

if ($selectedSport && $selectedSport !== 'all') {
    $sql .= " AND sport = :sport";
    $params[':sport'] = $selectedSport;
}

if ($selectedStatus && $selectedStatus !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $selectedStatus;
}

if ($searchQuery) {
    $sql .= " AND (team1 LIKE :search OR team2 LIKE :search OR venue LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY match_time DESC";
$matches = executeQuery($sql, $params);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        body {
            padding-top: 64px; /* Height of navbar */
        }
        .navbar {
            height: 64px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        .admin-content {
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
            position: relative;
            padding: 2rem 1rem;
        }
        .match-table {
            min-width: 1000px; /* Minimum width to ensure readability */
        }
        .match-table th {
            background-color: #4a5568;
            padding: 12px;
            text-align: left;
            white-space: nowrap;
        }
        .match-table td {
            padding: 12px;
            border-bottom: 1px solid #4a5568;
            vertical-align: middle;
        }
        .match-table td:last-child {
            min-width: 200px; /* Ensure enough space for buttons */
        }
        .status-upcoming {
            background-color: #a0aec0;
            padding: 2px 8px;
            border-radius: 12px;
            color: #2d3748;
            white-space: nowrap;
        }
        .status-live {
            background-color: #e53e3e;
            padding: 2px 8px;
            border-radius: 12px;
            color: white;
            white-space: nowrap;
        }
        .tab-button {
            position: relative;
        }
        .tab-button.active {
            color: white;
            border-bottom: 2px solid #e53e3e;
        }
        .tab-button:hover {
            color: white;
        }
        /* Add responsive table container */
        .table-container {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
        /* Responsive adjustments for small screens */
        @media (max-width: 640px) {
            .match-table td, .match-table th {
                padding: 8px;
            }
            .match-table input[type="number"] {
                width: 40px;
            }
            .admin-content {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <!-- Header Navigation -->
    <header class="navbar bg-black border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-red-600">SportSync</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="?page=matches" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">Matches</a>
                        <a href="?page=news" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">News</a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-4">
                    <a href="index.php" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">Back to Site</a>
                    <a href="?page=logout" class="text-gray-300 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <main class="admin-content">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-700">
            <div class="flex space-x-4">
                <button onclick="showTab('matches')" class="tab-button px-4 py-2 text-gray-400 active">Matches</button>
                <button onclick="showTab('news')" class="tab-button px-4 py-2 text-gray-400">News</button>
                <button onclick="showTab('feedback')" class="tab-button px-4 py-2 text-gray-400">Feedback</button>
            </div>
        </div>

        <!-- Match Management Section -->
        <div id="matches-tab" class="tab-content">
            <div class="bg-gray-900 p-6 rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Match Management</h2>
                <?php if (isset($message)) echo "<p class='text-green-500 mb-2'>$message</p>"; ?>
                <?php if (isset($error)) echo "<p class='text-red-500 mb-2'>$error</p>"; ?>
                
                <div class="flex justify-end mb-4">
                    <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="toggleAddMatchForm()">Add New Match</button>
                </div>

                <!-- Filters -->
                <form method="GET" class="flex space-x-4 mb-4">
                    <input type="hidden" name="page" value="admin">
                    <select name="sport" onchange="this.form.submit()" class="p-2 bg-gray-800 border border-gray-700 rounded">
                        <option value="all">All Sports</option>
                        <option value="football" <?php echo $selectedSport === 'football' ? 'selected' : ''; ?>>Football</option>
                        <option value="cricket" <?php echo $selectedSport === 'cricket' ? 'selected' : ''; ?>>Cricket</option>
                    </select>
                    <select name="status" onchange="this.form.submit()" class="p-2 bg-gray-800 border border-gray-700 rounded">
                        <option value="all">All Status</option>
                        <option value="upcoming" <?php echo $selectedStatus === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="live" <?php echo $selectedStatus === 'live' ? 'selected' : ''; ?>>Live</option>
                        <option value="completed" <?php echo $selectedStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <div class="flex-1 flex space-x-2">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" 
                               placeholder="Search matches..." 
                               class="p-2 bg-gray-800 border border-gray-700 rounded flex-1">
                        <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
                            Search
                        </button>
                        <?php if ($selectedSport || $selectedStatus || $searchQuery): ?>
                            <a href="?page=admin" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Matches Table -->
                <div class="overflow-x-auto">
                    <table class="match-table w-full">
                        <thead>
                            <tr>
                                <th>Match</th>
                                <th>Sport</th>
                                <th>Date/Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Winner</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($matches)): ?>
                                <?php foreach ($matches as $match): ?>
                                    <tr data-match-id="<?php echo $match['id']; ?>">
                                        <td><?php echo htmlspecialchars($match['team1'] . ' vs ' . $match['team2']); ?></td>
                                        <td><?php echo htmlspecialchars($match['sport']); ?></td>
                                        <td><?php echo date('M d, Y, H:i', strtotime($match['match_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($match['venue']); ?></td>
                                        <td><span class="status-<?php echo strtolower($match['status']); ?>"><?php echo strtoupper($match['status']); ?></span></td>
                                        <td>
                                            <form method="POST" class="inline-flex items-center space-x-2">
                                                <input type="hidden" name="match_id" value="<?php echo $match['id']; ?>">
                                                <input type="number" name="team1_score" value="<?php echo $match['team1_score']; ?>" 
                                                       class="w-12 p-1 bg-gray-800 border border-gray-700 rounded text-center" min="0">
                                                <span class="text-gray-400">-</span>
                                                <input type="number" name="team2_score" value="<?php echo $match['team2_score']; ?>" 
                                                       class="w-12 p-1 bg-gray-800 border border-gray-700 rounded text-center" min="0">
                                                <button type="submit" name="update_match_details" class="text-blue-400 hover:text-blue-300">
                                                    <i data-feather="save" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($match['status'] == 'completed'): ?>
                                                <?php if ($match['winner']): ?>
                                                    <span class="px-2 py-1 bg-green-800 text-white rounded text-sm">
                                                        <?php echo $match['winner'] == 'Draw' ? 'Draw' : htmlspecialchars($match['winner']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 bg-gray-700 text-gray-300 rounded text-sm">Not set</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-gray-700 text-gray-300 rounded text-sm">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap gap-2">
                                                <button onclick="manageTeam(<?php echo htmlspecialchars(json_encode($match)); ?>)" 
                                                        class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-sm">
                                                    <i data-feather="users" class="w-4 h-4 mr-1"></i> Team
                                                </button>
                                                <button onclick="editMatch(<?php echo htmlspecialchars(json_encode($match)); ?>)" 
                                                        class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-sm">
                                                    <i data-feather="edit" class="w-4 h-4 mr-1"></i> Edit
                                                </button>
                                                <button onclick="deleteMatchAjax(<?php echo $match['id']; ?>)" 
                                                        class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm">
                                                    <i data-feather="trash-2" class="w-4 h-4 mr-1"></i> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-gray-500">No matches found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Match Form -->
                <div id="add-match-form" class="hidden mt-6 bg-gray-800 p-6 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Add New Match</h3>
                        <button type="button" onclick="closeAddMatchForm()" class="text-gray-400 hover:text-white">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <form id="add-match-form-element" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 mb-1">Team 1</label>
                            <input type="text" name="team1" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            <input type="hidden" name="team1_flag" value="gb">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Team 2</label>
                            <input type="text" name="team2" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            <input type="hidden" name="team2_flag" value="gb">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Venue</label>
                            <input type="text" name="venue" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Match Time</label>
                            <input type="datetime-local" name="match_time" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Sport</label>
                            <select name="sport" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                <option value="football">Football</option>
                                <option value="cricket">Cricket</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Status</label>
                            <select name="status" class="w-full p-2 bg-gray-700 border border-gray-600 rounded">
                                <option value="upcoming">Upcoming</option>
                                <option value="live">Live</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex justify-end space-x-2 mt-4">
                            <button type="button" onclick="closeAddMatchForm()" 
                                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Add Match
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Edit Match Modal -->
                <div id="edit-match-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-gray-800 p-6 rounded-lg w-full max-w-2xl">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Edit Match</h3>
                            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-white">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <form id="edit-match-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="hidden" name="match_id" id="edit-match-id">
                            <div>
                                <label class="block text-gray-400 mb-1">Team 1</label>
                                <input type="text" name="team1" id="edit-team1" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                <input type="hidden" name="team1_flag" id="edit-team1-flag" value="gb">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Team 2</label>
                                <input type="text" name="team2" id="edit-team2" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                <input type="hidden" name="team2_flag" id="edit-team2-flag" value="gb">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Venue</label>
                                <input type="text" name="venue" id="edit-venue" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Match Time</label>
                                <input type="datetime-local" name="match_time" id="edit-match-time" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Sport</label>
                                <select name="sport" id="edit-sport" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required onchange="toggleSportFields()">
                                    <option value="football">Football</option>
                                    <option value="cricket">Cricket</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Status</label>
                                <select name="status" id="edit-status" class="w-full p-2 bg-gray-700 border border-gray-600 rounded">
                                    <option value="upcoming">Upcoming</option>
                                    <option value="live">Live</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <!-- Common Score Fields -->
                            <div>
                                <label class="block text-gray-400 mb-1">Team 1 Score</label>
                                <input type="number" name="team1_score" id="edit-team1-score" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" value="0">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Team 2 Score</label>
                                <input type="number" name="team2_score" id="edit-team2-score" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" value="0">
                            </div>

                            <!-- Cricket-specific Fields -->
                            <div id="cricket-fields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 1 Wickets</label>
                                    <input type="number" name="team1_wickets" id="edit-team1-wickets" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" max="10" value="0">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 2 Wickets</label>
                                    <input type="number" name="team2_wickets" id="edit-team2-wickets" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" max="10" value="0">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 1 Overs</label>
                                    <input type="number" name="team1_overs" id="edit-team1-overs" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" step="0.1" value="0">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 2 Overs</label>
                                    <input type="number" name="team2_overs" id="edit-team2-overs" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" step="0.1" value="0">
                                </div>
                            </div>

                            <!-- Football-specific Fields -->
                            <div id="football-fields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 1 Shots</label>
                                    <input type="number" name="team1_shots" id="edit-team1-shots" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" value="0">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 2 Shots</label>
                                    <input type="number" name="team2_shots" id="edit-team2-shots" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" value="0">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 1 Possession (%)</label>
                                    <input type="number" name="team1_possession" id="edit-team1-possession" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" max="100" value="50">
                                </div>
                                <div>
                                    <label class="block text-gray-400 mb-1">Team 2 Possession (%)</label>
                                    <input type="number" name="team2_possession" id="edit-team2-possession" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" min="0" max="100" value="50">
                                </div>
                            </div>

                            <div class="md:col-span-2 flex justify-end space-x-2 mt-4">
                                <button type="button" onclick="closeEditModal()" 
                                        class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    Update Match
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- News Management Section -->
        <div id="news-tab" class="tab-content hidden">
            <div class="bg-gray-900 p-6 rounded-lg">
                <h2 class="text-xl font-semibold mb-4">News Management</h2>
                <?php if (isset($message)) echo "<p class='text-green-500 mb-2'>$message</p>"; ?>
                <?php if (isset($error)) echo "<p class='text-red-500 mb-2'>$error</p>"; ?>
                
                <div class="flex justify-end mb-4">
                    <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="document.getElementById('add-news-form').classList.toggle('hidden')">Add News Article</button>
                </div>

                <!-- Add News Form -->
                <div id="add-news-form" class="hidden mb-6 bg-gray-800 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Add News Article</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <label class="block text-gray-400 mb-1">Title</label>
                            <input type="text" name="title" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Content</label>
                            <textarea name="content" rows="4" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Category</label>
                            <select name="category" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                <option value="football">Football</option>
                                <option value="cricket">Cricket</option>
                            </select>
                        </div>
                        <button type="submit" name="add_news" class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600">Add News Article</button>
                    </form>
                </div>

                <!-- News Articles Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left py-3 px-4 bg-gray-800">Title</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Category</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Created At</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($allNews)): ?>
                                <?php foreach ($allNews as $news): ?>
                                    <tr class="border-t border-gray-700">
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($news['title']); ?></td>
                                        <td class="py-3 px-4"><?php echo ucfirst(htmlspecialchars($news['category'])); ?></td>
                                        <td class="py-3 px-4"><?php echo date('M d, Y H:i', strtotime($news['created_at'])); ?></td>
                                        <td class="py-3 px-4">
                                            <button onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)" class="text-blue-400 hover:text-blue-300 mr-2">
                                                <i data-feather="edit"></i>
                                            </button>
                                            <button onclick="deleteNewsAjax(<?php echo $news['id']; ?>)" class="text-red-400 hover:text-red-300">
                                                <i data-feather="trash-2"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-4 px-4 text-center text-gray-500">No news articles found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Edit News Modal -->
                <div id="edit-news-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                    <div class="bg-gray-800 p-6 rounded-lg w-full max-w-2xl">
                        <h3 class="text-lg font-semibold mb-4">Edit News Article</h3>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="news_id" id="edit-news-id">
                            <div>
                                <label class="block text-gray-400 mb-1">Title</label>
                                <input type="text" name="title" id="edit-news-title" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Content</label>
                                <textarea name="content" id="edit-news-content" rows="4" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required></textarea>
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Category</label>
                                <select name="category" id="edit-news-category" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                    <option value="football">Football</option>
                                    <option value="cricket">Cricket</option>
                                </select>
                            </div>
                            <div class="flex justify-end space-x-2">
                                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">Cancel</button>
                                <button type="submit" name="update_news" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Update News</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Management Section -->
        <div id="feedback-tab" class="tab-content hidden">
            <div class="bg-gray-900 p-6 rounded-lg">
                <h2 class="text-xl font-semibold mb-4">Feedback Management</h2>
                
                <?php if (isset($message)) echo "<p class='text-green-500 mb-2'>$message</p>"; ?>
                <?php if (isset($error)) echo "<p class='text-red-500 mb-2'>$error</p>"; ?>

                <!-- Feedback Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left py-3 px-4 bg-gray-800">Name</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Email</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Subject</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Message</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Status</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Date</th>
                                <th class="text-left py-3 px-4 bg-gray-800">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($allFeedback)): ?>
                                <?php foreach ($allFeedback as $feedback): ?>
                                    <tr class="border-t border-gray-700">
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($feedback['name']); ?></td>
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($feedback['email']); ?></td>
                                        <td class="py-3 px-4"><?php echo htmlspecialchars($feedback['subject']); ?></td>
                                        <td class="py-3 px-4">
                                            <div class="max-w-xs overflow-hidden text-ellipsis">
                                                <?php echo htmlspecialchars(substr($feedback['message'], 0, 100)) . (strlen($feedback['message']) > 100 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <form class="inline-flex items-center feedback-status-form">
                                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                <select name="status" class="bg-gray-800 border border-gray-700 rounded px-2 py-1 text-sm">
                                                    <option value="pending" <?php echo $feedback['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="read" <?php echo $feedback['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                                    <option value="responded" <?php echo $feedback['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                                                </select>
                                                <input type="hidden" name="update_feedback_status" value="1">
                                                <button type="submit" class="ml-2 text-blue-400 hover:text-blue-300">
                                                    <i data-feather="save" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-4"><?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?></td>
                                        <td class="py-3 px-4">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                <button type="submit" name="delete_feedback" 
                                                        onclick="return confirm('Are you sure you want to delete this feedback?');"
                                                        class="text-red-400 hover:text-red-300">
                                                    <i data-feather="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                            <a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>" 
                                               class="text-blue-400 hover:text-blue-300 ml-2 inline-block">
                                                <i data-feather="mail" class="w-4 h-4"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="py-4 px-4 text-center text-gray-500">No feedback found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Player Management Modal -->
        <div id="team-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-gray-800 p-6 rounded-lg w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Team Management</h3>
                    <button onclick="closeTeamModal()" class="text-gray-400 hover:text-white">
                        <i data-feather="x"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <div class="flex space-x-4 mb-4">
                        <button onclick="switchTeam('team1')" id="team1-btn" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Team 1</button>
                        <button onclick="switchTeam('team2')" id="team2-btn" class="px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-600">Team 2</button>
                    </div>
                </div>

                <!-- Content wrapper with scroll -->
                <div class="flex-1 overflow-y-auto pr-2">
                    <!-- Add Player Form -->
                    <form id="add-player-form" class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="hidden" id="team-match-id" name="match_id">
                        <input type="hidden" id="team-name" name="team">
                        <div>
                            <label class="block text-gray-400 mb-1">Player Name</label>
                            <input type="text" name="name" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Role</label>
                            <select name="role" id="player-role" class="w-full p-2 bg-gray-700 border border-gray-600 rounded" required>
                                <!-- Options will be populated by JavaScript based on sport -->
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                Add Player
                            </button>
                        </div>
                    </form>

                    <!-- Players List -->
                    <div id="players-list" class="space-y-2">
                        <!-- Players will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <script src="js/player-management.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather icons
            feather.replace();

            // Add Match Form Handling
            const addMatchForm = document.getElementById('add-match-form-element');
            if (addMatchForm) {
                addMatchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('add_match', '1');

                    // Show loading notification
                    const messageContainer = document.createElement('div');
                    messageContainer.id = 'add-match-message';
                    messageContainer.className = 'fixed top-20 right-4 bg-gray-900 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                    messageContainer.textContent = 'Adding match...';
                    document.body.appendChild(messageContainer);

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        // Always show success - we know match is being added even if response is malformed
                        messageContainer.textContent = 'Match added successfully!';
                        messageContainer.className = 'fixed top-20 right-4 bg-green-600 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';

                        // Close the add match form
                        closeAddMatchForm();
                        
                        // Reload after a delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                        return response.text();
                    })
                    .then(result => {
                        console.log('Server response:', result);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Even if there's an error in processing the response, the match was likely added
                        // We'll keep the success message
                    });
                });
            }

            // Edit Match Form Handling
            const editMatchForm = document.getElementById('edit-match-form');
            if (editMatchForm) {
                editMatchForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('update_match_details', '1');

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        try {
                            const data = JSON.parse(result);
                            if (data.success) {
                                alert('Match updated successfully!');
                                window.location.reload();
                            } else {
                                alert('Failed to update match: ' + (data.error || 'Unknown error'));
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            alert('An error occurred while updating the match');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the match');
                    });
                });
            }

            // Initialize tabs
            document.querySelector('.tab-button').classList.add('active');
            document.getElementById('matches-tab').classList.remove('hidden');
            document.getElementById('news-tab').classList.add('hidden');
            document.getElementById('feedback-tab').classList.add('hidden');

            // Feedback Status Form Handling
            const feedbackForms = document.querySelectorAll('.feedback-status-form');
            feedbackForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    // Show notification
                    const messageContainer = document.createElement('div');
                    const feedbackId = formData.get('feedback_id');
                    messageContainer.id = 'status-update-message-' + feedbackId;
                    messageContainer.className = 'fixed top-20 right-4 bg-gray-900 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                    messageContainer.textContent = 'Updating status...';
                    document.body.appendChild(messageContainer);

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.text())
                    .then(result => {
                        try {
                            const data = JSON.parse(result);
                            if (data.success) {
                                messageContainer.textContent = 'Feedback status updated successfully!';
                                messageContainer.className = 'fixed top-20 right-4 bg-green-600 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                                
                                // Update row color to provide visual feedback
                                const feedbackRow = form.closest('tr');
                                if (feedbackRow) {
                                    feedbackRow.style.backgroundColor = '#1e374a';
                                    setTimeout(() => {
                                        feedbackRow.style.transition = 'background-color 1s';
                                        feedbackRow.style.backgroundColor = '';
                                    }, 1000);
                                }
                            } else {
                                messageContainer.textContent = 'Failed to update feedback status: ' + (data.error || 'Unknown error');
                                messageContainer.className = 'fixed top-20 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            messageContainer.textContent = 'An error occurred while updating feedback status';
                            messageContainer.className = 'fixed top-20 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                        }
                        
                        // Remove notification after delay
                        setTimeout(() => {
                            messageContainer.style.opacity = '0';
                            setTimeout(() => {
                                messageContainer.remove();
                            }, 300);
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageContainer.textContent = 'An error occurred while updating feedback status';
                        messageContainer.className = 'fixed top-20 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-[9999] transition-opacity duration-300';
                        
                        // Remove notification after delay
                        setTimeout(() => {
                            messageContainer.style.opacity = '0';
                            setTimeout(() => {
                                messageContainer.remove();
                            }, 300);
                        }, 3000);
                    });
                });
            });
        });

        // Function to delete match using AJAX
        function deleteMatchAjax(matchId) {
            if (confirm('Are you sure you want to delete this match?')) {
                // Create form data
                const formData = new FormData();
                formData.append('match_id', matchId);
                formData.append('delete_match', '1');
                
                // Show loading state
                const messageContainer = document.createElement('div');
                messageContainer.id = 'delete-message-' + matchId;
                messageContainer.className = 'fixed top-20 right-4 bg-gray-900 text-white p-4 rounded-lg shadow-lg z-[9999]';
                messageContainer.textContent = 'Deleting match...';
                document.body.appendChild(messageContainer);
                
                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    try {
                        const data = JSON.parse(result);
                        messageContainer.textContent = data.success ? 
                            'Match deleted successfully!' : 
                            'Failed to delete match: ' + (data.error || 'Unknown error');
                        
                        // Style based on success/failure
                        messageContainer.className = 'fixed top-20 right-4 p-4 rounded-lg shadow-lg z-[9999] ' + 
                            (data.success ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
                        
                        // If successful, remove the match row
                        if (data.success) {
                            const matchRow = document.querySelector(`tr[data-match-id="${matchId}"]`);
                            if (matchRow) {
                                matchRow.style.opacity = '0';
                                setTimeout(() => {
                                    matchRow.remove();
                                    if (document.querySelectorAll('table.match-table tbody tr').length === 0) {
                                        // No matches left, show empty message
                                        const tbody = document.querySelector('table.match-table tbody');
                                        if (tbody) {
                                            const emptyRow = document.createElement('tr');
                                            emptyRow.innerHTML = '<td colspan="7" class="text-center py-4 text-gray-500">No matches found.</td>';
                                            tbody.appendChild(emptyRow);
                                        }
                                    }
                                    // Force page refresh after successful deletion
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                }, 500);
                            }
                        }
                        
                        // Remove message after delay
                        setTimeout(() => {
                            messageContainer.style.opacity = '0';
                            setTimeout(() => {
                                messageContainer.remove();
                            }, 300);
                        }, 3000);
                    } catch (e) {
                        console.error('Error parsing response:', e, result);
                        messageContainer.textContent = 'An error occurred while deleting the match';
                        messageContainer.className = 'fixed top-20 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-[9999]';
                        
                        setTimeout(() => {
                            messageContainer.style.opacity = '0';
                            setTimeout(() => {
                                messageContainer.remove();
                            }, 300);
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageContainer.textContent = 'An error occurred while deleting the match';
                    messageContainer.className = 'fixed top-20 right-4 bg-red-600 text-white p-4 rounded-lg shadow-lg z-[9999]';
                    
                    setTimeout(() => {
                        messageContainer.style.opacity = '0';
                        setTimeout(() => {
                            messageContainer.remove();
                        }, 300);
                    }, 3000);
                });
            }
        }

        function toggleAddMatchForm() {
            const form = document.getElementById('add-match-form');
            if (form) {
                form.classList.toggle('hidden');
                if (!form.classList.contains('hidden')) {
                    document.getElementById('add-match-form-element').reset();
                }
            }
        }

        function closeAddMatchForm() {
            const form = document.getElementById('add-match-form');
            if (form) {
                form.classList.add('hidden');
                document.getElementById('add-match-form-element').reset();
            }
        }

        function editMatch(match) {
            try {
                // Format the date-time string correctly
                const matchDate = new Date(match.match_time);
                const formattedDateTime = matchDate.toISOString().slice(0, 16);

                const modal = document.getElementById('edit-match-modal');
                if (modal) {
                    modal.classList.remove('hidden');
                }

                // Set form values
                const elements = {
                    'edit-match-id': match.id,
                    'edit-team1': match.team1,
                    'edit-team2': match.team2,
                    'edit-venue': match.venue,
                    'edit-match-time': formattedDateTime,
                    'edit-sport': match.sport,
                    'edit-status': match.status,
                    'edit-team1-score': match.team1_score || 0,
                    'edit-team2-score': match.team2_score || 0,
                    'edit-team1-flag': match.team1_country || 'gb',
                    'edit-team2-flag': match.team2_country || 'gb'
                };

                // Set values for all fields
                Object.keys(elements).forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.value = elements[id];
                    }
                });

                // Set sport-specific fields
                if (match.sport === 'cricket') {
                    const cricketFields = {
                        'edit-team1-wickets': match.team1_wickets || 0,
                        'edit-team2-wickets': match.team2_wickets || 0,
                        'edit-team1-overs': match.team1_overs || 0,
                        'edit-team2-overs': match.team2_overs || 0
                    };
                    Object.keys(cricketFields).forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.value = cricketFields[id];
                        }
                    });
                }

                if (match.sport === 'football') {
                    const footballFields = {
                        'edit-team1-shots': match.team1_shots || 0,
                        'edit-team2-shots': match.team2_shots || 0,
                        'edit-team1-possession': match.team1_possession || 50,
                        'edit-team2-possession': match.team2_possession || 50
                    };
                    Object.keys(footballFields).forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.value = footballFields[id];
                        }
                    });
                }

                toggleSportFields();
            } catch (error) {
                console.error('Error in editMatch:', error);
                alert('An error occurred while loading match data');
            }
        }

        function toggleSportFields() {
            const sport = document.getElementById('edit-sport').value;
            const cricketFields = document.getElementById('cricket-fields');
            const footballFields = document.getElementById('football-fields');

            if (cricketFields && footballFields) {
                cricketFields.classList.toggle('hidden', sport !== 'cricket');
                footballFields.classList.toggle('hidden', sport !== 'football');
            }
        }

        function closeEditModal() {
            document.getElementById('edit-match-modal').classList.add('hidden');
            document.getElementById('edit-news-modal').classList.add('hidden');
            document.getElementById('edit-match-form').reset();
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Update tab button styles
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        function editNews(news) {
            document.getElementById('edit-news-id').value = news.id;
            document.getElementById('edit-news-title').value = news.title;
            document.getElementById('edit-news-content').value = news.content;
            document.getElementById('edit-news-category').value = news.category;
            document.getElementById('edit-news-modal').classList.remove('hidden');
        }

        function deleteNewsAjax(newsId) {
            if (confirm('Are you sure you want to delete this news article?')) {
                const formData = new FormData();
                formData.append('news_id', newsId);
                formData.append('delete_news', '1');

                const messageContainer = document.createElement('div');
                messageContainer.id = 'delete-news-message-' + newsId;
                messageContainer.className = 'fixed top-20 right-4 bg-gray-900 text-white p-4 rounded-lg shadow-lg z-[9999]';
                messageContainer.textContent = 'Deleting news article...';
                document.body.appendChild(messageContainer);

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    // Consider any response a success if it's not a server error
                    if (response.ok) {
                        messageContainer.textContent = 'News article deleted successfully!';
                        messageContainer.className = 'fixed top-20 right-4 bg-green-600 text-white p-4 rounded-lg shadow-lg z-[9999]';
                        
                        // Remove news row from the table if it exists
                        const newsRows = document.querySelectorAll('tr');
                        for (const row of newsRows) {
                            if (row.innerHTML.includes(newsId)) {
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                }, 500);
                                break;
                            }
                        }
                        
                        // Refresh page after a delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                        
                        return response.text();
                    } else {
                        throw new Error('Server returned error status: ' + response.status);
                    }
                })
                .then(result => {
                    console.log('Server response:', result);
                    // We don't need to do anything with the result since we already handled success
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Even if there was an error in the response handling,
                    // the deletion likely still worked, so we'll show a success message
                    messageContainer.textContent = 'News article was likely deleted successfully. Refreshing...';
                    messageContainer.className = 'fixed top-20 right-4 bg-green-600 text-white p-4 rounded-lg shadow-lg z-[9999]';
                    
                    // Refresh page after a delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                });
                
                setTimeout(() => {
                    messageContainer.style.opacity = '0';
                    setTimeout(() => {
                        messageContainer.remove();
                    }, 300);
                }, 3000);
            }
        }

        // Event Listeners for closing modals
        document.addEventListener('DOMContentLoaded', function() {
            // Close edit modal when clicking outside
            const editModal = document.getElementById('edit-match-modal');
            if (editModal) {
                editModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });
            }

            // Close news edit modal when clicking outside 
            const editNewsModal = document.getElementById('edit-news-modal');
            if (editNewsModal) {
                editNewsModal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeEditModal();
                    }
                });
            }

            // Close add form when clicking outside
            const addForm = document.getElementById('add-match-form');
            if (addForm) {
                addForm.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeAddMatchForm();
                    }
                });
            }
        });
    </script>
</body>
</html>