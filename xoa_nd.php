<?php
session_start();
// Chỉ Admin mới có quyền xóa tài khoản
if (!isset($_SESSION['user_admin']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Bảo vệ: Không cho phép Admin tự xóa chính mình
    // Giả sử tên đăng nhập của admin hiện tại lưu trong SESSION
    $current_admin = $_SESSION['user_admin'];
    
    $sql = "DELETE FROM nguoi_dung WHERE id_nd = $id AND ten_dang_nhap != '$current_admin'";
    
    if ($conn->query($sql)) {
        echo "<script>alert('Xóa thành viên thành công!'); window.location.href='index.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
} else {
    header("Location: index.php");
}
?>