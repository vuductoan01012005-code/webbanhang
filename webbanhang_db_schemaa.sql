CREATE DATABASE IF NOT EXISTS webbanhang_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE webbanhang_db;

CREATE TABLE IF NOT EXISTS admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tài khoản mặc định: username = admin, password = 123456 (đã MD5)
-- MD5('123456') = e10adc3949ba59abbe56e057f20f883e
INSERT INTO admin (username, password, ho_ten) VALUES
('admin', 'e10adc3949ba59abbe56e057f20f883e', 'Quản trị viên');

-- Bảng khachhang
CREATE TABLE IF NOT EXISTS khachhang (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    sdt VARCHAR(20) DEFAULT NULL,
    dia_chi VARCHAR(255) DEFAULT NULL,
    mat_khau VARCHAR(255) DEFAULT NULL,
    ngay_dang_ky DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng danhmucsanpham (tương ứng module "quanlydanhmucsanpham" trong main.php)
CREATE TABLE IF NOT EXISTS danhmucsanpham (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ten_danhmuc VARCHAR(100) NOT NULL,
    mo_ta TEXT DEFAULT NULL,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng sanpham (tương ứng module "quanlysanpham" trong main.php)
CREATE TABLE IF NOT EXISTS sanpham (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_danhmuc INT UNSIGNED DEFAULT NULL,
    ten_sp VARCHAR(150) NOT NULL,
    mo_ta TEXT DEFAULT NULL,
    gia DECIMAL(15,2) NOT NULL DEFAULT 0,
    gia_khuyen_mai DECIMAL(15,2) DEFAULT NULL,
    hinh_anh VARCHAR(255) DEFAULT NULL,
    so_luong_ton INT DEFAULT 0,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sanpham_danhmuc
        FOREIGN KEY (ma_danhmuc) REFERENCES danhmucsanpham(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng donhang
-- trang_thai: 0 = Đã hủy, 1 = Đang xử lý, 2 = Hoàn thành (khớp với thongke.php)
CREATE TABLE IF NOT EXISTS donhang (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_kh INT UNSIGNED NOT NULL,
    ho_ten_nguoi_nhan VARCHAR(100) DEFAULT NULL,
    sdt_nguoi_nhan VARCHAR(20) DEFAULT NULL,
    dia_chi_giao VARCHAR(255) DEFAULT NULL,
    ngay_dat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tong_tien DECIMAL(15,2) NOT NULL DEFAULT 0,
    trang_thai TINYINT(1) NOT NULL DEFAULT 1,
    ghi_chu TEXT DEFAULT NULL,
    CONSTRAINT fk_donhang_khachhang
        FOREIGN KEY (ma_kh) REFERENCES khachhang(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng chitietdonhang
CREATE TABLE IF NOT EXISTS chitietdonhang (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ma_dh INT UNSIGNED NOT NULL,
    ma_sp INT UNSIGNED NOT NULL,
    so_luong INT UNSIGNED NOT NULL DEFAULT 1,
    don_gia DECIMAL(15,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_ctdh_donhang
        FOREIGN KEY (ma_dh) REFERENCES donhang(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ctdh_sanpham
        FOREIGN KEY (ma_sp) REFERENCES sanpham(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dữ liệu mẫu (để test thongke.php có biểu đồ / bảng dữ liệu)
-- Xóa phần này nếu không cần dữ liệu mẫu

INSERT INTO danhmucsanpham (ten_danhmuc) VALUES
('Áo thun'), ('Quần jeans'), ('Giày dép'), ('Phụ kiện');

INSERT INTO sanpham (ma_danhmuc, ten_sp, gia, hinh_anh, so_luong_ton) VALUES
(1, 'Áo thun nam basic', 150000, 'ao-thun-nam.jpg', 100),
(1, 'Áo thun nữ form rộng', 160000, 'ao-thun-nu.jpg', 80),
(2, 'Quần jeans slimfit', 350000, 'jeans-slimfit.jpg', 50),
(3, 'Giày sneaker trắng', 550000, 'sneaker-trang.jpg', 40),
(4, 'Nón lưỡi trai', 90000, 'non-luoi-trai.jpg', 120);

INSERT INTO khachhang (ho_ten, email, sdt, dia_chi) VALUES
('Nguyễn Văn A', 'a.nguyen@example.com', '0901111111', 'Hà Nội'),
('Trần Thị B', 'b.tran@example.com', '0902222222', 'TP.HCM'),
('Lê Văn C', 'c.le@example.com', '0903333333', 'Đà Nẵng');

INSERT INTO donhang (ma_kh, ho_ten_nguoi_nhan, sdt_nguoi_nhan, dia_chi_giao, ngay_dat, tong_tien, trang_thai) VALUES
(1, 'Nguyễn Văn A', '0901111111', 'Hà Nội', NOW() - INTERVAL 2 DAY, 460000, 2),
(2, 'Trần Thị B', '0902222222', 'TP.HCM', NOW() - INTERVAL 5 DAY, 350000, 1),
(3, 'Lê Văn C', '0903333333', 'Đà Nẵng', NOW() - INTERVAL 20 DAY, 640000, 2),
(1, 'Nguyễn Văn A', '0901111111', 'Hà Nội', NOW() - INTERVAL 40 DAY, 90000, 0);

INSERT INTO chitietdonhang (ma_dh, ma_sp, so_luong, don_gia) VALUES
(1, 1, 1, 150000),
(1, 2, 1, 160000),
(1, 5, 1, 90000),
(1, 4, 1, 550000),
(2, 3, 1, 350000),
(3, 4, 1, 550000),
(3, 5, 1, 90000),
(4, 5, 1, 90000);
