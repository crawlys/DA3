<?php
include "lib/database.php";
include "lib/format.php";
include "lib/session.php";

Session::checkSession();

$db = new Database();
$fm = new Format();

// Xử lý xóa (ẩn gói nếu đã có người dùng mua)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Kiểm tra xem gói đã có người mua chưa
    $checkPurchase = $db->select("SELECT * FROM thanhtoan WHERE MaGoi = $delete_id");

    if ($checkPurchase) {
        // Nếu đã có người mua, ẩn gói dịch vụ bằng cách đổi trạng thái thành 0
        $db->update("UPDATE goidichvu SET TrangThai = 0 WHERE MaGoi = $delete_id");
        $message = "Gói dịch vụ này đã có người mua. Gói sẽ được ẩn.";
    } else {
        // Nếu không có người mua, xóa gói dịch vụ
        $db->delete("DELETE FROM goidichvu WHERE MaGoi = $delete_id");
        $message = "Gói dịch vụ đã bị xóa.";
    }

    // Hiển thị thông báo
    echo "<script>alert('$message'); window.location.href = 'Services.php';</script>";
    exit();
}


// Xử lý thêm/sửa gói
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$TenGoi = $MoTa = $Gia = $ThoiGianSuDung = "";
$TrangThai = 1;

if ($action === 'edit' && $id > 0) {
    $result = $db->select("SELECT * FROM goidichvu WHERE MaGoi = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        $TenGoi = $row['TenGoi'];
        $MoTa = $row['MoTa'];
        $Gia = $row['Gia'];
        $ThoiGianSuDung = $row['ThoiGianSuDung'];
        $TrangThai = $row['TrangThai'];
    } else {
        echo "Gói dịch vụ không tồn tại.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TenGoi = $_POST['TenGoi'];
    $MoTa = $_POST['MoTa'];
    $Gia = $_POST['Gia'];
    $ThoiGianSuDung = $_POST['ThoiGianSuDung'];
    $TrangThai = $_POST['TrangThai'];

    if ($action === 'add') {
        $db->insert("INSERT INTO goidichvu (TenGoi, MoTa, Gia, ThoiGianSuDung, TrangThai) 
                     VALUES ('$TenGoi', '$MoTa', '$Gia', '$ThoiGianSuDung', '$TrangThai')");
    } elseif ($action === 'edit' && $id > 0) {
        $db->update("UPDATE goidichvu 
                     SET TenGoi = '$TenGoi', MoTa = '$MoTa', Gia = '$Gia', 
                         ThoiGianSuDung = '$ThoiGianSuDung', TrangThai = '$TrangThai' 
                     WHERE MaGoi = $id");
    }

    header("Location: Services.php");
    exit();
}

// Lấy danh sách gói dịch vụ
$query = "SELECT * FROM goidichvu";
$result = $db->select($query);

include "header.php";
include "sidebar.php";
?>
<div id="main-content">
<!-- Nội dung chính -->

<?php if ($action === 'add' || $action === 'edit'): ?>
    <div id="news-form">
        <h2><?= $action === 'add' ? 'Thêm gói dịch vụ' : 'Sửa gói dịch vụ' ?></h3>
        <form method="POST">
            <label for="TenGoi">Tên Gói:</label><br>
            <input type="text" name="TenGoi" value="<?= htmlspecialchars($TenGoi) ?>" required><br>

            <label for="MoTa">Mô Tả:</label><br>
            <textarea name="MoTa" required><?= htmlspecialchars($MoTa) ?></textarea><br>

            <label for="Gia">Giá:</label><br>
            <input type="number" name="Gia" step="0.01" value="<?= $Gia ?>" required><br>

            <label for="ThoiGianSuDung">Thời Gian Sử Dụng (ngày):</label><br>
            <input type="number" name="ThoiGianSuDung" value="<?= $ThoiGianSuDung ?>" required><br>

            <label for="TrangThai">Trạng Thái:</label><br>
            <select name="TrangThai">
                <option value="1" <?= $TrangThai == 1 ? 'selected' : '' ?>>Kích hoạt</option>
                <option value="0" <?= $TrangThai == 0 ? 'selected' : '' ?>>Ẩn</option>
            </select><br><br>

            <button type="submit">Lưu</button>
            <a href="Services.php" style="color: red;">Hủy</a>
        </form>
    </div>
<?php else: ?>
    <h2 style="margin-top: 20px; padding-bottom: 20px;">Quản lý gói dịch vụ</h2>
    <a href="Services.php?action=add" style = "margin-bottom: 20px;" class="btn btn-add">Thêm gói mới</a>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Gói</th>
                <th>Mô Tả</th>
                <th>Giá</th>
                <th>Thời Gian Sử Dụng</th>
                <th>Trạng Thái</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaGoi'] ?></td>
                        <td><?= htmlspecialchars($row['TenGoi']) ?></td>
                        <td><?= htmlspecialchars($row['MoTa']) ?></td>
                        <td><?= number_format($row['Gia'], 2) ?> VND</td>
                        <td><?= $row['ThoiGianSuDung'] ?> ngày</td>
                        <td><?= $row['TrangThai'] ? 'Kích hoạt' : 'Ẩn' ?></td>
                        <td>
                            <a href="services.php?action=edit&id=<?= $row['MaGoi'] ?>" class="btn btn-edit">Sửa</a> | 
                            <a href="services.php?delete_id=<?= $row['MaGoi'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')"class="btn btn-delete">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Không có gói dịch vụ nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
<style>
     form button {
    padding: 10px 20px;
    border: none;
    background-color: gray;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}

form button:hover {
    background-color: green;
}
</style>
