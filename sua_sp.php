<?php
session_start();
if (!isset($_SESSION['user_admin']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// --- ĐOẠN MÃ TỰ ĐỘNG SỬA LỖI DATABASE (CHỈ CHẠY 1 LẦN) ---
$check_column = $conn->query("SHOW COLUMNS FROM `san_pham` LIKE 'mo_ta'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE san_pham ADD COLUMN mo_ta TEXT NULL AFTER ten_sp");
}
// -------------------------------------------------------

$id = (int)$_GET['id'];
$res = $conn->query("SELECT * FROM san_pham WHERE id_sp=$id");
$old = $res->fetch_assoc();

if (isset($_POST['btn_update'])) {
    // Sử dụng real_escape_string để tránh lỗi khi nhập ký tự đặc biệt như dấu ' hoặc "
    $ten = $conn->real_escape_string($_POST['ten']);
    $mo_ta = $conn->real_escape_string($_POST['mo_ta']); 
    $gia = $_POST['gia'];
    $sl = (int)$_POST['sl'];
    $id_dm = $_POST['id_danh_muc'];
    $ten_file_anh = $old['hinh_anh'];

    if (isset($_FILES['hinh_anh_moi']) && $_FILES['hinh_anh_moi']['error'] == 0) {
        $ten_file_anh = time() . "_" . $_FILES['hinh_anh_moi']['name'];
        move_uploaded_file($_FILES['hinh_anh_moi']['tmp_name'], "uploads/" . $ten_file_anh);
    }

    $sql = "UPDATE san_pham SET 
            ten_sp='$ten', 
            mo_ta='$mo_ta',
            gia_tien='$gia', 
            so_luong_kho='$sl', 
            ma_danh_muc='$id_dm', 
            hinh_anh='$ten_file_anh' 
            WHERE id_sp=$id";

    if ($conn->query($sql)) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='index.php';</script>";
    } else {
        echo "Lỗi SQL: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container bg-white p-4 shadow-sm rounded" style="max-width: 600px;">
        <h2 class="text-center mb-4 text-warning">CẬP NHẬT LINH KIỆN</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="fw-bold">Tên sản phẩm:</label>
                <input type="text" name="ten" class="form-control" value="<?php echo htmlspecialchars($old['ten_sp']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold">Mô tả sản phẩm:</label>
                <textarea name="mo_ta" class="form-control" rows="4"><?php echo isset($old['mo_ta']) ? htmlspecialchars($old['mo_ta']) : ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Giá bán:</label>
                    <input type="number" name="gia" class="form-control" value="<?php echo $old['gia_tien']; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold">Số lượng tồn:</label>
                    <input type="number" name="sl" class="form-control" value="<?php echo $old['so_luong_kho']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Danh mục:</label>
                <select name="id_danh_muc" class="form-control">
                    <?php
                    $dm = $conn->query("SELECT * FROM danh_muc");
                    while($r = $dm->fetch_assoc()) {
                        $sel = ($r['id_danh_muc'] == $old['ma_danh_muc']) ? "selected" : "";
                        echo "<option value='".$r['id_danh_muc']."' $sel>".$r['ten_danh_muc']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Hình ảnh:</label><br>
                <img src="uploads/<?php echo $old['hinh_anh']; ?>" width="100" class="mb-2 rounded border">
                <input type="file" name="hinh_anh_moi" class="form-control">
            </div>

            <div class="d-flex gap-2">
                <button name="btn_update" class="btn btn-warning w-100 fw-bold">LƯU THAY ĐỔI</button>
                <a href="index.php" class="btn btn-secondary w-100">HỦY</a>
            </div>
        </form>
    </div>
</body>
</html>