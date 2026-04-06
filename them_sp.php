<?php
session_start();
if (!isset($_SESSION['user_admin']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// Tự động kiểm tra và thêm cột mo_ta nếu chưa có
$check_column = $conn->query("SHOW COLUMNS FROM `san_pham` LIKE 'mo_ta'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE san_pham ADD COLUMN mo_ta TEXT NULL AFTER ten_sp");
}

if (isset($_POST['btn_them'])) {
    // SỬA TẠI ĐÂY: Dùng isset để kiểm tra chắc chắn có dữ liệu mo_ta truyền lên
    $ma = $conn->real_escape_string($_POST['ma_sp']);
    $ten = $conn->real_escape_string($_POST['ten_sp']);
    $mo_ta = isset($_POST['mo_ta']) ? $conn->real_escape_string($_POST['mo_ta']) : ""; 
    $gia = $_POST['gia_tien'];
    $kho = $_POST['so_luong'];
    $id_dm = $_POST['id_danh_muc'];

    $anh = "default.jpg"; 
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $anh = time() . "_" . $_FILES['hinh_anh']['name'];
        if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }
        move_uploaded_file($_FILES['hinh_anh']['tmp_name'], "uploads/" . $anh);
    }

    // Câu lệnh SQL chuẩn theo database của ông
    $sql = "INSERT INTO san_pham (ma_san_pham, ten_sp, mo_ta, gia_tien, so_luong_kho, hinh_anh, ma_danh_muc) 
            VALUES ('$ma', '$ten', '$mo_ta', '$gia', '$kho', '$anh', '$id_dm')";

    if ($conn->query($sql)) {
        echo "<script>alert('Thêm linh kiện mới thành công!'); window.location.href='index.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm sản phẩm mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container bg-white p-4 shadow-sm rounded" style="max-width: 650px;">
        <h2 class="text-center mb-4 text-primary fw-bold">THÊM MỚI LINH KIỆN</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Mã sản phẩm:</label>
                    <input type="text" name="ma_sp" class="form-control" placeholder="VD: RES-001" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Tên sản phẩm:</label>
                    <input type="text" name="ten_sp" class="form-control" placeholder="VD: Điện trở 10k" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Mô tả sản phẩm:</label>
                <textarea name="mo_ta" class="form-control" rows="3" placeholder="Nhập thông số kỹ thuật..."></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Giá bán (VNĐ):</label>
                    <input type="number" name="gia_tien" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Số lượng nhập kho:</label>
                    <input type="number" name="so_luong" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Danh mục:</label>
                <select name="id_danh_muc" class="form-control">
                    <?php
                    $dm = $conn->query("SELECT * FROM danh_muc");
                    while($r = $dm->fetch_assoc()) echo "<option value='".$r['id_danh_muc']."'>".$r['ten_danh_muc']."</option>";
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Hình ảnh minh họa:</label>
                <input type="file" name="hinh_anh" class="form-control">
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button name="btn_them" type="submit" class="btn btn-primary w-100 fw-bold">THÊM VÀO KHO</button>
                <a href="index.php" class="btn btn-secondary w-100">QUAY LẠI</a>
            </div>
        </form>
    </div>
</body>
</html>