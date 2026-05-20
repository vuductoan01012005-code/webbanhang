<?php
include('../../config/config.php');
$tenloaisp =$_POST['tendanhmuc'];
$thutu =$_POST['thutu'];
if(isset($_POST['themdanhmuc'])){
    //thêm
    $sql_them = "INSERT INTO danh_muc(tendanhmuc, thutu) VALUE ('".$tenloaisp."', '".$thutu."')";
mysqli_query($conn, $sql_them);
header('Location:../../index.php?action=quanlydanhmucsanpham&query=them');



    }elseif(isset($_POST['suadanhmuc'])){
        //sửa
$sql_update = "UPDATE danh_muc SET tendanhmuc='".$tenloaisp."', thutu='".$thutu."' WHERE id_danhmuc= '$_GET[iddanhmuc]'";
mysqli_query($conn, $sql_update);
header('Location:../../index.php?action=quanlydanhmucsanpham&query=them');





    }else{
        //xóa
        $id=$_GET['iddanhmuc'];
        $sql_xoa = "DELETE FROM danh_muc WHERE id_danhmuc='".$id."'";
    mysqli_query($conn, $sql_xoa);
header('Location:../../index.php?action=quanlydanhmucsanpham&query=them');
        }
?>