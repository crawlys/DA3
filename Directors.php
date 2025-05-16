<?php
include "lib/database.php";
include "lib/format.php";
include "lib/session.php";

Session::checkSession();

$db = new Database();
$fm = new Format();

// Xử lý xóa
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Kiểm tra xem đạo diễn có phim nào không
    $checkQuery = "SELECT COUNT(*) AS phim_count FROM phim WHERE MaDaoDien = $delete_id";
    $checkResult = $db->select($checkQuery);
    $checkRow = $checkResult->fetch_assoc();

    if ($checkRow['phim_count'] > 0) {
        // Nếu đạo diễn có phim, cập nhật trạng thái thành 0
        $db->update("UPDATE daodien SET trang_thai = 0 WHERE MaDaoDien = $delete_id");
    } else {
        // Nếu không có phim, xóa đạo diễn
        $result = $db->select("SELECT AnhDaiDien FROM daodien WHERE MaDaoDien = $delete_id");
        if ($result) {
            $row = $result->fetch_assoc();
            if (!empty($row['AnhDaiDien']) && file_exists($row['AnhDaiDien'])) {
                unlink($row['AnhDaiDien']);
            }
        }
        $db->delete("DELETE FROM daodien WHERE MaDaoDien = $delete_id");
    }

    header("Location: Directors.php");
    exit();
}

// Xử lý tìm kiếm và phân trang
$search = isset($_GET['search']) ? $_GET['search'] : '';
$perPage = 5; // Số bản ghi mỗi trang
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Trang hiện tại
$start = ($page - 1) * $perPage; // Vị trí bắt đầu của trang hiện tại

// Câu truy vấn để lấy tất cả đạo diễn (kể cả trạng thái 1 và 0)
$query = "SELECT MaDaoDien, TenDaoDien, AnhDaiDien, trang_thai FROM daodien WHERE TenDaoDien LIKE '%$search%' LIMIT $start, $perPage";
$result = $db->select($query);

// Tính số trang
$query = "SELECT COUNT(*) AS total FROM daodien WHERE TenDaoDien LIKE '%$search%'";
$totalResult = $db->select($query);
$totalRow = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRow / $perPage);

// Xử lý thêm/sửa đạo diễn
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$TenDaoDien = "";
$AnhDaiDien = "";
$trang_thai = 1;

if ($action === 'edit' && $id > 0) {
    $result = $db->select("SELECT * FROM daodien WHERE MaDaoDien = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        $TenDaoDien = $row['TenDaoDien'];
        $AnhDaiDien = $row['AnhDaiDien'];
        $trang_thai = $row['trang_thai'];

    } else {
        echo "Đạo diễn không tồn tại.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $TenDaoDien = $_POST['TenDaoDien'];
    $TrangThai = isset($_POST['trang_thai']) ? intval($_POST['trang_thai']) : 1;

    // Kiểm tra và xử lý file upload
    if (!empty($_FILES['AnhDaiDien']['name'])) {  
        $target_dir = "uploads/";
        $file_name = basename($_FILES['AnhDaiDien']['name']);
        $target_file = $target_dir . time() . "_" . $file_name; // Tạo tên file duy nhất
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

        // Xóa ảnh cũ khi chỉnh sửa
        if ($action === 'edit' && !empty($AnhDaiDien) && file_exists($AnhDaiDien)) {
            unlink($AnhDaiDien);
        }
    } else {
        $target_file = $AnhDaiDien; // Giữ nguyên link cũ nếu không upload file mới
    }

    if ($action === 'add') {
        $db->insert("INSERT INTO daodien (TenDaoDien, AnhDaiDien, trang_thai) VALUES ('$TenDaoDien', '$target_file', '$TrangThai')");
    } elseif ($action === 'edit' && $id > 0) {
        $db->update("UPDATE daodien SET TenDaoDien = '$TenDaoDien', AnhDaiDien = '$target_file', trang_thai = $TrangThai WHERE MaDaoDien = $id");
    }

    header("Location: Directors.php");
    exit();
}

include "header.php";
include "sidebar.php";
?>

<div id="main-content">
    <!-- Thêm/Sửa đạo diễn -->
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div id="news-form">
            <h2><?= $action === 'add' ? 'Thêm Đạo Diễn' : 'Sửa Đạo Diễn' ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <label for="TenDaoDien">Tên Đạo Diễn:</label><br>
                <input type="text" name="TenDaoDien" value="<?= htmlspecialchars($TenDaoDien) ?>" required><br>

                <label for="AnhDaiDien">Ảnh Đại Diện:</label><br>
                <?php if ($AnhDaiDien): ?>
                    <img src="<?= htmlspecialchars($AnhDaiDien) ?>" alt="Ảnh Đại Diện" width="100" height="100"><br>
                <?php endif; ?>
                <input type="file" name="AnhDaiDien" accept="asset/imgs/*" <?= $action === 'add' ? 'required' : '' ?>><br><br>

                <label for="trang_thai">Trạng Thái:</label><br>
                <select name="trang_thai">
                    <option value="1" <?= $trang_thai == 1 ? 'selected' : '' ?>>Kích hoạt</option>
                    <option value="0" <?= $trang_thai == 0 ? 'selected' : '' ?>>Ẩn</option>
                </select><br><br>

                <button type="submit">Lưu</button>
                <a href="Directors.php" style="color: red;">Hủy</a>
            </form>
        </div>
    <?php else: ?>
        <h2 style="margin-top: 20px; padding-bottom: 20px;">Quản lý Đạo Diễn</h2>
        <a href="Directors.php?action=add" class="btn btn-add">Thêm Đạo Diễn</a>

        <!-- Form tìm kiếm -->
        <form method="GET" action="Directors.php">
            <input type="text" name="search" placeholder="Tìm kiếm đạo diễn" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit">Tìm kiếm</button>
        </form>

        <!-- Danh sách đạo diễn -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Đạo Diễn</th>
                    <th>Hình Ảnh</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result): ?>
<?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['MaDaoDien'] ?></td>
                            <td><?= htmlspecialchars($row['TenDaoDien']) ?></td>
                            <td>
                                <?php if ($row['AnhDaiDien']): ?>
                                    <img src="<?= htmlspecialchars($row['AnhDaiDien']) ?>" alt="Ảnh Đại Diện" width="50" height="50">
                                <?php else: ?>
                                    Không có ảnh
                                <?php endif; ?>
                            </td>
                            <td><?= $row['trang_thai'] ? 'Kích hoạt' : 'Ẩn' ?></td>
                            <td>
                                <a href="Directors.php?action=edit&id=<?= $row['MaDaoDien'] ?>" class="btn btn-edit">Sửa</a> | 
                                <a href="Directors.php?delete_id=<?= $row['MaDaoDien'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa?')" class="btn btn-delete">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Không có đạo diễn nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="Directors.php?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
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