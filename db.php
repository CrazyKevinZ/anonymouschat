<?php
/**
 * 数据库操作类 - SQLite
 */

class Database {
    private $pdo;
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
        $this->init();
    }
    
    private function init() {
        $db_path = $this->config['db']['path'];
        $db_dir = dirname($db_path);
        
        // 创建数据目录
        if (!is_dir($db_dir)) {
            mkdir($db_dir, 0755, true);
        }
        
        // 连接数据库
        try {
            $this->pdo = new PDO('sqlite:' . $db_path);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        } catch (Exception $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    
    private function createTables() {
        // 检查表是否存在
        $tables = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
        $table_names = array_column($tables, 'name');
        
        // 用户表
        if (!in_array('users', $table_names)) {
            $this->pdo->exec("
                CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    gender TEXT,
                    register_ip TEXT,
                    last_login_ip TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    last_login_at DATETIME
                )
            ");
        }
        
        // 房间表
        if (!in_array('rooms', $table_names)) {
            $this->pdo->exec("
                CREATE TABLE rooms (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT UNIQUE NOT NULL,
                    owner_id INTEGER,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (owner_id) REFERENCES users(id)
                )
            ");
        }
        
        // 聊天记录表
        if (!in_array('messages', $table_names)) {
            $this->pdo->exec("
                CREATE TABLE messages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    room_id INTEGER,
                    user_id INTEGER,
                    content TEXT,
                    image_url TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (room_id) REFERENCES rooms(id),
                    FOREIGN KEY (user_id) REFERENCES users(id)
                )
            ");
        }
        
        // 黑名单表
        if (!in_array('blacklist', $table_names)) {
            $this->pdo->exec("
                CREATE TABLE blacklist (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    type TEXT,
                    content TEXT UNIQUE,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
        
        // IP黑名单表
        if (!in_array('ip_blacklist', $table_names)) {
            $this->pdo->exec("
                CREATE TABLE ip_blacklist (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    ip TEXT UNIQUE NOT NULL,
                    reason TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('数据库查询失败: ' . $e->getMessage());
        }
    }
    
    public function exec($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
}
