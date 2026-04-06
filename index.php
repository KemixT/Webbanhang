<?php
session_start();
if (!isset($_SESSION['user_admin'])) {
    header("Location: dang_nhap.php");
    exit();
}

$la_admin = ($_SESSION['vai_tro'] == 'admin');
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8"); 

// 1. Lấy ngày lọc
$tu_ngay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01');
$den_ngay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

// 2. Tính Tổng vốn tồn kho
$tong_von = $conn->query("SELECT SUM(gia_tien * so_luong_kho) as tong FROM san_pham")->fetch_assoc()['tong'] ?? 0;

// 3. Tính Tổng doanh thu thực tế
$doanh_thu_query = $conn->query("SELECT SUM(gia_ban * so_luong_mua) as tong_dt 
                                FROM hoa_don 
                                WHERE DATE(ngay_thanh_toan) BETWEEN '$tu_ngay' AND '$den_ngay'");
$tong_doanh_thu = $doanh_thu_query->fetch_assoc()['tong_dt'] ?? 0;

// 4. Kiểm tra trạng thái xem báo cáo
$show_report = isset($_GET['view_report']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản trị Nội bộ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 5px; color: white; font-size: 13px; display: inline-block; margin: 2px; font-weight: bold; border: none; cursor: pointer; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter-box { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background: #fff3e0; padding: 15px; border-radius: 8px; border: 1px solid #ffe0b2; margin-top: 15px; }
        .role-badge { padding: 3px 8px; border-radius: 15px; font-size: 11px; font-weight: bold; }
        .bg-admin { background: #ffebee; color: #c62828; }
        .bg-user { background: #e3f2fd; color: #1565c0; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>HỆ THỐNG QUẢN TRỊ NỘI BỘ</h1>
    <p>Chào <b><?php echo $_SESSION['user_admin']; ?></b> (<?php echo $_SESSION['vai_tro']; ?>) | <a href="dang_xuat.php" style="color:red;">Đăng xuất</a></p>

    <div class="card" style="border-left: 5px solid #0277bd;">
        <h3 style="margin-top: 0; color: #0277bd;"><i class="fa fa-chart-pie"></i> THỐNG KÊ TỔNG QUAN</h3>
        <div style="display: flex; gap: 50px;">
            <div>
                <p style="margin:0; color: #666;">Giá trị hàng tồn:</p>
                <b style="font-size: 22px; color: red;"><?php echo number_format($tong_von); ?> VNĐ</b>
            </div>
            <div style="padding-left: 50px; border-left: 2px solid #eee;">
                <p style="margin:0; color: #666;">Doanh thu thực tế:</p>
                <b style="font-size: 22px; color: #28a745;"><?php echo number_format($tong_doanh_thu); ?> VNĐ</b>
            </div>
        </div>

        <form method="GET" class="filter-box">
            <div>
                <label style="display:block; font-size: 12px; font-weight:bold;">TỪ NGÀY:</label>
                <input type="date" name="tu_ngay" value="<?php echo $tu_ngay; ?>" style="padding:5px; border:1px solid #ccc; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; font-size: 12px; font-weight:bold;">ĐẾN NGÀY:</label>
                <input type="date" name="den_ngay" value="<?php echo $den_ngay; ?>" style="padding:5px; border:1px solid #ccc; border-radius:4px;">
            </div>
            <button type="submit" class="btn" style="background: #6f42c1;">
                <i class="fa fa-sync"></i> Lọc dữ liệu
            </button>
            <a href="index.php?view_report=1&tu_ngay=<?php echo $tu_ngay; ?>&den_ngay=<?php echo $den_ngay; ?>" class="btn" style="background: #17a2b8;">
                <i class="fa fa-file-invoice"></i> Xem báo cáo
            </a>
            <a href="xuat_excel.php?type=doanhthu&tu_ngay=<?php echo $tu_ngay; ?>&den_ngay=<?php echo $den_ngay; ?>" class="btn" style="background: #28a745;">
                <i class="fa fa-file-excel"></i> Xuất Excel
            </a>
        </form>
    </div>

    <?php if ($show_report): ?>
    <div class="card" style="border: 2px solid #17a2b8;">
        <h3 style="margin-top:0; color: #17a2b8;"><i class="fa fa-list-alt"></i> BÁO CÁO DOANH THU CHI TIẾT</h3>
        <table>
            <thead>
                <tr style="background: #e0f7fa;">
                    <th>STT</th>
                    <th>ID SP</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá Bán</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                    <th>Ngày Bán</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Sử dụng câu lệnh SQL cơ bản nhất để tránh lỗi Unknown column
                $sql_hd = "SELECT 
                            hd.id_sp, 
                            sp.ten_sp, 
                            hd.gia_ban, 
                            hd.so_luong_mua, 
                            hd.ngay_thanh_toan
                           FROM hoa_don hd
                           JOIN san_pham sp ON hd.id_sp = sp.id_sp
                           WHERE DATE(hd.ngay_thanh_toan) BETWEEN '$tu_ngay' AND '$den_ngay'
                           ORDER BY hd.ngay_thanh_toan DESC";
                
                $res_hd = $conn->query($sql_hd);
                $stt = 1;
                if($res_hd && $res_hd->num_rows > 0):
                    while($hd = $res_hd->fetch_assoc()): 
                        $thanh_tien = $hd['so_luong_mua'] * $hd['gia_ban'];
                    ?>
                    <tr>
                        <td class="text-center"><?php echo $stt++; ?></td>
                        <td><?php echo $hd['id_sp']; ?></td>
                        <td><b><?php echo $hd['ten_sp']; ?></b></td>
                        <td><?php echo number_format($hd['gia_ban']); ?> đ</td>
                        <td class="text-center"><?php echo $hd['so_luong_mua']; ?></td>
                        <td style="font-weight:bold; color:red;"><?php echo number_format($thanh_tien); ?> đ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($hd['ngay_thanh_toan'])); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center">Chưa có dữ liệu trong khoảng này.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div style="margin-top: 10px; text-align: right;">
            <a href="index.php?tu_ngay=<?php echo $tu_ngay; ?>&den_ngay=<?php echo $den_ngay; ?>" class="btn" style="background: #6c757d;">Đóng báo cáo</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($la_admin): ?>
    <div class="card">
        <h3 style="margin-top:0;"><i class="fa fa-users"></i> QUẢN LÝ DANH SÁCH NHÂN VIÊN</h3>
        <div style="margin-bottom: 10px;">
            <a href="them_nhan_vien.php" class="btn" style="background: green;">+ Thêm nhân viên mới</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Tên đăng nhập</th>
                    <th>Vai trò</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $res_nv = $conn->query("SELECT id_nd, ho_ten, ten_dang_nhap, vai_tro FROM nguoi_dung WHERE vai_tro != 'khach_hang'");
                while($nv = $res_nv->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $nv['id_nd']; ?></td>
                    <td><b><?php echo $nv['ho_ten']; ?></b></td>
                    <td><?php echo $nv['ten_dang_nhap']; ?></td>
                    <td>
                        <span class="role-badge <?php echo ($nv['vai_tro'] == 'admin') ? 'bg-admin' : 'bg-user'; ?>">
                            <?php echo ($nv['vai_tro'] == 'admin') ? 'Admin' : 'Nhân viên'; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="xoa_nd.php?id=<?php echo $nv['id_nd']; ?>" class="btn" style="background: red;" onclick="return confirm('Xóa nhân viên này?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3 style="margin-top:0;"><i class="fa fa-box"></i> DANH SÁCH LINH KIỆN TRONG KHO</h3>
        <div style="margin-bottom: 10px;">
            <?php if ($la_admin): ?>
                <a href="them_sp.php" class="btn" style="background: green;">+ Thêm sản phẩm</a>
            <?php endif; ?>
            <a href="nhap_xuat.php" class="btn" style="background: purple;">Nhập/Xuất kho nhanh</a>
            <a href="lich_su_nhap.php" class="btn" style="background: #e67e22;"><i class="fa fa-history"></i> Lịch sử nhập kho</a>
            <a href="xuat_excel.php?type=kho" class="btn" style="background: #1d6f42;">📥 Xuất Excel hàng tổn kho</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá bán</th>
                    <th>Số lượng</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $result_sp = $conn->query("SELECT san_pham.*, danh_muc.ten_danh_muc FROM san_pham LEFT JOIN danh_muc ON san_pham.ma_danh_muc = danh_muc.id_danh_muc ORDER BY id_sp DESC");
                while($row = $result_sp->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id_sp']; ?></td>
                    <td><b><?php echo $row['ten_sp']; ?></b></td>
                    <td style="color: blue; font-weight: bold;"><?php echo $row['ten_danh_muc'] ?? 'Chưa phân loại'; ?></td>
                    <td><?php echo number_format($row['gia_tien']); ?> đ</td>
                    <td style="<?php echo ($row['so_luong_kho'] < 5) ? 'background: #fff3cd;' : '';?>">
                        <?php echo ($row['so_luong_kho'] <= 0) ? '<b style="color:red;">HẾT HÀNG</b>' : $row['so_luong_kho']; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($la_admin): ?>
                            <a href="sua_sp.php?id=<?php echo $row['id_sp']; ?>" class="btn" style="background: orange;">Sửa</a>
                            <a href="xoa_sp.php?id=<?php echo $row['id_sp']; ?>" class="btn" style="background: red;" onclick="return confirm('Xóa sản phẩm?')">Xóa</a>
                        <?php else: ?>
                            <span style="color: #ccc; font-style: italic;">Chỉ Admin</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>