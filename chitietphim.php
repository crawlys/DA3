<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phim</title>
    
    <style>
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
            max-width: 800px;
            margin: 100px auto 20px auto;
            padding: 20px;
            border-radius: 10px;
        }

        h1, h2, h3 {
            color: #e50914;
        }

        video {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            border: 2px solid #e50914;
            border-radius: 10px;
            display: block;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        ul li img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        ul li span {
            color: #fff;
        }

        .button-container {
            display: flex;
            justify-content: flex-start;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e50914;
            color: white;
            text-align: right;
            text-decoration: none;
        }

        .button:hover {
            background-color: #b4060a;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-fixed">
        <?php include 'header.php'; ?>
    </header>
    
    <!-- Container chính -->
    <div class="container">
    <?php
    include 'db.php'; // Kết nối DB

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $sql = "SELECT p.TieuDe, p.MoTa, p.TrailerURL, p.MaDaoDien 
                FROM phim p 
                WHERE p.MaPhim = $id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            die("Không tìm thấy phim.");
        }

        // Lấy thông tin đạo diễn
        $maDaoDien = $row['MaDaoDien'];
        $daoDienSql = "SELECT TenDaoDien, AnhDaiDien FROM daodien WHERE MaDaoDien = $maDaoDien";
        $daoDienResult = $conn->query($daoDienSql);
        $daoDien = $daoDienResult->fetch_assoc();

        // Lấy danh sách diễn viên
        $actorSql = "SELECT d.TenDienVien, d.AnhDaiDien 
                     FROM phim_dienvien pd 
                     JOIN dienvien d ON pd.MaDienVien = d.MaDienVien 
                     WHERE pd.MaPhim = $id";
        $actorResult = $conn->query($actorSql);
        $actors = $actorResult->fetch_all(MYSQLI_ASSOC);
    } else {
        die("Không có ID phim.");
    }
?>
<!-- Trailer -->
<h3>Trailer</h3>
<video controls>
    <source src="uploads/<?php echo htmlspecialchars($row['TrailerURL']); ?>" type="video/mp4">
    Trình duyệt của bạn không hỗ trợ video.
</video>
<div class="button-container">
    <a href="xemphim.php?id=<?php echo $id; ?>" class="button">Xem phim</a>
</div>

<!-- Thông tin phim -->
<h1><?php echo htmlspecialchars($row['TieuDe']); ?></h1>
<p><strong>Mô tả:</strong> <?php echo htmlspecialchars($row['MoTa']); ?></p>

<!-- Thông tin đạo diễn -->
<h3>Đạo diễn</h3>
<p><strong>Tên:</strong> <?php echo htmlspecialchars($daoDien['TenDaoDien']); ?></p>
<img src="../Backend/<?php echo htmlspecialchars($daoDien['AnhDaiDien']); ?>" 
     alt="Ảnh đạo diễn" style="width: 80px; height: 80px; border-radius: 50%; margin-bottom: 20px;">

<!-- Danh sách diễn viên -->
<h3>Diễn viên</h3>
<ul>
    <?php foreach ($actors as $actor): ?>
        <li>
            <?php if (!empty($actor['AnhDaiDien'])): ?>
                <img src="../Backend/<?php echo htmlspecialchars($actor['AnhDaiDien']); ?>" 
                     alt="<?php echo htmlspecialchars($actor['TenDienVien']); ?>" style="width: 80px; height: 80px; border-radius: 50%; margin-right: 10px;">
            <?php endif; ?>
            <span><?php echo htmlspecialchars($actor['TenDienVien']); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
