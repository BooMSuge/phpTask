<?php
require 'app/config.php';
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$amount = $_POST['amount'];

// 获取用户的余额
$sql_balance = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_balance);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$balance = $user['balance'];

if ($amount > $balance) {
    echo json_encode(['success' => false, 'message' => '余额不足']);
    exit;
}

// 插入提现记录
$sql_insert = "INSERT INTO withdrawals (user_id, created_at, amount, status, notes) VALUES (?, NOW(), ?, 'pending', '')";
$stmt = $conn->prepare($sql_insert);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '准备插入失败: ' . $conn->error]);
    exit;
}

$stmt->bind_param("id", $user_id, $amount);

if ($stmt->execute()) {
    // 更新用户余额
    $new_balance = $balance - $amount;
    $sql_update = "UPDATE users SET balance = ? WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("di", $new_balance, $user_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => '提现申请成功']);
} else {
    echo json_encode(['success' => false, 'message' => '提现申请失败']);
}

$stmt->close();
$conn->close();
?>
