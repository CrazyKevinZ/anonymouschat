# 匿名聊天系统

一个超轻量级的PHP匿名聊天系统，仅需3-4个文件即可部署。

## 🎯 功能特性

### 用户系统
- ✅ 用户注册和登录
- ✅ 用户名和密码安全验证
- ✅ IP地址记录（注册IP和登录IP）
- ✅ 用户性别选项（男/女/保密）

### 聊天功能
- ✅ 多房间聊天
- ✅ 发送文本消息
- ✅ 发送图片（JPG/PNG/GIF）
- ✅ 实时消息加载
- ✅ 消息历史查询

### 页面显示
- ✅ 用户昵称格式：`《用户名-XX市网友》`
- ✅ XX市由用户IP通过qqwry.dat解析获得
- ✅ 消息包含发送者、时间戳等信息

### 管理员功能
- ✅ 创建/删除房间
- ✅ 删除单条消息
- ✅ 清空房间所有消息
- ✅ 查看所有用户信息
- ✅ 查看所有用户发言记录
- ✅ 管理黑名单字符

### 数据存储
- ✅ 本地SQLite数据库
- ✅ 用户信息、房间、聊天记录、黑名单分表存储
- ✅ 支持自动建表

## 📦 文件结构

```
anonymouschat/
├── index.php          # 前端界面（登录/注册/聊天/管理）
├── api.php            # 后端API接口
├── db.php             # SQLite数据库类
├── config.php         # 配置文件
├── qqwry.dat          # IP地区库（可选，从metowolf/qqwry.dat下载）
├── data/              # 自动创建，存放chat.db
├── uploads/           # 自动创建，存放上传的图片
└── README.md          # 本文件
```

## 🚀 快速开始

### 1. 下载并部署

```bash
git clone https://github.com/CrazyKevinZ/anonymouschat.git
cd anonymouschat
```

### 2. 创建必要目录

```bash
mkdir -p data uploads
chmod 755 data uploads
```

### 3. 下载qqwry.dat（可选）

从 [metowolf/qqwry.dat](https://github.com/metowolf/qqwry.dat/releases) 下载最新的qqwry.dat文件，放在项目根目录。

### 4. 访问

在浏览器中打开 `http://localhost/anonymouschat/index.php`

## 🔐 管理员功能

### 默认管理员密码

在 `config.php` 中设置：

```php
'admin_password' => 'admin123',
```

### 使用管理员功能

1. 登录到系统后，点击"管理员"标签
2. 输入正确的管理员密码
3. 可以进行创建房间、查看用户、管理消息等操作

## 📝 黑名单配置

在 `config.php` 中配置禁用的用户名和敏感词：

```php
'blacklist' => [
    'username' => ['admin', 'root', 'test'],
    'words' => ['敏感词1', '敏感词2'],
],
```

## 🎨 UI特性

- 现代化的渐变色设计
- 响应式布局，支持各种屏幕尺寸
- 实时消息刷新（每2秒）
- 流畅的动画和过渡效果

## 📊 API接口

| 功能 | 请求方式 | 接口 |
|---|---|---|
| 用户注册 | POST | `api.php?action=register` |
| 用户登录 | POST | `api.php?action=login` |
| 获取房间列表 | GET | `api.php?action=rooms` |
| 创建房间 | POST | `api.php?action=create_room` |
| 删除房间 | POST | `api.php?action=delete_room` |
| 发送消息 | POST | `api.php?action=send_message` |
| 获取消息 | GET | `api.php?action=messages` |
| 删除消息 | POST | `api.php?action=delete_message` |
| 清空房间消息 | POST | `api.php?action=clear_room` |
| 查看用户列表 | GET | `api.php?action=users` |
| 查看用户消息 | GET | `api.php?action=user_messages` |

## 🔧 配置说明

### config.php

```php
return [
    // 数据库配置
    'db' => [
        'path' => __DIR__ . '/data/chat.db',  // SQLite数据库路径
    ],
    
    // 上传配置
    'upload' => [
        'dir' => __DIR__ . '/uploads/',       // 上传目录
        'max_size' => 5 * 1024 * 1024,        // 最大文件大小（5MB）
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],  // 允许的文件类型
    ],
    
    // qqwry.dat IP定位库路径
    'qqwry' => [
        'path' => __DIR__ . '/qqwry.dat',     // qqwry.dat文件路径
    ],
    
    // 黑名单配置
    'blacklist' => [
        'username' => ['admin', 'root', 'test'],
        'words' => ['敏感词1', '敏感词2'],
    ],
    
    // 管理员密码
    'admin_password' => 'admin123',
];
```

## 📱 系统要求

- PHP 7.4+
- SQLite3扩展
- 可写的文件系统权限（data/、uploads/ 目录）

## 🔒 安全建议

1. **修改管理员密码** - 在生产环境中务必修改 `config.php` 中的 `admin_password`
2. **HTTPS支持** - 在生产环境中使用HTTPS
3. **黑名单管理** - 定期维护黑名单词汇
4. **日志审计** - 管理员面板可以查看所有用户活动
5. **IP限制** - 可以根据需要添加IP白名单功能

## 📄 许可证

MIT License

## 👨‍💻 开发者

CrazyKevinZ

## 🎁 贡献

欢迎提交Issue和Pull Request！
