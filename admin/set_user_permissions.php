<?php
session_start();
require '../app/config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../admin/login.html");
    exit();
}

$user_id = $_SESSION['admin']['id'];
$user_type = $_SESSION['admin']['user_type'];

if ($user_type == 'sub_admin') {
    $sql = "SELECT region_id FROM admin_region WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $region_ids = [];
        while ($row = $result->fetch_assoc()) {
            $region_ids[] = $row['region_id'];
        }
        
        $_SESSION['region_ids'] = $region_ids;
        $stmt->close();
    } else {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
} else {
    $_SESSION['region_ids'] = [];
}

$conn->close();
?>
