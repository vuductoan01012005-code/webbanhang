<?php
/**
 * ============================================================
 *  TRANG THEM SAN PHAM MOI (ADMINCP)
 * ============================================================
 *  File: admincp/sanpham_them.php
 *
 *  CHUC NANG:
 *   - Form nhap thong tin san pham moi
 *   - Upload anh san pham vao thu muc ../images/
 *   - Luu vao bang sanpham
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

$table_sanpham = "sanpham";

// Thu muc luu anh san pham (duong dan tuong doi tu file nay)
$thu_muc_anh = "../images/";

$loi = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ten_sp   = trim($_POST['ten_sp'] ?? '');
    $gia      = (float)($_POST['gia'] ?? 0);
    $so_luong = (int)($_POST['so_luong'] ?? 0);
    $mo_ta    = trim($_POST['mo_ta'] ?? '');
    $ten_anh  = '';

    if ($ten_sp === '' || $gia <= 0) {
        $loi = "Vui lòng nhập tên sản phẩm và giá hợp lệ.";
    } else {

        // ---------- XU LY UPLOAD ANH ----------
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === 0) {
            $duoi_hople = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ten_file_goc = $_FILES['hinh_anh']['name'];
            $duoi = strtolower(pathinfo($ten_file_goc, PATHINFO_EXTENSION));

            if (in_array($duoi, $duoi_hople)) {
                $ten_anh = uniqid('sp_') . '.' . $duoi;
                $duong_dan_luu = $thu_muc_anh . $ten_anh;
                move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $duong_dan_luu);
            } else {
                $loi = "Ảnh không hợp lệ. Chỉ chấp nhận jpg, jpeg, png, gif, webp.";
            }
        }

        if ($loi === '') {
            $duong_dan_anh_luu_db = "images/" . $ten_anh; // duong dan luu trong CSDL (tinh tu goc web)

            $stmt = $conn->prepare(
                "INSERT INTO $table_sanpham (ten_sp, gia, so_luong, hinh_anh, mo_ta) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sdiss", $ten_sp, $gia, $so_luong, $duong_dan_anh_luu_db, $mo_ta);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            header("Location: sanpham.php");
            exit();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thêm sản phẩm mới</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    h2 { color:#333; }
    .form-box {
        max-width:600px; background:#fff; border-radius:8px; padding:25px;
        box-shadow:0 1px 4px rgba(0,0,0,.1);
    }
    label { display:block; margin:12px 0 5px; font-weight:bold; font-size:14px; color:#444; }
    input[type=text], input[type=number], input[type=file], textarea {
        width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; box-sizing:border-box; font-size:14px;
    }
    textarea { resize:vertical; min-height:90px; }
    .btn-luu {
        margin-top:20px; background:#27ae60; color:#fff; border:none; padding:12px 25px;
        border-radius:5px; font-size:16px; cursor:pointer;
    }
    .loi { background:#fdecea; color:#c0392b; padding:10px 15px; border-radius:5px; margin-bottom:15px; }
    a.back { display:inline-block; margin-bottom:15px; color:#2980b9; text-decoration:none; }
</style>
</head>
<body>

<a class="back" href="sanpham.php">&larr; Quay lại danh sách sản phẩm</a>
<h2>➕ Thêm sản phẩm mới</h2>

<?php if ($loi): ?>
    <div class="loi"><?php echo htmlspecialchars($loi); ?></div>
<?php endif; ?>

<div class="form-box">
    <form method="POST" enctype="multipart/form-data">

        <label>Tên sản phẩm</label>
        <input type="text" name="ten_sp" required value="<?php echo htmlspecialchars($_POST['ten_sp'] ?? ''); ?>">

        <label>Giá (VNĐ)</label>
        <input type="number" name="gia" min="0" step="1000" required
               value="<?php echo htmlspecialchars($_POST['gia'] ?? ''); ?>">

        <label>Số lượng trong kho</label>
        <input type="number" name="so_luong" min="0" required
               value="<?php echo htmlspecialchars($_POST['so_luong'] ?? '0'); ?>">

        <label>Mô tả sản phẩm</label>
        <textarea name="mo_ta"><?php echo htmlspecialchars($_POST['mo_ta'] ?? ''); ?></textarea>

        <label>Hình ảnh sản phẩm</label>
        <input type="file" name="hinh_anh" accept="image/*">

        <button type="submit" class="btn-luu">Lưu sản phẩm</button>
    </form>
</div>

</body>
</html>
