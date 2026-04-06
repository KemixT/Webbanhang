<?php
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
if (isset($_POST['btn_dangky'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $name = $_POST['ho_ten'];
    $email = $_POST['email']; // Thêm Email
    $sdt = $_POST['sdt'];     // Thêm Số điện thoại
    
    // Kiểm tra tên đăng nhập đã tồn tại chưa
    $check = $conn->query("SELECT * FROM nguoi_dung WHERE ten_dang_nhap='$user'");
    if ($check->num_rows > 0) {
        $error = "Tên đăng nhập đã tồn tại!";
    } else {
        // Lưu thông tin kèm theo Email và SĐT
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, email, so_dien_thoai, vai_tro) 
                VALUES ('$user', '$pass', '$name', '$email', '$sdt', 'khach_hang')";
        
        if ($conn->query($sql)) {
            // Đẩy sang trang đăng nhập khách hàng sau khi thành công
            header("Location: dang_nhap_mua.php?msg=success");
            exit();
        } else {
            $error = "Lỗi: " . $conn->error;
        }
    }
}
?>
<form method="POST" style="width:350px; margin:50px auto; font-family:Arial; border:1px solid #ccc; padding:20px; border-radius:10px;">
    <h3 style="text-align:center;">ĐĂNG KÝ KHÁCH HÀNG</h3>
    Họ tên: <input name="ho_ten" required style="width:100%; margin-bottom:10px; padding:8px;">
    Email: <input name="email" type="email" required style="width:100%; margin-bottom:10px; padding:8px;">
    Số điện thoại: <input name="sdt" required style="width:100%; margin-bottom:10px; padding:8px;">
    <hr>
    Tên đăng nhập: <input name="user" required style="width:100%; margin-bottom:10px; padding:8px;">
    Mật khẩu: <input name="pass" type="password" required style="width:100%; margin-bottom:10px; padding:8px;">
    
    <button name="btn_dangky" type="submit" style="width:100%; background:green; color:white; border:none; padding:10px; cursor:pointer;">Đăng ký ngay</button>
    <p style="color:red;"><?php echo $error ?? ''; ?></p>
    <a href="dang_nhap_mua.php">Đã có tài khoản? Đăng nhập mua hàng</a>
</form>