<?php
/**
 * ============================================================
 *  TRANG GIO HANG
 * ============================================================
 *  File: pages/giohang.php
 *
 *  CHUC NANG:
 *   - Hien thi danh sach san pham trong gio (luu trong SESSION)
 *   - Cap nhat so luong
 *   - Xoa san pham khoi gio
 *   - Tinh tong tien
 *   - Nut "Tien hanh thanh toan" -> chuyen sang thanhtoan.php
 * ============================================================
 */

session_start();

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

$table_sanpham = "sanpham";

if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = [];
}

// ---------- XU LY CAP NHAT SO LUONG ----------
if (isset($_POST['capnhat'])) {
    foreach ($_POST['soluong'] as $ma_sp => $sl) {
        $ma_sp = (int)$ma_sp;
        $sl = (int)$sl;
        if ($sl <= 0) {
            unset($_SESSION['giohang'][$ma_sp]);
        } else {
            $_SESSION['giohang'][$ma_sp] = $sl;
        }
    }
    header("Location: giohang.php");
    exit();
}

// ---------- XU LY XOA SAN PHAM ----------
if (isset($_GET['xoa'])) {
    $ma_sp = (int)$_GET['xoa'];
    unset($_SESSION['giohang'][$ma_sp]);
    header("Location: giohang.php");
    exit();
}

// ---------- LAY THONG TIN SAN PHAM TRONG GIO ----------
$danh_sach_gio = [];
$tong_tien = 0;

if (!empty($_SESSION['giohang'])) {
    $ids = array_keys($_SESSION['giohang']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = $conn->prepare("SELECT id, ten_sp, gia, hinh_anh FROM $table_sanpham WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $sl = $_SESSION['giohang'][$row['id']];
        $thanh_tien = $row['gia'] * $sl;
        $tong_tien += $thanh_tien;

        $danh_sach_gio[] = [
            'id'         => $row['id'],
            'ten_sp'     => $row['ten_sp'],
            'gia'        => $row['gia'],
            'hinh_anh'   => $row['hinh_anh'],
            'so_luong'   => $sl,
            'thanh_tien' => $thanh_tien
        ];
    }
    $stmt->close();
}

$conn->close();

function formatTien($so) {
    return number_format((float)$so, 0, ',', '.') . ' d';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Giỏ hàng của bạn</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.1); }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:center; }
    th { background:#fafafa; }
    td.ten-sp { text-align:left; }
    img.sp-img { width:60px; height:60px; object-fit:cover; border-radius:4px; }
    input.soluong { width:60px; padding:5px; text-align:center; }
    .btn { padding:8px 16px; border:none; border-radius:5px; cursor:pointer; font-size:14px; }
    .btn-capnhat { background:#2980b9; color:#fff; }
    .btn-xoa { background:#e74c3c; color:#fff; text-decoration:none; padding:6px 12px; border-radius:4px; }
    .btn-thanhtoan { background:#27ae60; color:#fff; padding:12px 24px; font-size:16px; float:right; margin-top:15px; text-decoration: none; display:inline-block; }
    .tong-tien { text-align:right; font-size:18px; font-weight:bold; margin-top:15px; }
    .gio-rong { background:#fff; padding:40px; text-align:center; border-radius:8px; color:#888; }
</style>
</head>
<body>

<h2>🛒 Giỏ hàng của bạn</h2>

<?php if (empty($danh_sach_gio)): ?>

    <div class="gio-rong">
        Giỏ hàng của bạn đang trống.<br>
        <a href="index.php">Tiếp tục mua sắm</a>
    </div>

<?php else: ?>

    <form method="POST" action="giohang.php">
        <table>
            <tr>
                <th>Ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Xóa</th>
            </tr>
            <?php foreach ($danh_sach_gio as $sp): ?>
            <tr>
                <td><img class="sp-img" src="<?php echo htmlspecialchars($sp['hinh_anh']); ?>" alt=""></td>
                <td class="ten-sp"><?php echo htmlspecialchars($sp['ten_sp']); ?></td>
                <td><?php echo formatTien($sp['gia']); ?></td>
                <td>
                    <input type="number" min="0" class="soluong"
                           name="soluong[<?php echo $sp['id']; ?>]"
                           value="<?php echo $sp['so_luong']; ?>">
                </td>
                <td><?php echo formatTien($sp['thanh_tien']); ?></td>
                <td><a class="btn-xoa" href="giohang.php?xoa=<?php echo $sp['id']; ?>">Xóa</a></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <button type="submit" name="capnhat" class="btn btn-capnhat" style="margin-top:15px;">
            Cập nhật giỏ hàng
        </button>
    </form>

    <div class="tong-tien">Tổng tiền: <?php echo formatTien($tong_tien); ?></div>

    <a href="thanhtoan.php" class="btn-thanhtoan">Tiến hành thanh toán</a>
    <div style="clear:both;"></div>

<?php endif; ?>

</body>
</html>
