<?php


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

// Lay du lieu tu GET hoac POST
$ma_sp    = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$so_luong = isset($_REQUEST['sl']) ? (int)$_REQUEST['sl'] : 1;

if ($so_luong < 1) $so_luong = 1;

if ($ma_sp <= 0) {
    die("San pham khong hop le.");
}

// Kiem tra san pham co ton tai khong
$stmt = $conn->prepare("SELECT id FROM $table_sanpham WHERE id = ?");
$stmt->bind_param("i", $ma_sp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("San pham khong ton tai.");
}
$stmt->close();
$conn->close();

// Khoi tao gio hang neu chua co
if (!isset($_SESSION['giohang'])) {
    $_SESSION['giohang'] = [];
}

// Them / cong don so luong san pham vao gio hang
if (isset($_SESSION['giohang'][$ma_sp])) {
    $_SESSION['giohang'][$ma_sp] += $so_luong;
} else {
    $_SESSION['giohang'][$ma_sp] = $so_luong;
}

// Chuyen huong ve trang gio hang (hoac trang truoc do)
header("Location: giohang.php");
exit();
