<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['MaNguoiDung'])) {
    // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập (dangnhap.php)
    header("Location: dangnhap.php");
    exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
include('db.php');
$user_id = $_SESSION['MaNguoiDung'];
$sql = "SELECT * FROM nguoidung WHERE MaNguoiDung = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


?>
<?php include 'header.php'; ?>

<div class="user-info-container">
    <div class="user-profile">
        <h2>Thông tin tài khoản</h2>
        <div class="avatar-container">
            <!-- Hiển thị avatar người dùng -->
            <img src="images/user/<?php echo $user['Avatar']; ?>" alt="Avatar" class="user-avatar">
            <!-- Nút thay đổi avatar -->
            <button id="avatar-link" class="btn-upload-avatar">Đổi Avatar</button>
        </div>
        <p><strong>Tên người dùng:</strong> <?php echo $user['TenNguoiDung']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['Email']; ?></p>
        <p><strong>Vai trò:</strong> <?php echo $user['VaiTro']; ?></p>
        <p><strong>Ngày hết hạn VIP:</strong> <?php echo $user['NgayHetHanVIP'] ? date('d/m/Y', strtotime($user['NgayHetHanVIP'])) : 'Chưa có'; ?></p>
    </div>
    
    <div class="user-info-edit">
        <h2>Chỉnh sửa thông tin</h2>
        <form action="update_user_info.php" method="POST">
            <div class="form-group">
                <label for="TenNguoiDung">Tên người dùng:</label>
                <input type="text" name="TenNguoiDung" id="TenNguoiDung" value="<?php echo $user['TenNguoiDung']; ?>" required>
            </div>
            <div class="form-group">
                <label for="Email">Email:</label>
                <input type="email" name="Email" id="Email" value="<?php echo $user['Email']; ?>" required>
            </div>
            <button type="submit" class="btn-save">Cập nhật</button>
        </form>
    </div>
</div>

<!-- Modal tải lên avatar -->
<div id="upload-avatar-modal" class="modal">
    <div class="modal-content">
        <form action="upload_avatar.php" method="POST" enctype="multipart/form-data">
            <label for="avatar">Chọn ảnh avatar mới:</label>
            <input type="file" name="avatar" accept="image/*" required>
            <button type="submit" name="upload_avatar">Tải ảnh lên</button>
        </form>
        <button id="close-modal" class="btn-close-modal">Đóng</button>
    </div>
</div>

<script>
document.getElementById("avatar-link").addEventListener("click", function(e) {
    e.preventDefault(); // Ngừng hành động mặc định của thẻ <a>
    document.getElementById("upload-avatar-modal").style.display = "block"; // Hiển thị modal
});

document.getElementById("close-modal").addEventListener("click", function() {
    document.getElementById("upload-avatar-modal").style.display = "none"; // Ẩn modal khi đóng
});
</script>


<style>
    /* Fix the header at the top */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #333;
    color: white;
    padding: 10px 20px;
    z-index: 1000; /* Ensure the header stays on top of other content */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Add some margin to the body content to avoid being hidden under the fixed header */
body {
    margin-top: 150px; /* Adjust this value based on the header height */
}

.user-info-container {
  
    padding: 30px;
    max-width: 800px;
    margin: 30px auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.user-profile, .user-info-edit {
    width: 100%;
    margin-bottom: 20px;
}

.user-profile h2, .user-info-edit h2 {
    text-align: center;
}

.avatar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

input[type="text"], input[type="email"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.btn-save {
    background-color: red;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-save :hover{
    background-color: darkred;
}
/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    width: 400px;
    text-align: center;
}

.modal-content form {
    margin-bottom: 20px;
}

.modal-content button {
    background-color: #f44336;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.modal-content button:hover {
    background-color: #e53935;
}

</style>