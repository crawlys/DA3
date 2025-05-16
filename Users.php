<?php  
include "lib/database.php";
include "lib/session.php";

Session::checkSession();

$db = new Database();

// Xử lý xóa hoặc ẩn tài khoản
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    if ($delete_id == 6) {
        echo "Tài khoản quản lý không thể xóa hoặc ẩn.";
    } else {
        // Kiểm tra liên kết với bảng thanhtoan và binhluan
        $checkPayment = $db->select("SELECT * FROM thanhtoan WHERE MaNguoiDung = $delete_id");
        $checkComment = $db->select("SELECT * FROM binhluan WHERE MaNguoiDung = $delete_id");

        if ($checkPayment || $checkComment) {
            // Cập nhật trạng thái tài khoản là ẩn
            $db->update("UPDATE nguoidung SET TrangThai = 0 WHERE MaNguoiDung = $delete_id");
            echo "Tài khoản đã được ẩn thay vì xóa do có liên kết.";
        } else {
            // Xóa tài khoản không liên kết
            $db->delete("DELETE FROM nguoidung WHERE MaNguoiDung = $delete_id");
            echo "Tài khoản đã được xóa.";
        }
    }
    header("Location: Users.php");
    exit();
}

// Xử lý sửa vai trò
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['edit_id']);
    $VaiTro = $_POST['VaiTro'];

    if ($edit_id == 6) {
        echo "Tài khoản quản lý không thể sửa.";
    } else {
        $db->update("UPDATE nguoidung SET VaiTro = '$VaiTro' WHERE MaNguoiDung = $edit_id");
    }
    header("Location: Users.php");
    exit();
}

// Lấy thông tin tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : "";
$search = $db->link->real_escape_string($search); // Xử lý ký tự đặc biệt

// Lấy thông tin phân trang
$limit = 5; // Số lượng bản ghi trên mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Truy vấn tổng số bản ghi
$total_query = "
    SELECT COUNT(*) AS total
    FROM nguoidung
    LEFT JOIN thanhtoan ON nguoidung.MaNguoiDung = thanhtoan.MaNguoiDung
    LEFT JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi
    WHERE nguoidung.TenNguoiDung LIKE '%$search%' AND nguoidung.TrangThai = 1
";
$total_result = $db->select($total_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Truy vấn danh sách người dùng (chỉ trạng thái 1)
$query = "
    SELECT 
        nguoidung.MaNguoiDung,
        nguoidung.TenNguoiDung,
        nguoidung.Email,
        nguoidung.Avatar,
        goidichvu.TenGoi,
        thanhtoan.NgayThanhToan,
        DATE_ADD(thanhtoan.NgayThanhToan, INTERVAL goidichvu.ThoiGianSuDung DAY) AS NgayHetHanVIP,
        nguoidung.VaiTro
    FROM nguoidung
    LEFT JOIN thanhtoan ON nguoidung.MaNguoiDung = thanhtoan.MaNguoiDung
    LEFT JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi
    WHERE nguoidung.TenNguoiDung LIKE '%$search%' AND nguoidung.TrangThai = 1
    LIMIT $limit OFFSET $offset
";
$result = $db->select($query);

include "header.php";
include "sidebar.php";
?>

<div id="main-content">
    <h2 style="padding-top: 20px; padding-bottom: 20px;">Quản lý người dùng</h2>

    <!-- Form tìm kiếm -->
    <form method="GET" action="Users.php" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Tìm kiếm người dùng..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Tìm kiếm</button>
    </form>

    <!-- Bảng danh sách người dùng -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Người Dùng</th>
                <th>Email</th>
                <th>Avatar</th>
                <th>Gói Dịch Vụ</th>
                <th>Ngày Bắt Đầu VIP</th>
                <th>Ngày Hết Hạn VIP</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaNguoiDung'] ?></td>
                        <td><?= htmlspecialchars($row['TenNguoiDung']) ?></td>
                        <td><?= htmlspecialchars($row['Email']) ?></td>
                        <td>
                            <?php if ($row['Avatar']): ?>
                                <img src="../DA3/images/user/<?= htmlspecialchars($row['Avatar']) ?>" alt="Avatar" width="50" height="50">
                            <?php else: ?>
                                Không có ảnh
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['TenGoi']) ?: "Không đăng ký" ?></td>
                        <td><?= $row['NgayThanhToan'] ?: "N/A" ?></td>
                        <td><?= $row['NgayHetHanVIP'] ?: "N/A" ?></td>
                        <td>
                            <?php if ($row['MaNguoiDung'] != 6): ?>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="edit_id" value="<?= $row['MaNguoiDung'] ?>">
                                    <select name="VaiTro">
                                        <option value="User" <?= $row['VaiTro'] == 'User' ? 'selected' : '' ?>>User</option>
                                        <option value="Admin" <?= $row['VaiTro'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn-edit">Sửa</button>
                                </form>
                                <a href="Users.php?delete_id=<?= $row['MaNguoiDung'] ?>" 
                                    onclick="return confirm('Bạn có chắc chắn muốn xóa?')" 
                                    class="btn btn-delete">Xóa</a>
                            <?php else: ?>
                                Tài khoản quản lý
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">Không tìm thấy người dùng nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

<!-- Phân trang -->
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="Users.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
           class="<?= $i == $page ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>

</div>
<style>
    form input[type="text"], form button {
    margin-right: 10px;
}

form input[type="text"] {
    padding: 10px;
    width: 300px;
    border: 1px solid #ccc;
    border-radius: 5px;
} 

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
