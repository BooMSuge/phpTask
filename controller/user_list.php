<?php
require '../app/config.php'; // 包含数据库配置

// 获取传入的用户名参数
$username = isset($_GET['username']) ? $_GET['username'] : '';

// 构建SQL查询语句，根据是否有用户名参数动态生成SQL
if ($username) {
    $sql = "SELECT * FROM users WHERE username LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeUsername = "%$username%";
    $stmt->bind_param("s", $likeUsername);
} else {
    $sql = "SELECT * FROM users";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$users = array();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// 返回JSON格式的数据
echo json_encode($users);

// 关闭连接
$stmt->close();
$conn->close();
?>
