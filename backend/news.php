
<?php
/**
 * News Management Functions
 * 
 * This file contains functions for managing news articles including:
 * - Newsletter subscription
 * - Retrieving news articles
 * - Adding, updating, and deleting news content
 */

require_once __DIR__ . '/db.php';

/**
 * Subscribes an email address to the newsletter
 * 
 * @param string $email User's email address to subscribe
 * @return boolean True if subscription successful, false otherwise
 */
function subscribeNewsletter($email) {
    try {
        $db = getDB();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT email FROM subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            error_log("Subscription failed: Email already exists - " . $email);
            return false;
        }
        
        // Insert new subscription
        $stmt = $db->prepare("INSERT INTO subscriptions (email) VALUES (?)");
        $result = $stmt->execute([$email]);
        
        if ($result) {
            error_log("Successfully subscribed email: " . $email);
            return true;
        } else {
            error_log("Failed to insert subscription for email: " . $email);
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in subscribeNewsletter: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("General error in subscribeNewsletter: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets the latest news articles, optionally filtered by sport category
 * 
 * @param string|null $sport Optional sport category to filter by
 * @return array List of news articles
 */
function getLatestNews($sport = null) {
    $db = getDB();
    
    $sql = "SELECT * FROM news";
    if ($sport) {
        $sql .= " WHERE category = :category";
    }
    $sql .= " ORDER BY created_at DESC LIMIT 5";
    
    try {
        $params = $sport ? [':category' => $sport] : [];
        return executeQuery($sql, $params);
    } catch (Exception $e) {
        error_log("Error fetching news: " . $e->getMessage());
        return [];
    }
}

/**
 * Adds a new news article
 * 
 * @param string $title News article title
 * @param string $content News article content
 * @param string $category Sport category
 * @return boolean True if addition successful, false otherwise
 */
function addNews($title, $content, $category) {
    $db = getDB();
    $sql = "INSERT INTO news (title, content, category) VALUES (:title, :content, :category)";
    try {
        $params = [
            ':title' => $title,
            ':content' => $content,
            ':category' => $category
        ];
        executeQuery($sql, $params);
        return true;
    } catch (Exception $e) {
        error_log("Error adding news: " . $e->getMessage());
        return false;
    }
}

/**
 * Updates an existing news article
 * 
 * @param int $id News article ID
 * @param string $title Updated title
 * @param string $content Updated content
 * @param string $category Updated category
 * @return boolean True if update successful, false otherwise
 */
function updateNews($id, $title, $content, $category) {
    $db = getDB();
    $sql = "UPDATE news SET title = :title, content = :content, category = :category WHERE id = :id";
    try {
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':category' => $category
        ];
        return executeQuery($sql, $params)->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error updating news: " . $e->getMessage());
        return false;
    }
}

/**
 * Deletes a news article
 * 
 * @param int $id ID of the news article to delete
 * @return boolean True if deletion successful, false otherwise
 */
function deleteNews($id) {
    $db = getDB();
    $sql = "DELETE FROM news WHERE id = :id";
    try {
        $params = [':id' => $id];
        return executeQuery($sql, $params)->rowCount() > 0;
    } catch (Exception $e) {
        error_log("Error deleting news: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets all news articles in chronological order
 * 
 * @return array List of all news articles
 */
function getAllNews() {
    $db = getDB();
    $sql = "SELECT * FROM news ORDER BY created_at DESC";
    try {
        return executeQuery($sql);
    } catch (Exception $e) {
        error_log("Error fetching all news: " . $e->getMessage());
        return [];
    }
}
?>