<?php
require '../app/config.php';  // 包含数据库配置文件

// 假设从某个表单获取新的用户名和密码
$newUsername = '橙子';
$newPassword = 'waca8899';  // 新密码

// 密码哈希处理，为了安全存储
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// 准备 SQL 更新语句
$sql = "UPDATE admin SET username=?, password=? WHERE username='waca8899'";  // 替换 'currentAdminUsername' 为当前的用户名

// 预处理 SQL 语句
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('SQL prepare failed: ' . $conn->error);
}

// 绑定参数
$stmt->bind_param("ss", $newUsername, $hashedPassword);

// 执行更新
if ($stmt->execute()) {
    echo "账号和密码更新成功!";
} else {
    echo "更新失败: " . $stmt->error;
}

// 关闭语句和连接
$stmt->close();
$conn->close();
?>
