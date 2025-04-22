<?php
require_once __DIR__ . '/db.php';

function subscribeNewsletter($email) {
    $db = getDB();
    $sql = "SELECT email FROM newsletter WHERE email = :email";
    $existing = executeQuery($sql, [':email' => $email]);
    if (count($existing) > 0) {
        return false;
    }
    $sql = "INSERT INTO newsletter (email) VALUES (:email)";
    executeQuery($sql, [':email' => $email]);
    return true;
}

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