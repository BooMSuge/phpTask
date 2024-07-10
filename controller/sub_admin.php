<?php
require '../app/config.php'; // 包含数据库配置

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_regions') {
    // 获取所有地区数据
    $sql = "SELECT id, name FROM regions";
    $result = $conn->query($sql);

    $regions = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $regions[] = $row;
        }
    }

    echo json_encode(['success' => true, 'regions' => $regions]);

} elseif ($action === 'get_user_regions') {
    // 获取用户已有的地区权限
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $sql = "SELECT region_id FROM admin_region WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $regions = array();
    while ($row = $result->fetch_assoc()) {
        $regions[] = $row['region_id'];
    }

    echo json_encode(['success' => true, 'regions' => $regions]);

} elseif ($action === 'update_user') {
    // 更新用户数据及其地区权限
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $regions = $_POST['regions'];

    // 更新用户名
    $sql = "UPDATE admin SET username = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $id);
    $stmt->execute();
    $stmt->close();

    // 更新密码（如果有提供）
if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE admin SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashedPassword, $id);
    $stmt->execute();
    $stmt->close();
}

    // 清除旧的地区权限
    $sql = "DELETE FROM admin_region WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // 插入新的地区权限
    $sql = "INSERT INTO admin_region (admin_id, region_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    foreach ($regions as $region_id) {
        $stmt->bind_param("ii", $id, $region_id);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(['success' => true, 'message' => '用户更新成功']);

} else {
    // 默认行为：获取用户数据及其地区权限
    $username = isset($_GET['username']) ? $_GET['username'] : '';

    if ($username) {
        $sql = "SELECT admin.id, admin.username, GROUP_CONCAT(regions.name SEPARATOR ', ') AS regions, admin.created_at
                FROM admin
                LEFT JOIN admin_region ON admin.id = admin_region.admin_id
                LEFT JOIN regions ON admin_region.region_id = regions.id
                WHERE admin.username LIKE ? AND admin.user_type = 'sub_admin'
                GROUP BY admin.id, admin.username, admin.created_at";
        $stmt = $conn->prepare($sql);
        $likeUsername = "%$username%";
        $stmt->bind_param("s", $likeUsername);
    } else {
        $sql = "SELECT admin.id, admin.username, GROUP_CONCAT(regions.name SEPARATOR ', ') AS regions, admin.created_at
                FROM admin
                LEFT JOIN admin_region ON admin.id = admin_region.admin_id
                LEFT JOIN regions ON admin_region.region_id = regions.id
                WHERE admin.user_type = 'sub_admin'
                GROUP BY admin.id, admin.username, admin.created_at";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);

    $stmt->close();
}

$conn->close();
?>
