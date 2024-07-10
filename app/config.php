<?php
$servername = "localhost";
$username = "s8988297";
$password = "123456789";
$dbname = "s8988297";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);


// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function alert($str, $url) {
    echo "<script>alert('$str');location.href='$url';</script>";
}

