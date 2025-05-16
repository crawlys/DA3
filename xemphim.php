<?php

ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Xem phim</title>
    <style>
        /* CSS cho header cố định */
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
        ul li span {
            color: #fff;
        }

        form {

            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        form textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: lightgrey;
        }

        form button {
            background-color: #e50914;
            color: #fff;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        h2 {
    font-family: Arial, sans-serif;
    color: #333;
}

ul {
    list-style-type: none;
    padding: 0;
}


strong {
    color:rgb(201, 72, 72);
}

em {
    color: #666;
    font-size: 0.9em;
}

a {
    padding-left: 7px;
    font-size: 12px;
    text-decoration: none;
    color:rgb(124, 124, 124);
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
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
            $sql = "SELECT *, ChiDanhChoVIP FROM phim WHERE MaPhim = $id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
            } else {
                die("Không tìm thấy phim.");
            }

            $commentSql = "SELECT b.MaBinhLuan, TenNguoiDung, Avatar, NoiDung, NgayTao, b.MaNguoiDung FROM binhluan b 
                           JOIN nguoidung n ON b.MaNguoiDung = n.MaNguoiDung 
                           WHERE MaPhim = $id ORDER BY NgayTao DESC";
            $commentResult = $conn->query($commentSql);
            $comments = $commentResult->fetch_all(MYSQLI_ASSOC);
        } else {
            die("Không có ID phim.");
        }

        // Kiểm tra trạng thái VIP của người dùng
        $isVip = false;
        if (isset($_SESSION['MaNguoiDung'])) {
            $maNguoiDung = $_SESSION['MaNguoiDung'];
            $userSql = "SELECT NgayHetHanVIP FROM nguoidung WHERE MaNguoiDung = ?";
            $stmt = $conn->prepare($userSql);
            $stmt->bind_param("i", $maNguoiDung);
            $stmt->execute();
            $result = $stmt->get_result();
            $userData = $result->fetch_assoc();

            if ($userData && strtotime($userData['NgayHetHanVIP']) > time()) {
                $isVip = true; // Người dùng VIP
            }
        }

        // Lấy thông tin bình luận cần sửa (nếu có)
        $editComment = null;
        if (isset($_GET['edit']) && isset($_SESSION['MaNguoiDung'])) {
            $editId = intval($_GET['edit']);
            $editSql = "SELECT * FROM binhluan WHERE MaBinhLuan = ? AND MaNguoiDung = ?";
            $stmt = $conn->prepare($editSql);
            $stmt->bind_param("ii", $editId, $_SESSION['MaNguoiDung']);
            $stmt->execute();
            $result = $stmt->get_result();
            $editComment = $result->fetch_assoc();
        }

        // Xử lý thêm hoặc sửa bình luận
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['noidung']) && isset($_SESSION['MaNguoiDung'])) {
                $noidung = trim($_POST['noidung']);
                $maNguoiDung = intval($_SESSION['MaNguoiDung']);

                if (!empty($noidung)) {
                    if (isset($_POST['comment_id']) && !empty($_POST['comment_id'])) {
                        // Sửa bình luận
                        $maBinhLuan = intval($_POST['comment_id']);
                        $updateSql = "UPDATE binhluan SET NoiDung = ? WHERE MaBinhLuan = ? AND MaNguoiDung = ?";
                        $stmt = $conn->prepare($updateSql);
                        $stmt->bind_param("sii", $noidung, $maBinhLuan, $maNguoiDung);
                        $stmt->execute();
                    } else {
                        // Thêm bình luận mới
                        $insertSql = "INSERT INTO binhluan (MaPhim, MaNguoiDung, NoiDung, NgayTao) VALUES (?, ?, ?, NOW())";
                        $stmt = $conn->prepare($insertSql);
                        $stmt->bind_param("iis", $id, $maNguoiDung, $noidung);
                        $stmt->execute();
                    }
                    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id);
                    exit();
                }
            }
        }
        ?>

        <!-- Video đầy đủ -->
        <?php if ($row['ChiDanhChoVIP'] && !$isVip): ?>
            <p>Phim này chỉ dành cho người dùng VIP. <a href="dichvu.php">Nâng cấp VIP</a> để xem toàn bộ nội dung.</p>
        <?php else: ?>
            <video id="movie-player" controls>
                <source src="uploads/<?php echo htmlspecialchars($row['VideoURL']); ?>" type="video/mp4">
                Trình duyệt của bạn không hỗ trợ video.
            </video>
        <?php endif; ?>

        <script>
    // Lưu tiến độ xem vào cơ sở dữ liệu khi video dừng hoặc khi người dùng chuyển trang
    var video = document.getElementById('movie-player');
    var videoDuration = video.duration;

    // Khi video đã tải xong, kiểm tra xem có tiến độ xem trước đó không
    window.onload = function() {
        var userId = <?php echo $_SESSION['MaNguoiDung'] ?? 'null'; ?>;
        var movieId = <?php echo $id; ?>;
        
        if (userId) {
            // Lấy tiến độ xem nếu có
            fetch('get_progress.php?userId=' + userId + '&movieId=' + movieId)
                .then(response => response.json())
                .then(data => {
                    if (data && data.progress > 0) {
                        // Tiến độ đã lưu, bắt đầu phát từ tiến độ đó
                        video.currentTime = data.progress;
                    }
                });
        }
    };

    // Lưu tiến độ xem vào cơ sở dữ liệu khi video dừng
    video.addEventListener('pause', function() {
        var userId = <?php echo $_SESSION['MaNguoiDung'] ?? 'null'; ?>;
        var movieId = <?php echo $id; ?>;
        
        if (userId && video.currentTime > 0) {
            // Gửi tiến độ vào cơ sở dữ liệu
            fetch('save_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'userId=' + userId + '&movieId=' + movieId + '&progress=' + video.currentTime
            });
        }
    });

    // Lưu tiến độ khi người dùng chuyển trang
    window.onbeforeunload = function() {
        var userId = <?php echo $_SESSION['MaNguoiDung'] ?? 'null'; ?>;
        var movieId = <?php echo $id; ?>;
        
        if (userId && video.currentTime > 0) {
            fetch('save_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'userId=' + userId + '&movieId=' + movieId + '&progress=' + video.currentTime
            });
        }
    };
