<?php
require '../app/config.php'; // 包含数据库配置
session_start(); // 开始或继续使用 session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['userName'];
    $password = $_POST['passWord'];

    // 打印调试信息
    error_log("Received username: $username");
    error_log("Received password: $password");

    // 准备 SQL 语句
    $stmt = $conn->prepare("SELECT id, password, user_type FROM admin WHERE username = ?");
    if ($stmt === false) {
        error_log("SQL语句准备失败: " . $conn->error);
        echo "error: SQL statement preparation failed";
        exit;
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // 检查是否找到了用户
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $user_type);
        $stmt->fetch();
        
        // 打印从数据库中检索到的哈希密码和用户类型
        error_log("Database hashed password: $hashed_password");
        error_log("Database user type: $user_type");

        // 使用 password_verify() 函数检查密码
        if (password_verify($password, $hashed_password)) {
            // 设置 session 变量
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = $user_type; // 存储用户类型

            // 存储用户权限
            if ($user_type == 'sub_admin') {
                $region_stmt = $conn->prepare("SELECT region_id FROM admin_region WHERE admin_id = ?");
                if ($region_stmt) {
                    $region_stmt->bind_param("i", $user_id);
                    $region_stmt->execute();
                    $region_result = $region_stmt->get_result();
                    
                    $region_ids = [];
                    while ($row = $region_result->fetch_assoc()) {
                        $region_ids[] = $row['region_id'];
                    }
                    
                    $_SESSION['region_ids'] = $region_ids;
                    $region_stmt->close();
                }
            }

            echo "success";
        } else {
            echo "error: password incorrect";
        }
    } else {
        echo "error: no such user";
    }

    $stmt->close();
    $conn->close();
}
?>
