<?php
session_start();
session_destroy(); // 销毁所有的 session 数据
header('Location: login.html'); // 重定向到登录页面
exit();
?>