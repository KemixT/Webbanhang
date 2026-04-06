<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
if ($conn->connect_error) { die("Lỗi kết nối: " . $conn->connect_error); }
mysqli_set_charset($conn, "utf8");

$type = isset($_GET['type']) ? $_GET['type'] : 'kho';
$tu_ngay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01');
$den_ngay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-d');

if ($type == 'doanhthu') {
    $filename = "Bao_cao_doanh_thu_" . $tu_ngay . "_to_." . $den_ngay . ".xls";
} else {
    $filename = "Bao_cao_ton_kho_" . date('d-m-Y') . ".xls";
}

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=$filename");

echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
echo '<table border="1">';

if ($type == 'doanhthu') {
    echo '<tr><th colspan="7" style="font-size:20px; text-transform:uppercase;">BÁO CÁO DOANH THU CHI TIẾT</th></tr>';
    echo '<tr><th colspan="7">Thời gian: Từ ngày ' . $tu_ngay . ' đến ngày ' . $den_ngay . '</th></tr>';
    echo '<tr>
            <th style="background-color: #eee;">STT</th>
            <th style="background-color: #eee;">Mã Sản Phẩm</th>
            <th style="background-color: #eee;">Tên Sản Phẩm</th>
            <th style="background-color: #eee;">Tên Người Mua</th>
            <th style="background-color: #eee;">Giá Bán</th>
            <th style="background-color: #eee;">Số Lượng</th>
            <th style="background-color: #eee;">Thành Tiền</th>
          </tr>';

    $sql = "SELECT hoa_don.*, nguoi_dung.ho_ten 
            FROM hoa_don 
            LEFT JOIN nguoi_dung ON hoa_don.id_nguoi_dung = nguoi_dung.id_nd 
            WHERE DATE(ngay_thanh_toan) BETWEEN '$tu_ngay' AND '$den_ngay' 
            ORDER BY ngay_thanh_toan DESC";
    $res = $conn->query($sql);
    
    $stt = 1;
    $tong_cong = 0;
    while($row = $res->fetch_assoc()) {
        $thanh_tien = $row['gia_ban'] * $row['so_luong_mua'];
        $tong_cong += $thanh_tien;
        $nguoi_mua = $row['ho_ten'] ? $row['ho_ten'] : 'Khách vãng lai';
        echo "<tr>
                <td align='center'>$stt</td>
                <td>{$row['ma_san_pham']}</td>
                <td>{$row['ten_sp']}</td>
                <td>$nguoi_mua</td>
                <td align='right'>" . number_format($row['gia_ban']) . "</td>
                <td align='center'>{$row['so_luong_mua']}</td>
                <td align='right' style='color:red;'>" . number_format($thanh_tien) . "</td>
              </tr>";
        $stt++;
    }
    echo '<tr>
            <th colspan="6" align="right">TỔNG CỘNG DOANH THU:</th>
            <th align="right" style="background-color: yellow; color: red;">' . number_format($tong_cong) . ' VNĐ</th>
          </tr>';

} else {
    echo '<tr><th colspan="5" style="font-size:20px; text-transform:uppercase;">BÁO CÁO TỒN KHO LINH KIỆN</th></tr>';
    echo '<tr><th colspan="5">Ngày báo cáo: ' . date('d/m/Y H:i') . '</th></tr>';
    echo '<tr>
            <th style="background-color: #eee;">STT</th>
            <th style="background-color: #eee;">Mã SP</th>
            <th style="background-color: #eee;">Tên Sản Phẩm</th>
            <th style="background-color: #eee;">Tồn Kho</th>
            <th style="background-color: #eee;">Giá Trị Tồn</th>
          </tr>';
    
    $res = $conn->query("SELECT * FROM san_pham ORDER BY id_sp DESC");
    $stt = 1;
    $tong_kho = 0;
    while($row = $res->fetch_assoc()) {
        $gia_tri = $row['gia_tien'] * $row['so_luong_kho'];
        $tong_kho += $gia_tri;
        echo "<tr>
                <td align='center'>$stt</td>
                <td>{$row['ma_san_pham']}</td>
                <td>{$row['ten_sp']}</td>
                <td align='center'>{$row['so_luong_kho']}</td>
                <td align='right'>" . number_format($gia_tri) . "</td>
              </tr>";
        $stt++;
    }
    echo '<tr>
            <th colspan="4" align="right">TỔNG GIÁ TRỊ KHO:</th>
            <th align="right" style="background-color: yellow;">' . number_format($tong_kho) . ' VNĐ</th>
          </tr>';
}

echo '</table>';
?>