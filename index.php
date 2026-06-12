<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>匿名聊天系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            width: 90%;
            max-width: 900px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .auth-form, .chat-container {
            padding: 40px;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .hidden {
            display: none !important;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            color: #666;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab.disabled {
            color: #ccc;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .chat-layout {
            display: flex;
            height: 600px;
            gap: 15px;
        }
        
        .room-list {
            width: 200px;
            border-right: 1px solid #eee;
            overflow-y: auto;
            padding-right: 15px;
        }
        
        .room-item {
            padding: 12px;
            margin-bottom: 8px;
            background: #f5f5f5;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .room-item:hover {
            background: #efefef;
        }
        
        .room-item.active {
            background: #667eea;
            color: white;
        }
        
        .messages-area {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 15px;
            padding: 15px;
            background: #fafafa;
            border-radius: 5px;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 12px;
            background: white;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .message-author {
            color: #667eea;
            font-weight: 600;
            font-size: 13px;
        }
        
        .message-time {
            color: #999;
            font-size: 12px;
        }
        
        .message-content {
            color: #333;
            line-height: 1.5;
            word-break: break-word;
        }
        
        .message-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 8px;
            border-radius: 5px;
        }
        
        .input-area {
            display: flex;
            gap: 10px;
        }
        
        .input-area input[type="text"] {
            flex: 1;
        }
        
        .input-area textarea {
            flex: 1;
            height: 50px;
            resize: vertical;
        }
        
        .btn-send {
            width: 80px;
            flex-shrink: 0;
        }
        
        .btn-image {
            width: 60px;
            background: #667eea;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        
        .btn-image input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .admin-panel {
            padding: 20px;
        }
        
        .admin-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .admin-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .user-row {
            padding: 12px;
            background: white;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 12px;
            border-left: 3px solid #ddd;
        }
        
        .user-row .username {
            font-weight: 600;
            color: #333;
        }
        
        .user-row .info {
            color: #666;
            margin-top: 5px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        
        .alert.show {
            display: block;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .user-info {
            text-align: center;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .user-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .logout-btn {
            background: #dc3545;
            width: 100px;
            margin: 10px auto;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌐 匿名聊天系统</h1>
            <p>超轻量化 PHP 聊天平台</p>
        </div>
        
        <!-- 认证页面 -->
        <div id="authPage">
            <div class="auth-form">
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('login')">登录</div>
                    <div class="tab" onclick="switchTab('register')">注册</div>
                </div>
                
                <!-- 登录表单 -->
                <div id="loginForm">
                    <div class="alert" id="loginAlert"></div>
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" id="loginUsername" placeholder="输入您的用户名">
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" id="loginPassword" placeholder="输入您的密码">
                    </div>
                    <button class="btn" onclick="login()">登录</button>
                </div>
                
                <!-- 注册表单 -->
                <div id="registerForm" class="hidden">
                    <div class="alert" id="registerAlert"></div>
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" id="registerUsername" placeholder="设置用户名">
                    </div>
                    <div class="form-group">
                        <label>密码</label>
                        <input type="password" id="registerPassword" placeholder="设置密码">
                    </div>
                    <div class="form-group">
                        <label>性别</label>
                        <select id="registerGender">
                            <option value="unknown">保密</option>
                            <option value="male">男</option>
                            <option value="female">女</option>
                        </select>
                    </div>
                    <button class="btn" onclick="register()">注册</button>
                </div>
            </div>
        </div>
        
        <!-- 聊天页面 -->
        <div id="chatPage" class="hidden">
            <div class="user-info">
                <p>欢迎，<strong id="displayName"></strong></p>
                <button class="logout-btn" onclick="logout()">登出</button>
            </div>
            
            <div class="tabs">
                <div class="tab active" onclick="switchChatTab('chat')">聊天</div>
                <div class="tab" id="adminTab" class="hidden" onclick="switchChatTab('admin')">管理员</div>
            </div>
            
            <!-- 聊天标签 -->
            <div id="chatTab" class="chat-container">
                <div class="chat-layout">
                    <div class="room-list" id="roomList"></div>
                    <div class="messages-area">
                        <div class="alert" id="chatAlert"></div>
                        <div class="messages" id="messagesContainer"></div>
                        <div class="input-area">
                            <textarea id="messageContent" placeholder="输入消息..."></textarea>
                            <button class="btn btn-send" onclick="sendMessage()">发送</button>
                            <label class="btn btn-image">
                                📷
                                <input type="file" id="imageInput" accept="image/*" onchange="sendImage()">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 管理员标签 -->
            <div id="adminPanel" class="hidden admin-panel">
                <div class="admin-section">
                    <h3>创建房间</h3>
                    <div class="form-group">
                        <label>房间名称</label>
                        <input type="text" id="newRoomName">
                    </div>
                    <div class="form-group">
                        <label>房间描述</label>
                        <textarea id="newRoomDesc"></textarea>
                    </div>
                    <div class="form-group">
                        <label>管理员密码</label>
                        <input type="password" id="createRoomPwd">
                    </div>
                    <button class="btn" onclick="createRoom()">创建房间</button>
                </div>
                
                <div class="admin-section">
                    <h3>用户管理</h3>
                    <div class="form-group">
                        <label>管理员密码</label>
                        <input type="password" id="usersPwd">
                    </div>
                    <button class="btn" onclick="loadUsers()">查看所有用户</button>
                    <div id="usersList" style="margin-top: 20px;"></div>
                </div>
                
                <div class="admin-section">
                    <h3>消息管理</h3>
                    <div class="form-group">
                        <label>管理员密码</label>
                        <input type="password" id="messagesPwd">
                    </div>
                    <button class="btn" onclick="loadAllMessages()">查看所有消息</button>
                    <div id="allMessagesList" style="margin-top: 20px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentRoom = null;
        let currentUser = null;
        const ADMIN_USER = 'fibulun';  // 管理员用户名
        
        // 格式化日期时间
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }
        
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            if (tab === 'login') {
                document.getElementById('loginForm').classList.remove('hidden');
                document.getElementById('registerForm').classList.add('hidden');
            } else {
                document.getElementById('loginForm').classList.add('hidden');
                document.getElementById('registerForm').classList.remove('hidden');
            }
        }
        
        function switchChatTab(tab) {
            document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
            if (tab === 'chat') {
                document.getElementById('chatTab').classList.remove('hidden');
                document.getElementById('adminPanel').classList.add('hidden');
            } else {
                document.getElementById('chatTab').classList.add('hidden');
                document.getElementById('adminPanel').classList.remove('hidden');
            }
            event.target.classList.add('active');
            loadRooms();
        }
        
        function showAlert(elementId, msg, type) {
            const alert = document.getElementById(elementId);
            alert.textContent = msg;
            alert.className = 'alert show ' + type;
            setTimeout(() => alert.classList.remove('show'), 4000);
        }
        
        function register() {
            const username = document.getElementById('registerUsername').value;
            const password = document.getElementById('registerPassword').value;
            const gender = document.getElementById('registerGender').value;
            
            if (!username || !password) {
                showAlert('registerAlert', '请输入用户名和密码', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            formData.append('gender', gender);
            
            fetch('api.php?action=register', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    showAlert('registerAlert', '注册成功，请登录', 'success');
                    document.getElementById('registerUsername').value = '';
                    document.getElementById('registerPassword').value = '';
                    setTimeout(() => {
                        document.querySelectorAll('.tab')[0].click();
                    }, 1000);
                } else {
                    showAlert('registerAlert', data.msg || '注册失败', 'error');
                }
            })
            .catch(e => showAlert('registerAlert', '网络错误', 'error'));
        }
        
        function login() {
            const username = document.getElementById('loginUsername').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!username || !password) {
                showAlert('loginAlert', '请输入用户名和密码', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);
            
            fetch('api.php?action=login', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    currentUser = data.data;
                    document.getElementById('displayName').textContent = data.data.display_name;
                    document.getElementById('authPage').classList.add('hidden');
                    document.getElementById('chatPage').classList.remove('hidden');
                    
                    // 检查是否为管理员用户
                    if (data.data.username === ADMIN_USER) {
                        document.getElementById('adminTab').classList.remove('hidden');
                    }
                    
                    loadRooms();
                } else {
                    showAlert('loginAlert', data.msg || '登录失败', 'error');
                }
            })
            .catch(e => showAlert('loginAlert', '网络错误', 'error'));
        }
        
        function logout() {
            currentUser = null;
            currentRoom = null;
            document.getElementById('authPage').classList.remove('hidden');
            document.getElementById('chatPage').classList.add('hidden');
            document.getElementById('adminTab').classList.add('hidden');
            document.getElementById('loginUsername').value = '';
            document.getElementById('loginPassword').value = '';
        }
        
        function loadRooms() {
            fetch('api.php?action=rooms')
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    const roomList = document.getElementById('roomList');
                    roomList.innerHTML = '';
                    data.data.forEach(room => {
                        const div = document.createElement('div');
                        div.className = 'room-item';
                        div.innerHTML = `<strong>${room.name}</strong><p style="font-size: 11px; color: #999; margin-top: 5px;">${room.description || ''}</p>`;
                        div.onclick = () => selectRoom(room.id, room.name, div);
                        roomList.appendChild(div);
                    });
                }
            });
        }
        
        function selectRoom(roomId, roomName, element) {
            currentRoom = { id: roomId, name: roomName };
            document.querySelectorAll('.room-item').forEach(r => r.classList.remove('active'));
            element.classList.add('active');
            loadMessages();
        }
        
        function loadMessages() {
            if (!currentRoom) return;
            
            fetch(`api.php?action=messages&room_id=${currentRoom.id}`)
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    const container = document.getElementById('messagesContainer');
                    container.innerHTML = '';
                    data.data.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = 'message';
                        const dateTime = formatDateTime(msg.created_at);
                        div.innerHTML = `
                            <div class="message-header">
                                <div class="message-author">${msg.username || '匿名'}</div>
                                <div class="message-time">${dateTime}</div>
                            </div>
                            <div class="message-content">${msg.content}</div>
                            ${msg.image_url ? `<img src="${msg.image_url}" class="message-image">` : ''}
                        `;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
        
        function sendMessage() {
            if (!currentRoom) {
                showAlert('chatAlert', '请先选择房间', 'error');
                return;
            }
            
            const content = document.getElementById('messageContent').value;
            if (!content.trim()) {
                showAlert('chatAlert', '消息不能为空', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('room_id', currentRoom.id);
            formData.append('content', content);
            
            fetch('api.php?action=send_message', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    document.getElementById('messageContent').value = '';
                    loadMessages();
                } else {
                    showAlert('chatAlert', data.msg || '发送失败', 'error');
                }
            })
            .catch(e => showAlert('chatAlert', '网络错误', 'error'));
        }
        
        function sendImage() {
            if (!currentRoom) {
                showAlert('chatAlert', '请先选择房间', 'error');
                return;
            }
            
            const file = document.getElementById('imageInput').files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('room_id', currentRoom.id);
            formData.append('content', '[图片]');
            formData.append('image', file);
            
            fetch('api.php?action=send_message', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    document.getElementById('imageInput').value = '';
                    loadMessages();
                } else {
                    showAlert('chatAlert', data.msg || '上传失败', 'error');
                }
            })
            .catch(e => showAlert('chatAlert', '网络错误', 'error'));
        }
        
        function createRoom() {
            const name = document.getElementById('newRoomName').value;
            const desc = document.getElementById('newRoomDesc').value;
            const pwd = document.getElementById('createRoomPwd').value;
            
            if (!name || !pwd) {
                alert('请填写房间名称和管理员密码');
                return;
            }
            
            const formData = new FormData();
            formData.append('name', name);
            formData.append('description', desc);
            formData.append('admin_password', pwd);
            
            fetch('api.php?action=create_room', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    alert('房间创建成功');
                    document.getElementById('newRoomName').value = '';
                    document.getElementById('newRoomDesc').value = '';
                    document.getElementById('createRoomPwd').value = '';
                    loadRooms();
                } else {
                    alert(data.msg || '创建失败');
                }
            });
        }
        
        function loadUsers() {
            const pwd = document.getElementById('usersPwd').value;
            if (!pwd) {
                alert('请输入管理员密码');
                return;
            }
            
            fetch(`api.php?action=users&admin_password=${pwd}`)
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    const list = document.getElementById('usersList');
                    list.innerHTML = '';
                    data.data.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'user-row';
                        div.innerHTML = `
                            <div class="username">${user.username}</div>
                            <div class="info">性别: ${user.gender || '保密'} | 注册IP: ${user.register_ip} | 最后登录: ${user.last_login_at || '未登录'}</div>
                        `;
                        list.appendChild(div);
                    });
                } else {
                    alert(data.msg || '获取失败');
                }
            });
        }
        
        function loadAllMessages() {
            const pwd = document.getElementById('messagesPwd').value;
            if (!pwd) {
                alert('请输入管理员密码');
                return;
            }
            
            fetch(`api.php?action=user_messages&admin_password=${pwd}`)
            .then(r => r.json())
            .then(data => {
                if (data.code === 200) {
                    const list = document.getElementById('allMessagesList');
                    list.innerHTML = '';
                    data.data.forEach(msg => {
                        const div = document.createElement('div');
                        div.className = 'user-row';
                        const dateTime = formatDateTime(msg.created_at);
                        div.innerHTML = `
                            <div class="username">${msg.username || '匿名'} @ ${msg.room_name || '未知房间'}</div>
                            <div class="info">${msg.content}</div>
                            <div style="font-size: 10px; color: #ccc; margin-top: 5px;">${dateTime}</div>
                        `;
                        list.appendChild(div);
                    });
                } else {
                    alert(data.msg || '获取失败');
                }
            });
        }
        
        // 定时刷新消息
        setInterval(() => {
            if (currentRoom) loadMessages();
        }, 2000);
    </script>
</body>
</html>
