<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header("Location: dangnhap.php"); // Chuyển hướng đến trang đăng nhập
    exit();
}

$ma_nguoi_dung = $_SESSION['MaNguoiDung'];

// Lấy thông tin người dùng
$sql = "SELECT * FROM nguoidung WHERE MaNguoiDung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ma_nguoi_dung);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kiểm tra nếu người dùng có gói VIP
$hasVipPackage = !empty($user['MaGoi']); // Kiểm tra người dùng có gói VIP hay không

// Nếu người dùng có gói VIP, lấy thông tin gói dịch vụ
$package = [];
if ($hasVipPackage) {
    $sqlPackage = "SELECT * FROM goidichvu WHERE MaGoi = ?";
    $stmtPackage = $conn->prepare($sqlPackage);
    $stmtPackage->bind_param("i", $user['MaGoi']);
    $stmtPackage->execute();
    $resultPackage = $stmtPackage->get_result();
    $package = $resultPackage->fetch_assoc();
}

// Gia hạn gói VIP
if (isset($_POST['extend'])) {
    if ($hasVipPackage) {
        // Lấy thời gian gia hạn từ gói dịch vụ
        $package_duration = $package['ThoiGianSuDung']; // Giả sử 'ThoiGian' lưu trữ thời gian của gói (30, 60, 90 ngày)

        // Tính toán ngày hết hạn mới
        $new_expiry_date = date('Y-m-d', strtotime($user['NgayHetHanVIP'] . " +$package_duration days"));

        // Cập nhật ngày hết hạn trong cơ sở dữ liệu
        $sqlExtend = "UPDATE nguoidung SET NgayHetHanVIP = ? WHERE MaNguoiDung = ?";
        $stmtExtend = $conn->prepare($sqlExtend);
        $stmtExtend->bind_param("si", $new_expiry_date, $ma_nguoi_dung);

        if ($stmtExtend->execute()) {
            echo "<p>Gói VIP của bạn đã được gia hạn thêm $package_duration ngày. Ngày hết hạn mới: " . $new_expiry_date . "</p>";
            $user['NgayHetHanVIP'] = $new_expiry_date; // Cập nhật lại ngày hết hạn trong session
        } else {
            echo "<p>Có lỗi xảy ra khi gia hạn gói VIP. Vui lòng thử lại sau.</p>";
        }
    } else {
        echo "<p>Bạn không có gói VIP để gia hạn.</p>";
    }
}

// Hủy gói VIP
if (isset($_POST['cancel'])) {
    // Hủy gói VIP: Cập nhật MaGoi và NgayHetHanVIP thành NULL
    $sqlCancel = "UPDATE nguoidung SET MaGoi = NULL, NgayHetHanVIP = NULL WHERE MaNguoiDung = ?";
    $stmtCancel = $conn->prepare($sqlCancel);
    $stmtCancel->bind_param("i", $ma_nguoi_dung);

    if ($stmtCancel->execute()) {
        echo "<p>Gói VIP của bạn đã được hủy thành công.</p>";
        $user['MaGoi'] = NULL;
        $user['NgayHetHanVIP'] = NULL;
    } else {
        echo "<p>Có lỗi xảy ra khi hủy gói VIP. Vui lòng thử lại sau.</p>";
    }
}
?>

<?php include 'header.php'; ?>

<h1>Thông tin gói người dùng</h1>

<?php if (!$hasVipPackage): ?>
    <!-- Nếu người dùng chưa có gói VIP -->
    <div class="container">
        <p>Bạn chưa mua gói VIP nào. <a href="dichvu.php">Mua ngay?</a></p>
    </div>
<?php else: ?>
    <!-- Nếu người dùng có gói VIP -->
    <div class="container">
        <h2>Gói VIP của bạn</h2>
        <p><strong>Tên người dùng:</strong> <?php echo htmlspecialchars($user['TenNguoiDung']); ?></p>
        <p><strong>Gói dịch vụ:</strong> <?php echo htmlspecialchars($package['TenGoi']); ?></p> <!-- Lấy tên gói từ bảng goidichvu -->
        <p><strong>Mô tả gói dịch vụ:</strong> <?php echo htmlspecialchars($package['MoTa']); ?></p>
        <p><strong>Ngày hết hạn:</strong> <?php echo htmlspecialchars($user['NgayHetHanVIP']); ?></p>
        
        <!-- Gia hạn gói VIP -->
        <form method="POST" action="">
            <button type="submit" name="extend">Gia hạn gói</button>
        </form>

        <!-- Hủy gói VIP -->
        <form method="POST" action="">
            <button type="submit" name="cancel">Hủy gói</button>
        </form>
        <div class="back-button">
            <a href="index.php">Quay về trang chủ</a>
        </div>
    </div>
<?php endif; ?>

<!-- CSS Styles -->
<style>
/* CSS Styles */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #333;
    color: white;
    padding: 10px 20px;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

body {
    margin-top: 150px;
}

/* Tiêu đề chính */
h1 {
    text-align: center;
    color: #FFD700; /* Màu vàng nổi bật */
}

/* Khung thông tin người dùng */
.container {
    background-color: grey;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Hiệu ứng đổ bóng */
    padding: 20px;
    max-width: 600px;
    margin: 20px auto; /* Căn giữa khung */
}

/* Tiêu đề gói */
h2 {
    color: white; 
    text-align: center;
}

/* Định dạng các đoạn văn */
p {
    line-height: 1.6;
    text-align: center;
}

/* Nút bấm */
button {
    background-color: #FFD700; /* Màu vàng nổi bật */
    color: #000; /* Màu chữ đen */
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s; /* Hiệu ứng chuyển màu */
    display: block;
    margin: 0 auto; /* Căn giữa nút bấm */
    margin-bottom: 10px;
}

/* Hiệu ứng khi di chuột qua nút bấm */
button:hover {
    background-color: #FFA500; /* Màu cam khi hover */
}
a:hover {
    text-decoration: underline; /* Gạch chân khi hover */
}
</style>
