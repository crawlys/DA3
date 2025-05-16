<?php 
include_once('lib/config.php');
include_once('lib/Session.php');
include_once('lib/database.php');
include_once('lib/format.php');

// Kiểm tra quyền admin
Session::checkSession();

// Tạo đối tượng xử lý database
$db = new Database();

// Kiểm tra kết nối cơ sở dữ liệu
if (!$db->link) {
    die("Kết nối cơ sở dữ liệu thất bại: " . mysqli_connect_error());
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tieu_de = $mo_ta = $thoi_luong = $anh_dai_dien = $trailer_url = $video_url = $ngay_phat_hanh = "";
$chi_danh_cho_vip = 0;
$ma_dao_dien = 0;
$dienvien = []; // Mảng diễn viên được chọn
$theloai = []; // Mảng thể loại được chọn

// Nếu là chỉnh sửa hoặc xem phim, lấy dữ liệu phim cũ
if (($action == 'edit' || $action == 'view') && $id > 0) {
    $result = $db->select("SELECT * FROM phim WHERE MaPhim = $id");
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row) {
            $tieu_de = $row['TieuDe'];
            $mo_ta = $row['MoTa'];
            $thoi_luong = $row['ThoiLuong'];
            $anh_dai_dien = $row['AnhDaiDien'];
            $trailer_url = $row['TrailerURL'];
            $video_url = $row['VideoURL'];
            $chi_danh_cho_vip = $row['ChiDanhChoVIP'];
            $ma_dao_dien = $row['MaDaoDien'];
            $ngay_phat_hanh = $row['NgayPhatHanh'];  // Lấy Ngày Phát Hành

            // Lấy danh sách diễn viên và thể loại của phim
            $dienvien_result = $db->select("SELECT MaDienVien FROM phim_dienvien WHERE MaPhim = $id");
            while ($dienvien_row = $dienvien_result->fetch_assoc()) {
                $dienvien[] = $dienvien_row['MaDienVien'];
            }

            $theloai_result = $db->select("SELECT MaTheLoai FROM phim_theloai WHERE MaPhim = $id");
            while ($theloai_row = $theloai_result->fetch_assoc()) {
                $theloai[] = $theloai_row['MaTheLoai'];
            }
        } else {
            echo "Không tìm thấy phim với ID: $id";
        }
    } else {
        echo "Truy vấn dữ liệu phim thất bại.";
    }
}

// Lấy danh sách diễn viên và thể loại
$dienvien_list = $db->select("SELECT MaDienVien, TenDienVien FROM dienvien");
$theloai_list = $db->select("SELECT MaTheLoai, TenTheLoai FROM theloai");

// Xử lý lưu phim khi nhấn nút "Lưu"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieu_de = $_POST['tieu_de'];
    $mo_ta = $_POST['mo_ta'];
    $thoi_luong = $_POST['thoi_luong'];
    $trailer_url = $_POST['trailer_url'];
    $video_url = $_POST['video_url'];
    $chi_danh_cho_vip = isset($_POST['chi_danh_cho_vip']) ? 1 : 0;
    $ma_dao_dien = $_POST['ma_dao_dien'];
    $dienvien = $_POST['dienvien'] ?? [];
    $theloai = $_POST['theloai'] ?? [];
    $id = intval($_POST['id']);
    $ngay_phat_hanh = $_POST['ngay_phat_hanh'];  // Lấy Ngày Phát Hành từ form
    $action = $_POST['action'];

    // Xử lý ảnh đại diện
    $anh_dai_dien_file = $anh_dai_dien;
    if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_tmp = $_FILES['anh_dai_dien']['tmp_name'];
        $file_name = basename($_FILES['anh_dai_dien']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $anh_dai_dien_file = $file_name;
        } else {
            echo "Lỗi tải ảnh lên.";
        }
    }

    // Xử lý trailer file
    $trailer_file = $trailer_url;  // Giá trị mặc định là URL trailer cũ
    if (isset($_FILES['trailer_file']) && $_FILES['trailer_file']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_tmp = $_FILES['trailer_file']['tmp_name'];
        $file_name = basename($_FILES['trailer_file']['name']);
        $file_path = $upload_dir . $file_name;

        // Kiểm tra xem file có thể di chuyển vào thư mục uploads không
        if (move_uploaded_file($file_tmp, $file_path)) {
            $trailer_file = $file_name;  // Cập nhật trailer với tên file mới
        } else {
            echo "Lỗi tải trailer lên.";
        }
    }

    // Xử lý video file
    $video_file = $video_url;  // Giá trị mặc định là URL video cũ
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_tmp = $_FILES['video_file']['tmp_name'];
        $file_name = basename($_FILES['video_file']['name']);
        $file_path = $upload_dir . $file_name;

        // Kiểm tra xem file có thể di chuyển vào thư mục uploads không
        if (move_uploaded_file($file_tmp, $file_path)) {
            $video_file = $file_name;  // Cập nhật video với tên file mới
        } else {
            echo "Lỗi tải video lên.";
        }
    }

    // Thực hiện thêm hoặc chỉnh sửa phim
    if ($action == 'add') {
        $sql = "INSERT INTO phim (TieuDe, MoTa, ThoiLuong, AnhDaiDien, TrailerURL, VideoURL, ChiDanhChoVIP, MaDaoDien, NgayPhatHanh) 
                VALUES ('$tieu_de', '$mo_ta', $thoi_luong, '$anh_dai_dien_file', '$trailer_file', '$video_file', $chi_danh_cho_vip, $ma_dao_dien, '$ngay_phat_hanh')";
        $db->insert($sql);
        $id = $db->link->insert_id;
    } elseif ($action == 'edit' && $id > 0) {
        $sql = "UPDATE phim SET TieuDe = '$tieu_de', MoTa = '$mo_ta', ThoiLuong = $thoi_luong, TrailerURL = '$trailer_file', VideoURL = '$video_file', ChiDanhChoVIP = $chi_danh_cho_vip, MaDaoDien = $ma_dao_dien, NgayPhatHanh = '$ngay_phat_hanh'";
        if ($anh_dai_dien_file) {
            $sql .= ", AnhDaiDien = '$anh_dai_dien_file'";
        }
        $sql .= " WHERE MaPhim = $id";
        $db->update($sql);
    }

    // Xử lý liên kết diễn viên
    $db->delete("DELETE FROM phim_dienvien WHERE MaPhim = $id");
    foreach ($dienvien as $dienvien_id) {
        $db->insert("INSERT INTO phim_dienvien (MaPhim, MaDienVien) VALUES ($id, $dienvien_id)");
    }

    // Xử lý liên kết thể loại
    $db->delete("DELETE FROM phim_theloai WHERE MaPhim = $id");
    foreach ($theloai as $theloai_id) {
        $db->insert("INSERT INTO phim_theloai (MaPhim, MaTheLoai) VALUES ($id, $theloai_id)");
    }

    header("Location: movies.php");
    exit();
}

