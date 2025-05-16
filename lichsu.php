<?php
session_start();
include 'db.php';

if (!isset($_SESSION['MaNguoiDung'])) {
    header("Location: dangnhap.php");
    exit();
}

$userId = $_SESSION['MaNguoiDung'];

$sql = "SELECT l.MaLichSu, p.TieuDe, l.TienTrinhXem, l.NgayXem, p.VideoURL, p.MaPhim
        FROM lichsuxemphim l
        JOIN phim p ON l.MaPhim = p.MaPhim
        WHERE l.MaNguoiDung = ? ORDER BY l.NgayXem DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử xem phim</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
/* CSS Styles */
header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background-color: #333;
    color: white;
    padding: 10px 20px;
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

body {
    margin-top: 150px;
}

        .container {

            margin: 20px auto;
            max-width: 80%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
h1 {
    text-align: center;
    color :#2f2f2f;
}
/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-family: Arial, sans-serif;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

table th, table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    font-size: 14px;
    color : black;
}

/* Table header styling */
table th {
    background-color: #2f2f2f;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

/* Table row styling */
table tr:nth-child(even) {
    background-color:rgb(0, 0, 0);
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Table cell styling for actions (link) */
table td a {
    color: #e63946;
    text-decoration: none;
    font-weight: bold;
}

table td a:hover {
    text-decoration: underline;
}

/* Progress bar styling */
.progress {
    position: relative;
    height: 20px;
    width: 100px;
    background-color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
    margin: 5px 0;
}



/* Additional styles for when no history is available */
p {
    text-align: center;
    font-size: 1.2em;
    color: black;
    font-style: italic;
}

    </style>
</head>
<body>
    

    <div class="container">
        <h1>Lịch sử xem phim của bạn</h1>
        <?php if (empty($history)): ?>
            <p>Chưa có phim nào trong lịch sử của bạn.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Ngày xem</th>
                        <th>Tiến trình xem (%)</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['TieuDe']); ?></td>
                            <td><?php echo htmlspecialchars($item['NgayXem']); ?></td>
                            <td class="progress" data-url="../Backend/uploads/<?php echo htmlspecialchars($item['VideoURL']); ?>"
                                data-progress="<?php echo htmlspecialchars($item['TienTrinhXem']); ?>">
                                Đang tải...
                            </td>
                            <td>
                                <a href="xemphim.php?id=<?php echo $item['MaPhim']; ?>&progress=<?php echo intval($item['TienTrinhXem']); ?>">Tiếp tục xem</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const progressElements = document.querySelectorAll(".progress");
            
            progressElements.forEach(progressElement => {
                const videoURL = progressElement.dataset.url;
                const secondsWatched = parseFloat(progressElement.dataset.progress);

                const video = document.createElement("video");
                video.src = videoURL;

                video.addEventListener("loadedmetadata", function () {
                    const duration = video.duration;
                    if (duration > 0) {
                        const percentage = (secondsWatched / duration) * 100;
                        progressElement.textContent = `${Math.min(percentage, 100).toFixed(2)}%`;
                    } else {
                        progressElement.textContent = "Không xác định";
                    }
                });

                video.addEventListener("error", function () {
                    progressElement.textContent = "Không xác định";
                });
            });
        });
    </script>
</body>
</html>
