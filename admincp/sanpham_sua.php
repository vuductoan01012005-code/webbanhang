<?php
/**
 * ============================================================
 *  TRANG SUA SAN PHAM (ADMINCP)
 * ============================================================
 *  File: admincp/sanpham_sua.php
 *
 *  CACH DUNG: sanpham_sua.php?id=5
 *
 *  CHUC NANG:
 *   - Hien thi thong tin san pham hien tai de sua
 *   - Cho phep doi anh moi (neu khong chon anh moi, giu anh cu)
 *   - Cap nhat vao CSDL
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
$thu_muc_anh   = "../images/";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Sản phẩm không hợp lệ.");
}

$loi = "";

// ---------- XU LY CAP NHAT ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ten_sp   = trim($_POST['ten_sp'] ?? '');
    $gia      = (float)($_POST['gia'] ?? 0);
    $so_luong = (int)($_POST['so_luong'] ?? 0);
    $mo_ta    = trim($_POST['mo_ta'] ?? '');
    $anh_cu   = $_POST['anh_cu'] ?? '';
    $duong_dan_anh_luu_db = $anh_cu;

    if ($ten_sp === '' || $gia <= 0) {
        $loi = "Vui lòng nhập tên sản phẩm và giá hợp lệ.";
    } else {

        // Neu co chon anh moi thi upload va thay the
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === 0) {
            $duoi_hople = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $duoi = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));

            if (in_array($duoi, $duoi_hople)) {
                $ten_anh = uniqid('sp_') . '.' . $duoi;
                move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $thu_muc_anh . $ten_anh);
                $duong_dan_anh_luu_db = "images/" . $ten_anh;
            } else {
                $loi = "Ảnh không hợp lệ. Chỉ chấp nhận jpg, jpeg, png, gif, webp.";
            }
        }

        if ($loi === '') {
            $stmt = $conn->prepare(
                "UPDATE $table_sanpham SET ten_sp = ?, gia = ?, so_luong = ?, hinh_anh = ?, mo_ta = ? WHERE id = ?"
            );
            $stmt->bind_param("sdissi", $ten_sp, $gia, $so_luong, $duong_dan_anh_luu_db, $mo_ta, $id);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            header("Location: sanpham.php");
            exit();
        }
    }
}

// ---------- LAY THONG TIN SAN PHAM HIEN TAI ----------
$stmt = $conn->prepare("SELECT * FROM $table_sanpham WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$sp = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$sp) {
    die("Không tìm thấy sản phẩm.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sửa sản phẩm</title>
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
        margin-top:20px; background:#2980b9; color:#fff; border:none; padding:12px 25px;
        border-radius:5px; font-size:16px; cursor:pointer;
    }
    .loi { background:#fdecea; color:#c0392b; padding:10px 15px; border-radius:5px; margin-bottom:15px; }
    a.back { display:inline-block; margin-bottom:15px; color:#2980b9; text-decoration:none; }
    img.anh-hientai { width:100px; border-radius:6px; margin-top:8px; }
</style>
</head>
<body>

<a class="back" href="sanpham.php">&larr; Quay lại danh sách sản phẩm</a>
<h2>✏️ Sửa sản phẩm</h2>

<?php if ($loi): ?>
    <div class="loi"><?php echo htmlspecialchars($loi); ?></div>
<?php endif; ?>

<div class="form-box">
    <form method="POST" enctype="multipart/form-data">

        <input type="hidden" name="anh_cu" value="<?php echo htmlspecialchars($sp['hinh_anh']); ?>">

        <label>Tên sản phẩm</label>
        <input type="text" name="ten_sp" required value="<?php echo htmlspecialchars($sp['ten_sp']); ?>">

        <label>Giá (VNĐ)</label>
        <input type="number" name="gia" min="0" step="1000" required value="<?php echo htmlspecialchars($sp['gia']); ?>">

        <label>Số lượng trong kho</label>
        <input type="number" name="so_luong" min="0" required value="<?php echo htmlspecialchars($sp['so_luong']); ?>">

        <label>Mô tả sản phẩm</label>
        <textarea name="mo_ta"><?php echo htmlspecialchars($sp['mo_ta']); ?></textarea>

        <label>Ảnh hiện tại</label>
        <img class="anh-hientai" src="<?php echo htmlspecialchars($sp['hinh_anh']); ?>" alt="">

        <label>Chọn ảnh mới (bỏ trống nếu không đổi ảnh)</label>
        <input type="file" name="hinh_anh" accept="image/*">

        <button type="submit" class="btn-luu">Cập nhật sản phẩm</button>
    </form>
</div>

</body>
</html>
