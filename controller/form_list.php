<?php
require '../app/config.php';
session_start();

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$region_ids = $_SESSION['region_ids'] ?? [];

// 初始化查询条件
$conditions = [];
$params = [];

if ($user_type == 'sub_admin' && !empty($region_ids)) {
    $region_placeholders = implode(',', array_fill(0, count($region_ids), '?'));
    $conditions[] = "region IN ($region_placeholders)";
    foreach ($region_ids as $region_id) {
        // 获取地区名称
        $region_sql = "SELECT name FROM regions WHERE id = ?";
        $region_stmt = $conn->prepare($region_sql);
        $region_stmt->bind_param("i", $region_id);
        $region_stmt->execute();
        $region_result = $region_stmt->get_result();
        $region_row = $region_result->fetch_assoc();
        $params[] = $region_row['name'];
        $region_stmt->close();
    }
} elseif ($user_type == 'sub_admin') {
    $conditions[] = "1 = 0"; // 确保没有数据返回
}

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $conditions[] = "username LIKE ?";
    $params[] = "%" . $_GET['username'] . "%";
}

if (isset($_GET['verification_code']) && !empty($_GET['verification_code'])) {
    $conditions[] = "verification_code LIKE ?";
    $params[] = "%" . $_GET['verification_code'] . "%";
}

if (isset($_GET['phone']) && !empty($_GET['phone'])) {
    $conditions[] = "phone LIKE ?";
    $params[] = "%" . $_GET['phone'] . "%";
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $conditions[] = "status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['region']) && !empty($_GET['region'])) {
    $conditions[] = "region = ?";
    $params[] = $_GET['region'];
}

if (isset($_GET['notes']) && !empty($_GET['notes'])) {
    $conditions[] = "notes LIKE ?";
    $params[] = "%" . $_GET['notes'] . "%";
}

$whereClause = "";
if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

$sql = "SELECT id, username, verification_code, phone, created_at, status, notes, region FROM claims $whereClause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // 根据用户类型决定是否隐藏手机号
    if ($user_type == 'sub_admin') {
        $row['phone'] = substr($row['phone'], -4) ? '****' . substr($row['phone'], -4) : $row['phone'];
    }
    $data[] = $row;
}

$response = [
    "code" => 0,
    "msg" => "",
    "count" => count($data),
    "data" => $data
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>
