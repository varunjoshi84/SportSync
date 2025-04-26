<?php
require_once __DIR__ . '/db.php';
function subscribeNewsletter($email) {
    try {
        $db = getDB();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT email FROM subscriptions WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return false;
        }
        
        // Insert new subscription
        $stmt = $db->prepare("INSERT INTO subscriptions (email) VALUES (?)");
        $result = $stmt->execute([$email]);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    } catch (PDOException $e) {
        return false;
    } catch (Exception $e) {
        return false;
    }
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
        return false;
    }
}

function updateNews($id, $title, $content, $category) {
    try {
        $db = getDB();
        $stmt = $db->prepare("UPDATE news SET title = :title, content = :content, category = :category WHERE id = :id");
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':content' => $content,
            ':category' => $category
        ];
        $result = $stmt->execute($params);
        return $result;
    } catch (Exception $e) {
        error_log("Error updating news: " . $e->getMessage());
        return false;
    }
}

function deleteNews($id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM news WHERE id = :id");
        $params = [':id' => $id];
        $stmt->execute($params);
        
        // Return true if execution was successful
        return true;
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
        return [];
    }
}
?>