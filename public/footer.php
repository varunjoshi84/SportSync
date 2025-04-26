<?php
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $email = trim($_POST['email'] ?? '');
    $errors = [];

    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            
            // Check if subscriptions table exists
            $tables = $db->query("SHOW TABLES LIKE 'subscriptions'")->fetchAll(PDO::FETCH_COLUMN);
            if (empty($tables)) {
                // Create subscriptions table if it doesn't exist
                $db->exec("CREATE TABLE subscriptions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM subscriptions WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "This email is already subscribed";
            } else {
                // Insert new subscription
                $stmt = $db->prepare("INSERT INTO subscriptions (email) VALUES (?)");
                $result = $stmt->execute([$email]);
                
                if ($result) {
                    // Send welcome email
                    $emailSent = sendSubscriptionEmail($email);
                    
                    if ($emailSent) {
                        $_SESSION['success_message'] = "Thank you for subscribing! We've sent a confirmation email.";
                    } else {
                        $_SESSION['success_message'] = "Thank you for subscribing!";
                    }
                    
                    // Redirect to prevent form resubmission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $errors[] = "Failed to add your subscription. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "An error occurred while processing your subscription. Please try again later.";
        } catch (Exception $e) {
            $errors[] = "An unexpected error occurred. Please try again later.";
        }
    }
}
?>

<footer class="bg-black text-white py-12">
<div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400"></div>
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About Section -->
            <div class="col-span-1">
                <h3 class="text-xl font-bold mb-4">About SportSync</h3>
                <p class="text-gray-400">Your one-stop destination for live sports scores, updates, and news. Stay connected with your favorite sports and never miss a moment.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-span-1">
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="?page=home" class="text-gray-400 hover:text-red-500">Home</a></li>
                    <li><a href="?page=live-scores" class="text-gray-400 hover:text-red-500">Live Scores</a></li>
                    <li><a href="?page=football" class="text-gray-400 hover:text-red-500">Football</a></li>
                    <li><a href="?page=cricket" class="text-gray-400 hover:text-red-500">Cricket</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-span-1">
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <ul class="space-y-2 text-gray-400">
                    <li>Email: info@sportsync.com</li>
                    <li>Phone: +91 9631117684</li>
                    <li><a href="?page=feedback" class="hover:text-red-500">Feedback</a></li>
                </ul>
            </div>

            <!-- Newsletter Subscription -->
            <div class="col-span-1">
                <h3 class="text-xl font-bold mb-4">Subscribe to Newsletter</h3>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="space-y-4">
                    <div>
                        <input type="email" id="subscribe-email" name="email" placeholder="Enter your email" 
                               class="w-full bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500"
                               required>
                    </div>
                    <button type="submit" name="subscribe" 
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                        Subscribe
                    </button>
                </form>
                <?php if (!empty($errors)): ?>
                    <div class="mt-2 text-sm text-red-500">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="mt-2 text-sm text-green-500">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Copyright -->
        <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> SportSync. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</body>
</html>