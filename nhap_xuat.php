<?php
session_start();
// Hiển thị lỗi để kiểm soát nếu có vấn đề phát sinh
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// Xử lý khi bấm nút Xác nhận
if (isset($_POST['btn_save'])) {
    $id_sp = $_POST['id_sp'];
    $sl = $_POST['so_luong'];
    $loai = $_POST['loai_giao_dich'];
    $ghi_chu = !empty($_POST['ghi_chu']) ? $_POST['ghi_chu'] : "Giao dịch kho";
    $ngay = date('Y-m-d H:i:s');
    $ma_hd = ($loai == 'nhap' ? "NK-" : "XK-") . time();

    // 1. Lấy giá tiền và kiểm tra tồn kho
    $sp_query = $conn->query("SELECT gia_tien, so_luong_kho FROM san_pham WHERE id_sp = $id_sp");
    $sp = $sp_query->fetch_assoc();

    if ($loai == 'xuat' && $sp['so_luong_kho'] < $sl) {
        echo "<script>alert('Lỗi: Số lượng tồn kho không đủ!'); window.location='nhap_xuat.php';</script>";
        exit();
    }

    // 2. Chèn vào bảng hoa_don_ban (Khớp với ảnh DBeaver của ông giáo)
    $tong_tien_tinh = $sl * $sp['gia_tien'];
    $sql_hd = "INSERT INTO hoa_don_ban (ma_hd, ngay_ban, ten_khach_hang, tong_tien) 
               VALUES ('$ma_hd', '$ngay', '$ghi_chu', $tong_tien_tinh)";
    
    if ($conn->query($sql_hd)) {
        $id_hdb = $conn->insert_id;

        // 3. Chèn chi tiết hóa đơn (Nếu ông đã có bảng chi_tiet_hdb)
        $conn->query("INSERT INTO chi_tiet_hdb (id_hdb, id_sp, so_luong_ban, gia_ban_luc_do) 
                      VALUES ($id_hdb, $id_sp, $sl, {$sp['gia_tien']})");

        // 4. Cập nhật số lượng trong bảng san_pham
        $phep_tinh = ($loai == 'nhap') ? "+" : "-";
        $conn->query("UPDATE san_pham SET so_luong_kho = so_luong_kho $phep_tinh $sl WHERE id_sp = $id_sp");

        // Thành công: Chuyển hướng sang trang lịch sử
        header("Location: lich_su_nhap.php");
        exit();
    } else {
        die("Lỗi Database: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Điều Phối Kho VIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .container { margin-top: 60px; }
        .card-vip { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .card-header-vip {
            background: linear-gradient(45deg, #0d6efd, #00d4ff);
            color: white;
            padding: 20px;
            font-weight: bold;
            text-align: center;
            border: none;
        }
        .btn-confirm {
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-confirm:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13,110,253,0.4); }
        .form-label { font-weight: 600; color: #444; }
        .form-control, .form-select { border-radius: 10px; padding: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-vip">
                <div class="card-header-vip">
                    <h3 class="m-0"><i class="fa-solid fa-boxes-stacked"></i> ĐIỀU PHỐI KHO</h3>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Chọn sản phẩm:</label>
                            <select name="id_sp" class="form-select" required>
                                <?php 
                                $res = $conn->query("SELECT id_sp, ten_sp, so_luong_kho FROM san_pham");
                                while($r = $res->fetch_assoc()) {
                                    echo "<option value='{$r['id_sp']}'>{$r['ten_sp']} (Tồn: {$r['so_luong_kho']})</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình thức giao dịch:</label>
                            <select name="loai_giao_dich" class="form-select">
                                <option value="nhap" class="text-success">Nhập thêm (+)</option>
                                <option value="xuat" class="text-danger">Xuất kho (-)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số lượng:</label>
                            <input type="number" name="so_luong" class="form-control" value="1" min="1" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Ghi chú / Đối tác:</label>
                            <input type="text" name="ghi_chu" class="form-control" placeholder="Tên khách hàng hoặc nhà cung cấp...">
                        </div>

                        <button type="submit" name="btn_save" class="btn btn-primary btn-confirm w-100 text-white">
                            <i class="fa-solid fa-circle-check"></i> XÁC NHẬN & LƯU LỊCH SỬ
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="text-decoration-none text-muted small"><i class="fa fa-arrow-left"></i> Quay lại trang chủ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>