<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// 1. TỰ ĐỘNG SỬA LỖI DATABASE: Kiểm tra và thêm cột id_nguoi_dung nếu chưa có
$check_column = $conn->query("SHOW COLUMNS FROM `hoa_don` LIKE 'id_nguoi_dung'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE hoa_don ADD COLUMN id_nguoi_dung INT AFTER id_hd");
}

// 2. XỬ LÝ THANH TOÁN (Trừ kho + Ghi hóa đơn)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tru_kho_thanh_toan') {
    if (!empty($_SESSION['cart'])) {
        // Lấy ID người dùng từ session, nếu chưa đăng nhập thì để NULL
        $id_nd = isset($_SESSION['id_nd']) ? $_SESSION['id_nd'] : "NULL";

        foreach ($_SESSION['cart'] as $id_sp => $sl_mua) {
            $id_sp = (int)$id_sp;
            
            // Lấy thông tin sản phẩm để lưu vào hóa đơn
            $res = $conn->query("SELECT * FROM san_pham WHERE id_sp = $id_sp");
            $sp = $res->fetch_assoc();
            
            if ($sp) {
                $ma_sp = $sp['ma_san_pham'];
                $ten_sp = $sp['ten_sp'];
                $gia_ban = $sp['gia_tien'];

                // Trừ số lượng trong kho
                $conn->query("UPDATE san_pham SET so_luong_kho = so_luong_kho - $sl_mua WHERE id_sp = $id_sp");
                
                // Lưu vào bảng hóa đơn (kèm theo ID người mua)
                $sql_insert = "INSERT INTO hoa_don (id_nguoi_dung, id_sp, ma_san_pham, ten_sp, gia_ban, so_luong_mua) 
                               VALUES ($id_nd, $id_sp, '$ma_sp', '$ten_sp', $gia_ban, $sl_mua)";
                $conn->query($sql_insert);
            }
        }
        
        // Xóa giỏ hàng sau khi xong
        unset($_SESSION['cart']);
        echo "<script>alert('✅ Thanh toán thành công! Đã lưu người mua.'); window.location.href = 'mua_hang.php';</script>";
        exit();
    }
}

// 3. LOGIC THÊM VÀO GIỎ HÀNG (AJAX)
if (isset($_GET['id']) && isset($_GET['quantity'])) {
    $id = (int)$_GET['id'];
    $sl_mua = (int)$_GET['quantity'];
    
    $res = $conn->query("SELECT so_luong_kho, ten_sp FROM san_pham WHERE id_sp = $id");
    $sp = $res->fetch_assoc();

    if (!$sp || $sp['so_luong_kho'] < $sl_mua) {
        $msg = !$sp ? "Sản phẩm không tồn tại!" : "Sản phẩm {$sp['ten_sp']} chỉ còn {$sp['so_luong_kho']} cái!";
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit();
    }

    $_SESSION['cart'][$id] = isset($_SESSION['cart'][$id]) ? $_SESSION['cart'][$id] + $sl_mua : $sl_mua;
    $totalItems = array_sum($_SESSION['cart']);
    echo json_encode(['status' => 'success', 'totalItems' => $totalItems]);
    exit();
}
?>