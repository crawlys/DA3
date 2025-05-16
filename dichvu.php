<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

// Lấy dữ liệu gói dịch vụ từ cơ sở dữ liệu
$sql = "SELECT MaGoi, TenGoi, MoTa, Gia, ThoiGianSuDung FROM goidichvu WHERE TrangThai = 1";
$result = $conn->query($sql);

$packages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $packages[$row['MaGoi']] = [
            'name' => $row['TenGoi'],
            'description' => $row['MoTa'],
            'price' => $row['Gia'],
            'duration' => (int)$row['ThoiGianSuDung'] // Ép kiểu thành số nguyên
        ];
    }
}

// Kiểm tra nếu đã gửi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['package']) && array_key_exists($_POST['package'], $packages)) {
        $package = $_POST['package'];
        $price = $packages[$package]['price'];

        // Lưu thông tin vào session
        $_SESSION['final_price'] = $price;
        $_SESSION['package'] = $package;

        // Chuyển hướng đến trang thanh toán
        header("Location: thanhtoan.php");
        exit();
    } else {
        $error_message = "Vui lòng chọn gói dịch vụ hợp lệ.";
    }
}
?>

<?php include 'header.php'; ?>
<div class="vip-package">
    <h2>Chọn gói</h2>
    <form method="post" action="">
        <div class="package-section">
            <?php foreach ($packages as $key => $package): ?>
                <label>
                    <input type="radio" name="package" value="<?= htmlspecialchars($key) ?>" <?= $key == array_key_first($packages) ? 'checked' : '' ?>> 
                    <?= htmlspecialchars($package['name']) ?> - <?= number_format($package['price'], 2) ?> ₫
                    <br>
                    <small><?= htmlspecialchars($package['description']) ?></small>
                </label>
            <?php endforeach; ?>
        </div>
        <!-- <div class="payment-info">
            <p>Dịch vụ: Phim gói</p>
            <p>Ngày có hiệu lực: <span id="start-date"><?= date('d/m/Y') ?></span></p>
            <p>Ngày hết hạn: <span id="end-date"></span></p>
        </div> -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <button type="submit" class="confirm-btn">Xác nhận</button>
    </form>
</div>



<?php include 'footer.php'; ?>


<style>
   /* CSS Styles (không thay đổi) */
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

.vip-package {
    max-width: 1000px;
    margin: 0 auto;
    padding: 10px;
    font-family: Arial, sans-serif;
}

.vip-package h2 {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    color: red;
}

.package-section {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.package-section label {
    display: block;
    font-size: 16px;
    padding: 10px;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.package-section label:hover {
    border-color: #007bff;
}

.package-section input[type="radio"] {
    margin-right: 10px;
    width: 16px;
    height: 16px;
    accent-color: #007bff;
    transform: scale(0.8);
}

.payment-info {
    background-color: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.payment-info p {
    font-size: 16px;
    margin: 8px 0;
    color: #555;
}

.payment-info span {
    font-weight: bold;
    color: #007bff;
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

.confirm-btn:hover {
    background-color: lightcoral;
}


</style>
