<?php
/**
 * File: pages/dathang_thanhcong.php
 * Trang hien thi sau khi dat hang thanh cong.
 */
$ma_dh = isset($_GET['ma_dh']) ? (int)$_GET['ma_dh'] : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt hàng thành công</title>
<style>
    body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:20px; }
    .box {
        max-width:500px; margin:60px auto; background:#fff; border-radius:8px;
        padding:40px; text-align:center; box-shadow:0 1px 4px rgba(0,0,0,.1);
    }
    .icon { font-size:50px; }
    h2 { color:#27ae60; }
    a.btn {
        display:inline-block; margin-top:20px; background:#2980b9; color:#fff;
        padding:10px 25px; border-radius:5px; text-decoration:none;
    }
</style>
</head>
<body>

<div class="box">
    <div class="icon">✅</div>
    <h2>Đặt hàng thành công!</h2>
    <p>Cảm ơn bạn đã mua hàng. Mã đơn hàng của bạn là <strong>#<?php echo $ma_dh; ?></strong>.</p>
    <p>Chúng tôi sẽ liên hệ với bạn sớm để xác nhận giao hàng.</p>
    <a class="btn" href="../index.php">Tiếp tục mua sắm</a>
</div>

</body>
</html>
