

<?php
$sql_lietke_dh = "SELECT * FROM cart,cart_details,san_pham 
                  WHERE cart_details.id_sanpham=san_pham.id_sanpham 
                  AND cart_details.code_cart = '$_GET[code]'
                  ORDER BY cart_details.id_cart_details DESC";
$query_lietke_dh = mysqli_query($conn, $sql_lietke_dh);
?>
<p> Liệt kê đơn hàng</p>
<table border="1" style="width:100%; border-collapse:collapse;">
  <tr>
    <th>Id </th>
    <th>Mã đơn hàng</th>
    <th>Tên sản phẩm</th>
    <th>Số lượng</th>
    <th>Đơn giá</th>
    <th>Thành tiền</th>
  
  </tr>
<?php
$i=0;
$tongtien = 0;
while($row = mysqli_fetch_array($query_lietke_dh)){
$i++;
$thanhtien=$row['giasp']*$row['soluongmua'];
$tongtien += $thanhtien;
?>
  <tr>
    <td><?php echo $i; ?></td>
    <td><?php echo $row['code_cart']; ?></td>
    <td><?php echo $row['tensanpham']; ?></td>
    <td><?php echo $row['soluongmua']; ?></td>
    <td><?php echo number_format($row['giasp'],0,',','.').'vnđ' ?></td>
    <td><?php echo number_format($thanhtien,0,',','.').'vnđ' ?></td>
   
    
  </tr>
<?php
}
?>
<tr>
 <td colspan="6">
<p>
    Tổng tiền:<?php echo number_format($tongtien,0,',','.').'vnđ' ?>
    <p><a href="">Đã thanh toán</a></p>
     <p><a href="">Đã thanh toán</a></p>
</p>
    </td>
</tr>
</table>