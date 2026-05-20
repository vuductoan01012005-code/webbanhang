<P> chi tiết sản phẩm </p>
<?php
      $sql_chitiet = "SELECT * 
                FROM  san_pham, danh_muc 
                WHERE san_pham.id_danhmuc= danh_muc.id_danhmuc
                AND san_pham.id_sanpham='$_GET[id]' LIMIT 1";
               
    $query_chitiet = mysqli_query($conn,$sql_chitiet);
  while($row_chitiet = mysqli_fetch_array($query_chitiet)) {
?>
<div class="wrapper_chitiet">
<div class="hinhanh_sanpham">
       <img width=100% src="admincp/modules/quanlysp/uploads/<?php echo $row_chitiet['hinhanh']?>">
</div>
<form method="POST" action="pages/main/themgiohang.php?idsanpham=<?php echo $row_chitiet['id_sanpham']?>">
<div class="chitiet_sanpham">
    <h3>Tên sản phẩm:  <?php echo $row_chitiet['tensanpham'] ?></h3>
    <p>Mã sp:  <?php echo $row_chitiet['masp']?></p>
     <p>Giá sp:  <?php echo number_format($row_chitiet['giasp'],0,',','.').' vnđ'?></p>
     <p>Số lượng:  <?php echo $row_chitiet['soluong']?></p>
     <p>Tên danh mục:  <?php echo $row_chitiet['tendanhmuc']?></p>
     <p> <input class="themgiohang" name="themgiohang" type="submit" value="Thêm giỏ hàng"></p>
</div>
</form>
</div>
<?php
  }
?>