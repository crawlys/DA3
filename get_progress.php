<?php
include 'db.php'; // Kết nối với DB

$userId = isset($_GET['userId']) ? intval($_GET['userId']) : null;
$movieId = isset($_GET['movieId']) ? intval($_GET['movieId']) : null;

if ($userId && $movieId) {
    $sql = "SELECT TienTrinhXem, p.ThoiLuong FROM lichsuxemphim l JOIN phim p ON l.MaPhim = p.MaPhim WHERE l.MaNguoiDung = ? AND l.MaPhim = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $movieId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $progress = $row['TienTrinhXem'] / $row['ThoiLuong'] * 100;
        echo json_encode(['progress' => number_format($progress, 2)]);
    } else {
        echo json_encode(['progress' => 0]);
    }
} else {
    echo json_encode(['progress' => 0]);
}
?>
