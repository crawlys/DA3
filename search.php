
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

<!-- main content starts -->
<div class="main-content">
    <?php
    include 'db.php'; // Kết nối CSDL

    // Lấy từ khóa tìm kiếm từ URL
    if (isset($_GET['keyword'])) {
        $keyword = mysqli_real_escape_string($conn, $_GET['keyword']); // Bảo vệ khỏi SQL injection

        // Truy vấn tìm kiếm phim theo từ khóa
        $sql = "
            SELECT p.MaPhim, p.TieuDe, p.MoTa, p.ThoiLuong, p.AnhDaiDien, p.ChiDanhChoVIP
            FROM phim p
            WHERE p.TieuDe LIKE '%$keyword%' OR p.MoTa LIKE '%$keyword%' OR EXISTS (
                SELECT 1
                FROM phim_theloai pt
                WHERE pt.MaPhim = p.MaPhim AND pt.MaTheLoai IN (
                    SELECT MaTheLoai FROM theloai WHERE TenTheLoai LIKE '%$keyword%'
                )
            )
        ";

        $result = mysqli_query($conn, $sql);

        // Xử lý kết quả
        $listPhim = "";
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
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
                                    <a href='chitietphim.php?MaPhim=" . $row['MaPhim'] . "'>" . $tenPhim . "</a>
                                </h6>
                                <div class='movie-time d-flex align-items-center my-2'>
                                    <div class='badge badge-secondary p-1 mr-2'>" . ($row['ChiDanhChoVIP'] ? 'VIP' : '16+') . "</div>
                                    <span class='text-white'>" . $thoiLuong . " phút</span>
                                </div>
                                <div class='hover-buttons'>
                                    <span class='btn btn-hover iq-button' onclick='window.location.href=\"chitietphim.php?id=" . $row['MaPhim'] . "\"'>
                                        <i class='fa fa-play mr-1'></i>
                                        Xem ngay
                                    </span>
                                </div>
                            </div>
                        </div>
                    </li>";
            }
        } else {
            $listPhim = "<p class='text-white'>Không tìm thấy kết quả cho từ khóa '" . htmlspecialchars($keyword) . "'</p>";
        }
    } else {
        $listPhim = "<p class='text-white'>Vui lòng nhập từ khóa tìm kiếm.</p>";
    }
    ?>

    <section id="iq-favorites">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12 overflow-hidden">
                    <div class="iq-main-header d-flex align-items-center justify-content-between">
                    <h4 class="main-title">Danh sách kết quả tìm kiếm cho từ khóa: <span class="text-primary"><?php echo htmlspecialchars($keyword); ?></span></h4>
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
</div>
<!-- main content ends -->

<?php include 'footer.php'; ?>

<!-- js files -->
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
