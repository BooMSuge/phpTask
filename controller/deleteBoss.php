<?php
require '../app/config.php'; 

// 获取从客户端传来的 ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // 准备 SQL 语句
    $sql = "DELETE FROM claims WHERE id = $id";

    // 执行查询
    if ($conn->query($sql) === TRUE) {
        echo "记录删除成功";
    } else {
        echo "错误: " . $conn->error;
    }
} else {
    echo "无效的 ID";
}

// 关闭数据库连接
$conn->close();
?>
