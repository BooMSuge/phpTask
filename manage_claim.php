<?php
session_start();
require 'app/config.php';

header('Content-Type: application/json');

// 检查是否已登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '用户未登录']);
    exit;
}

$user_id = $_SESSION['user_id']; // 从会话获取用户ID
$username = $_SESSION['username']; // 从会话获取用户名，确保在登录时设置了此会话变量

// 检查请求方法并处理相应逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cardCode'], $_POST['phoneNumber'], $_POST['region'])) {
        $cardCode = $_POST['cardCode'];
        $phone = $_POST['phoneNumber'];
        $region = $_POST['region'];

        // 检查卡密长度和手机号格式
        if (strlen($cardCode) !== 10 || !preg_match("/^1[3-9]\d{9}$/", $phone)) {
            echo json_encode(['success' => false, 'message' => '卡密或手机号格式不正确']);
            exit;
        }

        // 插入新的申请
        $sql = "INSERT INTO claims (user_id, username, verification_code, phone, region) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => '数据库预处理失败: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("issss", $user_id, $username, $cardCode, $phone, $region);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '申请已提交']);
        } else {
            echo json_encode(['success' => false, 'message' => '提交失败: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => '缺少必要的参数']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
}

$conn->close();
?>
