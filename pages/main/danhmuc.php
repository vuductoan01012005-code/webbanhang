<?php
    $sql_pro = "SELECT * 
                FROM  san_pham 
                WHERE san_pham.id_danhmuc= '$_GET[id]' 
                ORDER BY id_sanpham DESC";
    $query_pro = mysqli_query($conn,$sql_pro);
  

        $sql_cate = "SELECT * 
                FROM  danh_muc 
                WHERE id_danhmuc= '$_GET[id]' 
                LIMIT 1";
    $query_cate = mysqli_query($conn,$sql_cate);
    $row_title = mysqli_fetch_array($query_cate);

?>
<h3>Danh mục sản phẩm : <?php echo $row_title['tendanhmuc']?></h3>
            <ul class="product_list">
                <?php
                while ($row_pro = mysqli_fetch_array($query_pro)) {
                ?>
                <li>
                    <a  href="index.php?quanly=sanpham&id=<?php echo $row_pro['id_sanpham']?>">
                    <img src="admincp/modules/quanlysp/uploads/<?php echo $row_pro['hinhanh']?>">
                    <p class="title_product">Tên sản phẩm: <?php echo $row_pro['tensanpham']?></p>
                    <p class="price_product">Giá:  <?php echo number_format($row_pro['giasp'],0,',','.').'vnđ'?> </p>
</>
</li>
<?php
                }
?>
</ul>