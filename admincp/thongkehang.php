<?php

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Vui lòng truy cập qua index.php (không được mở trực tiếp file này).");
}

// Cấu hình tên bảng / cột (sửa lại nếu CSDL thật khác)
$table_donhang    = "donhang";
$col_tongtien     = "tong_tien";
$col_ngaydat      = "ngay_dat";
$table_khachhang  = "khachhang";
$table_sanpham    = "sanpham";
$table_ctdh       = "chitietdonhang";

// Thống kê tổng quan
$tong_doanh_thu = 0;
$res = $conn->query("SELECT SUM($col_tongtien) AS tong FROM $table_donhang WHERE trang_thai != 0");
if ($res && $row = $res->fetch_assoc()) {
    $tong_doanh_thu = $row['tong'] ?? 0;
}

$tong_don_hang = 0;
$res = $conn->query("SELECT COUNT(*) AS soluong FROM $table_donhang");
if ($res && $row = $res->fetch_assoc()) {
    $tong_don_hang = $row['soluong'];
}

$tong_khach_hang = 0;
$res = $conn->query("SELECT COUNT(*) AS soluong FROM $table_khachhang");
if ($res && $row = $res->fetch_assoc()) {
    $tong_khach_hang = $row['soluong'];
}

// Doanh thu theo tháng (năm hiện tại)
$doanh_thu_theo_thang = array_fill(1, 12, 0);
$sql = "SELECT MONTH($col_ngaydat) AS thang, SUM($col_tongtien) AS tong
        FROM $table_donhang
        WHERE YEAR($col_ngaydat) = YEAR(CURDATE())
        GROUP BY MONTH($col_ngaydat)";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $doanh_thu_theo_thang[(int)$row['thang']] = (float)$row['tong'];
    }
}

// Top 5 sản phẩm bán chạy
$top_san_pham = [];
$sql = "SELECT sp.ten_sp AS ten, SUM(ct.so_luong) AS da_ban
        FROM $table_ctdh ct
        JOIN $table_sanpham sp ON ct.ma_sp = sp.id
        GROUP BY ct.ma_sp
        ORDER BY da_ban DESC
        LIMIT 5";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $top_san_pham[] = $row;
    }
}

// 10 đơn hàng gần đây nhất
$don_hang_gan_day = [];
$sql = "SELECT id, $col_ngaydat AS ngay, $col_tongtien AS tien, trang_thai
        FROM $table_donhang
        ORDER BY $col_ngaydat DESC
        LIMIT 10";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $don_hang_gan_day[] = $row;
    }
}

function formatTien($so) {
    return number_format((float)$so, 0, ',', '.') . ' d';
}
?>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .tk-cards { display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap; }
    .tk-card { background:#fff; border-radius:8px; padding:20px 25px; box-shadow:0 1px 4px rgba(0,0,0,.1); flex:1; min-width:200px; }
    .tk-card h3 { margin:0 0 8px; font-size:14px; color:#888; text-transform:uppercase; }
    .tk-card p { margin:0; font-size:24px; font-weight:bold; color:#2c3e50; }
    .tk-card.doanhthu { border-left:5px solid #27ae60; }
    .tk-card.donhang  { border-left:5px solid #2980b9; }
    .tk-card.khachhang{ border-left:5px solid #e67e22; }
    .tk-box { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.1); margin-bottom:30px; }
    .tk-box table { width:100%; border-collapse:collapse; margin-top:10px; }
    .tk-box th, .tk-box td { padding:10px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    .tk-box th { background:#fafafa; color:#555; }
    .tk-trangthai { padding:3px 10px; border-radius:12px; font-size:12px; color:#fff; }
    .ts-0 { background:#e74c3c; }
    .ts-1 { background:#f39c12; }
    .ts-2 { background:#27ae60; }
</style>

<div class="clear"></div>
<div class="main">

    <h3 style="color:#333;">📊 Thống kê doanh thu</h3>

    <div class="tk-cards">
        <div class="tk-card doanhthu">
            <h3>Tổng doanh thu</h3>
            <p><?php echo formatTien($tong_doanh_thu); ?></p>
        </div>
        <div class="tk-card donhang">
            <h3>Tổng đơn hàng</h3>
            <p><?php echo (int)$tong_don_hang; ?></p>
        </div>
        <div class="tk-card khachhang">
            <h3>Tổng khách hàng</h3>
            <p><?php echo (int)$tong_khach_hang; ?></p>
        </div>
    </div>

    <div class="tk-box">
        <h3>Doanh thu theo tháng (năm nay)</h3>
        <canvas id="bieuDoDoanhThu" height="90"></canvas>
    </div>

    <div class="tk-box">
        <h3>Top 5 sản phẩm bán chạy</h3>
        <table>
            <tr><th>#</th><th>Tên sản phẩm</th><th>Số lượng đã bán</th></tr>
            <?php if (empty($top_san_pham)): ?>
                <tr><td colspan="3">Chưa có dữ liệu</td></tr>
            <?php else: $i = 1; foreach ($top_san_pham as $sp): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($sp['ten']); ?></td>
                    <td><?php echo (int)$sp['da_ban']; ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>

    <div class="tk-box">
        <h3>Đơn hàng gần đây</h3>
        <table>
            <tr><th>Mã ĐH</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr>
            <?php if (empty($don_hang_gan_day)): ?>
                <tr><td colspan="4">Chưa có đơn hàng</td></tr>
            <?php else: foreach ($don_hang_gan_day as $dh): ?>
                <tr>
                    <td>#<?php echo (int)$dh['id']; ?></td>
                    <td><?php echo htmlspecialchars($dh['ngay']); ?></td>
                    <td><?php echo formatTien($dh['tien']); ?></td>
                    <td>
                        <?php
                        $ts = (int)$dh['trang_thai'];
                        $nhan = ['0' => 'Đã hủy', '1' => 'Đang xử lý', '2' => 'Hoàn thành'];
                        echo '<span class="tk-trangthai ts-' . $ts . '">' . ($nhan[$ts] ?? 'Không rõ') . '</span>';
                        ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </table>
    </div>

</div>

<script>
const ctxThongKe = document.getElementById('bieuDoDoanhThu');
new Chart(ctxThongKe, {
    type: 'bar',
    data: {
        labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
        datasets: [{
            label: 'Doanh thu (VND)',
            data: <?php echo json_encode(array_values($doanh_thu_theo_thang)); ?>,
            backgroundColor: '#2980b9'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
