<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

if (isset($_POST['btn_save'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass']; // Nên dùng password_hash nếu muốn bảo mật cao
    $name = $_POST['name'];
    $sdt  = $_POST['sdt'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Câu lệnh INSERT có thêm SDT và Email
    $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, so_dien_thoai, email, vai_tro) 
            VALUES ('$user', '$pass', '$name', '$sdt', '$email', '$role')";

    if ($conn->query($sql)) {
        echo "<script>alert('Thêm nhân viên thành công!'); window.location='danh_sach_nhan_vien.php';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Nhân Viên VIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .card-vip { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(45deg, #0d6efd, #00d4ff); color: white; padding: 20px; font-weight: bold; }
        .form-control, .form-select { border-radius: 10px; padding: 10px; margin-bottom: 15px; }
        .btn-save { background: linear-gradient(45deg, #0d6efd, #0099ff); border: none; padding: 12px; font-weight: bold; border-radius: 10px; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-vip">
                <div class="card-header text-center">
                    <h4 class="m-0"><i class="fa-user-plus"></i> THÊM NHÂN VIÊN MỚI</h4>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST">
                        <label class="small fw-bold">Tên đăng nhập</label>
                        <input type="text" name="user" class="form-control" placeholder="Ví dụ: nhanvien01" required>
                        
                        <label class="small fw-bold">Mật khẩu</label>
                        <input type="password" name="pass" class="form-control" placeholder="••••••" required>
                        
                        <label class="small fw-bold">Họ và Tên</label>
                        <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="small fw-bold">Số điện thoại</label>
                                <input type="text" name="sdt" class="form-control" placeholder="09xxx...">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="abc@gmail.com">
                            </div>
                        </div>

                        <label class="small fw-bold">Vai trò</label>
                        <select name="role" class="form-select">
                            <option value="Nhân viên kho">Nhân viên kho</option>
                            <option value="Quản trị viên">Quản trị viên</option>
                        </select>

                        <button type="submit" name="btn_save" class="btn btn-primary btn-save w-100 mt-3">
                             <i class="fa fa-save"></i> LƯU NGAY
                        </button>
                        <div class="text-center mt-3 small">
                            <a href="index.php" class="text-muted text-decoration-none">Quay lại</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>