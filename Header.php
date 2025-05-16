<?php
include 'lib/Session.php'; 
Session::init();

// Kiểm tra nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
if (!Session::get('login')) {
    header("Location: admin_login.php");
    exit();
}

// Nếu đăng nhập, lấy thông tin từ session
$tenNguoiDung = Session::get('TenNguoiDung');
$avatar = Session::get('Avatar') ?: 'assets/imgs/avt_admin.jpg'; // Avatar mặc định nếu không có
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị</title>
    <link rel="stylesheet" href="assets/css/css1.css"> <!-- CSS tổng quát -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <script>
        // JavaScript để điều khiển dropdown menu khi nhấp vào avatar
        document.addEventListener('DOMContentLoaded', function() {
            const avatar = document.getElementById('user-avatar');
            const dropdown = document.querySelector('.dropdown');

            // Thêm sự kiện click vào avatar
            avatar.addEventListener('click', function() {
                dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
            });

            // Nếu người dùng nhấp ra ngoài dropdown, ẩn dropdown
            window.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target) && event.target !== avatar) {
                    dropdown.style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <header class="main-header">
        <div class="user-profile">
            <span><?php echo htmlspecialchars($tenNguoiDung); ?></span>
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="User Avatar" id="user-avatar">
            <!-- Dropdown Menu -->
            <div class="dropdown">
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    </header>