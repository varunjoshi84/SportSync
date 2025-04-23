# SportSync

SportSync is a comprehensive sports platform that provides live scores, match updates, and news for football and cricket matches. The platform allows users to track their favorite matches, receive notifications, and stay updated with the latest sports news.

## Project Overview

SportSync is a web application built with PHP and MySQL that offers the following features:

- **Live Scores**: Real-time updates for football and cricket matches
- **Match Filtering**: Filter matches by sport (football/cricket) and status (live/upcoming/all)
- **User Authentication**: Sign up, login, and profile management
- **Favorite Matches**: Save and track your favorite matches
- **Newsletter Subscription**: Subscribe to receive updates and news
- **Feedback System**: Submit feedback with email confirmation
- **Admin Dashboard**: Manage matches, players, and content

## Project Structure

The project has been organized with a clean directory structure:

```
sportsync/
├── backend/           # Backend PHP files
│   ├── api/           # API endpoints
│   ├── auth.php       # Authentication functions
│   ├── db.php         # Database connection and functions
│   ├── email.php      # Email functionality using PHPMailer
│   ├── feedback.php   # Feedback handling
│   ├── init_db.php    # Database initialization
│   ├── match.php      # Match-related functions
│   ├── newsletter.php # Newsletter subscription handling
│   ├── news.php       # News-related functions
│   ├── player.php     # Player-related functions
│   └── user.php       # User-related functions
├── public/            # Public-facing files
│   ├── api/           # Public API endpoints
│   ├── css/           # Stylesheets
│   ├── images/        # Images and assets
│   ├── js/            # JavaScript files
│   ├── admin.php      # Admin dashboard
│   ├── cricket.php    # Cricket matches page
│   ├── feedback.php   # Feedback form
│   ├── football.php   # Football matches page
│   ├── header.php     # Common header
│   ├── footer.php     # Common footer
│   ├── home.php       # Homepage
│   ├── index.php      # Main entry point
│   ├── live-scores.php # Live scores page
│   ├── login.php      # Login page
│   ├── signup.php     # Registration page
│   └── thank-you.php  # Thank you page
├── tests/             # Test files
├── vendor/            # Composer dependencies
├── .gitignore         # Git ignore file
├── composer.json      # Composer configuration
└── README.md          # Project documentation
```

## Recent Changes and Improvements

We've recently made several improvements to the project:

1. **User Account Management**:
   - Fixed password update functionality to properly hash and store updated passwords
   - Added password strength requirements (more than 6 characters)
   - Implemented secure account deletion with password verification
   - Added two-step confirmation process for account deletion

2. **Consolidated Feedback System**:
   - Merged duplicate feedback files into a single `feedback.php`
   - Improved error handling and logging
   - Added better input validation and sanitization

3. **Consolidated Newsletter System**:
   - Merged duplicate subscription files into a single `newsletter.php`
   - Enhanced error handling and logging
   - Improved email validation and duplicate checking

4. **Database Structure Improvements**:
   - Consolidated database initialization into a single `init_db.php` file
   - Added missing tables (subscriptions, news)
   - Improved table structure and relationships

5. **Directory Structure Cleanup**:
   - Removed empty template files
   - Organized test files into a dedicated directory
   - Improved overall project organization

6. **Email Functionality**:
   - Implemented PHPMailer for sending emails
   - Added thank-you emails for feedback submissions
   - Added welcome emails for newsletter subscriptions

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer for PHP dependencies

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/varunjoshi84/SportSync.git
   cd sportsync
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Configure your database**:
   - Create a new MySQL database
   - Update database credentials in `backend/config.php`:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database_name');
     define('DB_USER', 'your_database_user');
     define('DB_PASS', 'your_database_password');
     ```

4. **Initialize the database**:
   ```bash
   php backend/init_db.php
   ```

5. **Configure email settings**:
   - Update email configuration in `backend/email.php`:
     ```php
     $mail->isSMTP();
     $mail->Host = 'smtp.gmail.com';
     $mail->SMTPAuth = true;
     $mail->Username = 'your_email@gmail.com';
     $mail->Password = 'your_app_password';
     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
     $mail->Port = 587;
     ```
   - For Gmail, you'll need to create an App Password in your Google Account settings

6. **Set up your web server**:
   - Point your web server to the `public` directory
   - Ensure mod_rewrite is enabled for Apache
   - Configure your virtual host if needed

7. **Access the application**:
   - Open your browser and navigate to `http://localhost/sportsync`
   - You should see the SportSync homepage

## Usage

### For Users

- **View Live Scores**: Navigate to the Live Scores page to see all current matches
- **Filter Matches**: Use the filter buttons to view matches by sport or status
- **Create an Account**: Sign up to save favorite matches and receive notifications
- **Subscribe to Newsletter**: Enter your email in the footer to subscribe
- **Submit Feedback**: Use the feedback form to send your comments

### For Administrators

- **Access Admin Dashboard**: Navigate to `/admin.php` and log in with admin credentials
- **Manage Matches**: Add, edit, or delete matches
- **Manage Players**: Add or update player information
- **View Subscriptions**: See all newsletter subscribers
- **View Feedback**: Read and respond to user feedback

## Development

### Adding New Features

1. Create a new branch for your feature:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes and commit them:
   ```bash
   git add .
   git commit -m "Add your feature description"
   ```

3. Push your branch to GitHub:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Create a pull request on GitHub to merge your changes into the main branch

### Running Tests

The project includes test files in the `tests` directory. To run tests:

```bash
php tests/test.php
php tests/test_db.php
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

Varun Joshi - varunjoshi84@gmail.com
Project Link: https://github.com/varunjoshi84/SportSync