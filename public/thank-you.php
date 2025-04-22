<?php
require_once __DIR__ . '/../backend/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - SportSync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-black text-white min-h-screen">
    <?php include 'header.php'; ?>

    <main class="container mx-auto px-4 py-16">
        <div class="max-w-2xl mx-auto text-center">
            <div class="bg-gray-900 p-8 rounded-lg shadow-lg">
                <div class="mb-6">
                    <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold mb-4">Thank You!</h1>
                <p class="text-gray-300 text-lg mb-6">
                    We appreciate you taking the time to share your feedback with us. 
                    Your input helps us improve our services.
                </p>
                
                <div class="space-y-4">
                    <p class="text-gray-400">
                        Our team will review your feedback and take appropriate action if needed.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="?page=home" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                            Return Home
                        </a>
                        <a href="?page=live-scores" class="inline-block bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-150 ease-in-out">
                            View Live Scores
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html> 