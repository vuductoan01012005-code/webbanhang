<?php
/**
 * ============================================================
 *  TRANG QUAN LY DON HANG (DANH SACH)
 * ============================================================
 *  File: admincp/donhang.php
 *
 *  CHUC NANG:
 *   - Hien thi danh sach tat ca don hang
 *   - Loc theo trang thai (dang xu ly / hoan thanh / da huy)
 *   - Tim kiem theo ten nguoi nhan / sdt
 *   - Bam vao 1 don hang -> xem chi tiet (donhang_chitiet.php)
 *
 *  LUU Y: gia dinh bang `donhang` co cac cot:
 *   id, ma_kh, ten_nguoinhan, sdt, diachi, ngay_dat, tong_tien, trang_thai
 *  Neu du an ban dat ten khac, sua lai phan CAU HINH ben duoi.
 * ============================================================
 */

session_start();

// Kiem tra dang nhap admin (tuy chinh theo he thong dang nhap co san cua ban)
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

// ---------- LOC / TIM KIEM ----------
$loc_trangthai = isset($_GET['trangthai']) ? $_GET['trangthai'] : '';
$tukhoa        = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';

$dieu_kien = [];
$tham_so   = [];
$kieu      = '';

if ($loc_trangthai !== '') {
    $dieu_kien[] = "trang_thai = ?";
    $tham_so[]   = (int)$loc_trangthai;
    $kieu       .= 'i';
}

if ($tukhoa !== '') {
    $dieu_kien[] = "(ten_nguoinhan LIKE ? OR sdt LIKE ?)";
    $tu = "%$tukhoa%";
    $tham_so[] = $tu;
    $tham_so[] = $tu;
    $kieu     .= 'ss';
}

$sql = "SELECT id, ten_nguoinhan, sdt, ngay_dat, tong_tien, trang_thai FROM $table_donhang";
if (!empty($dieu_kien)) {
    $sql .= " WHERE " . implode(' AND ', $dieu_kien);
}
$sql .= " ORDER BY ngay_dat DESC";

$stmt = $conn->prepare($sql);
if (!empty($tham_so)) {
    $stmt->bind_param($kieu, ...$tham_so);
}
$stmt->execute();
$result = $stmt->get_result();

$danh_sach = [];
while ($row = $result->fetch_assoc()) {
    $danh_sach[] = $row;
}
$stmt->close();
$conn->close();

function formatTien($so) {
    return number_format((float)$so, 0, ',', '.') . ' d';
}

$nhan_trangthai = ['0' => 'Đã hủy', '1' => 'Đang xử lý', '2' => 'Hoàn thành'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý đơn hàng</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    .filter-box {
        background:#fff; padding:15px 20px; border-radius:8px; margin-bottom:20px;
        display:flex; gap:15px; flex-wrap:wrap; align-items:center; box-shadow:0 1px 4px rgba(0,0,0,.1);
    }
    .filter-box select, .filter-box input[type=text] {
        padding:8px; border:1px solid #ddd; border-radius:5px;
    }
    .filter-box button {
        padding:8px 18px; background:#2980b9; color:#fff; border:none; border-radius:5px; cursor:pointer;
    }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.1); }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    th { background:#fafafa; color:#555; }
    a.xem { color:#2980b9; text-decoration:none; font-weight:bold; }
    .trangthai { padding:4px 12px; border-radius:12px; font-size:12px; color:#fff; }
    .ts-0 { background:#e74c3c; }
    .ts-1 { background:#f39c12; }
    .ts-2 { background:#27ae60; }
</style>
</head>
<body>

<h2>📦 Quản lý đơn hàng</h2>

<form method="GET" class="filter-box">
    <select name="trangthai">
        <option value="">-- Tất cả trạng thái --</option>
        <option value="1" <?php if ($loc_trangthai === '1') echo 'selected'; ?>>Đang xử lý</option>
        <option value="2" <?php if ($loc_trangthai === '2') echo 'selected'; ?>>Hoàn thành</option>
        <option value="0" <?php if ($loc_trangthai === '0') echo 'selected'; ?>>Đã hủy</option>
    </select>

    <input type="text" name="tukhoa" placeholder="Tìm theo tên hoặc SĐT..."
           value="<?php echo htmlspecialchars($tukhoa); ?>">

    <button type="submit">Lọc / Tìm</button>
</form>

<table>
    <tr>
        <th>Mã ĐH</th>
        <th>Người nhận</th>
        <th>SĐT</th>
        <th>Ngày đặt</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Xem</th>
    </tr>
    <?php if (empty($danh_sach)): ?>
        <tr><td colspan="7" style="text-align:center;">Không có đơn hàng nào.</td></tr>
    <?php else: foreach ($danh_sach as $dh): ?>
        <tr>
            <td>#<?php echo (int)$dh['id']; ?></td>
            <td><?php echo htmlspecialchars($dh['ten_nguoinhan']); ?></td>
            <td><?php echo htmlspecialchars($dh['sdt']); ?></td>
            <td><?php echo htmlspecialchars($dh['ngay_dat']); ?></td>
            <td><?php echo formatTien($dh['tong_tien']); ?></td>
            <td>
                <?php $ts = (int)$dh['trang_thai']; ?>
                <span class="trangthai ts-<?php echo $ts; ?>"><?php echo $nhan_trangthai[$ts] ?? 'Không rõ'; ?></span>
            </td>
            <td><a class="xem" href="donhang_chitiet.php?id=<?php echo $dh['id']; ?>">Chi tiết</a></td>
        </tr>
    <?php endforeach; endif; ?>
</table>

</body>
</html>
