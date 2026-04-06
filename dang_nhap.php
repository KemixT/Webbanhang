<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

if (isset($_POST['btn_reg'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $name = $_POST['ho_ten'];
    $check = $conn->query("SELECT * FROM nguoi_dung WHERE ten_dang_nhap='$user'");
    if ($check->num_rows > 0) {
        $error_reg = "Tên đăng nhập này đã được sử dụng!";
    } else {
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, vai_tro) VALUES ('$user', '$pass', '$name', 'khach_hang')";
        if ($conn->query($sql)) {
            $_SESSION['id_nd'] = $conn->insert_id;
            $_SESSION['user_admin'] = $user;
            $_SESSION['vai_tro'] = 'khach_hang';
            $_SESSION['ho_ten'] = $name;
            header("Location: mua_hang.php");
            exit();
        }
    }
}

if (isset($_POST['btn_login'])) {
    $u = $_POST['user'];
    $p = $_POST['pass'];
    $res = $conn->query("SELECT * FROM nguoi_dung WHERE ten_dang_nhap='$u' AND mat_khau='$p'");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $_SESSION['id_nd'] = $row['id_nd'];
        $_SESSION['user_admin'] = $row['ten_dang_nhap'];
        $_SESSION['vai_tro'] = $row['vai_tro'];
        $_SESSION['ho_ten'] = $row['ho_ten'];
        if ($row['vai_tro'] == 'admin') {
            header("Location: index.php");
        } else {
            header("Location: mua_hang.php");
        }
        exit();
    } else {
        $error_login = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập hệ thống</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        input { width: 90%; padding: 12px; margin: 10px 0; border: 1px solid #dddfe2; border-radius: 6px; font-size: 16px; }
        .btn-login { width: 95%; padding: 12px; background-color: #1877f2; border: none; color: white; font-size: 20px; font-weight: bold; border-radius: 6px; cursor: pointer; }
        .btn-reg-green { background-color: #42b72a; color: white; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .divider { border-bottom: 1px solid #dadde1; margin: 20px 0; }
        .link { color: #1877f2; text-decoration: none; font-size: 14px; cursor: pointer; }
    </style>
    <script>
        function toggleForm(type) {
            document.getElementById('login_form').style.display = (type === 'login') ? 'block' : 'none';
            document.getElementById('reg_form').style.display = (type === 'reg') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="login-container">
        <div id="login_form">
            <h2 style="color:#1877f2; font-size: 32px; margin-bottom: 10px;">Đăng nhập</h2>
            <form method="POST">
                <input type="text" name="user" placeholder="Email hoặc số điện thoại" required>
                <input type="password" name="pass" placeholder="Mật khẩu" required>
                <button name="btn_login" class="btn-login">Đăng nhập</button>
                <div class="divider"></div>
                <button type="button" class="btn-reg-green" onclick="toggleForm('reg')">Tạo tài khoản mới</button>
            </form>
            <?php if(isset($error_login)) echo "<p style='color:red'>$error_login</p>"; ?>
        </div>

        <div id="reg_form" style="display:none;">
            <h2>Đăng ký thành viên</h2>
            <form method="POST">
                <input type="text" name="ho_ten" placeholder="Họ và Tên" required>
                <input type="text" name="user" placeholder="Tên đăng nhập" required>
                <input type="password" name="pass" placeholder="Mật khẩu mới" required>
                <button name="btn_reg" class="btn-reg-green" style="width:95%">Đăng ký ngay</button>
                <p class="link" onclick="toggleForm('login')">Đã có tài khoản?</p>
            </form>
            <?php if(isset($error_reg)) echo "<p style='color:red'>$error_reg</p>"; ?>
        </div>
    </div>
</body>
</html>