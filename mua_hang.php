<?php
session_start();
$conn = new mysqli("localhost", "root", "", "quan_ly_kho");
mysqli_set_charset($conn, "utf8");

// XỬ LÝ THÊM VÀO GIỎ HÀNG TRỰC TIẾP TRÊN TRANG NÀY
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
    // Sau khi thêm thì reload trang để cập nhật số trên badge
    header("Location: mua_hang.php");
    exit();
}

$total_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) { $total_items += $qty; }
}

$search = isset($_GET['query']) ? $_GET['query'] : "";
$sort = isset($_GET['sort']) ? $_GET['sort'] : "newest";
$category = isset($_GET['category']) ? $_GET['category'] : "";

$order_sql = "san_pham.id_sp DESC"; 
if($sort == "price_asc") $order_sql = "san_pham.gia_tien ASC";
if($sort == "price_desc") $order_sql = "san_pham.gia_tien DESC";

$where_clauses = [];
if ($search != "") { $where_clauses[] = "(san_pham.ten_sp LIKE '%$search%' OR san_pham.ma_san_pham LIKE '%$search%')"; }
if ($category != "") { $where_clauses[] = "danh_muc.ten_danh_muc = '$category'"; }
$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$sql = "SELECT san_pham.*, danh_muc.ten_danh_muc 
        FROM san_pham 
        LEFT JOIN danh_muc ON san_pham.ma_danh_muc = danh_muc.id_danh_muc 
        $where_sql ORDER BY $order_sql";
$result = $conn->query($sql);

