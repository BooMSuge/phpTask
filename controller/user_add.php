<?php
require '../app/config.php'; // 包含数据库配置

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // 检查密码是否匹配
    if ($password !== $confirmPassword) {
        echo "两次密码不一样，请重试";
        exit;
    }

    // 检查用户名是否已存在
    $checkUserSql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($checkUserSql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "用户名已存在";
        exit;
    }
    $stmt->close();

    // 密码散列
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 插入新用户数据
    $sql = "INSERT INTO admin (username, password, user_type) VALUES (?, ?, 'sub_admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashedPassword);
    if ($stmt->execute()) {
        echo "账号添加成功";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
