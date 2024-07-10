<?php
require '../app/config.php'; // 包含数据库配置

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取前端发送的数据
    $id = $_POST['id'] ?? '';  // 更安全的数据获取方法
    $verification_code = $_POST['verification_code'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $status = $_POST['status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $region = $_POST['region'] ?? '';

    // 准备SQL查询语句，确保字段匹配数据库中的列
    $sql = "UPDATE claims SET phone=?, status=?, notes=?, region=? WHERE id=?";

    // 使用预处理语句
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    // 绑定参数需要匹配数据类型：两个字符串和一个整数
    if (!$stmt->bind_param("ssssi", $phone, $status, $notes, $region, $id)) {
        echo json_encode(["success" => false, "message" => "Binding parameters failed: " . $stmt->error]);
        exit;
    }

    // 执行预处理语句
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "更新成功"], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "Execute failed: " . $stmt->error]);
    }

    // 关闭预处理语句和连接
    $stmt->close();
    $conn->close();
}
?>
