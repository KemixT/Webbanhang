<?php
session_start();

// Kết nối database
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Bước 1: Xóa sản phẩm khỏi tất cả các bảng hóa đơn liên quan
    // Xóa ở bảng hoa_don (dựa trên lỗi fk_hd_sanpham trong ảnh của ông)
    $conn->query("DELETE FROM hoa_don WHERE id_sp = $id");
    
    // Xóa ở bảng chi_tiet_hdb (dựa trên lỗi fk_ct_sanpham trong ảnh của ông)
    $conn->query("DELETE FROM chi_tiet_hdb WHERE id_sp = $id");

    // Bước 2: Bây giờ mới xóa sản phẩm gốc trong bảng san_pham
    $sql = "DELETE FROM san_pham WHERE id_sp = $id";
    
    if ($conn->query($sql)) {
        // Thành công thì quay về trang danh sách
        header("Location: index.php?status=success");
        exit();
    } else {
        // Nếu vẫn lỗi thì hiện thông báo đơn giản
        echo "Không thể xóa sản phẩm. Lỗi: " . $conn->error;
    }
} else {
    header("Location: index.php");
    exit();
}
?>  