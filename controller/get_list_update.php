<?php
require '../app/config.php'; // 包含数据库配置


// 假设您通过 GET 请求传递了 id
$id = $_GET['id'];

// 查询数据库
$sql = "SELECT * FROM claims WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 输出数据
    while($row = $result->fetch_assoc()) {
        echo json_encode($row);
    }
} else {
    echo "0 结果";
}
$conn->close();
?>
