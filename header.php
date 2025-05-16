<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PHIMHAY</title>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link rel="stylesheet" href="css/slick.css" />
  <link rel="stylesheet" href="css/slick-theme.css" />
  <link rel="stylesheet" href="css/owl.carousel.min.css" />
  <link rel="stylesheet" href="css/animate.min.css" />
  <link rel="stylesheet" href="css/magnific-popup.css" />
  <link rel="stylesheet" href="css/select2.min.css" />
  <link rel="stylesheet" href="css/select2-bootstrap4.min.css" />
  <link rel="stylesheet" href="css/slick-animation.css" />
  <link rel="stylesheet" href="style.css"/>
</head>

<body>
<?php
include('db.php'); // Bao gồm kết nối cơ sở dữ liệu

// Kiểm tra nếu session chưa bắt đầu thì bắt đầu
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['MaNguoiDung'])) {
    // Lấy thông tin người dùng từ session
    $ma_nguoidung = $_SESSION['MaNguoiDung']; // Lấy MaNguoiDung từ session
    $tenNguoiDung = $_SESSION['TenNguoiDung']; // Lấy Tên Người Dùng từ session
    $avatar = isset($_SESSION['Avatar']) ? $_SESSION['Avatar'] : 'images/user/user.png'; // Lấy avatar từ session, nếu không có thì dùng avatar mặc định
} else {
    // Nếu người dùng chưa đăng nhập
    $ma_nguoidung = null;
    $tenNguoiDung = null;
    $avatar = 'images/user/user.png'; // Avatar mặc định nếu chưa đăng nhập
}

// Nếu người dùng đã đăng nhập, truy vấn cơ sở dữ liệu để lấy avatar từ bảng nguoidung
if ($ma_nguoidung) {
    $sql = "SELECT Avatar FROM nguoidung WHERE MaNguoiDung = '$ma_nguoidung'";
    $result = mysqli_query($conn, $sql);

    // Kiểm tra xem có kết quả trả về không
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $avatar = $row['Avatar']; // Lấy avatar từ cơ sở dữ liệu
    } else {
        $avatar = 'images/user/user.png'; // Nếu không có avatar trong cơ sở dữ liệu, sử dụng avatar mặc định
    }
}
// Lấy danh sách thể loại từ CSDL
$theLoaiSql = "SELECT MaTheLoai, TenTheLoai FROM theloai WHERE TrangThai = 1";
$theLoaiResult = $conn->query($theLoaiSql);
?>

<header id="main-header">
    <div class="main-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <nav class="navbar navbar-expand-lg navbar-light p-0">
                        <a href="#" class="navbar-toggler c-toggler" data-toggle="collapse" data-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <div class="navbar-toggler-icon" data-toggle="collapse">
                                <span class="navbar-menu-icon navbar-menu-icon--top"></span>
                                <span class="navbar-menu-icon navbar-menu-icon--middle"></span>
                                <span class="navbar-menu-icon navbar-menu-icon--bottom"></span>
                            </div>
                        </a>
                        <a href="index.php" class="navbar-brand">
                            <img src="images/logo1.png" class="img-fluid logo" alt="Logo" />
                        </a>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <div class="menu-main-menu-container">
                                <ul id="top-menu" class="navbar-nav ml-auto">
                                    <li class="menu-item"><a href="index.php">Trang Chủ</a></li>
                                    <li class="menu-item">
                                        <a href="#">Thể loại</a>
                                        <ul class="sub-menu">
                                        <?php while ($row = $theLoaiResult->fetch_assoc()): ?>
                                            <li class="menu-item">
            <a href="theloai.php?MaTheLoai=<?php echo $row['MaTheLoai']; ?>">
                <?php echo htmlspecialchars($row['TenTheLoai']); ?>
            </a>
        </li>
    <?php endwhile; ?>
</ul>
                               </li>
                                    <li class="menu-item"><a href="tintuc.php">Tin Tức</a></li>
                                    <li class="menu-item">
                                        <a href="dichvu.php">GÓI VIP</a>
                                       
                                </ul>
                            </div>
                        </div>
                        <div class="navbar-right menu-right">
                            <ul class="d-flex align-items-center list-inline m-0">
                            <li class="nav-item nav-icon">
    <a href="#" class="search-toggle device-search">
        <i class="fa fa-search"></i>
    </a>
    <div class="search-box iq-search-bar d-search">
        <form action="search.php" method="GET" class="searchbox">
            <div class="form-group position-relative">
                <input type="text" name="keyword" class="text search-input font-size-12"
                    placeholder="type here to search..." required />
                <button type="submit" class="search-link fa fa-search"></button>
            </div>
        </form>
    </div>
</li>
                                <?php if ($tenNguoiDung): ?>
                                    <!-- Khi người dùng đã đăng nhập -->
                                    <li class="nav-item nav-icon">
                                        <a href="#" class="iq-user-dropdown search-toggle d-flex align-items-center p-0">
                                            <!-- Hiển thị avatar người dùng -->
                                            <img src="images/user/<?php echo $avatar; ?>" class="img-fluid user-m rounded-circle" alt="Avatar" />
                                                 
                                        </a>
                                        <div class="iq-sub-dropdown iq-user-dropdown">
                                            <div class="iq-card shadow-none m-0">
                                                <div class="iq-card-body p-0 pl-3 pr-3">
                                                    <a href="nguoidung.php" class="iq-sub-card setting-dropdown">
                                                        <div class="media align-items-center">
                                                            <div class="right-icon">
                                                                <i class="fa fa-user text-primary"></i>
                                                            </div>
                                                            <div class="media-body ml-3">
                                                                <h6 class="mb-0">Thông tin tài khoản</h6>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="goinguoidung.php" class="iq-sub-card setting-dropdown">
                                                        <div class="media align-items-center">
                                                            <div class="right-icon">
                                                                <i class="fa fa-inr text-primary"></i>
                                                            </div>
                                                            <div class="media-body ml-3">
                                                                <h6 class="mb-0">Gói VIP của bạn</h6>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="lichsu.php" class="iq-sub-card setting-dropdown">
                                                        <div class="media align-items-center">
                                                            <div class="right-icon">
                                                            <i class="fa-solid fa-film"></i>
                                                            </div>
                                                            <div class="media-body ml-3">
                                                                <h6 class="mb-0">Lịch sử xem</h6>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="dangxuat.php" class="iq-sub-card setting-dropdown">
                                                        <div class="media align-items-center">
                                                            <div class="right-icon">
                                                                <i class="fa fa-sign-out text-primary"></i>
                                                            </div>
                                                            <div class="media-body ml-3">
                                                                <h6 class="mb-0">Đăng xuất</h6>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php else: ?>
                                    <!-- Khi người dùng chưa đăng nhập -->
                                    <li class="nav-item nav-icon">
                                        <a href="#" onclick="window.location.href='dangnhap.php';" class="iq-user-dropdown search-toggle d-flex align-items-center p-0">
                                            <i class="fa fa-user"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </nav>
                    <div class="nav-overlay"></div>
                </div>
            </div>
        </div>
    </div>
</header>
</body>