<?php
session_start();
require 'app/config.php';

// 检查是否有用户登录
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => '未认证的用户']);
    exit;
}

$username = $_SESSION['username']; // 获取当前登录用户的用户名

ini_set('max_execution_time', '300'); // 5 分钟
ini_set('memory_limit', '512M');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 获取用户的 image_url
    $sql = "SELECT image_url FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($image_url);
    $stmt->fetch();

    if ($image_url) {
        echo json_encode(['success' => true, 'image_url' => $image_url]);
    } else {
        echo json_encode(['success' => false, 'message' => '没有找到图片']);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 检查是否有文件上传
    if (isset($_FILES['qrCode']) && $_FILES['qrCode']['error'] == 0) {
        $target_dir = __DIR__ . "/uploads/";
        $relative_target_dir = "/uploads/";
        $target_file = $target_dir . basename($_FILES["qrCode"]["name"]);
        $relative_target_file = $relative_target_dir . basename($_FILES["qrCode"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // 仅允许特定的文件格式
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowed_types)) {
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                    exit;
                }
            }
            if (move_uploaded_file($_FILES["qrCode"]["tmp_name"], $target_file)) {
                // 检查文件是否存在
                if (file_exists($target_file)) {
                    // 文件存在，更新数据库
                    $sql = "UPDATE users SET image_url = ? WHERE username = ?";
                    $stmt = $conn->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param("ss", $relative_target_file, $username);
                        if ($stmt->execute()) {
                            echo json_encode(['success' => true, 'message' => '文件上传成功']);
                        } else {
                            echo json_encode(['success' => false, 'message' => '数据库更新失败: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['success' => false, 'message' => 'SQL 语句准备失败: ' . $conn->error]);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => '文件上传失败: 文件未找到']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => '文件上传失败']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => '不支持的文件格式']);
        }
    } else {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过 php.ini 中的 upload_max_filesize 指令值',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过 HTML 表单中 MAX_FILE_SIZE 指令值',
            UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => '文件上传被 PHP 扩展程序中断',
        ];
        $error_code = $_FILES['qrCode']['error'];
        $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : '未知错误';
        echo json_encode(['success' => false, 'message' => '文件上传失败: ' . $error_message]);
    }
}

$conn->close();
?>
