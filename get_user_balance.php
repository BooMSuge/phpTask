<?php
session_start();
require 'app/config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'balance' => $row['balance']]);
} else {
    echo json_encode(['success' => false, 'message' => '无法获取余额']);
}

$stmt->close();
$conn->close();
?>
