<?php 
include_once('lib/session.php');
include_once('lib/database.php');
include_once('lib/format.php');

// Kiểm tra quyền admin
Session::checkSession(); 

// Tạo đối tượng xử lý database và format
$db = new Database();
$fm = new Format();

// Xử lý xóa bình luận
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM binhluan WHERE MaBinhLuan = $delete_id";
    $db->delete($delete_query);
    
    header("Location: comments.php");
    exit();
}

// Tìm kiếm bình luận
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Lấy danh sách bình luận từ DB với tìm kiếm
$query = "
    SELECT binhluan.MaBinhLuan, binhluan.NoiDung, binhluan.NgayTao, 
           nguoidung.TenNguoiDung, phim.TieuDe
    FROM binhluan
    INNER JOIN nguoidung ON binhluan.MaNguoiDung = nguoidung.MaNguoiDung
    INNER JOIN phim ON binhluan.MaPhim = phim.MaPhim
    WHERE binhluan.NoiDung LIKE '%$search%' 
       OR nguoidung.TenNguoiDung LIKE '%$search%' 
       OR phim.TieuDe LIKE '%$search%'
    ORDER BY binhluan.NgayTao DESC";

// Phân trang
$limit = 8; // Số bình luận mỗi trang
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$query .= " LIMIT $limit OFFSET $offset";
$result = $db->select($query);

// Tính tổng số trang
$total_query = "
    SELECT COUNT(*) AS total 
    FROM binhluan
    INNER JOIN nguoidung ON binhluan.MaNguoiDung = nguoidung.MaNguoiDung
    INNER JOIN phim ON binhluan.MaPhim = phim.MaPhim
    WHERE binhluan.NoiDung LIKE '%$search%' 
       OR nguoidung.TenNguoiDung LIKE '%$search%' 
       OR phim.TieuDe LIKE '%$search%'";
$total_result = $db->select($total_query);
$total_comments = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_comments / $limit);

include 'header.php';  // Bao gồm header
include 'sidebar.php'; // Bao gồm sidebar
?>

<div id="main-content">
    <div id="comments-list">
        <h2 style="margin-top: 20px; padding-bottom: 20px;">Danh sách bình luận</h2>
        
        <!-- Form tìm kiếm -->
        <form method="get" action="comments.php" class="search-form" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>" />
            <button type="submit" class="btn-search">Tìm kiếm</button>
        </form>

        <!-- Bảng danh sách bình luận -->
        <table class="table-comments">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Người dùng</th>
                    <th>Phim</th>
                    <th>Nội dung</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['MaBinhLuan'] ?></td>
                            <td><?= htmlspecialchars($row['TenNguoiDung']) ?></td>
                            <td><?= htmlspecialchars($row['TieuDe']) ?></td>
                            <td><?= htmlspecialchars($row['NoiDung']) ?></td>
                            <td><?= $fm->formatDate($row['NgayTao']) ?></td>
                            <td>
                                <a href="comments.php?delete_id=<?= $row['MaBinhLuan'] ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Bạn có chắc muốn xóa bình luận này không?');" >Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Không có bình luận nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Phân trang -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="comments.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                   class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
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