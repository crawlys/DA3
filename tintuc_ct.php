<?php 
include 'header.php'; 
include 'db.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra xem ID tin tức đã được truyền qua URL chưa
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Truy vấn dữ liệu chi tiết tin tức từ bảng tin_tuc theo ID
    $sql = "SELECT tieu_de, noi_dung, tac_gia, ngay_tao, anh_bia FROM tin_tuc WHERE id = $id";
    $result = $conn->query($sql);

    // Kiểm tra nếu tin tức tồn tại
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $tieuDe = $row['tieu_de'];
        $noiDung = $row['noi_dung'];
        $tacGia = $row['tac_gia'];
        $ngayTao = date('d/m/Y', strtotime($row['ngay_tao']));
        $anhBia = $row['anh_bia'];

        // Kiểm tra nếu ảnh tồn tại trong thư mục upload
        $anhBiaPath = "uploads/$anhBia";
        if (!file_exists($anhBiaPath) || empty($anhBia)) {
            $anhBiaPath = "images/default.jpg"; // Ảnh mặc định nếu không tìm thấy ảnh
        }
    } else {
        echo "<p>Không tìm thấy tin tức này.</p>";
        exit;
    }
} else {
    echo "<p>Không có tin tức nào được tìm thấy.</p>";
    exit;
}
?>
<body>
  <main>
    <section class="news-detail">
      <h1><?php echo $tieuDe; ?></h1>
      <p><strong>Tác giả:</strong> <?php echo $tacGia; ?></p>
      <p><small>Ngày tạo: <?php echo $ngayTao; ?></small></p>

      <img src="<?php echo $anhBiaPath; ?>" alt="<?php echo $tieuDe; ?>" class="news-detail-image">

      <div class="news-content">
        <p><?php echo nl2br($noiDung); ?></p>
      </div>

      <a href="tintuc.php" class="back-to-list">Quay lại danh sách tin tức</a>
    </section>
  </main>

  <style>
    /* Fix the header at the top */
    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      background-color: #333;
      color: white;
      padding: 10px 10px;
      z-index: 1000;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    body {
      margin-top: 150px;
    }

    main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .news-detail {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
    }

    .news-detail h1 {
      font-size: 2.5em;
      margin-bottom: 20px;
    }

    .news-detail p {
      font-size: 1.1em;
      line-height: 1.6;
      margin: 10px 0;
    }

    .news-detail-image {
      width: 40%;
      max-width: 800px;
      height: auto;
      margin: 20px 0;
      border-radius: 8px;
    }

    .news-content p {
      font-size: 1.1em;
      line-height: 1.8;
      margin-bottom: 20px;
    }

    .back-to-list {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #ff7e5f;
      font-weight: bold;
      font-size: 1.2em;
    }

    .back-to-list:hover {
      text-decoration: underline;
    }
  </style>
</body>
</html>