$is_logged_in = false;
$name_to_show = "";
if (isset($_SESSION['ho_ten'])) {
    $name_to_show = $_SESSION['ho_ten'];
    $is_logged_in = true;
} elseif (isset($_SESSION['user_admin'])) {
    $name_to_show = $_SESSION['user_admin'];
    $is_logged_in = true;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Electricity - Cửa Hàng Linh Kiện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0b0b0b; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; }
        .side-banner { position: fixed; top: 100px; width: 220px; z-index: 999; display: block !important; }
        @media (max-width: 1500px) { .side-banner { display: none !important; } }
        .banner-left { left: 20px; }
        .banner-right { right: 20px; }
        .side-banner img { width: 100%; height: 650px; object-fit: cover; border: 3px solid #28a745; border-radius: 15px; box-shadow: 0 0 25px rgba(40, 167, 69, 0.6); transition: 0.3s; }
        .navbar { background: #121212; border-bottom: 2px solid #28a745; z-index: 1000; padding: 10px 0; }
        .navbar-brand { color: #28a745 !important; font-size: 26px; font-weight: 800; }
        .search-btn { background: #28a745; color: white; border: none; padding: 0 15px; }
        .btn-logout { color: #ff4d4d; text-decoration: none; font-weight: bold; margin-left: 15px; border: 1px solid #ff4d4d; padding: 4px 10px; border-radius: 5px; font-size: 14px; }
        .btn-login { color: #28a745; text-decoration: none; font-weight: bold; border: 1px solid #28a745; padding: 5px 15px; border-radius: 5px; }
        .promo-banner { background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80&w=1000'); background-size: cover; border-radius: 12px; padding: 40px; margin-bottom: 30px; border: 1px solid #28a745; }
        .category-link { display: block; padding: 12px; color: #bbb; text-decoration: none; background: #1a1a1a; border-radius: 5px; margin-bottom: 6px; }
        .category-link.active, .category-link:hover { background: #28a745; color: white; }
        .product-card { background: #1a1a1a; border: 1px solid #333; border-radius: 10px; transition: 0.3s; height: 100%; text-align: center; overflow: hidden; display: flex; flex-direction: column; cursor: pointer; }
        .product-card:hover { border-color: #28a745; transform: translateY(-5px); }
        .card-img-top { height: 200px; object-fit: contain; background: #222; padding: 10px; }
        .price-text { color: #28a745; font-size: 20px; font-weight: bold; }
        .btn-add-cart { background: #28a745; color: white; font-weight: bold; padding: 12px; border: none; width: 100%; }
        .sort-bar { background: #1a1a1a; padding: 12px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #333; }
        .sort-btn { background: #252525; color: #aaa; border: 1px solid #444; padding: 5px 12px; border-radius: 4px; text-decoration: none; margin-left: 5px; font-size: 13px; }
        .sort-btn.active { background: #28a745; color: white; }
    </style>
</head>
<body>

<div class="side-banner banner-left"><img src="https://images.unsplash.com/photo-1597733336794-12d05021d510?auto=format&fit=crop&q=80&w=600"></div>
<div class="side-banner banner-right"><img src="https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&q=80&w=600"></div>

<nav class="navbar sticky-top mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="mua_hang.php">ELECTRO<span style="color:white">HUB</span></a>
        <form class="d-flex w-50" action="mua_hang.php" method="GET">
            <input type="text" name="query" class="form-control" placeholder="Tìm linh kiện..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
        </form>
        <div class="d-flex align-items-center">
            <?php if ($is_logged_in): ?>
                <span class="text-white fw-bold me-2">👤 <?= htmlspecialchars($name_to_show) ?></span>
                <a href="dang_xuat.php" class="btn-logout" onclick="return confirm('Bạn muốn đăng xuất chứ?')">Đăng xuất</a>
            <?php else: ?>
                <a href="dang_nhap.php" class="btn-login me-3">ĐĂNG NHẬP</a>
            <?php endif; ?>
            <a href="giohang.php" class="text-success position-relative ms-3">
                <i class="fa fa-shopping-basket fs-3"></i>
                <span id="cart-count" class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-circle" style="<?= ($total_items > 0) ? '' : 'display:none;' ?>">
                    <?= $total_items ?>
                </span>
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="promo-banner text-center">
        <h2 class="fw-bold text-success">FLASH SALE - GIÁ HỦY DIỆT</h2>
        <p class="fs-5">Linh kiện tự động hóa ưu đãi 50%!</p>
        <button class="btn btn-success rounded-pill px-4 fw-bold">SĂN DEAL NGAY</button>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <h5 class="fw-bold mb-3 text-success">📂 DANH MỤC</h5>
            <a href="mua_hang.php" class="category-link <?= ($category == '') ? 'active' : '' ?>">Tất cả sản phẩm</a>
            <?php
            $res_dm = $conn->query("SELECT * FROM danh_muc");
            while($dm = $res_dm->fetch_assoc()):
                $curr_dm = $dm['ten_danh_muc'];
            ?>
                <a href="?category=<?= urlencode($curr_dm) ?>&query=<?= urlencode($search) ?>" class="category-link <?= ($category == $curr_dm) ? 'active' : '' ?>">
                    <?= $curr_dm ?>
                </a>
            <?php endwhile; ?>
        </div>

        <div class="col-lg-9">
            <div class="sort-bar d-flex align-items-center">
                <span class="text-secondary small fw-bold me-auto">— GỢI Ý HÔM NAY —</span>
                <a href="?sort=newest" class="sort-btn <?= ($sort == 'newest') ? 'active' : '' ?>">Mới nhất</a>
                <a href="?sort=price_asc" class="sort-btn <?= ($sort == 'price_asc') ? 'active' : '' ?>">Giá thấp</a>
                <a href="?sort=price_desc" class="sort-btn <?= ($sort == 'price_desc') ? 'active' : '' ?>">Giá cao</a>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $json_data = json_encode([
                            'ten' => $row['ten_sp'],
                            'gia' => number_format($row['gia_tien']) . 'đ',
                            'anh' => 'uploads/' . $row['hinh_anh'],
                            'dm' => $row['ten_danh_muc'],
                            'mota' => $row['mo_ta'] ?? 'Sản phẩm chất lượng.'
                        ], JSON_HEX_APOS | JSON_HEX_QUOT);
                    ?>
                        <div class="col">
                            <div class="product-card" onclick='openModal(<?= $json_data ?>)'>
                                <img src="uploads/<?= $row['hinh_anh'] ?>" class="card-img-top" onerror="this.src='https://via.placeholder.com/300/1a1a1a/28a745?text=Linh+Kien'">
                                <div class="p-3">
                                    <h6 class="fw-bold text-white"><?= $row['ten_sp'] ?></h6>
                                    <p class="price-text"><?= number_format($row['gia_tien']) ?>đ</p>
                                    
                                    <form method="POST" action="mua_hang.php" onclick="event.stopPropagation()">
                                        <input type="hidden" name="product_id" value="<?= $row['id_sp'] ?>">
                                        <div class="d-flex justify-content-center align-items-center mb-3">
                                            <button type="button" class="btn btn-dark btn-sm px-3" onclick="changeQty(<?= $row['id_sp'] ?>, -1)">-</button>
                                            <input type="text" name="quantity" id="qty_<?= $row['id_sp'] ?>" value="1" readonly class="bg-transparent text-white text-center border-0 mx-2" style="width:30px; font-weight:bold;">
                                            <button type="button" class="btn btn-dark btn-sm px-3" onclick="changeQty(<?= $row['id_sp'] ?>, 1)">+</button>
                                        </div>
                                        <button type="submit" name="add_to_cart" class="btn-add-cart">
                                            <i class="fa fa-cart-plus"></i> THÊM VÀO GIỎ
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: #1a1a1a; border: 1px solid #28a745; color: white;">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold text-success" id="m_title">Chi tiết sản phẩm</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 text-center">
                        <img id="m_img" src="" class="img-fluid rounded border border-secondary" style="max-height: 300px;">
                    </div>
                    <div class="col-md-7">
                        <h3 id="m_name" class="fw-bold text-white"></h3>
                        <h4 id="m_price" class="text-success fw-bold"></h4>
                        <p class="text-secondary small">Danh mục: <span id="m_cate" class="text-white"></span></p>
                        <hr class="border-secondary">
                        <p id="m_desc" style="color: #bbb; font-size: 15px;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openModal(data) {
    document.getElementById('m_title').innerText = data.ten;
    document.getElementById('m_name').innerText = data.ten;
    document.getElementById('m_price').innerText = data.gia;
    document.getElementById('m_img').src = data.anh;
    document.getElementById('m_cate').innerText = data.dm;
    document.getElementById('m_desc').innerText = data.mota;
    var myModal = new bootstrap.Modal(document.getElementById('productModal'));
    myModal.show();
}

function changeQty(id, delta) {
    let input = document.getElementById('qty_' + id);
    let val = parseInt(input.value) + delta;
    if (val >= 1) input.value = val;
}
</script>
</body>
</html>