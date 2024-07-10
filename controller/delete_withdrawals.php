<?php
require '../app/config.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$ids = $data['ids'];
if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => '没有选择要删除的记录']);
    exit;
}

// 将ID数组转换为逗号分隔的字符串
$idList = implode(',', array_map('intval', $ids));

// 删除提现记录
$deleteSql = "DELETE FROM withdrawals WHERE id IN ($idList)";
if ($conn->query($deleteSql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '删除记录失败']);
}

$conn->close();
?>