include 'header.php';
include 'sidebar.php';
?>

<link rel="stylesheet" href="assets/css/movies.css">

<div id="main-content">
    <div id="add-edit-movies">
    <?php if ($action == 'add' || $action == 'edit'): ?>
        <h2><?= $action == 'add' ? 'Thêm phim' : 'Chỉnh sửa phim' ?></h2>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?= $action ?>">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div>
                <label for="tieu_de">Tiêu đề:</label>
                <input type="text" name="tieu_de" value="<?= htmlspecialchars($tieu_de) ?>" required>
            </div>

            <div>
                <label for="mo_ta">Mô tả:</label>
                <textarea name="mo_ta" required><?= htmlspecialchars($mo_ta) ?></textarea>
            </div>

            <div>
                <label for="thoi_luong">Thời lượng:</label>
                <input type="number" name="thoi_luong" value="<?= htmlspecialchars($thoi_luong) ?>" required>
            </div>

            <div>
            <label for="ngay_phat_hanh">Ngày phát hành:</label>
            <input type="date" name="ngay_phat_hanh" value="<?= htmlspecialchars(date('Y-m-d', strtotime($ngay_phat_hanh))) ?>" required>

        </div>


            <div>
                <label for="anh_dai_dien">Ảnh đại diện:</label>
                <input type="file" name="anh_dai_dien">
                <?php if ($anh_dai_dien): ?>
                    <p>Ảnh hiện tại: <img src="uploads/<?= $anh_dai_dien ?>" alt="Ảnh đại diện" width="100"></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="trailer_file">Tải lên trailer:</label>
                <input type="file" name="trailer_file">
                <?php if ($trailer_url): ?>
                    <p>Trailer hiện tại: <a href="uploads/<?= $trailer_url ?>" target="_blank"><?= htmlspecialchars($trailer_url) ?></a></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="video_file">Tải lên video:</label>
                <input type="file" name="video_file">
                <?php if ($video_url): ?>
                    <p>Video hiện tại: <a href="uploads/<?= $video_url ?>" target="_blank"><?= htmlspecialchars($video_url) ?></a></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="chi_danh_cho_vip">Chỉ dành cho VIP:</label>
                <input type="checkbox" name="chi_danh_cho_vip" <?= $chi_danh_cho_vip ? 'checked' : '' ?>>
            </div>

            <div>
                <label for="ma_dao_dien">Đạo diễn:</label>
                <select name="ma_dao_dien">
                    <option value="0">Chọn đạo diễn</option>
                    <?php
                    $dao_dien_list = $db->select("SELECT MaDaoDien, TenDaoDien FROM daodien");
                    while ($dao_dien_row = $dao_dien_list->fetch_assoc()) {
                        echo '<option value="' . $dao_dien_row['MaDaoDien'] . '" ' . ($ma_dao_dien == $dao_dien_row['MaDaoDien'] ? 'selected' : '') . '>';
                        echo $dao_dien_row['TenDaoDien'];
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="dienvien">Chọn diễn viên:</label>
                <select name="dienvien[]" multiple>
                    <?php
                    while ($dienvien_row = $dienvien_list->fetch_assoc()) {
                        echo '<option value="' . $dienvien_row['MaDienVien'] . '" ' . (in_array($dienvien_row['MaDienVien'], $dienvien) ? 'selected' : '') . '>';
                        echo $dienvien_row['TenDienVien'];
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>

            <div>
                <label for="theloai">Chọn thể loại:</label>
                <select name="theloai[]" multiple>
                    <?php
                    while ($theloai_row = $theloai_list->fetch_assoc()) {
                        echo '<option value="' . $theloai_row['MaTheLoai'] . '" ' . (in_array($theloai_row['MaTheLoai'], $theloai) ? 'selected' : '') . '>';
                        echo $theloai_row['TenTheLoai'];
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>

            <div>
                <button type="submit">Lưu</button>
            </div>
        </form>
    <?php endif; ?>
    </div>
</div>

