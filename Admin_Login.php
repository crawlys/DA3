<?php
include 'lib/Session.php';
include 'lib/Database.php';
Session::init();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $matkhau = md5($_POST['matkhau']); // Băm mật khẩu bằng MD5

    if (empty($email) || empty($matkhau)) {
        $thongbao = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $db = new Database();
        $query = "SELECT * FROM nguoidung WHERE Email='$email' AND MatKhau='$matkhau' AND VaiTro='admin' AND TrangThai=1";
        $result = $db->select($query);

        if ($result != false) {
            $value = $result->fetch_assoc();
            Session::set("login", true);
            Session::set("TenNguoiDung", $value['TenNguoiDung']);
            Session::set("VaiTro", $value['VaiTro']);
            header("Location: Dashboard.php");
        } else {
            $thongbao = "Email hoặc mật khẩu không đúng, hoặc tài khoản không phải admin!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>Đăng Nhập Quản Trị</h2>
            <?php if (isset($thongbao)) { ?>
                <p class="error-msg"><?php echo $thongbao; ?></p>
            <?php } ?>
            <form action="admin_login.php" method="post">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" placeholder="Nhập email..." required>
                <label for="matkhau">Mật khẩu:</label>
                <input type="password" id="matkhau" name="matkhau" placeholder="Nhập mật khẩu..." required>
                <div class="actions">
                    <button type="submit">Đăng Nhập</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>