<?php
require '../app/config.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'])) {
    // 单条删除
    $id = intval($data['id']);
    $deleteSql = "DELETE FROM claims WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '删除记录失败']);
    }
    
    $stmt->close();
} elseif (isset($data['ids'])) {
    // 批量删除
    $ids = $data['ids'];
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => '没有选择要删除的记录']);
        exit;
    }

    // 将ID数组转换为逗号分隔的字符串
    $idList = implode(',', array_map('intval', $ids));

    // 删除记录
    $deleteSql = "DELETE FROM claims WHERE id IN ($idList)";
    if ($conn->query($deleteSql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '删除记录失败']);
    }
}

$conn->close();
?>
