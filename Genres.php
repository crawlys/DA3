<?php
include "lib/database.php";
include "lib/session.php";

Session::checkSession();

$db = new Database();

// Items per page
$limit = 8;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = max($page, 1);

// Xử lý xóa thể loại
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check for related records
    $related_query = "SELECT COUNT(*) AS count FROM phim_theloai WHERE MaTheLoai = $delete_id"; // Replace `some_related_table` with the actual table
    $related_result = $db->select($related_query);
    $related_count = $related_result ? $related_result->fetch_assoc()['count'] : 0;

    if ($related_count > 0) {
        // If there are related records, prompt to hide instead of delete
        $db->update("UPDATE theloai SET TrangThai = 0 WHERE MaTheLoai = $delete_id");
        echo "<script>alert('Thể loại này đã có liên kết, không thể xóa. Trạng thái đã được chuyển sang Ẩn.');</script>";
    } else {
        // If no related records, proceed with deletion
        $db->delete("DELETE FROM theloai WHERE MaTheLoai = $delete_id");
    }

    header("Location: Genres.php");
    exit();
}

// Xử lý thêm/sửa
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$TenTheLoai = "";
$TrangThai = 1;

if ($action === 'edit' && $id > 0) {
    $result = $db->select("SELECT * FROM theloai WHERE MaTheLoai = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        $TenTheLoai = $row['TenTheLoai'];
        $TrangThai = $row['TrangThai'];
    } else {
        echo "Thể loại không tồn tại.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TenTheLoai = $_POST['TenTheLoai'];
    $TrangThai = isset($_POST['TrangThai']) ? intval($_POST['TrangThai']) : 1;

    if ($action === 'add') {
        $db->insert("INSERT INTO theloai (TenTheLoai, TrangThai) VALUES ('$TenTheLoai', $TrangThai)");
    } elseif ($action === 'edit' && $id > 0) {
        $db->update("UPDATE theloai SET TenTheLoai = '$TenTheLoai', TrangThai = $TrangThai WHERE MaTheLoai = $id");
    }

    header("Location: Genres.php");
    exit();
}

// Lấy danh sách thể loại với tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$total_query = "SELECT COUNT(*) AS total FROM theloai WHERE TenTheLoai LIKE '%$search%'";
$total_result = $db->select($total_query);
$total_rows = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_rows / $limit);
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM theloai WHERE TenTheLoai LIKE '%$search%' LIMIT $limit OFFSET $offset";
$result = $db->select($query);

include "header.php";
include "sidebar.php";
?>

<div id="main-content">
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div id="news-form">
            <h2><?= $action === 'add' ? 'Thêm Thể Loại' : 'Sửa Thể Loại' ?></h3>
            <form method="POST">
                <label for="TenTheLoai">Tên Thể Loại:</label><br>
                <input type="text" name="TenTheLoai" value="<?= htmlspecialchars($TenTheLoai) ?>" required><br>

                <label for="TrangThai">Trạng Thái:</label><br>
                <select name="TrangThai">
                    <option value="1" <?= $TrangThai == 1 ? 'selected' : '' ?>>Kích hoạt</option>
                    <option value="0" <?= $TrangThai == 0 ? 'selected' : '' ?>>Ẩn</option>
                </select><br><br>

                <button type="submit">Lưu</button>
                <a href="Genres.php" style="color: red;">Hủy</a>
            </form>
        </div>
    <?php else: ?>
        <h2 style="padding-top: 20px; padding-bottom: 20px;">Quản lý thể loại</h2>
        <a href="Genres.php?action=add" class="btn btn-add">Thêm Thể Loại</a>

        <!-- Form tìm kiếm -->
        <form method="GET" action="Genres.php" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Tìm kiếm thể loại" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Thể Loại</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['MaTheLoai'] ?></td>
                            <td><?= htmlspecialchars($row['TenTheLoai']) ?></td>
                            <td><?= $row['TrangThai'] ? 'Kích hoạt' : 'Ẩn' ?></td>
                            <td>
                                <a href="Genres.php?action=edit&id=<?= $row['MaTheLoai'] ?>" class="btn btn-edit">Sửa</a> | 
                                <a href="Genres.php?delete_id=<?= $row['MaTheLoai'] ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Không có thể loại nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="Genres.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>">
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
