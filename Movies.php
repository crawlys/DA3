<?php 
include_once('lib/config.php');
include_once('lib/Session.php');
include_once('lib/database.php');
include_once('lib/format.php');

// Kiểm tra quyền admin
Session::checkSession();

// Tạo đối tượng xử lý database
$db = new Database();

// Xử lý xóa thể loại
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Kiểm tra liên kết với các bảng khác (phim_theloai, phim_dienvien)
    // Gỡ liên kết với các thể loại phim
    $delete_related_genres = "DELETE FROM phim_theloai WHERE MaPhim = $delete_id";
    $db->delete($delete_related_genres);

    // Gỡ liên kết với các diễn viên phim
    $delete_related_actors = "DELETE FROM phim_dienvien WHERE MaPhim = $delete_id";
    $db->delete($delete_related_actors);

    // Kiểm tra nếu phim có liên kết với thể loại hoặc diễn viên, chuyển trạng thái sang Ẩn
    $related_query = "
        SELECT COUNT(*) AS count 
        FROM phim_theloai pt
        WHERE pt.MaPhim = $delete_id
    ";
    $related_result = $db->select($related_query);
    $related_count = $related_result->fetch_assoc()['count'];

    $related_query_actors = "
        SELECT COUNT(*) AS count 
        FROM phim_dienvien pd
        WHERE pd.MaPhim = $delete_id
    ";
    $related_result_actors = $db->select($related_query_actors);
    $related_count_actors = $related_result_actors->fetch_assoc()['count'];

    // Nếu phim có liên kết với thể loại hoặc diễn viên, ẩn phim đi thay vì xóa
    if ($related_count > 0 || $related_count_actors > 0) {
        // Cập nhật trạng thái phim thành Ẩn
        $db->update("UPDATE phim SET TrangThai = 0 WHERE MaPhim = $delete_id");
        echo "<script>alert('Phim này có liên kết với thể loại hoặc diễn viên, không thể xóa. Trạng thái đã được chuyển sang Ẩn.');</script>";
    } else {
        // Nếu không có liên kết, xóa phim
        $db->delete("DELETE FROM phim WHERE MaPhim = $delete_id");
        echo "<script>alert('Phim đã được xóa thành công.');</script>";
    }

    header("Location: movies.php");
    exit();
}

// Xử lý chuyển đổi trạng thái
if (isset($_GET['action']) && $_GET['action'] == 'toggle_status' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Lấy thông tin trạng thái của phim
    $status_query = "SELECT TrangThai FROM phim WHERE MaPhim = $id";
    $status_result = $db->select($status_query);
    if ($status_result && $status_result->num_rows > 0) {
        $status = $status_result->fetch_assoc()['TrangThai'];

        // Đổi trạng thái phim
        $new_status = $status == 1 ? 0 : 1;  // Nếu trạng thái hiện tại là 1, chuyển thành 0 và ngược lại
        $db->update("UPDATE phim SET TrangThai = $new_status WHERE MaPhim = $id");
        echo "<script>alert('Trạng thái phim đã được thay đổi.');</script>";
    } else {
        echo "<script>alert('Không tìm thấy phim này.');</script>";
    }

    header("Location: movies.php");
    exit();
}

// Lấy thông tin tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : "";
$search = $db->link->real_escape_string($search); 

// Lấy thông tin phân trang
$limit = 5; 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Truy vấn tổng số bản ghi
$total_query = "
    SELECT COUNT(*) AS total
    FROM phim
    WHERE TieuDe LIKE '%$search%'
";
$total_result = $db->select($total_query);
if ($total_result && $total_result->num_rows > 0) {
    $total_rows = $total_result->fetch_assoc()['total'];
} else {
    $total_rows = 0;
}
$total_pages = ceil($total_rows / $limit);

// Truy vấn danh sách phim
$query = "
    SELECT MaPhim, TieuDe, ThoiLuong, AnhDaiDien, ChiDanhChoVip, TrangThai, NgayPhatHanh
    FROM phim
    WHERE TieuDe LIKE '%$search%'
    ORDER BY NgayPhatHanh DESC
    LIMIT $limit OFFSET $offset
";
$result = $db->select($query);

// Xử lý xem chi tiết phim
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Lấy chi tiết phim
    $movie_query = "
        SELECT MaPhim, TieuDe, MoTa, NgayPhatHanh, ThoiLuong, AnhDaiDien, TrailerURL, VideoURL, ChiDanhChoVIP, TrangThai
        FROM phim
        WHERE MaPhim = $id
    ";
    $movie_result = $db->select($movie_query);
    if ($movie_result && $movie_result->num_rows > 0) {
        $movie = $movie_result->fetch_assoc();

        // Lấy thể loại phim
        $theloai_query = "
            SELECT t.TenTheLoai
            FROM theloai t
            JOIN phim_theloai pt ON pt.MaTheLoai = t.MaTheLoai
            WHERE pt.MaPhim = $id
        ";
        $theloai_result = $db->select($theloai_query);
        $theloai_list = [];
        if ($theloai_result && $theloai_result->num_rows > 0) {
            while ($theloai = $theloai_result->fetch_assoc()) {
                $theloai_list[] = $theloai['TenTheLoai'];
            }
        }

        // Lấy diễn viên
        $dienvien_query = "
            SELECT d.TenDienVien
            FROM dienvien d
            JOIN phim_dienvien pd ON pd.MaDienVien = d.MaDienVien
            WHERE pd.MaPhim = $id
        ";
        $dienvien_result = $db->select($dienvien_query);
        $dienvien_list = [];
        if ($dienvien_result && $dienvien_result->num_rows > 0) {
            while ($dienvien = $dienvien_result->fetch_assoc()) {
                $dienvien_list[] = $dienvien['TenDienVien'];
            }
        }
    } else {
        echo "Phim không tồn tại.";
        exit;
    }
}

