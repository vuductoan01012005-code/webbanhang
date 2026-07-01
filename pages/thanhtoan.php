<?php
/**
 * ============================================================
 *  TRANG THANH TOAN / DAT HANG
 * ============================================================
 *  File: pages/thanhtoan.php
 *
 *  CHUC NANG:
 *   - Hien thi form thong tin nguoi nhan (ho ten, sdt, dia chi)
 *   - Hien thi tom tat gio hang + tong tien
 *   - Khi bam "Dat hang": luu don hang vao bang `donhang` va
 *     chi tiet vao bang `chitietdonhang`, sau do xoa gio hang
 *
 *  LUU Y: gia dinh cau truc CSDL:
 *   - donhang(id, ma_kh, ten_nguoinhan, sdt, diachi, ngay_dat, tong_tien, trang_thai)
 *   - chitietdonhang(id, ma_dh, ma_sp, so_luong, don_gia)
 *   - sanpham(id, ten_sp, gia)
 *   Neu du an ban dat ten bang/cot khac, sua lai phan CAU HINH.
 *
 *  Neu he thong da co dang nhap khach hang (session luu ma_kh),
 *  file se tu dong lay ma_kh tu session; neu chua dang nhap van
 *  cho phep dat hang voi ma_kh = 0 (khach vang lai) - ban co the
 *  bat buoc dang nhap bang cach bo comment doan kiem tra ben duoi.
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

// ---------- CAU HINH TEN BANG / COT ----------
$table_sanpham = "sanpham";
$table_donhang = "donhang";
$table_ctdh    = "chitietdonhang";

// Neu he thong ban da co dang nhap khach hang, lay ma_kh tu session:
// (doi ten bien session cho dung voi phan dang nhap co san cua ban)
$ma_kh = isset($_SESSION['ma_kh']) ? (int)$_SESSION['ma_kh'] : 0;

// Neu muon BAT BUOC dang nhap moi duoc thanh toan, bo comment 3 dong duoi:
// if ($ma_kh === 0) {
//     header("Location: dangnhap.php"); exit();
// }

if (empty($_SESSION['giohang'])) {
    header("Location: giohang.php");
    exit();
}

$thongbao = "";

// ---------- XU LY DAT HANG ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dathang'])) {

    $ten_nguoinhan = trim($_POST['ten_nguoinhan'] ?? '');
    $sdt           = trim($_POST['sdt'] ?? '');
    $diachi        = trim($_POST['diachi'] ?? '');

    if ($ten_nguoinhan === '' || $sdt === '' || $diachi === '') {
        $thongbao = "Vui lòng nhập đầy đủ thông tin.";
    } else {

        // Lay thong tin san pham trong gio de tinh tong tien va luu chi tiet
        $ids = array_keys($_SESSION['giohang']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $stmt = $conn->prepare("SELECT id, gia FROM $table_sanpham WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        $chi_tiet = [];
        $tong_tien = 0;
        while ($row = $result->fetch_assoc()) {
            $sl = $_SESSION['giohang'][$row['id']];
            $thanh_tien = $row['gia'] * $sl;
            $tong_tien += $thanh_tien;
            $chi_tiet[] = [
                'ma_sp'    => $row['id'],
                'so_luong' => $sl,
                'don_gia'  => $row['gia']
            ];
        }
        $stmt->close();

        // Them don hang vao bang donhang
        $stmt = $conn->prepare(
            "INSERT INTO $table_donhang (ma_kh, ten_nguoinhan, sdt, diachi, ngay_dat, tong_tien, trang_thai)
             VALUES (?, ?, ?, ?, NOW(), ?, 1)"
        );
        $stmt->bind_param("isssd", $ma_kh, $ten_nguoinhan, $sdt, $diachi, $tong_tien);
        $stmt->execute();
        $ma_don_hang = $conn->insert_id;
        $stmt->close();

        // Them chi tiet don hang vao bang chitietdonhang
        $stmt = $conn->prepare(
            "INSERT INTO $table_ctdh (ma_dh, ma_sp, so_luong, don_gia) VALUES (?, ?, ?, ?)"
        );
        foreach ($chi_tiet as $ct) {
            $stmt->bind_param("iiid", $ma_don_hang, $ct['ma_sp'], $ct['so_luong'], $ct['don_gia']);
            $stmt->execute();
        }
        $stmt->close();

        // Xoa gio hang sau khi dat thanh cong
        unset($_SESSION['giohang']);

        // Chuyen sang trang thong bao dat hang thanh cong
        header("Location: dathang_thanhcong.php?ma_dh=" . $ma_don_hang);
        exit();
    }
}

// ---------- LAY DU LIEU GIO HANG DE HIEN THI TOM TAT ----------
$danh_sach_gio = [];
$tong_tien = 0;

$ids = array_keys($_SESSION['giohang']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = $conn->prepare("SELECT id, ten_sp, gia FROM $table_sanpham WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $sl = $_SESSION['giohang'][$row['id']];
    $thanh_tien = $row['gia'] * $sl;
    $tong_tien += $thanh_tien;
    $danh_sach_gio[] = [
        'ten_sp'     => $row['ten_sp'],
        'so_luong'   => $sl,
        'thanh_tien' => $thanh_tien
    ];
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
<title>Thanh toán đơn hàng</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    .container { display:flex; gap:25px; flex-wrap:wrap; }
    .form-box, .tomtat-box { background:#fff; border-radius:8px; padding:25px; box-shadow:0 1px 4px rgba(0,0,0,.1); }
    .form-box { flex:2; min-width:300px; }
    .tomtat-box { flex:1; min-width:250px; }
    label { display:block; margin:12px 0 5px; font-weight:bold; font-size:14px; color:#444; }
    input[type=text], textarea {
        width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box; font-size:14px;
    }
    textarea { resize:vertical; min-height:70px; }
    .btn-dathang {
        margin-top:20px; background:#27ae60; color:#fff; border:none; padding:12px 25px;
        border-radius:5px; font-size:16px; cursor:pointer; width:100%;
    }
    .thongbao { background:#fdecea; color:#c0392b; padding:10px 15px; border-radius:5px; margin-bottom:15px; }
    .sp-item { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #eee; font-size:14px; }
    .tong-tien { text-align:right; font-size:18px; font-weight:bold; margin-top:15px; color:#27ae60; }
</style>
</head>
<body>

<h2>💳 Thanh toán đơn hàng</h2>

<?php if ($thongbao): ?>
    <div class="thongbao"><?php echo htmlspecialchars($thongbao); ?></div>
<?php endif; ?>

<form method="POST" action="thanhtoan.php">
<div class="container">

    <div class="form-box">
        <h3>Thông tin nhận hàng</h3>

        <label>Họ và tên người nhận</label>
        <input type="text" name="ten_nguoinhan" required
               value="<?php echo htmlspecialchars($_POST['ten_nguoinhan'] ?? ''); ?>">

        <label>Số điện thoại</label>
        <input type="text" name="sdt" required
               value="<?php echo htmlspecialchars($_POST['sdt'] ?? ''); ?>">

        <label>Địa chỉ giao hàng</label>
        <textarea name="diachi" required><?php echo htmlspecialchars($_POST['diachi'] ?? ''); ?></textarea>

        <button type="submit" name="dathang" class="btn-dathang">Xác nhận đặt hàng</button>
    </div>

    <div class="tomtat-box">
        <h3>Đơn hàng của bạn</h3>
        <?php foreach ($danh_sach_gio as $sp): ?>
            <div class="sp-item">
                <span><?php echo htmlspecialchars($sp['ten_sp']); ?> x<?php echo $sp['so_luong']; ?></span>
                <span><?php echo formatTien($sp['thanh_tien']); ?></span>
            </div>
        <?php endforeach; ?>
        <div class="tong-tien">Tổng cộng: <?php echo formatTien($tong_tien); ?></div>
    </div>

</div>
</form>

</body>
</html>
