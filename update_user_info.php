<div class="user-info-container">
    <div class="user-profile">
        <h2>Thông tin tài khoản</h2>
        <form action="update_user_info.php" method="POST">
            <div class="form-group">
                <label for="TenNguoiDung">Tên người dùng:</label>
                <input type="text" name="TenNguoiDung" value="<?php echo $user['TenNguoiDung']; ?>" required>
            </div>
            <div class="form-group">
                <label for="Email">Email:</label>
                <input type="email" name="Email" value="<?php echo $user['Email']; ?>" required>
            </div>
            <button type="submit" class="btn-save">Cập nhật</button>
        </form>
    </div>
</div>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    header("Location: dangnhap.php");
    exit();
}

// Kết nối cơ sở dữ liệu
include('db.php');

$user_id = $_SESSION['MaNguoiDung'];
$tenNguoiDung = $_POST['TenNguoiDung'];
$email = $_POST['Email'];

// Cập nhật thông tin người dùng
$sql = "UPDATE nguoidung SET TenNguoiDung = ?, Email = ? WHERE MaNguoiDung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $tenNguoiDung, $email, $user_id);

if ($stmt->execute()) {
    // Thành công: Quay lại trang thông tin người dùng
    header("Location: nguoidung.php");
} else {
    // Thất bại: Hiển thị lỗi
    echo "Lỗi: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
