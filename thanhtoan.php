<?php
session_start();
include 'db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header("Location: dangnhap.php"); // Chuyển hướng đến trang đăng nhập
    exit();
}

// Kiểm tra thông tin thanh toán trong session
if (!isset($_SESSION['final_price']) || !isset($_SESSION['package'])) {
    header("Location: index.php"); // Chuyển hướng nếu không có thông tin thanh toán
    exit();
}

// Lấy thông tin từ session
$final_price = $_SESSION['final_price'];
$package_id = $_SESSION['package'];

// Lấy thông tin gói dịch vụ từ CSDL
$sql = "SELECT TenGoi, MoTa, ThoiGianSuDung FROM goidichvu WHERE MaGoi = ? AND TrangThai = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $package_id);
$stmt->execute();
$result = $stmt->get_result();
$package_info = $result->fetch_assoc();

// Kiểm tra nếu gói dịch vụ không tồn tại
if (!$package_info) {
    echo "<p>Gói dịch vụ không tồn tại hoặc không khả dụng. Vui lòng chọn lại!</p>";
    exit();
}

// Thông tin gói dịch vụ
$package_name = htmlspecialchars($package_info['TenGoi']);
$package_description = htmlspecialchars($package_info['MoTa']);
$duration = (int)$package_info['ThoiGianSuDung']; // Ép kiểu để tránh lỗi

// Kiểm tra xem người dùng có gói VIP còn hiệu lực hay không
$user_id = $_SESSION['MaNguoiDung'];
$sql_check_vip = "SELECT NgayHetHanVIP FROM nguoidung WHERE MaNguoiDung = ? AND TrangThai = 1";
$stmt = $conn->prepare($sql_check_vip);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_vip = $stmt->get_result();
$user_vip_info = $result_vip->fetch_assoc();

// Nếu người dùng có VIP còn hiệu lực
if ($user_vip_info && strtotime($user_vip_info['NgayHetHanVIP']) > time()) {
    echo "<p>Bạn đã có gói VIP còn hiệu lực. Không thể mua gói mới ngay lúc này.</p>";
    exit();
}

// Ngày bắt đầu và ngày hết hạn
$start_date = date('Y-m-d'); // Ngày thanh toán (bắt đầu)
$end_date = date('Y-m-d', strtotime("+$duration days")); // Ngày hết hạn (tính từ ngày thanh toán)

