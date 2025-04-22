<?php include __DIR__ . '/header.php';
require_once __DIR__ . '/../backend/news.php';

// Get initial news
$currentSport = isset($_GET['sport']) ? $_GET['sport'] : null;
$news = getLatestNews($currentSport);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportSync - Live Sports Updates</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .carousel-item {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .carousel-item.active {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-black min-h-screen flex flex-col">
    <div class="flex-grow">
        <!-- Hero Section -->
        <div class="flex flex-col items-center justify-center text-center mt-32 px-4">
            <h1 class="text-6xl font-bold text-white">
                Your Live <span class="text-red-500">Sports Hub</span>
            </h1>
            <p class="text-gray-400 mt-4 max-w-2xl text-xl">
                Real-time scores, match updates, and personalized notifications for all your favorite sports in one place.
            </p>
            <a href="?page=live-scores" class="mt-8 px-8 py-3 bg-red-500 text-white rounded-full hover:bg-red-600 transition">
                View Live Scores →
            </a>
        </div>

        <!-- Latest Sports News Section -->
        <div class="max-w-7xl mx-auto mt-16 px-4">
            <div class="bg-[#0A0A0A] rounded-2xl border border-gray-800 p-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-white">Latest Sports News</h2>
                    <div class="flex space-x-2">
                        <button onclick="filterNews('football')" class="px-4 py-2 bg-[#1A1A1A] text-white rounded-lg hover:bg-red-500 transition" id="football-btn">Football</button>
                        <button onclick="filterNews('cricket')" class="px-4 py-2 bg-[#1A1A1A] text-white rounded-lg hover:bg-red-500 transition" id="cricket-btn">Cricket</button>
                    </div>
                </div>

                <div id="news-carousel" class="min-h-[200px]">
                    <?php if (!empty($news)): ?>
                        <?php foreach ($news as $index => $item): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> text-white">
                                <div class="flex gap-6">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <p class="text-gray-400"><?php echo htmlspecialchars(substr($item['content'], 0, 200)) . '...'; ?></p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            <?php echo date('F j, Y', strtotime($item['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <p class="text-xl text-gray-500 mb-2">No sports news available at the moment</p>
                            <p class="text-gray-600">Please check back later</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Live Matches Section -->
        <div class="max-w-7xl mx-auto mt-16 px-4 mb-16">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white">Live Matches</h2>
                <div class="flex space-x-2">
                    <a href="?page=football" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-red-500">Football</a>
                    <a href="?page=cricket" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-red-500">Cricket</a>
                </div>
            </div>

            <!-- Match Cards -->
            <div class="grid md:grid-cols-3 gap-4" id="match-cards">
                <?php
                try {
                    $matches = getLiveMatches();
                    if (!empty($matches)) {
                        foreach ($matches as $match) {
                            $statusClass = $match['status'] === 'live' ? 'bg-red-700' : 'bg-gray-700';
                            ?>
                            <div class="border border-gray-700 rounded-lg p-4 bg-black text-white">
                                <div class="flex justify-between items-center text-sm text-gray-400 mb-2">
                                    <span><?php echo htmlspecialchars($match['sport']); ?> · <?php echo date('H:i', strtotime($match['match_time'])); ?></span>
                                    <span class="<?php echo $statusClass; ?> px-2 py-0.5 rounded-full text-xs">
                                        <?php echo strtoupper($match['status']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <img src="https://flagcdn.com/w40/<?php echo strtolower($match['team1_country'] ?? 'us'); ?>.svg" 
                                             alt="<?php echo htmlspecialchars($match['team1']); ?> flag" class="w-5 h-5">
                                        <span><?php echo htmlspecialchars($match['team1']); ?></span>
                                    </div>
                                    <span class="font-bold"><?php echo $match['team1_score'] ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <img src="https://flagcdn.com/w40/<?php echo strtolower($match['team2_country'] ?? 'us'); ?>.svg" 
                                             alt="<?php echo htmlspecialchars($match['team2']); ?> flag" class="w-5 h-5">
                                        <span><?php echo htmlspecialchars($match['team2']); ?></span>
                                    </div>
                                    <span><?php echo $match['team2_score'] ?? '0'; ?></span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-2">
                                    <span><?php echo htmlspecialchars($match['location'] ?? 'TBD'); ?></span>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <?php $is_favorite = isMatchFavorited($_SESSION['user_id'], $match['id']); ?>
                                        <button onclick="toggleFavorite(<?php echo $match['id']; ?>, <?php echo $is_favorite ? 'true' : 'false'; ?>)" 
                                                class="text-red-500 hover:text-red-400 cursor-pointer">
                                            <?php echo $is_favorite ? '♡ Remove' : '♡ Favorite'; ?>
                                        </button>
                                    <?php else: ?>
                                        <a href="?page=login" class="text-red-500 hover:text-red-400">♡ Login to Favorite</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="text-center text-gray-500 py-8 col-span-3">No live matches at the moment.</div>';
                    }
                } catch (Exception $e) {
                    error_log("Error fetching matches: " . $e->getMessage());
                    echo '<div class="text-center text-red-500 py-8 col-span-3">Unable to load matches. Please try again later.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/footer.php'; ?>

    <script>
        let currentSlide = 0;
        const items = document.querySelectorAll('.carousel-item');
        let carouselInterval;

        function showSlide(index) {
            items.forEach(item => item.classList.remove('active'));
            currentSlide = (index + items.length) % items.length;
            items[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function startCarousel() {
            if (items.length > 1) {
                carouselInterval = setInterval(nextSlide, 3000);
            }
        }

        function stopCarousel() {
            clearInterval(carouselInterval);
        }

        async function filterNews(sport) {
            const buttons = ['football-btn', 'cricket-btn'];
            buttons.forEach(btn => {
                document.getElementById(btn).classList.remove('bg-red-500');
                if (btn === `${sport}-btn`) {
                    document.getElementById(btn).classList.add('bg-red-500');
                }
            });

            try {
                const response = await fetch(`/api/news.php?sport=${sport}`);
                const data = await response.json();
                
                const carousel = document.getElementById('news-carousel');
                if (data.length === 0) {
                    carousel.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <p class="text-xl text-gray-500 mb-2">No ${sport} news available at the moment</p>
                            <p class="text-gray-600">Please check back later</p>
                        </div>
                    `;
                } else {
                    carousel.innerHTML = data.map((item, index) => `
                        <div class="carousel-item ${index === 0 ? 'active' : ''} text-white">
                            <div class="flex gap-6">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold mb-2">${item.title}</h3>
                                    <p class="text-gray-400">${item.content.substring(0, 200)}...</p>
                                    <p class="text-sm text-gray-500 mt-2">
                                        ${item.created_at ? new Date(item.created_at).toLocaleDateString() : ''}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
                
                // Reset carousel
                currentSlide = 0;
                stopCarousel();
                startCarousel();
            } catch (error) {
                console.error('Error fetching news:', error);
            }
        }

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

        // Start the carousel when the page loads
        startCarousel();
    </script>
</body>
</html> 