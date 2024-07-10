<?php
require 'app/config.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = [];
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

if (!empty($announcements)) {
    echo json_encode(['success' => true, 'announcements' => $announcements]);
} else {
    echo json_encode(['success' => false, 'message' => '没有公告']);
}

$conn->close();
?>
