<?php
// changeStatus.php
require '../app/config.php';  // 确保这里的路径根据你的项目结构调整

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? '';
$status = $data['status'] ?? '';
$notes = $data['notes'] ?? '';

if ($id && $status) {
    $sql = "UPDATE withdrawals SET status = ?, notes = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $notes, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

$conn->close();
?>
