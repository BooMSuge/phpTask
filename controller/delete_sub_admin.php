<?php
include '../app/config.php'; // 包含数据库连接配置

// 获取POST请求的id参数
$id = $_POST['id'];

// 检查是否提供了id参数
if (isset($id) && is_numeric($id)) {
    // 先删除 admin_region 表中所有引用该 admin_id 的记录
    $stmt = $conn->prepare("DELETE FROM admin_region WHERE admin_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $stmt->close();

        // 然后删除 admin 表中的记录
        $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "删除成功"]);
        } else {
            echo json_encode(["success" => false, "message" => "删除失败： " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "删除失败： " . $stmt->error]);
        $stmt->close();
    }
} else {
    echo json_encode(["success" => false, "message" => "无效的ID"]);
}

// 关闭数据库连接
$conn->close();
?>
