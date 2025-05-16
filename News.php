<?php 
include_once('lib/config.php');
include_once('lib/Session.php');
include_once('lib/database.php');
include_once('lib/format.php');

// Kiểm tra quyền admin
Session::checkSession(); 

// Tạo đối tượng xử lý database
$db = new Database();

// Xử lý xóa bài viết
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $db->delete("DELETE FROM tin_tuc WHERE id = $delete_id");
    header("Location: News.php");
    exit();
}

// Xử lý thêm hoặc chỉnh sửa bài viết
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tieu_de = $noi_dung = $tac_gia = $anh_bia = "";

// Nếu là chỉnh sửa hoặc xem bài viết, lấy dữ liệu bài viết cũ
if (($action == 'edit' || $action == 'view') && $id > 0) {
    $result = $db->select("SELECT * FROM tin_tuc WHERE id = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        $tieu_de = $row['tieu_de'];
        $noi_dung = $row['noi_dung'];
        $tac_gia = $row['tac_gia'];
        $anh_bia = $row['anh_bia'];
    } else {
        echo "Không tìm thấy bài viết với ID: $id";
    }
}


// Xử lý lưu bài viết khi nhấn nút "Lưu"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieu_de = $db->link->real_escape_string($_POST['tieu_de']); // Thoát ký tự đặc biệt
    $noi_dung = $db->link->real_escape_string($_POST['noi_dung']); 
    $tac_gia = $db->link->real_escape_string($_POST['tac_gia']); 
    $action = $_POST['action'];
    $anh_bia_file = $anh_bia;

    // Xử lý ảnh bìa (nếu có)
    if (isset($_FILES['anh_bia']) && $_FILES['anh_bia']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_tmp = $_FILES['anh_bia']['tmp_name'];
        $file_name = basename($_FILES['anh_bia']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $anh_bia_file = $file_name; // Lưu tên file ảnh
        } else {
            echo "Lỗi tải ảnh lên.";
        }
    }

    // Thực hiện thêm hoặc chỉnh sửa bài viết
    if ($action == 'add') {
        // Thêm bài viết mới
        $sql = "INSERT INTO tin_tuc (tieu_de, noi_dung, tac_gia, anh_bia) 
                VALUES ('$tieu_de', '$noi_dung', '$tac_gia', '$anh_bia_file')";
        $db->insert($sql);
    } elseif ($action == 'edit' && $id > 0) {
        // Chỉnh sửa bài viết
        $sql = "UPDATE tin_tuc SET tieu_de = '$tieu_de', noi_dung = '$noi_dung', tac_gia = '$tac_gia'";
        if ($anh_bia_file) {
            $sql .= ", anh_bia = '$anh_bia_file'";
        }
        $sql .= " WHERE id = $id";
        $db->update($sql);
    }

    header("Location: news.php");
    exit();
}

// Lấy thông tin tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : "";
$search = $db->link->real_escape_string($search); 

// Lấy thông tin phân trang
$limit = 4; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Truy vấn tổng số bản ghi
$total_query = "
    SELECT COUNT(*) AS total
    FROM tin_tuc
    WHERE tieu_de LIKE '%$search%'
";
$total_result = $db->select($total_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Truy vấn danh sách bài viết
$query = "
    SELECT id, tieu_de, tac_gia, ngay_tao, anh_bia
    FROM tin_tuc
    WHERE tieu_de LIKE '%$search%'
    ORDER BY ngay_tao DESC
    LIMIT $limit OFFSET $offset
";
$result = $db->select($query);

include 'header.php';  
include 'sidebar.php';  
?>

<!-- Thêm CSS cho bảng -->
<div id="main-content">
    <?php if ($action == 'add' || $action == 'edit'): ?>
        <!-- Form thêm hoặc chỉnh sửa bài viết -->
        <div id="news-form">
            <h2><?= $action == 'add' ? 'Thêm bài viết' : 'Chỉnh sửa bài viết' ?></h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $action ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <label>Tiêu đề:</label><br>
                <input type="text" name="tieu_de" value="<?= htmlspecialchars($tieu_de) ?>" required><br>

                <label>Nội dung:</label><br>
                <textarea name="noi_dung" required><?= htmlspecialchars($noi_dung) ?></textarea><br>

                <label>Tác giả:</label><br>
                <input type="text" name="tac_gia" value="<?= htmlspecialchars($tac_gia) ?>" required><br><br>

                <!-- Trường cho ảnh bìa -->
                <label>Ảnh bìa:</label><br>
                <input type="file" name="anh_bia"><br>
                <?php if ($anh_bia): ?>
                    <img src="uploads/<?= htmlspecialchars($anh_bia) ?>" width="100" alt="Ảnh bìa cũ"><br>
                <?php endif; ?><br>

                <button type="submit">Lưu</button>
                <a href="news.php" style="color:red">Hủy</a>
            </form>
        </div>
    <?php elseif ($action == 'view'): ?>
        <!-- Chi tiết bài viết -->
        <div id="news-detail">
            <h2>Xem bài viết</h2>
            <?php if ($anh_bia): ?>
                <p><strong>Ảnh bìa:</strong> <img src="uploads/<?= htmlspecialchars($anh_bia) ?>" width="100"></p>
            <?php endif; ?>
            <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($tieu_de) ?></p>
            <p><strong>Tác giả:</strong> <?= htmlspecialchars($tac_gia) ?></p>
            <p><strong>Nội dung:</strong> <?= nl2br(htmlspecialchars($noi_dung)) ?></p>
            <a href="news.php" style="float: right; margin-left: 20px;">&#10149; Quay lại</a>
    
        </div>
    <?php else: ?>
        <!-- Danh sách bài viết -->
        <div id="news-list">
            <h2 style="margin-top: 20px; padding-bottom: 20px;">Danh sách bài viết</h2>
            <a href="news.php?action=add" class="btn btn-add">Thêm bài viết mới</a>

            <!-- Form tìm kiếm -->
            <form method="GET" action="news.php" style="margin-bottom: 20px;">
                <input type="text" name="search" placeholder="Tìm kiếm bài viết..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Tìm kiếm</button>
            </form>

            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th>Ảnh bìa</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['tieu_de']) ?></td>
                                <td><?= htmlspecialchars($row['tac_gia']) ?></td>
                                <td>
                                    <?php if ($row['anh_bia']): ?>
                                        <img src="uploads/<?= htmlspecialchars($row['anh_bia']) ?>" width="100" alt="Ảnh bìa">
                                    <?php else: ?>
                                        Không có ảnh
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['ngay_tao']) ?></td>
                                <td>
                                    <a href="news.php?action=view&id=<?= $row['id'] ?>" class="btn btn-view">Xem</a> | 
                                    <a href="news.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-edit">Sửa</a> | 
                                    <a href="news.php?delete_id=<?= $row['id'] ?>" 
                                        onclick="return confirm('Bạn có chắc muốn xóa bài viết này không?');" class="btn btn-delete">Xóa</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Không có bài viết nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="news.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                       class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
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
