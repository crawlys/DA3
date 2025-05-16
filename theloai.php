
<?php include 'header.php'; ?>
  <!-- slider starts  -->
  <section id="home" class="iq-main-slider p-0">
    <div id="home-slider" class="slider m-0 p-0">
      <div class="slide slick-bg s-bg-4">
        <div class="container-fluid position-relative h-100">
          <div class="slider-inner h-100">
            <div class="row align-items-center h--100">
              <div class="col-xl-6 col-lg-12 col-md-12">
                <a href="javascript:void(0)">
                  <div class="channel-logo" data-animation-in="fadeInLeft" data-delay-in="0.5">
                    <img src="images/logo1.png" class="c-logo" alt="" />
                  </div>
                </a>
                <h1 class="slider-text big-title title text-uppercase" data-animation-in="fadeInLeft"
                  data-delay-in="0.6">
                  Ối Trời Ơi!
                </h1>
                <div class="d-flex flex-wrap align-items-center fadeInLeft animated" data-animation-in="fadeInLeft"
                  style="opacity: 1">
                  <div class="slider-ratting d-flex align-items-center mr-4 mt-2 mt-md-3">
                    
                    <!-- <span class="text-white ml-2">7.3(đánh giá)</span> -->
                  </div>
                  <div class="d-flex align-items-center mt-2 mt-md-3">
                    <span class="badge badge-secondary p-2">13+</span>
                    <span class="ml-3">2h 21p</span>
                  </div>
                </div>
                <p data-animation-in="fadeInUp">
                Sau một chuỗi các sự kiện không may, 
                Leah cùng đồng đội và người bạn mới Jelly của cô đã vô tình bị cuốn đến một hòn đảo xa xôi. 
                Trong khi đó, Finny tỉnh dậy và nhận ra rằng mình đã bị lạc trong một vùng đất lạ lẫm.
                </p>
                <div class="trending-list" data-animation-in="fadeInUp" data-delay-in="1.2">
                  <div class="text-primary title starring">
                    Diễn viên :
                    <span class="text-body">Ava Connolly, Dermot Magennis, Max Carolan</span>
                  </div>
                  <div class="text-primary title genres">
                    Thể loại : <span class="text-body">Hài hước,hoạt hình</span>
                  </div>
                  <div class="text-primary title tag">
                    Đạo diễn :
                    <span class="text-body">Toby Genkel</span>
                  </div>
                </div>
                <div class="d-flex align-items-center r-mb-23 mt-4" data-animation-in="fadeInUp" data-delay-in="1.2">
                  <a href="#" class="btn btn-hover iq-button"><i class="fa fa-play mr-3"></i>Xem ngay</a>
                  <a href="#" class="btn btn-link">Xem thêm</a>
                </div>
              </div>
            </div>
            <div class="col-xl-5 col-lg-12 col-md-12 trailor-video">
              <a href="video/trailer.mp4" class="video-open playbtn">
                <img src="images/play.png" class="play" alt="" />
                <span class="w-trailor">Xem trailer</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    
  </section>
  <!-- slider ends -->

  <!-- main content starts  -->
  <div class="main-content">
    <?php
include 'db.php'; // Kết nối CSDL

// Lấy MaTheLoai từ URL
if (isset($_GET['MaTheLoai'])) {
    $maTheLoai = intval($_GET['MaTheLoai']); 

    // Truy vấn danh sách phim theo thể loại
    $sql = "SELECT p.MaPhim, p.TieuDe, p.MoTa, p.ThoiLuong, p.AnhDaiDien, p.ChiDanhChoVIP
            FROM phim p
            INNER JOIN phim_theloai pt ON p.MaPhim = pt.MaPhim
            WHERE pt.MaTheLoai = ? AND p.TrangThai = 1";  // Lọc chỉ phim đang hoạt động (TrangThai = 1)
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $maTheLoai);
    $stmt->execute();
    $result = $stmt->get_result();

    // Tạo danh sách phim
    $listPhim = "";
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $hinhAnh = isset($row['AnhDaiDien']) ? $row['AnhDaiDien'] : 'default.jpg'; // Hình ảnh mặc định
            $tenPhim = isset($row['TieuDe']) ? $row['TieuDe'] : 'Tên phim không có';
            $thoiLuong = isset($row['ThoiLuong']) ? $row['ThoiLuong'] : 0;

            $listPhim .= "
            <li class='slide-item'>
              <div class='block-images position-relative'>
                <div class='img-box'>
                  <img src='../Backend/uploads/" . $hinhAnh . "' class='img-fluid' alt='" . $tenPhim . "' />
                </div>
                <div class='block-description'>
                  <h6 class='iq-title'>
                    <a href='chitietphim.php?id=" . $row['MaPhim'] . "'>" . $tenPhim . "</a>
                  </h6>
                  <div class='movie-time d-flex align-items-center my-2'>
                    <div class='badge badge-secondary p-1 mr-2'>" . ($row['ChiDanhChoVIP'] ? 'VIP' : '16+') . "</div>
                    <span class='text-white'>" . $thoiLuong . " phút</span>
                  </div>
                  <div class='hover-buttons'>
                    <span class='btn btn-hover iq-button' onclick='window.location.href=\"chitietphim.php?id=" . $row['MaPhim'] . "\"'>
                      <i class='fa fa-play mr-1'></i>
                      Play Now
                    </span>
                  </div>
                </div>
                <div class='block-social-info'>
                  <ul class='list-inline p-0 m-0 music-play-lists'>
                    <li class='share'>
                      <span><i class='fa fa-share-alt'></i></span>
                      <div class='share-box'>
                        <div class='d-flex align-items-center'>
                          <a href='#' class='share-ico'><i class='fa fa-share-alt'></i></a>
                          <a href='#' class='share-ico'><i class='fa fa-youtube'></i></a>
                          <a href='#' class='share-ico'><i class='fa fa-instagram'></i></a>
                        </div>
                      </div>
                    </li>
                    <li>
                      <span><i class='fa fa-heart'></i></span>
                      <span class='count-box'>Free</span>
                    </li>
                    <li>
                      <span><i class='fa fa-plus'></i></span>
                    </li>
                  </ul>
                </div>
              </div>
            </li>";
        }
    } else {
        $listPhim = "<p class='text-white'>Không có phim nào thuộc thể loại này.</p>";
    }
}
?>
<section id="iq-favorites">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12 overflow-hidden">
        <div class="iq-main-header d-flex align-items-center justify-content-between">
          <h4 class="main-title">Danh sách phim</h4>
        </div>
        <div class="favorite-contens">
          <ul class="favorites-slider list-inline row p-0 mb-0">
            <?php echo $listPhim; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>
   <!-- trending section  -->
  </div>

  <!-- main content ends  -->


  <?php include 'footer.php'; ?>

  <!-- js files  -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/slick.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/select2.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/slick-animation.min.js"></script>

  <script src="main.js"></script>
</body>
</html>