<?php
session_start();
require 'app/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['password'];
    $user_id = $_SESSION['user_id'];  // 确保 session 中已经设置了 user_id

    // 更新密码到数据库（不进行加密）
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $new_password, $user_id);
        if ($stmt->execute()) {
            // 清除所有的 session 变量
            $_SESSION = array();

            // 如果使用基于 cookie 的会话，删除会话 cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // 最终，销毁会话
            session_destroy();

            echo json_encode(['success' => true, 'redirect' => 'login.html']);
        } else {
            echo json_encode(['success' => false, 'message' => '更新失败']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => '数据库错误: ' . $conn->error]);
    }
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => '无效请求']);
}
?>
