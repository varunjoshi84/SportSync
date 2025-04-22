<?php
require_once __DIR__ . '/../backend/match.php';
require_once __DIR__ . '/../backend/email.php';
?>

<footer class="bg-gray-900 text-white py-12">
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
                    <li>Phone: +1 234 567 890</li>
                    <li><a href="?page=feedback" class="hover:text-red-500">Send Feedback</a></li>
                </ul>
            </div>

            <!-- Newsletter Subscription -->
            <div class="col-span-1">
                <h3 class="text-xl font-bold mb-4">Subscribe to Newsletter</h3>
                <div id="newsletter-form">
                    <div class="flex flex-col space-y-4">
                        <input type="email" id="subscribe-email" placeholder="Enter your email" 
                               class="bg-gray-800 text-white px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button onclick="subscribeNewsletter()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition-colors">
                            Subscribe
                        </button>
                    </div>
                    <div id="newsletter-message" class="mt-2 text-sm"></div>
                </div>
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
<script>
function subscribeNewsletter() {
    const emailInput = document.getElementById('subscribe-email');
    const messageDiv = document.getElementById('newsletter-message');
    const subscribeButton = document.querySelector('#newsletter-form button');
    const email = emailInput.value.trim();

    // Reset message
    messageDiv.textContent = '';
    messageDiv.className = 'mt-2 text-sm';

    if (!email) {
        messageDiv.textContent = 'Please enter your email address.';
        messageDiv.classList.add('text-red-500');
        return;
    }

    // Show loading state
    subscribeButton.textContent = 'Subscribing...';
    subscribeButton.disabled = true;

    const formData = new FormData();
    formData.append('email', email);

    fetch('../backend/newsletter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.textContent = data.message;
            messageDiv.classList.add('text-green-500');
            emailInput.value = ''; // Clear input on success
        } else {
            messageDiv.textContent = data.message;
            messageDiv.classList.add('text-red-500');
        }
    })
    .catch(error => {
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.classList.add('text-red-500');
    })
    .finally(() => {
        // Reset button state
        subscribeButton.textContent = 'Subscribe';
        subscribeButton.disabled = false;
    });
}

// Add enter key support for the newsletter form
document.getElementById('subscribe-email').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        subscribeNewsletter();
    }
});
</script>
</body>
</html>