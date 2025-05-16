<?php
include "lib/database.php";
include "lib/session.php";

Session::checkSession();

$db = new Database();

// Items per page
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max($page, 1);

// Xử lý xóa thể loại
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check for related records
    $related_query = "SELECT COUNT(*) AS count FROM phim_dienvien WHERE MaDienVien = $delete_id"; // Replace `some_related_table` with the actual table
    $related_result = $db->select($related_query);
    $related_count = $related_result ? $related_result->fetch_assoc()['count'] : 0;

    if ($related_count > 0) {
        // If there are related records, prompt to hide instead of delete
        $db->update("UPDATE dienvien SET TrangThai = 0 WHERE MaDienVien = $delete_id");
        echo "<script>alert('Thể loại này đã có liên kết, không thể xóa. Trạng thái đã được chuyển sang Ẩn.');</script>";
    } else {
        // If no related records, proceed with deletion
        $db->delete("DELETE FROM dienvien WHERE MaDienVien = $delete_id");
    }

    header("Location: Actors.php");
    exit();
}
// Xử lý thêm/sửa
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$TenDienVien = "";
$AnhDaiDien = "";
$TrangThai = 1;

if ($action === 'edit' && $id > 0) {
    $result = $db->select("SELECT * FROM dienvien WHERE MaDienVien = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        $TenDienVien = $row['TenDienVien'];
        $AnhDaiDien = $row['AnhDaiDien'];
        $TrangThai = $row['TrangThai'];
    } else {
        echo "Diễn viên không tồn tại.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TenDienVien = $_POST['TenDienVien'];
    $TrangThai = isset($_POST['TrangThai']) ? intval($_POST['TrangThai']) : 1;

    // Xử lý file upload
    if (!empty($_FILES['AnhDaiDien']['name'])) {
        $target_dir = "uploads/";
        $file_name = basename($_FILES['AnhDaiDien']['name']);
        $target_file = $target_dir . time() . "_" . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Kiểm tra loại file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            die("Chỉ chấp nhận các file ảnh: JPG, JPEG, PNG, GIF.");
        }

        // Di chuyển file vào thư mục uploads
        if (!move_uploaded_file($_FILES['AnhDaiDien']['tmp_name'], $target_file)) {
            die("Không thể tải ảnh lên.");
        }

        // Xóa ảnh cũ nếu sửa
        if ($action === 'edit' && !empty($AnhDaiDien) && file_exists($AnhDaiDien)) {
            unlink($AnhDaiDien);
        }
    } else {
        $target_file = $AnhDaiDien;
    }

    if ($action === 'add') {
        $db->insert("INSERT INTO dienvien (TenDienVien, AnhDaiDien, TrangThai) VALUES ('$TenDienVien', '$target_file', $TrangThai)");
    } elseif ($action === 'edit' && $id > 0) {
        $db->update("UPDATE dienvien SET TenDienVien = '$TenDienVien', AnhDaiDien = '$target_file', TrangThai = $TrangThai WHERE MaDienVien = $id");
    }

    header("Location: Actors.php");
    exit();
}

// Lấy danh sách diễn viên với tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$total_query = "SELECT COUNT(*) AS total FROM dienvien WHERE TenDienVien LIKE '%$search%'";
$total_result = $db->select($total_query);
$total_rows = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $limit);
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM dienvien WHERE TenDienVien LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result = $db->select($query);

include "header.php";
include "sidebar.php";
?>

<div id="main-content">
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div id="news-form">
            <h2><?= $action === 'add' ? 'Thêm Diễn Viên' : 'Sửa Diễn Viên' ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="TenDienVien">Tên Diễn Viên:</label><br>
                <input type="text" name="TenDienVien" value="<?= htmlspecialchars($TenDienVien) ?>" required><br>

                <label for="AnhDaiDien">Ảnh Đại Diện:</label><br>
                <?php if ($AnhDaiDien): ?>
                    <img src="<?= htmlspecialchars($AnhDaiDien) ?>" alt="Ảnh Đại Diện" width="100" height="100"><br>
                <?php endif; ?>
                <input type="file" name="AnhDaiDien" accept="asset/imgs/*" <?= $action === 'add' ? 'required' : '' ?>><br><br>
                <label for="TrangThai">Trạng Thái:</label><br>
                <select name="TrangThai">
                    <option value="1" <?= $TrangThai == 1 ? 'selected' : '' ?>>Kích hoạt</option>
                    <option value="0" <?= $TrangThai == 0 ? 'selected' : '' ?>>Ẩn</option>
                </select><br><br>

                <button type="submit">Lưu</button>
                <a href="Actors.php" style="color: red;">Hủy</a>
            </form>
        </div>
    <?php else: ?>
        <h2 style="margin-top: 20px; padding-bottom: 20px;">Quản lý Diễn Viên</h2>
        <a href="Actors.php?action=add" class="btn btn-add">Thêm Diễn Viên</a>

        <!-- Form tìm kiếm -->
        <form method="GET" action="Actors.php" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Tìm kiếm diễn viên" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Diễn Viên</th>
                    <th>Ảnh Đại Diện</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['MaDienVien'] ?></td>
                            <td><?= htmlspecialchars($row['TenDienVien']) ?></td>
                            <td>
                                <?php if ($row['AnhDaiDien']): ?>
                                    <img src="<?= htmlspecialchars($row['AnhDaiDien']) ?>" alt="Ảnh Đại Diện" width="50" height="50">
                                <?php else: ?>
                                    Không có ảnh
                                <?php endif; ?>
                            </td>
                            <td><?= $row['TrangThai'] ? 'Kích hoạt' : 'Ẩn' ?></td>
                            <td>
                                <a href="Actors.php?action=edit&id=<?= $row['MaDienVien'] ?>" class="btn btn-edit">Sửa</a> | 
                                <a href="Actors.php?delete_id=<?= $row['MaDienVien'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a> 
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Không có diễn viên nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="Actors.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
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
