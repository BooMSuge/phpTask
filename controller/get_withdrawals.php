<?php
require '../app/config.php'; // 包含数据库配置

header('Content-Type: application/json');

// 初始化查询条件
$whereClauses = [];
$params = [];
$types = '';

// 检查是否有用户名过滤条件
if (!empty($_GET['username'])) {
    $whereClauses[] = 'u.username LIKE ?';
    $params[] = '%' . $_GET['username'] . '%';
    $types .= 's';
}

// 检查是否有服务状态过滤条件
if (!empty($_GET['status'])) {
    $whereClauses[] = 'w.status = ?';
    $params[] = $_GET['status'];
    $types .= 's';
}

// 构建查询语句
$sql = "SELECT w.id, w.created_at, w.amount, w.status, w.notes, u.username, u.image_url
        FROM withdrawals w
        JOIN users u ON w.user_id = u.id";

if (!empty($whereClauses)) {
    $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
}

// 预处理SQL语句
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['code' => 1, 'msg' => '数据库查询错误']);
    exit;
}

// 绑定参数
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// 执行查询
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $result->free();
}

// 关闭数据库连接
$conn->close();

// 构建符合规范的JSON响应
$response = [
    'code' => 0,
    'msg'  => '',
    'count' => count($data),
    'data' => $data
];

echo json_encode($response);
?>