// Bao gồm header và sidebar
include 'header.php';
include 'sidebar.php';
?>

<div id="main-content">
    <div id="news-detail">
        <?php if (isset($movie)): ?>
            <h2>Chi tiết phim: <?= htmlspecialchars($movie['TieuDe']) ?></h2>
            <!-- Hiển thị ảnh đại diện -->
            <img src="../Backend/uploads<?= htmlspecialchars($movie['AnhDaiDien']) ?>" alt="Ảnh đại diện" width="200">
            <p><strong>Tiêu đề:</strong> <?= htmlspecialchars($movie['TieuDe']) ?></p>
            <p><strong>Mô tả:</strong> <?= htmlspecialchars($movie['MoTa']) ?></p>
            <p><strong>Thời lượng:</strong> <?= $movie['ThoiLuong'] ?> phút</p>
            <p><strong>Ngày phát hành:</strong> <?= date("d/m/Y", strtotime($movie['NgayPhatHanh'])) ?></p>
            <p><strong>Trạng thái:</strong> <?= $movie['TrangThai'] ? 'Kích hoạt' : 'Ẩn' ?></p>
            <p><strong>VIP:</strong> <?= $movie['ChiDanhChoVIP'] ? 'Có' : 'Không' ?></p>
            <p><strong>Thể loại:</strong> <?= implode(", ", $theloai_list) ?></p>
            <p><strong>Diễn viên:</strong> <?= implode(", ", $dienvien_list) ?></p>
            
            <!-- Hiển thị Trailer nếu có -->
            <?php if (!empty($movie['TrailerURL'])): ?>
                <p><strong>Trailer:</strong></p>
                <video width="200" controls>
                    <source src=".. /Backend/uploads<?= htmlspecialchars($movie['TrailerURL']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <!-- Hiển thị Video nếu có -->
            <?php if (!empty($movie['VideoURL'])): ?>
                <p><strong>Video:</strong></p>
                <video width="200" controls>
                    <source src=".. /Backend/uploads<?= htmlspecialchars($movie['VideoURL']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <p><a href="movies.php" style="float: right; margin-left: 20px;">&#10149;Quay lại</a></p>
        <?php else: ?>
    </div>

        <h2 style="margin-bottom: 20px; padding-top: 20px;">Danh sách phim</h2>
        
        <!-- Nút thêm phim -->
        <a href="add_edit_movies.php?action=add" class="btn btn-add">Thêm phim mới</a>
        
        <!-- Form tìm kiếm -->
        <form method="get" action="movies.php">
            <input type="text" name="search" placeholder="Tìm kiếm phim..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>

        <table>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Thời gian</th>
                <th>Ảnh bìa</th>
                <th>Vip</th>
                <th>Trạng thái</th>
                <th>Ngày phát hành</th>
                <th>Hành động</th>
            </tr>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaPhim'] ?></td>
                        <td><?= htmlspecialchars($row['TieuDe']) ?></td>
                        <td><?= $row['ThoiLuong'] ?> phút</td>
                        <td><img src="uploads/<?= htmlspecialchars($row['AnhDaiDien']) ?>" width="100" alt="Ảnh đại diện"></td>
                        <td><?= $row['ChiDanhChoVip'] ? 'Vip' : 'Không Vip' ?></td>
                        <td class="status-column">
                            <span class="<?= $row['TrangThai'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $row['TrangThai'] ? 'Kích hoạt' : 'Ẩn' ?>
                            </span>
                            <a href="movies.php?action=toggle_status&id=<?= $row['MaPhim'] ?>" 
                            class="status-toggle <?= $row['TrangThai'] ? 'status-inactive' : 'status-active' ?>">
                            <?= $row['TrangThai'] ? 'Ẩn' : 'Kích hoạt' ?>
                            </a>
                        </td>

                        <td><?= date("d/m/Y", strtotime($row['NgayPhatHanh'])) ?></td>
                        <td>
                            <a href="movies.php?action=view&id=<?= $row['MaPhim'] ?>" class="btn btn-view">Xem</a> | 
                            <a href="add_edit_movies.php?action=edit&id=<?= $row['MaPhim'] ?>" class="btn btn-edit">Sửa</a> | 
                            <a href="movies.php?delete_id=<?= $row['MaPhim'] ?>" 
                                onclick="return confirm('Bạn có chắc chắn muốn xóa phim này không?');" class="btn btn-delete">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">Không có phim nào.</td></tr>
            <?php endif; ?>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="movies.php?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                   class="<?= $i == $page ? 'active' : '' ?>">
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
    /* Cập nhật giao diện cho cột trạng thái */
.status-column {
    font-weight: bold;
    text-align: center;
    padding: 5px 10px;
    border-radius: 5px;
}

.status-active {
    color: white;
    background-color: #4CAF50; /* Màu xanh cho trạng thái Kích hoạt */
}

.status-inactive {
    color: white;
    background-color: #f44336; /* Màu đỏ cho trạng thái Ẩn */
}

.status-toggle {
    font-size: 12px;
    padding: 2px 5px;
    text-decoration: none;
    border-radius: 5px;
    display: inline-block;
}

.status-toggle:hover {
    opacity: 0.8;
}

</style>