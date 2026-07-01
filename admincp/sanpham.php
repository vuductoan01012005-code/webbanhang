<?php
/**
 * ============================================================
 *  TRANG QUAN LY SAN PHAM (DANH SACH)
 * ============================================================
 *  File: admincp/sanpham.php
 *
 *  CHUC NANG:
 *   - Hien thi danh sach san pham
 *   - Tim kiem theo ten
 *   - Xoa san pham
 *   - Link sang trang them / sua san pham
 *
 *  LUU Y: gia dinh bang `sanpham` co cac cot:
 *   id, ten_sp, gia, so_luong, hinh_anh, mo_ta
 *  Neu du an ban dat ten khac, sua lai phan CAU HINH ben duoi.
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
$table_sanpham = "sanpham";

$thongbao = "";

// ---------- XU LY XOA SAN PHAM ----------
if (isset($_GET['xoa'])) {
    $id_xoa = (int)$_GET['xoa'];
    $stmt = $conn->prepare("DELETE FROM $table_sanpham WHERE id = ?");
    $stmt->bind_param("i", $id_xoa);
    $stmt->execute();
    $stmt->close();
    $thongbao = "Đã xóa sản phẩm.";
}

// ---------- TIM KIEM ----------
$tukhoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';

if ($tukhoa !== '') {
    $stmt = $conn->prepare("SELECT id, ten_sp, gia, so_luong, hinh_anh FROM $table_sanpham WHERE ten_sp LIKE ? ORDER BY id DESC");
    $tu = "%$tukhoa%";
    $stmt->bind_param("s", $tu);
} else {
    $stmt = $conn->prepare("SELECT id, ten_sp, gia, so_luong, hinh_anh FROM $table_sanpham ORDER BY id DESC");
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý sản phẩm</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    .top-bar {
        display:flex; justify-content:space-between; align-items:center;
        margin-bottom:20px; flex-wrap:wrap; gap:10px;
    }
    .filter-box { display:flex; gap:10px; }
    .filter-box input[type=text] { padding:8px; border:1px solid #ddd; border-radius:5px; min-width:220px; }
    .filter-box button { padding:8px 18px; background:#2980b9; color:#fff; border:none; border-radius:5px; cursor:pointer; }
    a.btn-them {
        background:#27ae60; color:#fff; text-decoration:none; padding:10px 18px; border-radius:5px; font-size:14px;
    }
    table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.1); }
    th, td { padding:12px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    th { background:#fafafa; color:#555; }
    img.sp-img { width:55px; height:55px; object-fit:cover; border-radius:4px; }
    a.sua { color:#2980b9; text-decoration:none; font-weight:bold; margin-right:12px; }
    a.xoa { color:#e74c3c; text-decoration:none; font-weight:bold; }
    .thongbao { background:#eafaf1; color:#27ae60; padding:10px 15px; border-radius:5px; margin-bottom:15px; }
    .het-hang { color:#e74c3c; font-size:12px; }
</style>
</head>
<body>

<h2>🛍️ Quản lý sản phẩm</h2>

<?php if ($thongbao): ?>
    <div class="thongbao"><?php echo htmlspecialchars($thongbao); ?></div>
<?php endif; ?>

<div class="top-bar">
    <form method="GET" class="filter-box">
        <input type="text" name="tukhoa" placeholder="Tìm theo tên sản phẩm..."
               value="<?php echo htmlspecialchars($tukhoa); ?>">
        <button type="submit">Tìm</button>
    </form>
    <a class="btn-them" href="sanpham_them.php">+ Thêm sản phẩm</a>
</div>

<table>
    <tr>
        <th>Ảnh</th>
        <th>Tên sản phẩm</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th>Thao tác</th>
    </tr>
    <?php if (empty($danh_sach)): ?>
        <tr><td colspan="5" style="text-align:center;">Chưa có sản phẩm nào.</td></tr>
    <?php else: foreach ($danh_sach as $sp): ?>
        <tr>
            <td><img class="sp-img" src="<?php echo htmlspecialchars($sp['hinh_anh']); ?>" alt=""></td>
            <td><?php echo htmlspecialchars($sp['ten_sp']); ?></td>
            <td><?php echo formatTien($sp['gia']); ?></td>
            <td>
                <?php echo (int)$sp['so_luong']; ?>
                <?php if ((int)$sp['so_luong'] <= 0): ?>
                    <div class="het-hang">Hết hàng</div>
                <?php endif; ?>
            </td>
            <td>
                <a class="sua" href="sanpham_sua.php?id=<?php echo $sp['id']; ?>">Sửa</a>
                <a class="xoa" href="sanpham.php?xoa=<?php echo $sp['id']; ?>"
                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</a>
            </td>
        </tr>
    <?php endforeach; endif; ?>
</table>

</body>
</html>
