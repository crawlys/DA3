<?php
// Kết nối cơ sở dữ liệu
include('db.php');

session_start(); // Bắt đầu session để truy cập biến session

// Kiểm tra nếu người dùng đã đăng nhập và có MaNguoiDung trong session
if (!isset($_SESSION['MaNguoiDung'])) {
    // Nếu chưa đăng nhập, chuyển hướng người dùng đến trang đăng nhập
    header("Location: dangnhap.php");
    exit();
}

// Lấy MaNguoiDung từ session
$ma_nguoidung = $_SESSION['MaNguoiDung']; 

// Kiểm tra xem người dùng đã gửi ảnh chưa
if (isset($_POST['upload_avatar'])) {
    // Kiểm tra nếu có file được tải lên
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        // Đường dẫn thư mục lưu ảnh
        $upload_dir = 'images/user/';
        
        // Lấy thông tin file tải lên
        $avatar = $_FILES['avatar']['name'];
        $avatar_tmp = $_FILES['avatar']['tmp_name'];
        
        // Đảm bảo rằng tệp là một hình ảnh hợp lệ
        $imageFileType = strtolower(pathinfo($avatar, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            // Tạo tên tệp mới để tránh trùng lặp
            $new_avatar = uniqid('avatar_') . '.' . $imageFileType;
            
            // Di chuyển tệp từ thư mục tạm vào thư mục đích
            if (move_uploaded_file($avatar_tmp, $upload_dir . $new_avatar)) {
                // Cập nhật cơ sở dữ liệu để lưu tên avatar mới
                $sql = "UPDATE nguoidung SET Avatar = '$new_avatar' WHERE MaNguoiDung = '$ma_nguoidung'";
                
                if (mysqli_query($conn, $sql)) {
                    echo "Cập nhật avatar thành công!";
                    // Cập nhật lại session hoặc dữ liệu người dùng nếu cần
                    header("Location: nguoidung.php"); // Điều hướng lại đến trang hồ sơ
                    exit();
                } else {
                    echo "Lỗi khi cập nhật avatar: " . mysqli_error($conn);
                }
            } else {
                echo "Lỗi khi tải lên ảnh!";
            }
        } else {
            echo "Chỉ cho phép tải lên hình ảnh định dạng JPG, JPEG, PNG, GIF!";
        }
    } else {
        echo "Không có tệp nào được tải lên hoặc xảy ra lỗi!";
    }
}
?>
