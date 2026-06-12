<?php
/**
 * 匿名聊天系统 API 接口
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

$db = new Database($config);
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$user_ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// 检查是否为管理员
function isAdmin($username) {
    return $username === 'fibulun';
}

// 获取IP对应的地区
function getLocationByIP($ip, $qqwry_path) {
    if (!file_exists($qqwry_path)) {
        return '未知';
    }
    
    $fp = fopen($qqwry_path, 'rb');
    $ip_long = ip2long($ip);
    
    fseek($fp, 0);
    $first = unpack('Noffset', fread($fp, 4))['offset'];
    $last = unpack('Noffset', fread($fp, 4))['offset'];
    
    fclose($fp);
    
    // 简化版本，仅返回示意地区
    $regions = [
        '北京', '上海', '广州', '深圳', '杭州', '南京', '武汉', '成都',
        '西安', '重庆', '天津', '苏州', '长沙', '青岛', '郑州', '沈阳'
    ];
    return $regions[crc32($ip) % count($regions)];
}

// 响应函数
function response($code, $msg = '', $data = null) {
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    exit;
}

// 用户注册
if ($action === 'register' && $method === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? 'unknown';
    
    if (!$username || !$password) {
        response(400, '用户名和密码不能为空');
    }
    
    if (in_array($username, $config['blacklist']['username'])) {
        response(400, '用户名已被禁用');
    }
    
    $exists = $db->fetch('SELECT id FROM users WHERE username = ?', [$username]);
    if ($exists) {
        response(400, '用户名已存在');
    }
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $db->exec(
        'INSERT INTO users (username, password, gender, register_ip) VALUES (?, ?, ?, ?)',
        [$username, $password_hash, $gender, $user_ip]
    );
    
    response(200, '注册成功');
}

// 用户登录
if ($action === 'login' && $method === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$username || !$password) {
        response(400, '用户名和密码不能为空');
    }
    
    $user = $db->fetch('SELECT * FROM users WHERE username = ?', [$username]);
    if (!$user || !password_verify($password, $user['password'])) {
        response(401, '用户名或密码错误');
    }
    
    $location = getLocationByIP($user_ip, $config['qqwry']['path']);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['location'] = $location;
    $_SESSION['display_name'] = $user['username'] . '-' . $location . '市网友';
    
    $db->exec(
        'UPDATE users SET last_login_ip = ?, last_login_at = CURRENT_TIMESTAMP WHERE id = ?',
        [$user_ip, $user['id']]
    );
    
    response(200, '登录成功', [
        'user_id' => $user['id'],
        'username' => $user['username'],
        'display_name' => $_SESSION['display_name']
    ]);
}

// 获取房间列表
if ($action === 'rooms' && $method === 'GET') {
    $rooms = $db->fetchAll('SELECT id, name, owner_id, description FROM rooms');
    response(200, '获取成功', $rooms);
}

// 创建房间（管理员）
if ($action === 'create_room' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $name = $_POST['name'] ?? '';
    
    if (!$name) {
        response(400, '房间名称不能为空');
    }
    
    $exists = $db->fetch('SELECT id FROM rooms WHERE name = ?', [$name]);
    if ($exists) {
        response(400, '房间已存在');
    }
    
    $db->exec(
        'INSERT INTO rooms (name, description) VALUES (?, ?)',
        [$name, $_POST['description'] ?? '']
    );
    
    response(200, '房间创建成功');
}

// 删除房间（管理员）
if ($action === 'delete_room' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $room_id = $_POST['room_id'] ?? '';
    
    if (!$room_id) {
        response(400, '房间ID不能为空');
    }
    
    $db->exec('DELETE FROM messages WHERE room_id = ?', [$room_id]);
    $db->exec('DELETE FROM rooms WHERE id = ?', [$room_id]);
    
    response(200, '房间删除成功');
}

// 发送消息
if ($action === 'send_message' && $method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        response(401, '未登录');
    }
    
    $room_id = $_POST['room_id'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if (!$room_id) {
        response(400, '房间ID不能为空');
    }
    
    // 检查黑名单词
    $blacklist_words = $db->fetchAll("SELECT content FROM blacklist WHERE type = 'word'");
    foreach ($blacklist_words as $item) {
        if (strpos($content, $item['content']) !== false) {
            response(400, '消息包含禁用内容');
        }
    }
    
    $image_url = null;
    if (isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $config['upload']['allowed_types'])) {
            response(400, '不支持的文件类型');
        }
        
        if ($file['size'] > $config['upload']['max_size']) {
            response(400, '文件过大');
        }
        
        if (!is_dir($config['upload']['dir'])) {
            mkdir($config['upload']['dir'], 0755, true);
        }
        
        $filename = uniqid() . '.' . $ext;
        $upload_path = $config['upload']['dir'] . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $image_url = 'uploads/' . $filename;
        }
    }
    
    $db->exec(
        'INSERT INTO messages (room_id, user_id, content, image_url) VALUES (?, ?, ?, ?)',
        [$room_id, $_SESSION['user_id'], $content, $image_url]
    );
    
    response(200, '消息发送成功');
}

// 获取消息历史
if ($action === 'messages' && $method === 'GET') {
    $room_id = $_GET['room_id'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    
    if (!$room_id) {
        response(400, '房间ID不能为空');
    }
    
    $messages = $db->fetchAll(
        'SELECT m.id, m.content, m.image_url, m.created_at, u.username, u.gender FROM messages m '
        . 'LEFT JOIN users u ON m.user_id = u.id '
        . 'WHERE m.room_id = ? ORDER BY m.created_at DESC LIMIT ?',
        [$room_id, $limit]
    );
    
    response(200, '获取成功', array_reverse($messages));
}

// 删除消息（管理员）
if ($action === 'delete_message' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $message_id = $_POST['message_id'] ?? '';
    
    $db->exec('DELETE FROM messages WHERE id = ?', [$message_id]);
    response(200, '消息删除成功');
}

// 清空房间消息（管理员）
if ($action === 'clear_room' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $room_id = $_POST['room_id'] ?? '';
    
    $db->exec('DELETE FROM messages WHERE room_id = ?', [$room_id]);
    response(200, '房间消息已清空');
}

// 获取用户信息（管理员）
if ($action === 'users' && $method === 'GET') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $users = $db->fetchAll(
        'SELECT id, username, gender, register_ip, last_login_ip, created_at, last_login_at FROM users'
    );
    
    response(200, '获取成功', $users);
}

// 获取所有用户的发言记录（管理员）
if ($action === 'user_messages' && $method === 'GET') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $user_id = $_GET['user_id'] ?? '';
    
    if ($user_id) {
        $messages = $db->fetchAll(
            'SELECT m.id, m.room_id, m.content, m.created_at, r.name as room_name '
            . 'FROM messages m LEFT JOIN rooms r ON m.room_id = r.id '
            . 'WHERE m.user_id = ? ORDER BY m.created_at DESC',
            [$user_id]
        );
    } else {
        $messages = $db->fetchAll(
            'SELECT m.id, m.room_id, m.user_id, m.content, m.created_at, u.username, r.name as room_name '
            . 'FROM messages m LEFT JOIN users u ON m.user_id = u.id '
            . 'LEFT JOIN rooms r ON m.room_id = r.id '
            . 'ORDER BY m.created_at DESC LIMIT 500'
        );
    }
    
    response(200, '获取成功', $messages);
}

// 获取黑名单词（管理员）
if ($action === 'get_blacklist_words' && $method === 'GET') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $words = $db->fetchAll("SELECT id, content FROM blacklist WHERE type = 'word'");
    response(200, '获取成功', $words);
}

// 添加黑名单词（管理员）
if ($action === 'add_blacklist_word' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $content = $_POST['content'] ?? '';
    
    if (!$content) {
        response(400, '内容不能为空');
    }
    
    try {
        $db->exec("INSERT INTO blacklist (type, content) VALUES (?, ?)", ['word', $content]);
        response(200, '添加成功');
    } catch (Exception $e) {
        response(400, '该词已存在');
    }
}

// 删除黑名单词（管理员）
if ($action === 'delete_blacklist_word' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $id = $_POST['id'] ?? '';
    
    $db->exec("DELETE FROM blacklist WHERE id = ? AND type = 'word'", [$id]);
    response(200, '删除成功');
}

// 获取IP黑名单（管理员）
if ($action === 'get_ip_blacklist' && $method === 'GET') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $ips = $db->fetchAll("SELECT id, ip, reason FROM ip_blacklist ORDER BY created_at DESC");
    response(200, '获取成功', $ips);
}

// 添加IP黑名单（管理员）
if ($action === 'add_ip_blacklist' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $ip = $_POST['ip'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (!$ip) {
        response(400, 'IP不能为空');
    }
    
    try {
        $db->exec("INSERT INTO ip_blacklist (ip, reason) VALUES (?, ?)", [$ip, $reason]);
        response(200, '添加成功');
    } catch (Exception $e) {
        response(400, '该IP已存在');
    }
}

// 删除IP黑名单（管理员）
if ($action === 'delete_ip_blacklist' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $id = $_POST['id'] ?? '';
    
    $db->exec("DELETE FROM ip_blacklist WHERE id = ?", [$id]);
    response(200, '删除成功');
}

// 编辑用户信息（管理员）
if ($action === 'update_user' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $user_id = $_POST['user_id'] ?? '';
    $gender = $_POST['gender'] ?? '';
    
    if (!$user_id) {
        response(400, '用户ID不能为空');
    }
    
    $db->exec("UPDATE users SET gender = ? WHERE id = ?", [$gender, $user_id]);
    response(200, '更新成功');
}

// 删除用户所有消息（管理员）
if ($action === 'delete_user_messages' && $method === 'POST') {
    if (!isset($_SESSION['username']) || !isAdmin($_SESSION['username'])) {
        response(403, '权限不足');
    }
    
    $user_id = $_POST['user_id'] ?? '';
    
    if (!$user_id) {
        response(400, '用户ID不能为空');
    }
    
    $db->exec("DELETE FROM messages WHERE user_id = ?", [$user_id]);
    response(200, '删除成功');
}

response(404, '接口不存在');
