<?php
if(isset($_POST["doimatkhau"]) ) {
    $taikhoan =$_POST['email'];
     $matkhau_cu =md5($_POST['password_cu']);
      $matkhau_moi =md5($_POST['password_moi']);
     $sql="SELECT *  FROM dangky WHERE email='".$taikhoan."'AND matkhau='".$matkhau_cu."' LIMIT 1";
     $row = mysqli_query($conn,$sql);
     $count = mysqli_num_rows($row);
     if($count > 0) {
        $sql_update=mysqli_query($conn,"UPDATE dangky SET matkhau='".$matkhau_moi."' WHERE email='".$taikhoan."' ");
         echo '<p style="color:green"> Mật khẩu đã được thay đổi </p>';
} else {
    echo '<p style="color:red"> Tài khoản hoặc mật khẩu cũ không đúng, vui lòng nhập lại!! </p>';
}
}
?>

 <form action="" method="POST" autocomplete="off">
    <table border="1" class="table-login" style="text-align:center; border-collapse:collapse;">
        <tr>
            <td colspan="2"><h3>Đổi mật khẩu </h3></td>
            
        </tr>
        <tr>
            <td>Tài khoản</td>
            <td><input type="text" name="email"></td>
        </tr>
         <tr>
            <td>Mật khẩu cũ</td>
            <td><input type="password" name="password_cu"></td>
        </tr>
         <tr>
            <td>Mật khẩu mới</td>
            <td><input type="password" name="password_moi"></td>
        </tr>
        <tr>
            
            <td colspan="2"><input type="submit" name="doimatkhau" value="Đổi mật khẩu"></td>
        </tr>
    </table>
    </form>