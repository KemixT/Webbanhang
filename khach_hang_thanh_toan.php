<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// Kiểm tra giỏ hàng, nếu trống thì bắt quay lại mua
if (empty($_SESSION['cart'])) {
    header("Location: giohang.php");
    exit();
}

$tong_cong = 0;
$cart_items = [];
foreach ($_SESSION['cart'] as $id => $sl) {
    // Lấy thông tin sản phẩm từ Database
    $res = $conn->query("SELECT * FROM san_pham WHERE id_sp = $id");
    if ($res && $sp = $res->fetch_assoc()) {
        $sp['so_luong_mua'] = $sl; // Thêm số lượng khách mua vào mảng
        $cart_items[] = $sp;
        $tong_cong += ($sp['gia_tien'] * $sl);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán Đơn Hàng - ElectroHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; color: #333; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .checkout-header { background: white; padding: 20px 0; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: white; }
        .section-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; color: #2c3e50; text-transform: uppercase; letter-spacing: 1px; }
        .section-title i { color: #0d6efd; margin-right: 10px; }
        .form-label { font-weight: 600; font-size: 0.9rem; color: #555; }
        .form-control, .form-select { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
        .product-list { max-height: 400px; overflow-y: auto; }
        .product-item { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee; }
        .product-img { width: 65px; height: 65px; object-fit: cover; border-radius: 8px; margin-right: 15px; border: 1px solid #eee; }
        .btn-checkout { background: #ff5722; color: white; font-weight: 700; padding: 15px; border-radius: 10px; width: 100%; margin-top: 25px; transition: 0.3s; border: none; text-transform: uppercase; font-size: 1.1rem; }
        .btn-checkout:hover { background: #e64a19; transform: translateY(-2px); color: white; }
        .price-text { color: #dc3545; font-weight: 700; }
        .bg-light-custom { background-color: #f9f9f9; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body>

<div class="checkout-header border-bottom">
    <div class="container d-flex justify-content-between align-items-center">
        <h4 class="mb-0 fw-bold text-primary">ElectroHub</h4>
        <div class="text-muted small">Hotline: 1900 xxxx</div>
    </div>
</div>

<div class="container">
    <form action="xuly_giohang.php" method="POST">
        <input type="hidden" name="action" value="tru_kho_thanh_toan">

        <div class="row g-4">
            <div class="col-lg-7 col-md-12">
                <div class="card p-4">
                    <h5 class="section-title"><i class="fa-solid fa-map-location-dot"></i> 1. Thông tin giao hàng</h5>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Họ và tên người nhận *</label>
                            <input type="text" name="ho_ten" class="form-control" placeholder="Ví dụ: Nguyễn Văn A" required 
                                   value="<?php echo $_SESSION['ho_ten'] ?? ''; ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Số điện thoại liên hệ *</label>
                            <input type="text" name="sdt" class="form-control" placeholder="Ví dụ: 0901234567" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tỉnh / Thành phố *</label>
                            <select name="tinh_thanh" id="province" class="form-select" required>
                                <option value="" selected disabled>Chọn Tỉnh/TP</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quận / Huyện *</label>
                            <select name="quan_huyen" id="district" class="form-select" required>
                                <option value="" selected disabled>Chọn Quận/Huyện</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phường / Xã *</label>
                            <select name="phuong_xa" id="ward" class="form-select" required>
                                <option value="" selected disabled>Chọn Phường/Xã</option>
                            </select>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Số nhà, tên đường *</label>
                            <textarea name="dia_chi_chi_tiet" class="form-control" rows="3" placeholder="Số 123, đường ABC..." required></textarea>
                        </div>
                    </div>

                    <h5 class="section-title mt-4"><i class="fa-solid fa-credit-card"></i> 2. Phương thức thanh toán</h5>
                    <div class="bg-light-custom border">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="pttt" id="pttt1" value="COD" checked>
                            <label class="form-check-label fw-bold" for="pttt1">
                                <i class="fa-solid fa-money-bill-wave me-2 text-success"></i> Thanh toán khi nhận hàng (COD)
                            </label>
                            <div class="text-muted small ms-4">Bạn chỉ thanh toán khi đã nhận được hàng.</div>
                        </div>
                        <div class="form-check opacity-50">
                            <input class="form-check-input" type="radio" name="pttt" id="pttt2" value="Bank" disabled>
                            <label class="form-check-label fw-bold" for="pttt2">
                                <i class="fa-solid fa-building-columns me-2 text-primary"></i> Chuyển khoản ngân hàng (Đang bảo trì)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-md-12">
                <div class="card p-4 sticky-top" style="top: 90px; z-index: 10;">
                    <h5 class="section-title"><i class="fa-solid fa-basket-shopping"></i> Đơn hàng của bạn (<?php echo count($cart_items); ?>)</h5>
                    
                    <div class="product-list mb-3">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="product-item">
                            <img src="uploads/<?php echo $item['hinh_anh']; ?>" class="product-img" onerror="this.src='https://via.placeholder.com/65'">
                            <div class="flex-grow-1">
                                <div class="fw-bold small"><?php echo $item['ten_sp']; ?></div>
                                <div class="text-muted small">Số lượng: <?php echo $item['so_luong_mua']; ?></div>
                            </div>
                            <div class="text-end fw-bold">
                                <?php echo number_format($item['gia_tien'] * $item['so_luong_mua']); ?> đ
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span class="fw-bold"><?php echo number_format($tong_cong); ?> đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success fw-bold">Miễn phí</span>
                    </div>
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                        <span class="fw-bold fs-5">TỔNG CỘNG:</span>
                        <span class="fs-4 price-text"><?php echo number_format($tong_cong); ?> đ</span>
                    </div>

                    <button type="submit" class="btn btn-checkout shadow">
                        HOÀN TẤT ĐẶT HÀNG <i class="fa-solid fa-arrow-right ms-2"></i>
                    </button>
                    
                    <a href="giohang.php" class="btn btn-link w-100 text-secondary mt-3 small">Quay lại giỏ hàng</a>
                </div>
            </div>
        </div>
    </form>
</div>

<footer class="mt-5 py-4 text-center text-muted border-top bg-white">
    <div class="container">
        <small>© 2026 ElectroHub - Linh kiện điện tử chất lượng cao</small>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. Lấy danh sách Tỉnh/Thành
        $.getJSON('https://provinces.open-api.vn/api/p/', function(data) {
            $.each(data, function(index, value) {
                $('#province').append('<option value="' + value.name + '" data-id="' + value.code + '">' + value.name + '</option>');
            });
        });

        // 2. Khi chọn Tỉnh -> Lấy danh sách Quận/Huyện
        $('#province').change(function() {
            let provinceCode = $(this).find(':selected').data('id');
            $('#district').html('<option value="" selected disabled>Chọn Quận/Huyện</option>');
            $('#ward').html('<option value="" selected disabled>Chọn Phường/Xã</option>');
            
            if (provinceCode) {
                $.getJSON('https://provinces.open-api.vn/api/p/' + provinceCode + '?depth=2', function(data) {
                    $.each(data.districts, function(index, value) {
                        $('#district').append('<option value="' + value.name + '" data-id="' + value.code + '">' + value.name + '</option>');
                    });
                });
            }
        });

        // 3. Khi chọn Quận -> Lấy danh sách Phường/Xã
        $('#district').change(function() {
            let districtCode = $(this).find(':selected').data('id');
            $('#ward').html('<option value="" selected disabled>Chọn Phường/Xã</option>');
            
            if (districtCode) {
                $.getJSON('https://provinces.open-api.vn/api/d/' + districtCode + '?depth=2', function(data) {
                    $.each(data.wards, function(index, value) {
                        $('#ward').append('<option value="' + value.name + '">' + value.name + '</option>');
                    });
                });
            }
        });
    });
</script>

</body>
</html>