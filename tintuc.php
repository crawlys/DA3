<?php 
include 'header.php'; 
include 'db.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn dữ liệu từ bảng tin_tuc
$sql = "SELECT id, tieu_de, noi_dung, tac_gia, ngay_tao, anh_bia FROM tin_tuc ORDER BY ngay_tao DESC";
$result = $conn->query($sql);
?>
<body>
  <main>
    <!-- Phần tiêu đề -->
    <section class="news-header">
      <h1>Tin Tức Phim Mới Nhất</h1>
      <p>Cập nhật thông tin về những bộ phim hot nhất hiện nay và các sự kiện điện ảnh đáng chú ý.</p>
    </section>

    <!-- Danh sách tin tức phim -->
    <section class="news-list">
      <?php
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $tieuDe = $row['tieu_de'];
              $noiDung = substr($row['noi_dung'], 0, 100) . '...'; // Rút gọn nội dung
              $anhBia = $row['anh_bia']; // Lấy đường dẫn ảnh bìa từ cột anh_bia
              $ngayTao = date('d/m/Y', strtotime($row['ngay_tao'])); // Định dạng ngày

              // Kiểm tra nếu ảnh tồn tại trong thư mục upload
              $anhBiaPath = "../Backend/uploads/$anhBia";
              if (!file_exists($anhBiaPath) || empty($anhBia)) {
                  $anhBiaPath = "images/default.jpg"; // Ảnh mặc định nếu không tìm thấy ảnh
              }

              echo "
              <article class='news-item'>
                  <img src='$anhBiaPath' alt='$tieuDe'>
                  <div class='news-content'>
                      <h2><a href='tintuc_ct.php?id={$row['id']}'>$tieuDe</a></h2>
                      <p>$noiDung</p>
                      <p><small>Ngày tạo: $ngayTao</small></p>
                      <a href='tintuc_ct.php?id={$row['id']}' class='read-more'>Đọc thêm <i class='fas fa-arrow-right'></i></a>
                  </div>
              </article>";
          }
      } else {
          echo "<p>Không có tin tức nào được tìm thấy.</p>";
      }
      ?>
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

    /* Add some margin to the body content to avoid being hidden under the fixed header */
    body {
      margin-top: 150px; /* Adjust this value based on the header height */
    }
    
    main {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .news-header {
      text-align: center;
      margin-top: 0px;
      margin-bottom: 40px;
      padding: 20px;
      background: linear-gradient(to right, #ff7e5f, #feb47b);
      color: white;
      border-radius: 8px;
    }

    .news-header h1 {
      font-size: 2.5em;
    }

    .news-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .news-item {
      background: black;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .news-item:hover {
      transform: scale(1.03);
    }

    .news-item img {
      width: 100%;
      height: auto;
    }

    .news-content {
      padding: 15px;
    }

    .news-content h2 {
      font-size: 1.5em;
      margin: 0 0 10px;
    }

    .news-content p {
      margin: 0 0 15px;
      line-height: 1.5;
    }

    .read-more {
      text-decoration: none;
      color: #ff7e5f;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
    }

    .read-more i {
      margin-left: 5px;
    }
  </style>

<?php include 'footer.php'; ?>
</body>
</html>
