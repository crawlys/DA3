<?php
include_once('lib/session.php');
include_once('lib/database.php');
include_once('lib/format.php');

// Kiểm tra quyền admin
Session::checkSession(); 

// Tạo đối tượng xử lý database và format
$db = new Database();
$fm = new Format();

// Số lượng kết quả mỗi trang
$limit = 10;

// Lấy trang hiện tại từ URL, nếu không có thì mặc định là trang 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit; // Tính toán vị trí bắt đầu của các kết quả

// Tìm kiếm
$search_keyword = "";
if (isset($_GET['search'])) {
    $search_keyword = $_GET['search'];
    $query = "
        SELECT thanhtoan.*, nguoidung.TenNguoiDung, goidichvu.TenGoi
        FROM thanhtoan
        INNER JOIN nguoidung ON thanhtoan.MaNguoiDung = nguoidung.MaNguoiDung
        INNER JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi
        WHERE nguoidung.TenNguoiDung LIKE '%$search_keyword%'
           OR goidichvu.TenGoi LIKE '%$search_keyword%'
           OR thanhtoan.PhuongThucThanhToan LIKE '%$search_keyword%'
        ORDER BY thanhtoan.NgayThanhToan DESC
        LIMIT $limit OFFSET $offset";
} else {
    // Lấy danh sách thanh toán mặc định
    $query = "
        SELECT thanhtoan.*, nguoidung.TenNguoiDung, goidichvu.TenGoi
        FROM thanhtoan
        INNER JOIN nguoidung ON thanhtoan.MaNguoiDung = nguoidung.MaNguoiDung
        INNER JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi
        ORDER BY thanhtoan.NgayThanhToan DESC
        LIMIT $limit OFFSET $offset";
}

// Truy vấn dữ liệu
$result = $db->select($query);

// Tính tổng số bản ghi
$total_query = "
    SELECT COUNT(*) AS total
    FROM thanhtoan
    INNER JOIN nguoidung ON thanhtoan.MaNguoiDung = nguoidung.MaNguoiDung
    INNER JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi
    WHERE nguoidung.TenNguoiDung LIKE '%$search_keyword%'
       OR goidichvu.TenGoi LIKE '%$search_keyword%'
       OR thanhtoan.PhuongThucThanhToan LIKE '%$search_keyword%'";

if (empty($search_keyword)) {
    $total_query = "
        SELECT COUNT(*) AS total
        FROM thanhtoan
        INNER JOIN nguoidung ON thanhtoan.MaNguoiDung = nguoidung.MaNguoiDung
        INNER JOIN goidichvu ON thanhtoan.MaGoi = goidichvu.MaGoi";
}

$total_result = $db->select($total_query);
$total_rows = $total_result->fetch_assoc()['total']; // Lấy tổng số bản ghi
$total_pages = ceil($total_rows / $limit); // Tính số trang

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="assets/css/css1.css">
<div id="main-content">
    <div id="payments-list">
        <h2 style="margin-top: 20px; padding-bottom: 20px;">Danh sách giao dịch</h2>
        <form method="get" action="payments.php">
            <input type="text" name="search" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search_keyword) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>

        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên Người Dùng</th>
                <th>Tên Gói</th>
                <th>Số Tiền</th>
                <th>Phương Thức</th>
                <th>Ngày Thanh Toán</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['MaThanhToan'] ?></td>
                        <td><?= htmlspecialchars($row['TenNguoiDung']) ?></td>
                        <td><?= htmlspecialchars($row['TenGoi']) ?></td>
                        <td><?= number_format($row['SoTien'], 2) ?> VND</td>
                        <td><?= htmlspecialchars($row['PhuongThucThanhToan']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['NgayThanhToan'])) ?></td>
                        <td>
                            <a href="javascript:void(0);" class="detail-btn btn btn-view" data-id="<?= $row['MaThanhToan'] ?>">Xem chi tiết</a>
                        </td>
                    </tr>
                    <tr class="detail-row" id="detail-<?= $row['MaThanhToan'] ?>" style="display: none;">         
                        <td colspan="7">
                            <form method="POST" class="detail-form">
                                <h3>Chi tiết giao dịch</h3>
                                <p><strong>ID Giao Dịch:</strong> <?= $row['MaThanhToan'] ?></p>
                                <p><strong>Tên Người Dùng:</strong> <?= htmlspecialchars($row['TenNguoiDung']) ?></p>
                                <p><strong>Tên Gói:</strong> <?= htmlspecialchars($row['TenGoi']) ?></p>
                                <p><strong>Số Tiền:</strong> <?= number_format($row['SoTien'], 2) ?> VND</p>
                                <p><strong>Phương Thức Thanh Toán:</strong> <?= htmlspecialchars($row['PhuongThucThanhToan']) ?></p>
                                <p><strong>Ngày Thanh Toán:</strong> <?= date('d/m/Y H:i', strtotime($row['NgayThanhToan'])) ?></p>

                                <button type="button" class="close-btn">Đóng</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Không có giao dịch nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Hiển thị phân trang -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="payments.php?page=<?= $i ?>&search=<?= urlencode($search_keyword) ?>"
               class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>

<script src="assets/js/payments.js"></script>

<style>
/* CSS cho form chi tiết giao dịch */
.detail-form {
    display: grid;
    grid-template-columns: 1fr 1fr; /* Chia form thành 2 cột */
    gap: 15px 20px; /* Tạo khoảng cách giữa các hàng và cột */
    background-color: #ffffff; /* Màu nền trắng để nổi bật */
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Hiệu ứng đổ bóng nhẹ */
    width: 100%; /* Form full chiều rộng cha */
    max-width: 600px; /* Giới hạn chiều rộng */
    margin: 20px auto; /* Căn giữa form */
}

/* Tiêu đề của form */
.detail-form h3 {
    grid-column: 1 / span 2; /* Tiêu đề chiếm toàn bộ chiều ngang */
    text-align: center;
    color: #333;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 20px;
    text-transform: uppercase; /* In hoa tiêu đề */
}

/* Hiệu ứng khi bảng chi tiết hiện */
.detail-row {
    background-color: #f4f6f9;
    transition: all 0.3s ease-in-out;
}

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
