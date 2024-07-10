<?php
session_start();
require 'app/config.php';

header('Content-Type: application/json');

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '用户未登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 查询数据库获取用户详细信息
$sql = "SELECT username, balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '数据库查询错误']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'username' => $user['username'], 'balance' => $user['balance']]);
} else {
    echo json_encode(['success' => false, 'message' => '无法找到用户信息']);
}

$stmt->close();
$conn->close();
?>
