<?php
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

$id = (int)$_GET['id'];
$sp = $conn->query("SELECT san_pham.*, danh_muc.ten_danh_muc FROM san_pham 
                    LEFT JOIN danh_muc ON san_pham.ma_danh_muc = danh_muc.id_danh_muc 
                    WHERE id_sp = $id")->fetch_assoc();

if (!$sp) die("Không tìm thấy sản phẩm!");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết: <?php echo $sp['ten_sp']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow-sm rounded" style="max-width: 800px;">
    <a href="mua_hang.php" class="btn btn-sm btn-outline-secondary mb-3">← Quay lại</a>
    
    <div class="row">
        <div class="col-md-5 text-center">
            <img src="uploads/<?php echo $sp['hinh_anh']; ?>" class="img-fluid rounded border">
        </div>
        
        <div class="col-md-7">
            <h3 class="fw-bold"><?php echo $sp['ten_sp']; ?></h3>
            <p class="text-muted">Mã: <?php echo $sp['ma_san_pham']; ?> | Danh mục: <?php echo $sp['ten_danh_muc']; ?></p>
            <h4 class="text-danger fw-bold"><?php echo number_format($sp['gia_tien']); ?> đ</h4>
            <p>Trạng thái: <b><?php echo ($sp['so_luong_kho'] > 0) ? "Còn $sp[so_luong_kho] món" : "Hết hàng"; ?></b></p>
            
            <div class="mt-3 p-3 bg-light border rounded">
                <h6>Mô tả sản phẩm:</h6>
                <small class="text-secondary">
                    <?php echo !empty($sp['mo_ta']) ? nl2br($sp['mo_ta']) : "Chưa có mô tả."; ?>
                </small>
            </div>
        </div>
    </div>
</div>
</body>
</html>