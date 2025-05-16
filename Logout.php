<?php
// Bao gồm Session để xử lý phiên làm việc
include 'lib/Session.php';
Session::init();

// Hủy session khi người dùng đăng xuất
Session::destroy();

// Chuyển hướng về trang đăng nhập
header('Location: admin_login.php');
exit();
