<?php
session_start();
require 'app/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, verification_code, phone, created_at, username , status , notes FROM claims WHERE user_id = ? ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '数据库查询错误']);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$claims = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'data' => $claims]);

$stmt->close();
$conn->close();
?>
