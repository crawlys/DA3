<?php
include('db.php'); // Kết nối database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenNguoiDung = $_POST['username'];
    $email = $_POST['email'];
    $matKhau = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $vaiTro = "user"; // Vai trò mặc định là user
    $trangThai = 1; // Mặc định trạng thái kích hoạt

    // Chuẩn bị câu lệnh SQL
    $sql = "INSERT INTO NguoiDung (TenNguoiDung, Email, MatKhau, VaiTro, TrangThai) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $tenNguoiDung, $email, $matKhau, $vaiTro, $trangThai);

    if ($stmt->execute()) {
        // Chuyển hướng sang trang đăng nhập
        header('Location: dangnhap.php');
        exit();
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký</title>
    <style>
    /* Nền toàn bộ trang */
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background: url('https://images.pexels.com/photos/7991579/pexels-photo-7991579.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center fixed; /* Hình nền */
    background-size: cover;
    background-color: #333; /* Màu nền thay thế nếu ảnh không tải được */
}

/* Định dạng toàn bộ section */
.logout-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

/* Định dạng form bên trong section */
.logout-container form {
    background-color: rgba(58, 56, 56, 0.9); /* Màu nền trắng với độ trong suốt */
    padding: 20px 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.7);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

/* Tiêu đề của form */
.logout-container form h1 {
    font-size: 24px;
    color: #e50914;
    margin-bottom: 20px;
    font-weight: bold;
}

/* Nhãn cho các trường */
.logout-container form label {
  font-size: 14px;
  color: #ffffff;
  display: block; /* Đưa label xuống dòng */
  margin-bottom: 5px;
  text-align: left; /* Căn lề trái */
  padding: 10px;
}

/* Ô nhập liệu */
.logout-container form input {
  width: 90%;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #cccccc;
  outline: none;
  font-size: 14px;
  background-color: #333; /* Nền ô input tối hơn */
  color: #ffffff; /* Màu chữ trắng */
}

/* Hiệu ứng khi nhập vào ô input */
.logout-container form input:focus {
    border-color: #e50914;
    outline: none;
    box-shadow: 0 0 5px rgba(229, 9, 20, 0.5);
}

/* Nút bấm */
.logout-container form button {
  width: 100%;
  padding: 10px;
  background-color:rgb(180, 2, 2); 
  color: white;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s; /* Hiệu ứng khi hover */
  margin-top: 10px;
}

/* Hiệu ứng khi rê chuột lên nút bấm */
.logout-container form button:hover {
    background-color: #b00710;
}

/* Định dạng nhỏ gọn trên thiết bị di động */
@media (max-width: 768px) {
    .logout-container form {
        padding: 20px;
    }

    .logout-container form h1 {
        font-size: 20px;
    }
}

    </style>
</head>
<body>
<section class="logout-container">
<form action="dangky.php" method="POST">
<h1>Đăng Ký</h1>
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit">Đăng Ký</button>
    </form>
</section>
</body>
</html>
