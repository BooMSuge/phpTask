<?php
require '../app/config.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$status = $data['status'];
$notes = $data['notes'];

// 获取提现记录
$withdrawalSql = "SELECT * FROM withdrawals WHERE id = ?";
$stmt = $conn->prepare($withdrawalSql);
$stmt->bind_param("i", $id);
$stmt->execute();
$withdrawalResult = $stmt->get_result();
$withdrawal = $withdrawalResult->fetch_assoc();

if (!$withdrawal) {
    echo json_encode(['success' => false, 'message' => '找不到对应的提现记录']);
    exit;
}

$userId = $withdrawal['user_id'];
$amount = floatval($withdrawal['amount']); // 确保金额是浮点数
$currentStatus = $withdrawal['status'];

// 开始事务
$conn->begin_transaction();

try {
    // 更新提现记录
    $sql = "UPDATE withdrawals SET status = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $notes, $id);

    if (!$stmt->execute()) {
        throw new Exception('更新提现记录失败');
    }

    // 更新用户余额
    $updateBalanceSql = "";
    if ($status == 'confirmed' && $currentStatus == 'pending') {
        // 确认提现（从待处理状态变为已完成状态），无需更新余额
    } elseif ($status == 'rejected') {
        if ($currentStatus == 'pending' || $currentStatus == 'confirmed') {
            // 拒绝提现并退还用户余额（从待处理或已完成状态变为拒绝状态）
            $updateBalanceSql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        }
    }

    if (!empty($updateBalanceSql)) {
        $stmt = $conn->prepare($updateBalanceSql);
        $stmt->bind_param("di", $amount, $userId);
        if (!$stmt->execute()) {
            throw new Exception('更新用户余额失败');
        }
    }

    // 记录日志
    $logSql = "INSERT INTO withdrawal_logs (withdrawal_id, user_id, status, amount, notes, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $logStmt = $conn->prepare($logSql);
    $logStmt->bind_param("iissd", $id, $userId, $status, $amount, $notes);
    if (!$logStmt->execute()) {
        throw new Exception('记录日志失败');
    }

    // 提交事务
    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // 回滚事务
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>
