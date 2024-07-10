<?php
session_start();
require 'app/config.php';

header('Content-Type: application/json');

// 检查请求方法并处理相应逻辑
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['code'])) {
        // 处理验证字符串保存逻辑
        $code = $_POST['code'] ?? '';
        if (strlen($code) === 10) {
            $sql = "INSERT INTO verification_codes (code, user_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $code, $_SESSION['user_id']); // 假设用户ID存储在会话中
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'uid' => $_SESSION['user_id']]);
            } else {
                echo json_encode(['success' => false, 'message' => '无法保存验证代码']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => '无效的验证代码']);
        }
    } elseif (isset($_POST['phone']) && isset($_POST['uid'])) {
        // 处理申请提交逻辑
        $phone = $_POST['phone'];
        $uid = $_POST['uid'];
        // 这里添加实际的申请处理逻辑，如保存到数据库
        echo json_encode(['success' => true, 'message' => '申请已提交']);
    } else {
        echo json_encode(['success' => false, 'message' => '请求参数错误']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
}

$conn->close();
?>
