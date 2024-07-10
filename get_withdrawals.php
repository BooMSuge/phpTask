<?php
require 'app/config.php';

$user_id = $_GET['user_id'];

$sql = "SELECT id, created_at, amount, status, remarks , notes FROM withdrawals WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$withdrawals = [];
while ($row = $result->fetch_assoc()) {
    $withdrawals[] = $row;
}

echo json_encode($withdrawals);

$stmt->close();
$conn->close();
?>
