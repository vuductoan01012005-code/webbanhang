<?php
include('../../config/config.php');
$tensanpham =$_POST['tensanpham'];
$masp =$_POST['masp'];
$giasp =$_POST['giasp'];
$soluong =$_POST['soluong'];
$hinhanh =$_FILES['hinhanh']['name'];
$hinhanh_tmp =$_FILES['hinhanh']['tmp_name'];
$hinhanh = time().'_'.$hinhanh;
$tomtat =$_POST['tomtat'];
$noidung =$_POST['noidung'];
$tinhtrang =$_POST['tinhtrang'];
$danhmuc =$_POST['danhmuc'];


if(isset($_POST['themsanpham'])){
    //thêm
    $sql_them = "INSERT INTO san_pham(tensanpham, masp, giasp, soluong, hinhanh, tomtat, noidung, tinhtrang, id_danhmuc)
     VALUE ('".$tensanpham."', '".$masp."', '".$giasp."', '".$soluong."', '".$hinhanh."', '".$tomtat."', '".$noidung."', '".$tinhtrang."','".$danhmuc."')";

mysqli_query($conn, $sql_them);
move_uploaded_file($hinhanh_tmp,'uploads/'.$hinhanh);
header('Location:../../index.php?action=quanlysanpham&query=them');




    }elseif(isset($_POST['suasanpham'])){
        //sửa
       if($_FILES['hinhanh']['name']!=''){
            move_uploaded_file($hinhanh_tmp,'uploads/'.$hinhanh);

$sql_update = "UPDATE san_pham 
               SET tensanpham='".$tensanpham."', 
                   masp='".$masp."',
                   giasp='".$giasp."',
                   soluong='".$soluong."',
                   hinhanh='".$hinhanh."',
                   tomtat='".$tomtat."',
                   noidung='".$noidung."',
                   tinhtrang='".$tinhtrang."',
                   id_danhmuc='".$danhmuc."'
             WHERE id_sanpham= '$_GET[idsanpham]'";

  $sql="SELECT * FROM san_pham WHERE id_sanpham='$_GET[idsanpham]' LIMIT 1";
        $query = mysqli_query($conn, $sql);
        while( $row = mysqli_fetch_array($query)){
        unlink('uploads/'.$row['hinhanh']);
        }

        }else{
     $sql_update = "UPDATE san_pham 
                    SET tensanpham='".$tensanpham."', 
                        masp='".$masp."',
                        giasp='".$giasp."',
                        soluong='".$soluong."',
                        tomtat='".$tomtat."',
                        noidung='".$noidung."',
                        tinhtrang='".$tinhtrang."',
                        id_danhmuc='".$danhmuc."'
                  WHERE id_sanpham= '$_GET[idsanpham]'";
        }

mysqli_query($conn, $sql_update);
header('Location:../../index.php?action=quanlysanpham&query=them');





    }else{
        //xóa
         $id=$_GET['idsanpham'];
        $sql="SELECT * FROM san_pham WHERE id_sanpham='$id' LIMIT 1";
        $query = mysqli_query($conn, $sql);
        while( $row = mysqli_fetch_array($query)){
        unlink('uploads/'.$row['hinhanh']);
        }
        $sql_xoa = "DELETE FROM san_pham WHERE id_sanpham='".$id."'";
    mysqli_query($conn, $sql_xoa);
header('Location:../../index.php?action=quanlysanpham&query=them');
        }
?>