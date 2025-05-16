<?php
session_start(); // Khởi tạo session
include('db.php'); // Kết nối database
// Kiểm tra nếu người dùng đã gửi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Chuẩn bị câu lệnh SQL để lấy thông tin người dùng
    $sql = "SELECT * FROM nguoidung WHERE TenNguoiDung = ? OR Email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Lỗi chuẩn bị câu lệnh SQL: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra nếu có người dùng với thông tin nhập vào
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra mật khẩu
        if (password_verify($password, $user['MatKhau'])) {
            // Nếu đăng nhập thành công, lưu thông tin người dùng vào session
            $_SESSION['MaNguoiDung'] = $user['MaNguoiDung'];
            $_SESSION['TenNguoiDung'] = $user['TenNguoiDung'];
            $_SESSION['Avatar'] = $user['Avatar'];
           

            // Chuyển hướng tới trang chủ (index.php)
            header("Location: index.php");
            exit();
        } else {
            // Thông báo mật khẩu không đúng
            echo "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        // Thông báo tên đăng nhập hoặc email không tồn tại
        echo "Tên đăng nhập hoặc mật khẩu không đúng!";
    }

    $stmt->close();
    $conn->close();
}
?>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PHIM HAY</title>
  <style>
    /* Đặt hình nền */
/* Đặt hình nền */
body {
  margin: 0;
  padding: 0;
  font-family: Arial, sans-serif;
  background: url('https://images.pexels.com/photos/7991579/pexels-photo-7991579.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center fixed; /* Hình nền */
  background-size: cover; /* Ảnh phủ toàn màn hình */
  color: #ffffff; /* Màu chữ trắng để nổi bật */
}

/* Định dạng container của form đăng nhập */
.login-container {
  width: 100%;
  max-width: 400px; /* Giới hạn chiều rộng form */
  margin: 100px auto; /* Căn giữa theo chiều ngang và cách đỉnh 100px */
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.7); /* Hiệu ứng đổ bóng sâu hơn */
  background-color: rgba(0, 0, 0, 0.7); /* Nền mờ cho phần đăng nhập */
  backdrop-filter: blur(10px); /* Hiệu ứng làm mờ */
  text-align: center; /* Căn giữa text */
}

/* Tiêu đề chính */
.login-container h1 {
  font-size: 28px; /* Cỡ chữ lớn hơn cho tiêu đề */
  margin-bottom: 10px;
  color:rgb(255, 255, 255); /* Màu vàng nổi bật */
}

/* Thông điệp nhỏ */
.login-container p {
  margin-bottom: 20px;
  font-size: 14px;
  color: #cccccc;
}

/* Định dạng các form-group */
.form-group {
  margin-bottom: 20px;
  text-align: left; /* Căn lề trái */
}

/* Nhãn (label) */
.form-group label {
  font-size: 14px;
  color: #ffffff;
  display: block; /* Đưa label xuống dòng */
  margin-bottom: 5px;
}

/* Ô nhập liệu */
.form-group input {
  width: 100%;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #cccccc;
  outline: none;
  font-size: 14px;
  background-color: #333; /* Nền ô input tối hơn */
  color: #ffffff; /* Màu chữ trắng */
}

/* Đổi màu ô input khi focus */
.form-group input:focus {
  border: 1px solidrgb(179, 5, 5); /* Viền vàng khi focus */
}

/* Nút bấm */
.btn {
  width: 100%;
  padding: 10px;
  background-color:rgb(180, 2, 2); /* Màu vàng nổi bật */
  color: #000;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s; /* Hiệu ứng khi hover */
}

/* Hiệu ứng khi di chuột qua nút bấm */
.btn:hover {
  background-color:rgb(172, 5, 5); /* Màu cam đậm hơn khi hover */
}

/* Footer dưới form */
.login-footer {
  margin-top: 20px;
  font-size: 14px;
}

.login-footer a {
  color:rgb(194, 9, 9);
  text-decoration: none;
}

.login-footer a:hover {
  text-decoration: underline;
}
  </style>
</head>
<body>
  <section class="login-container">
    <h1>Đăng Nhập</h1>
    <p>Hãy nhập thông tin tài khoản để đăng nhập.</p>

    <form action="dangnhap.php" method="POST">
      <div class="form-group">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
      </div>

      <div class="form-group">
        <label for="password">Mật khẩu:</label>
        <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
      </div>

      <button type="submit" class="btn">Đăng Nhập</button>
    </form>

    <div class="login-footer">
      <p>Chưa có tài khoản? <a href="dangky.php">Đăng ký ngay</a></p>
     
    </div>
  </section>
</body>