// Kiểm tra nếu thanh toán thành công
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method']; // Lấy phương thức thanh toán người dùng chọn

    // Cập nhật thông tin thanh toán vào bảng thanhtoan
    $insertPaymentSql = "
        INSERT INTO thanhtoan (MaNguoiDung, MaGoi, SoTien, PhuongThucThanhToan, NgayThanhToan) 
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insertPaymentSql);
    $stmt->bind_param("iisss", $user_id, $package_id, $final_price, $payment_method, $start_date);

    if ($stmt->execute()) {
        // Cập nhật ngày hết hạn VIP và MaGoi cho người dùng
        $updateVIPDateSql = "
            UPDATE nguoidung 
            SET NgayHetHanVIP = ?, MaGoi = ? 
            WHERE MaNguoiDung = ?
        ";
        $stmt = $conn->prepare($updateVIPDateSql);
        $stmt->bind_param("sii", $end_date, $package_id, $user_id);

        if ($stmt->execute()) {
            // Nếu thanh toán và cập nhật ngày hết hạn thành công, hiển thị thông báo
            $success_message = "Thanh toán thành công! Bạn đã mua gói <strong>$package_name</strong> với giá <strong>" . number_format($final_price, 0) . " VNĐ</strong>. Bạn hiện có quyền xem phim VIP!";
        } else {
            // Nếu có lỗi trong quá trình cập nhật ngày hết hạn VIP
            $error_message = "Có lỗi xảy ra trong quá trình cập nhật ngày hết hạn VIP. Vui lòng thử lại.";
        }
    } else {
        // Nếu có lỗi trong quá trình cập nhật thanh toán
        $error_message = "Có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="styles.css"> <!-- Đảm bảo thêm file CSS -->
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="payment-summary">
        <?php if (!isset($success_message) && !isset($error_message)): ?>
            <h2>Thông Tin Thanh Toán</h2>
            <p><strong>Dịch vụ:</strong> Phim gói</p>
            <p><strong>Gói dịch vụ:</strong> <?= $package_name ?></p>
            <p><strong>Mô tả:</strong> <?= $package_description ?></p>
            <p><strong>Tổng thanh toán:</strong> <?= number_format($final_price); ?> ₫</p>
            <p><strong>Ngày có hiệu lực:</strong> <?= $start_date ?></p>
            <p><strong>Ngày hết hạn:</strong> <?= $end_date ?></p>

            <h3>Chọn phương thức thanh toán</h3>
            <form method="post"> <!-- Không cần chuyển hướng đến trang khác -->
                <input type="hidden" name="package_id" value="<?= htmlspecialchars($package_id) ?>"> <!-- Gửi ID gói dịch vụ -->
                <div class="payment-methods">
                    <input type="radio" id="credit_card" name="payment_method" value="Thẻ tín dụng" required>
                    <label for="credit_card">
                        <img src="images/card.jpg" alt="Thẻ tín dụng" class="payment-image">
                        Thẻ tín dụng
                    </label>

                    <input type="radio" id="bank_transfer" name="payment_method" value="Ngân hàng">
                    <label for="bank_transfer">
                        <img src="images/bank.jpg" alt="Ngân hàng" class="payment-image">
                        Ngân hàng
                    </label>

                    <input type="radio" id="paypal" name="payment_method" value="PayPal">
                    <label for="paypal">
                        <img src="images/paypal.webp" alt="PayPal" class="payment-image">
                        PayPal
                    </label>
                </div>
                <button type="submit" class="confirm-btn">Xác nhận thanh toán</button>
            </form>
        <?php elseif (isset($success_message)): ?>
            <div class="success-message">
                <h3>Bạn đã mua VIP thành công!</h3>
                <p><?= $success_message ?></p>
                <p><strong>Ngày bắt đầu:</strong> <?= $start_date ?></p>
                <p><strong>Ngày hết hạn:</strong> <?= $end_date ?></p>
            </div>
        <div class="back-button">
            <a href="index.php">Quay về trang chủ</a>
        </div>
        <?php elseif (isset($error_message)): ?>
            <div class="error-message">
                <h3>Có lỗi xảy ra!</h3>
                <p><?= $error_message ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>




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

.payment-methods label {
    cursor: pointer;
    margin: 10px;
}

.payment-summary {
    max-width: 600px;
    margin: 10px auto;
    padding: 3px;
    border-radius: 5px;
    background-color: #222; /* Nền tối cho khung */
}

.payment-summary h2 {
    text-align: center;
    color: red;
}

.payment-summary p {
    font-size: 17px;
    margin: 20px 0;
}

.success-message {
    background-color: #1a1a1a; /* Nền tối cho thông báo thành công */
    padding: 10px;
    border-radius: 5px;
    margin: 15px 0;
    text-align: center;
}

.success-message h3 {
    color: #ff0000; /* Màu đỏ cho tiêu đề thông báo thành công */
}

input[type="radio"]:checked + label .payment-image {
    border-color: #ff0000; /* Đổi màu viền khi được chọn */
}

.confirm-btn {
    display: block;
    width: 100%;
    padding: 12px;
    font-size: 18px;
    color: #fff;
    background-color: red;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-image {
    width: 50px; /* Điều chỉnh kích thước hình ảnh theo nhu cầu */
    height: auto;
    margin-right: 10px;
    border: 2px solid transparent; /* Để tạo hiệu ứng khi chọn */
    transition: border-color 0.3s;
}

.confirm-btn:hover {
    background-color: lightcoral;
}
.payment-methods input[type="radio"] {
    width: 16px; /* giảm kích thước nút radio */
    height: 16px;
    accent-color:rgb(35, 99, 196); /* màu của radio button */
    margin-right: 8px; /* khoảng cách giữa nút và nhãn */
}

</style>
