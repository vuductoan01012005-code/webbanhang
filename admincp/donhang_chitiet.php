<?php
/**
 * ============================================================
 *  TRANG CHI TIET DON HANG (ADMINCP)
 * ============================================================
 *  File: admincp/donhang_chitiet.php
 *
 *  CHUC NANG:
 *   - Xem thong tin nguoi nhan, dia chi, ngay dat
 *   - Xem danh sach san pham trong don (chitietdonhang)
 *   - Doi trang thai don hang (Dang xu ly / Hoan thanh / Da huy)
 *
 *  CACH DUNG: donhang_chitiet.php?id=5
 *
 *  LUU Y: gia dinh cau truc CSDL:
 *   - donhang(id, ma_kh, ten_nguoinhan, sdt, diachi, ngay_dat, tong_tien, trang_thai)
 *   - chitietdonhang(id, ma_dh, ma_sp, so_luong, don_gia)
 *   - sanpham(id, ten_sp, hinh_anh)
 * ============================================================
 */

session_start();

if (!isset($_SESSION['admin']) && !isset($_SESSION['admin_id'])) {
    // header("Location: login.php");
    // exit();
}

// ---------- KET NOI DATABASE (sua lai cho dung voi du an cua ban) ----------
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "webbanhang";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ket noi CSDL that bai: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// ---------- CAU HINH TEN BANG / COT ----------
$table_donhang = "donhang";
$table_ctdh    = "chitietdonhang";
$table_sanpham = "sanpham";

$ma_dh = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ma_dh <= 0) {
    die("Đơn hàng không hợp lệ.");
}

$thongbao = "";

// ---------- XU LY CAP NHAT TRANG THAI ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['capnhat_trangthai'])) {
    $trangthai_moi = (int)$_POST['trang_thai'];
    $stmt = $conn->prepare("UPDATE $table_donhang SET trang_thai = ? WHERE id = ?");
    $stmt->bind_param("ii", $trangthai_moi, $ma_dh);
    $stmt->execute();
    $stmt->close();
    $thongbao = "Cập nhật trạng thái thành công.";
}

// ---------- LAY THONG TIN DON HANG ----------
$stmt = $conn->prepare("SELECT * FROM $table_donhang WHERE id = ?");
$stmt->bind_param("i", $ma_dh);
$stmt->execute();
$don_hang = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$don_hang) {
    die("Không tìm thấy đơn hàng.");
}

// ---------- LAY CHI TIET SAN PHAM TRONG DON ----------
$stmt = $conn->prepare(
    "SELECT ct.so_luong, ct.don_gia, sp.ten_sp, sp.hinh_anh
     FROM $table_ctdh ct
     JOIN $table_sanpham sp ON ct.ma_sp = sp.id
     WHERE ct.ma_dh = ?"
);
$stmt->bind_param("i", $ma_dh);
$stmt->execute();
$result = $stmt->get_result();

$chi_tiet = [];
while ($row = $result->fetch_assoc()) {
    $chi_tiet[] = $row;
}
$stmt->close();
$conn->close();

function formatTien($so) {
    return number_format((float)$so, 0, ',', '.') . ' d';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết đơn hàng #<?php echo $ma_dh; ?></title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    .container { display:flex; gap:25px; flex-wrap:wrap; }
    .box { background:#fff; border-radius:8px; padding:20px 25px; box-shadow:0 1px 4px rgba(0,0,0,.1); }
    .info-box { flex:1; min-width:280px; }
    .sp-box { flex:2; min-width:300px; }
    .info-box p { margin:8px 0; font-size:14px; }
    .info-box b { color:#555; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    th { background:#fafafa; }
    img.sp-img { width:50px; height:50px; object-fit:cover; border-radius:4px; }
    select, button {
        padding:8px 14px; border-radius:5px; border:1px solid #ddd; font-size:14px;
    }
    button {
        background:#2980b9; color:#fff; border:none; cursor:pointer; margin-left:8px;
    }
    .thongbao { background:#eafaf1; color:#27ae60; padding:10px 15px; border-radius:5px; margin-bottom:15px; }
    .tong-tien { text-align:right; font-size:18px; font-weight:bold; margin-top:15px; color:#27ae60; }
    a.back { display:inline-block; margin-bottom:15px; color:#2980b9; text-decoration:none; }
</style>
</head>
<body>

<a class="back" href="donhang.php">&larr; Quay lại danh sách đơn hàng</a>
<h2>📦 Chi tiết đơn hàng #<?php echo $ma_dh; ?></h2>

<?php if ($thongbao): ?>
    <div class="thongbao"><?php echo htmlspecialchars($thongbao); ?></div>
<?php endif; ?>

<div class="container">

    <div class="box info-box">
        <h3>Thông tin người nhận</h3>
        <p><b>Họ tên:</b> <?php echo htmlspecialchars($don_hang['ten_nguoinhan']); ?></p>
        <p><b>SĐT:</b> <?php echo htmlspecialchars($don_hang['sdt']); ?></p>
        <p><b>Địa chỉ:</b> <?php echo htmlspecialchars($don_hang['diachi']); ?></p>
        <p><b>Ngày đặt:</b> <?php echo htmlspecialchars($don_hang['ngay_dat']); ?></p>

        <form method="POST" style="margin-top:20px;">
            <label><b>Trạng thái đơn hàng</b></label><br><br>
            <select name="trang_thai">
                <option value="1" <?php if ($don_hang['trang_thai'] == 1) echo 'selected'; ?>>Đang xử lý</option>
                <option value="2" <?php if ($don_hang['trang_thai'] == 2) echo 'selected'; ?>>Hoàn thành</option>
                <option value="0" <?php if ($don_hang['trang_thai'] == 0) echo 'selected'; ?>>Đã hủy</option>
            </select>
            <button type="submit" name="capnhat_trangthai">Cập nhật</button>
        </form>
    </div>

    <div class="box sp-box">
        <h3>Sản phẩm trong đơn</h3>
        <table>
            <tr><th>Ảnh</th><th>Tên sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr>
            <?php foreach ($chi_tiet as $sp): ?>
            <tr>
                <td><img class="sp-img" src="<?php echo htmlspecialchars($sp['hinh_anh']); ?>" alt=""></td>
                <td><?php echo htmlspecialchars($sp['ten_sp']); ?></td>
                <td><?php echo (int)$sp['so_luong']; ?></td>
                <td><?php echo formatTien($sp['don_gia']); ?></td>
                <td><?php echo formatTien($sp['don_gia'] * $sp['so_luong']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="tong-tien">Tổng cộng: <?php echo formatTien($don_hang['tong_tien']); ?></div>
    </div>

</div>

</body>
</html>
