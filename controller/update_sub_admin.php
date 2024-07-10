<?php
require '../app/config.php'; // 包含数据库配置
header('Content-Type: application/json');

$id = $_POST['id'];
$username = $_POST['username'];
$password = isset($_POST['password']) ? $_POST['password'] : null;

$response = ["success" => false, "message" => "未知错误"];

if (empty($id) || empty($username)) {
    $response['message'] = "缺少必要的参数";
    echo json_encode($response);
    exit;
}

// 检查用户名是否已存在（排除当前用户自己）
$checkSql = "SELECT * FROM admin WHERE username = ? AND id != ?";
$stmt = $conn->prepare($checkSql);
if ($stmt === false) {
    $response['message'] = "SQL语句准备失败: " . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("si", $username, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response['message'] = "用户名已存在";
    echo json_encode($response);
    exit;
}
$stmt->close();

// 如果提供了密码，则直接使用明文密码
if (!empty($password)) {
    $hashedPassword = $password;
} else {
    // 如果未提供密码，获取当前密码
    $fetchPasswordSql = "SELECT password FROM admin WHERE id = ?";
    $fetchStmt = $conn->prepare($fetchPasswordSql);
    if ($fetchStmt === false) {
        $response['message'] = "SQL语句准备失败: " . $conn->error;
        echo json_encode($response);
        exit;
    }
    $fetchStmt->bind_param("i", $id);
    $fetchStmt->execute();
    $passwordResult = $fetchStmt->get_result();
    if ($passwordResult->num_rows > 0) {
        $passwordRow = $passwordResult->fetch_assoc();
        $hashedPassword = $passwordRow['password'];
    } else {
        $response['message'] = "用户不存在";
        echo json_encode($response);
        exit;
    }
    $fetchStmt->close();
}

// 更新用户信息
$sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $response['message'] = "SQL语句准备失败: " . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("sssi", $username, $hashedPassword, $balance, $id);
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = "用户信息更新成功";
} else {
    $response['message'] = "更新失败: " . $stmt->error;
}
$stmt->close();
$conn->close();

echo json_encode($response);
?>
