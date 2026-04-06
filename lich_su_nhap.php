<?php
session_start();
if (!isset($_SESSION['user_admin'])) { header("Location: dang_nhap.php"); exit(); }

$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// Truy vấn lấy dữ liệu và tính toán sơ bộ
$sql = "SELECT hdb.ma_hd, sp.ten_sp, ct.so_luong_ban, ct.gia_ban_luc_do, hdb.ngay_ban 
        FROM hdb 
        JOIN chi_tiet_hdb ct ON hdb.id_hdb = ct.id_hdb
        JOIN san_pham sp ON ct.id_sp = sp.id_sp
        ORDER BY hdb.ngay_ban DESC";
$result = $conn->query($sql);

// Tính toán thống kê nhanh
$total_nhap = 0; $total_xuat = 0;
$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
    if(strpos($row['ma_hd'], 'NK') !== false) $total_nhap += $row['so_luong_ban'];
    if(strpos($row['ma_hd'], 'XK') !== false) $total_xuat += $row['so_luong_ban'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Kho VIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma; }
        .container { margin-top: 30px; }
        .stat-card { border: none; border-radius: 15px; transition: 0.3s; color: white; }
        .stat-card:hover { transform: translateY(-5px); }
        .bg-gradient-blue { background: linear-gradient(45deg, #007bff, #00d4ff); }
        .bg-gradient-green { background: linear-gradient(45deg, #28a745, #20c997); }
        .bg-gradient-red { background: linear-gradient(45deg, #dc3545, #ff4d5a); }
        .table-container { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .badge-nhap { background: #e8f5e9; color: #2e7d32; border: 1px solid #2e7d32; }
        .badge-xuat { background: #ffebee; color: #c62828; border: 1px solid #c62828; }
        .table thead th { background: #f8f9fa; border: none; color: #666; text-transform: uppercase; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark"><i class="fa-solid fa-clock-rotate-left"></i> NHẬT KÝ KHO HÀNG</h2>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-blue p-3">
                <div class="d-flex justify-content-between">
                    <div><h5>Tổng Giao Dịch</h5><h2 class="fw-bold"><?php echo count($data); ?></h2></div>
                    <i class="fa fa-exchange-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-green p-3">
                <div class="d-flex justify-content-between">
                    <div><h5>Số Lượng Nhập</h5><h2 class="fw-bold">+<?php echo $total_nhap; ?></h2></div>
                    <i class="fa fa-arrow-down fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-gradient-red p-3">
                <div class="d-flex justify-content-between">
                    <div><h5>Số Lượng Xuất</h5><h2 class="fw-bold">-<?php echo $total_xuat; ?></h2></div>
                    <i class="fa fa-arrow-up fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Loại</th>
                    <th>Thời Gian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data as $row): 
                    $is_nhap = (strpos($row['ma_hd'], 'NK') !== false);
                ?>
                <tr>
                    <td><code class="fw-bold text-dark"><?php echo $row['ma_hd']; ?></code></td>
                    <td>
                        <div class="fw-bold"><?php echo $row['ten_sp']; ?></div>
                        <small class="text-muted">Đơn giá: <?php echo number_format($row['gia_ban_luc_do']); ?>đ</small>
                    </td>
                    <td><span class="fw-bold <?php echo $is_nhap ? 'text-success' : 'text-danger'; ?>">
                        <?php echo ($is_nhap ? '+' : '-') . $row['so_luong_ban']; ?>
                    </span></td>
                    <td>
                        <span class="badge rounded-pill <?php echo $is_nhap ? 'badge-nhap' : 'badge-xuat'; ?>">
                            <?php echo $is_nhap ? 'NHẬP KHO' : 'XUẤT KHO'; ?>
                        </span>
                    </td>
                    <td class="text-secondary"><?php echo date('d/m/Y | H:i', strtotime($row['ngay_ban'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>