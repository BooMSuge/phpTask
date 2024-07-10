<?php
session_start();

// 检查是否为管理员或子管理员
if (!isset($_SESSION['user_logged_in']) || !isset($_SESSION['user_type'])) {
    header("Location: ../admin/login.html"); // 重定向到登录页面
    exit();
}

// 确保权限已设置
if ($_SESSION['user_type'] == 'sub_admin' && !isset($_SESSION['region_ids'])) {
    header("Location: ../admin/login.html"); // 如果没有设置权限则重定向到登录页面
    exit();
}

$title = ['user_type'] == 'admin' ? '超级后台管理' : '后台管理';


?>
<!DOCTYPE html>
<html>

<head>
   <meta charset="utf-8">
     <title><?php echo $title; ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../layui/css/layui.css" rel="stylesheet">
    <script src="../layui/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">

<style>
:root {
    --primary-color: #fff; /* 主题颜色，纯白色 */
    --text-color: #333; /* 主要文字颜色，深灰色 */
    --background-color: #fff; /* 背景颜色，纯白色 */
    
}

/* 通用按钮样式 */


/* 主体字体样式 */
body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
}

/* 通用导航栏样式 */
.layui-nav {
    background-color: var(--primary-color);
    color: var(--text-color);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 导航项和图标字体颜色 */
.layui-nav a {
    color: var(--text-color) !important;
}

/* 左侧导航栏宽度 */
.layui-side {
    min-width: 200px;
}
/* 选中项的颜色 */
.active {
    color: var(--primary-color);
}

/* 表头样式 */
.layui-table thead tr {
    background-color: var(--primary-color);
    color: var(--text-color);
}
.layui-nav .layui-nav-child dd a:active {
    color: #fff !important; /* 例如: #ff0000 */
}

</style>


</head>

<body>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <div class="layui-logo layui-hide-xs layui-bg-white"><a href="/admin">
            <?php echo $_SESSION['user_type'] == 'admin' ? '超级后台管理' : '后台管理'; ?>
        </a></div>
            <ul class="layui-nav layui-layout-right">
                <li class="layui-nav-item layui-hide layui-show-sm-inline-block">
                    <a href="javascript:;">
                        <?php echo $_SESSION['username']; ?>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a href="../admin/user_list.php">账号管理</a></dd>
                        <dd><a href="../controller/out_login.php">退出登录</a></dd>
                    </dl>
                </li>
            </ul>
        </div>
        <div class="layui-side layui-bg-white">
            <div class="layui-side-scroll">
                <!-- 左侧导航区域（可配合layui已有的垂直导航） -->
              <ul class="layui-nav layui-nav-tree" lay-filter="test">
    <li class="layui-nav-item layui-nav-itemed">
        <a href="javascript:;"><i class="layui-icon layui-icon-template-1"></i>任务</a>
        <dl class="layui-nav-child">
            <!-- 子导航项 -->
            <dd layui-icon-app><a href="index.php"><i class="layui-icon layui-icon-app"></i>用户申请</a></dd>
            <dd layui-icon-app><a href="withdrawal.php"><i class="layui-icon layui-icon-dollar"></i>提现记录</a></dd>
        </dl>
    </li>
    <li class="layui-nav-item layui-nav-itemed">
        <a href="javascript:;"><i class="layui-icon layui-icon-user"></i> 用户管理</a>
        <dl class="layui-nav-child">
            <dd><a href="user_list.php"><i class="layui-icon layui-icon-username"></i> 用户列表</a></dd>
            <!-- 子导航项 -->
             <?php if ($_SESSION['user_type'] == 'admin') { ?>
               <dd><a href="user_add.php"><i class="layui-icon layui-icon-addition"></i> 子管理添加</a></dd>
               <dd><a href="sub_admin.php"><i class="layui-icon layui-icon-username"></i> 子管理列表</a></dd>
               <?php } ?>
        </dl>
    </li>
    <?php if ($_SESSION['user_type'] == 'admin') { ?>
     <li class="layui-nav-item">
        <a href="city.php"><i class="layui-icon layui-icon-component"></i>地区管理</a>
        </li>
          <?php } ?>
     <li class="layui-nav-item">
        <a href="announcements.php"><i class="layui-icon layui-icon-note"></i>添加公告</a>
        </li>
</ul>

            </div>
        </div>
           <script src="../layui/layui.js"></script>
</body>
</html>
