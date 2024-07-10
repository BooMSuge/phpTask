<?php
require 'app/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'check_username':
            $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            echo json_encode(["status" => $stmt->num_rows > 0 ? "exists" : "available"]);
            $stmt->close();
            break;
        
        case 'register':
            $username = filter_var($_POST['registerUsername'], FILTER_SANITIZE_STRING);
            $password = $_POST['registerPassword']; // 使用明文密码（注意安全风险）

            // 先检查用户名是否存在
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "用户名已存在"]);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $password);
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "注册失败"]);
                }
            }
            $stmt->close();
            break;
    }

    $conn->close();
}
?>
