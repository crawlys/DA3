<?php
$host = 'localhost'; 
$username = 'root';
$password = '';
$database = 'xemphim'; 

// Kết nối MySQL
$conn = new mysqli($host, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}
?>
