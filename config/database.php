<?php
// config/database.php

// Database konfigürasyonu (dashboard-stats.php ile tam uyumlu)
$config = [
    'host' => 'localhost',
    'dbname' => 'u529933284_otel',
    'username' => 'u529933284_otel',
    'password' => 'Ab141930.',
    'charset' => 'utf8mb4',
    'port' => 3306
];

// Ortam ayarı
define('ENVIRONMENT', 'development'); // production, development, testing

// Hata raporlama
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Singleton Database sınıfı - BASİT ve ÇALIŞAN versiyon
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        global $config;
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']};port={$config['port']}";
            
            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            
        } catch(PDOException $e) {
            // JSON formatında hata döndür (API uyumlu)
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DB_CONNECTION_FAILED',
                    'message' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Database connection failed',
                    'config' => ENVIRONMENT === 'development' ? [
                        'host' => $config['host'],
                        'dbname' => $config['dbname'],
                        'username' => $config['username']
                    ] : null
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // STATIC HELPER METHODS
    public static function query($sql, $params = []) {
        try {
            $stmt = self::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw $e;
        }
    }
    
    public static function fetch($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }
    
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public static function fetchColumn($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    public static function execute($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
}

// BASİT HELPER FONKSİYONLARI
function getDB() {
    return Database::getInstance()->getConnection();
}

function db_query($sql, $params = []) {
    return Database::query($sql, $params);
}

function db_fetch($sql, $params = []) {
    return Database::fetch($sql, $params);
}

function db_fetch_all($sql, $params = []) {
    return Database::fetchAll($sql, $params);
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// DEBUG FONKSİYONU
function debug($data, $exit = false) {
    if (ENVIRONMENT === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($exit) exit();
    }
}

// SESSION BAŞLATMA (otomatik)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>