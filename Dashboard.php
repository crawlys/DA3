<?php 
// Bắt đầu phiên làm việc
include 'lib/session.php';
Session::checkSession(); // Kiểm tra quyền truy cập, chỉ cho phép admin vào trang này

// Kết nối với cơ sở dữ liệu
include 'lib/database.php';
$db = new Database();

// Lấy thông tin về các bộ phim, người dùng, bình luận, và dịch vụ
$phimQuery = "SELECT * FROM phim WHERE TrangThai = 1";
$phimResult = $db->select($phimQuery);

$nguoiDungQuery = "SELECT * FROM nguoidung WHERE TrangThai = 1";
$nguoiDungResult = $db->select($nguoiDungQuery);

$binhLuanQuery = "SELECT * FROM binhluan";
$binhLuanResult = $db->select($binhLuanQuery);

$goiDichVuQuery = "SELECT * FROM goidichvu WHERE TrangThai = 1";
$goiDichVuResult = $db->select($goiDichVuQuery);

include "header.php";
include "sidebar.php";

?>

<!-- Nội dung của trang dashboard -->
<div id="main-content">
    <h2 class="dashboard-title">Chào mừng, <?php echo Session::get('TenNguoiDung'); ?>!</h2>

    <!-- Tổng quan -->
    <div class="overview">
        <div class="overview-item">
            <h3>Phim</h3>
            <p><?php echo $phimResult ? mysqli_num_rows($phimResult) : '0'; ?> bộ phim hiện có</p>
            <a href="movies.php" class="btn">Quản lý phim</a>
        </div>
        <div class="overview-item">
            <h3>Người dùng</h3>
            <p><?php echo $nguoiDungResult ? mysqli_num_rows($nguoiDungResult) : '0'; ?> người dùng đã đăng ký</p>
            <a href="users.php" class="btn">Quản lý người dùng</a>
        </div>
        <div class="overview-item">
            <h3>Bình luận</h3>
            <p><?php echo $binhLuanResult ? mysqli_num_rows($binhLuanResult) : '0'; ?> bình luận</p>
            <a href="comments.php" class="btn">Quản lý bình luận</a>
        </div>
        <div class="overview-item">
            <h3>Dịch vụ</h3>
            <p><?php echo $goiDichVuResult ? mysqli_num_rows($goiDichVuResult) : '0'; ?> gói dịch vụ</p>
            <a href="services.php" class="btn">Quản lý dịch vụ</a>
        </div>
    </div>

    <!-- Các hoạt động gần đây -->
    <div class="recent-activities">
        <h2 class="section-title">Các hoạt động gần đây</h2>
        <div class="activities">
            <div class="activity">
                <h4>Phim mới thêm</h4>
                <ul>
                    <?php
                    $newMoviesQuery = "SELECT * FROM phim ORDER BY NgayPhatHanh DESC LIMIT 5";
                    $newMoviesResult = $db->select($newMoviesQuery);
                    if ($newMoviesResult) {
                        while ($movie = mysqli_fetch_assoc($newMoviesResult)) {
                            echo "<li>{$movie['TieuDe']} - " . date('d-m-Y', strtotime($movie['NgayPhatHanh'])) . "</li>";
                        }
                    } else {
                        echo "<li>Không có phim mới.</li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="activity">
                <h4>Bình luận mới</h4>
                <ul>
                    <?php
                    $newCommentsQuery = "SELECT * FROM binhluan ORDER BY NgayTao DESC LIMIT 5";
                    $newCommentsResult = $db->select($newCommentsQuery);
                    if ($newCommentsResult) {
                        while ($comment = mysqli_fetch_assoc($newCommentsResult)) {
                            echo "<li><strong>{$comment['MaNguoiDung']}</strong>: {$comment['NoiDung']} - " . date('d-m-Y', strtotime($comment['NgayTao'])) . "</li>";
                        }
                    } else {
                        echo "<li>Không có bình luận mới.</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Các biểu đồ thống kê -->
    <div class="statistics">
        <h2 class="section-title">Thống kê nhanh</h2>
        <div class="stat">
            <h3>Tổng số người dùng</h3>
            <p><?php echo mysqli_num_rows($nguoiDungResult); ?></p>
        </div>
        <div class="stat">
            <h3>Tổng số phim</h3>
            <p><?php echo mysqli_num_rows($phimResult); ?></p>
        </div>
        <div class="stat">
            <h3>Tổng số bình luận</h3>
            <p><?php echo mysqli_num_rows($binhLuanResult); ?></p>
        </div>
    </div>
</div>

<style>
    .dashboard-title {
        font-size: 24px;
        margin-bottom: 20px;
        color: #f39c12;
    }

    .overview {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .overview-item {
        background-color: #1a1a1a;
        border-radius: 5px;
        padding: 20px;
        color: #e0e0e0;
        width: 23%;
        margin-bottom: 20px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
    }

    .overview-item h3 {
        margin-bottom: 10px;
        color: #f39c12;
    }

    .btn {
        display: inline-block;
        margin-top: 10px;
        padding: 10px 20px;
        background-color: #f39c12;
        color: #1a1a1a;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .btn:hover {
        background-color: #e67e22;
    }

    .recent-activities, .statistics {
        margin-top: 40px;
    }

    .section-title {
        font-size: 20px;
        margin-bottom: 20px;
        color: #f39c12;
    }

    .activities {
        display: flex;
        justify-content: space-between;
    }

    .activity {
        width: 48%;
    }

    .activity ul {
        list-style: none;
        padding: 0;
    }

    .activity ul li {
        background-color: #1a1a1a;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        color: #e0e0e0;
    }

    .statistics {
        display: flex;
        justify-content: space-between;
    }

    .stat {
        background-color: #1a1a1a;
        border-radius: 5px;
        padding: 20px;
        text-align: center;
        width: 30%;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
    }

    .stat h3 {
        margin-bottom: 10px;
        color: #f39c12;
    }

    .stat p {
        color: #e0e0e0;
    }
</style>
