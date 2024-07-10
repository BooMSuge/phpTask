<?php
require '../app/config.php';

header('Content-Type: application/json; charset=utf-8');

$response = ["code" => 1, "message" => "操作失败"];

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$conn->set_charset("utf8mb4");

if ($method == 'GET') {
    // 获取数据
    $sql = "SELECT * FROM regions ORDER BY id DESC";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    $response = ["code" => 0, "message" => "成功", "data" => $data];
} elseif ($method == 'POST') {
    if ($action == 'add') {
        // 添加数据
        $region = $input['region'] ?? '';
        $stmt = $conn->prepare("INSERT INTO regions (name) VALUES (?)");
        $stmt->bind_param('s', $region);
        if ($stmt->execute()) {
            $response = ["code" => 0, "message" => "添加成功"];
        } else {
            $response['message'] = '添加失败：' . $stmt->error;
        }
        $stmt->close();
    } elseif ($action == 'edit') {
        // 更新数据
        $id = $input['id'] ?? '';
        $region = $input['region'] ?? '';
        $stmt = $conn->prepare("UPDATE regions SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $region, $id);
        if ($stmt->execute()) {
            $response = ["code" => 0, "message" => "更新成功"];
        } else {
            $response['message'] = '更新失败：' . $stmt->error;
        }
        $stmt->close();
    } elseif ($action == 'delete') {
        // 删除数据
        $id = $input['id'] ?? '';

        // 先删除 admin_region 表中的相关记录
        $stmt = $conn->prepare("DELETE FROM admin_region WHERE region_id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $stmt->close();

            // 然后删除 regions 表中的记录
            $stmt = $conn->prepare("DELETE FROM regions WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $response = ["code" => 0, "message" => "删除成功"];
            } else {
                $response['message'] = '删除失败：' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = '删除失败：' . $stmt->error;
            $stmt->close();
        }
    }
}

echo json_encode($response);
$conn->close();
?>
