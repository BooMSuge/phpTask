<?php
require 'app/config.php';

header('Content-Type: application/json');

$response = ["success" => false, "regions" => [], "message" => ""];

try {
    $sql = "SELECT name FROM regions ORDER BY id DESC";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response['regions'][] = $row;
            }
            $response['success'] = true;
        } else {
            $response['message'] = "No regions found";
        }
    } else {
        $response['message'] = "Database query failed: " . $conn->error;
    }
} catch (Exception $e) {
    $response['message'] = "Exception: " . $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>
