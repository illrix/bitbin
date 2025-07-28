<?php
require_once 'config.php';

class Database {
    private $pdo;
    
    public function __construct() {
        $this->connect();
        $this->initializeDatabase();
    }
    
    private function connect() {
        try {
            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    private function initializeDatabase() {
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        $this->pdo->exec($schema);
    }
    
    public function createPaste($pasteId, $title, $content, $language, $expiration) {
        $expirationDate = null;
        if ($expiration > 0) {
            $expirationDate = date('Y-m-d H:i:s', time() + $expiration);
        }
        
        $sql = "INSERT INTO pastes (paste_id, title, content, language, expiration) 
                VALUES (:paste_id, :title, :content, :language, :expiration)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':paste_id' => $pasteId,
            ':title' => $title,
            ':content' => $content,
            ':language' => $language,
            ':expiration' => $expirationDate
        ]);
    }
    
    public function getPaste($pasteId) {
        $sql = "SELECT * FROM pastes WHERE paste_id = :paste_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':paste_id' => $pasteId]);
        
        $paste = $stmt->fetch();
        
        if ($paste) {
            // Check if paste has expired
            if ($paste['expiration'] && strtotime($paste['expiration']) < time()) {
                $this->deletePaste($pasteId);
                return null;
            }
            
            // Increment view count
            $this->incrementViews($pasteId);
        }
        
        return $paste;
    }
    
    public function getRecentPastes($limit = 10) {
        $sql = "SELECT paste_id, title, language, created_at, views 
                FROM pastes 
                WHERE expiration IS NULL OR expiration > datetime('now')
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function incrementViews($pasteId) {
        $sql = "UPDATE pastes SET views = views + 1 WHERE paste_id = :paste_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':paste_id' => $pasteId]);
    }
    
    private function deletePaste($pasteId) {
        $sql = "DELETE FROM pastes WHERE paste_id = :paste_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':paste_id' => $pasteId]);
    }
    
    public function cleanupExpiredPastes() {
        $sql = "DELETE FROM pastes WHERE expiration IS NOT NULL AND expiration < datetime('now')";
        $this->pdo->exec($sql);
    }
    
    public function generateUniqueId($length = 8) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        do {
            $id = '';
            for ($i = 0; $i < $length; $i++) {
                $id .= $characters[rand(0, strlen($characters) - 1)];
            }
            
            // Check if ID already exists
            $sql = "SELECT COUNT(*) FROM pastes WHERE paste_id = :paste_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':paste_id' => $id]);
            $exists = $stmt->fetchColumn() > 0;
            
        } while ($exists);
        
        return $id;
    }
}
?>

