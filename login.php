<?php
require 'app/config.php';
session_start();

header('Content-Type: application/json');

$response = array("success" => false, "message" => "未知错误");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['userName'];
    $password = $_POST['passWord'];  // 直接使用提交的明文密码

    // 准备 SQL 语句
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    if (!$stmt) {
        $response['message'] = "准备查询失败: " . $conn->error;
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // 检查是否找到了用户
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $stored_password);
        $stmt->fetch();
        // 直接比较密码
        if ($password === $stored_password) {
            // 设置 session 变量
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $username;
             $_SESSION['user_id'] = $user_id;
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '密码错误']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '用户名不存在']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => '请求方法错误']);
}
?>
