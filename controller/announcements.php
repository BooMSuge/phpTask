<?php
require '../app/config.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // 获取公告列表
    $result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    echo json_encode(['code' => 0, 'data' => $announcements, 'message' => '获取成功']);
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        // 更新公告
        $stmt = $conn->prepare("UPDATE announcements SET content = ? WHERE id = ?");
        $stmt->bind_param("si", $data['content'], $data['id']);
    } else {
        // 添加公告
        $stmt = $conn->prepare("INSERT INTO announcements (content) VALUES (?)");
        $stmt->bind_param("s", $data['content']);
    }

    if ($stmt->execute()) {
        echo json_encode(['code' => 0, 'message' => '操作成功']);
    } else {
        echo json_encode(['code' => 1, 'message' => '操作失败: ' . $stmt->error]);
    }

    $stmt->close();
} elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        echo json_encode(['code' => 1, 'message' => '缺少公告ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['code' => 0, 'message' => '删除成功']);
    } else {
        echo json_encode(['code' => 1, 'message' => '删除失败: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['code' => 1, 'message' => '请求方法错误']);
}

$conn->close();
?>