</script>

        
        <!-- Phần bình luận -->
        <h2>Bình luận</h2>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                <img src="images/user/<?= htmlspecialchars($comment['Avatar']) ?>" alt="Avatar" width="50" height="50">
                    <strong><?php echo htmlspecialchars($comment['TenNguoiDung']); ?></strong>: 
                    <?php echo htmlspecialchars($comment['NoiDung']); ?> 
                    <em>(<?php echo htmlspecialchars($comment['NgayTao']); ?>)</em>

                    <!-- Nút sửa chỉ hiển thị với người dùng đã cmt -->
                    <?php if (isset($_SESSION['MaNguoiDung']) && $_SESSION['MaNguoiDung'] == $comment['MaNguoiDung']): ?>
                        <a href="?id=<?php echo $id; ?>&edit=<?php echo $comment['MaBinhLuan']; ?>" title="Sửa">
    <i class="fas fa-edit"></i>
</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (isset($_SESSION['MaNguoiDung'])): ?>
            <h3><?php echo $editComment ? "Sửa bình luận" : "Thêm bình luận"; ?></h3>
            <form method="POST" action="">
                <textarea name="noidung" required><?php echo $editComment ? htmlspecialchars($editComment['NoiDung']) : ''; ?></textarea>
                <?php if ($editComment): ?>
                    <input type="hidden" name="comment_id" value="<?php echo $editComment['MaBinhLuan']; ?>">
                <?php endif; ?>
                <button type="submit"><?php echo $editComment ? "Cập nhật" : "Gửi bình luận"; ?></button>
            </form>
        <?php else: ?>
            <p>Vui lòng <a href="dangnhap.php">đăng nhập</a> để bình luận.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>