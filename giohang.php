<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// XỬ LÝ LOGIC (Tăng/Giảm/Xóa)
if (isset($_GET['action'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'delete') {
        unset($_SESSION['cart'][$id]);
    } elseif ($_GET['action'] == 'update') {
        $type = $_GET['type'];
        if ($type == 'plus') {
            $_SESSION['cart'][$id]++;
        } elseif ($type == 'minus' && $_SESSION['cart'][$id] > 1) {
            $_SESSION['cart'][$id]--;
        }
    }
    header("Location: giohang.php"); 
    exit();
}

$tong_cong = 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng - ElectroHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .cart-container { max-width: 1000px; margin: 50px auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .product-img { width: 80px; height: 80px; object-fit: contain; background: #f1f1f1; border-radius: 10px; }
        .btn-qty { width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: white; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #333; transition: 0.2s; }
        .btn-qty:hover { background: #28a745; color: white; border-color: #28a745; }
        .text-orange { color: #fd7e14; }
        .btn-checkout { background: #28a745; color: white; padding: 12px 40px; border-radius: 10px; font-weight: bold; text-decoration: none; transition: 0.3s; display: inline-block; }
        .btn-checkout:hover { background: #218838; transform: translateY(-2px); color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="cart-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0"><i class="fa fa-shopping-cart text-success me-2"></i>GIỎ HÀNG</h2>
            <a href="mua_hang.php" class="text-decoration-none text-muted small"><i class="fa fa-arrow-left me-1"></i> Tiếp tục mua sắm</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th class="text-center">Số lượng</th>
                        <th>Thành tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])):
                        foreach ($_SESSION['cart'] as $id => $qty):
                            $res = $conn->query("SELECT * FROM san_pham WHERE id_sp = $id");
                            if($row = $res->fetch_assoc()):
                                $thanh_tien = $row['gia_tien'] * $qty;
                                $tong_cong += $thanh_tien;
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="uploads/<?php echo $row['hinh_anh']; ?>" class="product-img me-3" onerror="this.src='https://via.placeholder.com/80'">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo $row['ten_sp']; ?></h6>
                                        <small class="text-muted">ID: #<?php echo $id; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo number_format($row['gia_tien'], 0, ',', '.'); ?> đ</td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <a href="?action=update&type=minus&id=<?php echo $id; ?>" class="btn-qty">-</a>
                                    <span class="fw-bold mx-2"><?php echo $qty; ?></span>
                                    <a href="?action=update&type=plus&id=<?php echo $id; ?>" class="btn-qty">+</a>
                                </div>
                            </td>
                            <td class="fw-bold text-success"><?php echo number_format($thanh_tien, 0, ',', '.'); ?> đ</td>
                            <td class="text-end">
                                <a href="?action=delete&id=<?php echo $id; ?>" class="btn btn-sm text-danger text-decoration-none" onclick="return confirm('Xóa sản phẩm này?')">
                                    <i class="fa fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endif; endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <p class="text-muted">Giỏ hàng trống trơn!</p>
                                <a href="mua_hang.php" class="btn btn-outline-dark btn-sm">Mua hàng ngay</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($tong_cong > 0): ?>
        <div class="row mt-5">
            <div class="col-md-12 text-end">
                <p class="mb-1 text-muted">Tổng cộng thanh toán:</p>
                <h3 class="fw-bold text-orange mb-4"><?php echo number_format($tong_cong, 0, ',', '.'); ?> VNĐ</h3>
                <a href="khach_hang_thanh_toan.php" class="btn btn-checkout shadow-sm">
                    THANH TOÁN NGAY <i class="fa fa-chevron-right ms-2"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>