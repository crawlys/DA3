<?php
include 'db.php'; // Kết nối với DB

$userId = isset($_POST['userId']) ? intval($_POST['userId']) : null;
$movieId = isset($_POST['movieId']) ? intval($_POST['movieId']) : null;
$progress = isset($_POST['progress']) ? floatval($_POST['progress']) : 0;

if ($userId && $movieId && $progress >= 0) {
    // Kiểm tra nếu có lịch sử xem phim của người dùng, nếu có thì cập nhật, nếu không thì thêm mới
    $sql = "SELECT MaLichSu FROM lichsuxemphim WHERE MaNguoiDung = ? AND MaPhim = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Cập nhật tiến độ xem
        $updateSql = "UPDATE lichsuxemphim SET TienTrinhXem = ?, NgayXem = NOW() WHERE MaNguoiDung = ? AND MaPhim = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("dii", $progress, $userId, $movieId);
        $stmt->execute();
    } else {
        // Thêm mới lịch sử xem phim
        $insertSql = "INSERT INTO lichsuxemphim (MaNguoiDung, MaPhim, TienTrinhXem, NgayXem) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("iid", $userId, $movieId, $progress);
        $stmt->execute();
    }
}
?>
