<?php
// 初始化会话
session_start();

// 清除所有会话变量
$_SESSION = array();

// 销毁会话
session_destroy();

// 重定向到登录页面
header('Location: index.php');
exit;