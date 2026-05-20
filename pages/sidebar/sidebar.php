        <div class="sidebar">
        <ul class="list_sidebar" > 
           
           
           <?php
                $sql_danhmuc = "SELECT * 
                FROM  danh_muc 
                ORDER BY id_danhmuc DESC";
    $query_danhmuc = mysqli_query($conn,$sql_danhmuc);
    while( $row = mysqli_fetch_assoc($query_danhmuc) ) {
            ?> 
             <li><a href="index.php?quanly=danhmucsanpham&id=<?php echo $row['id_danhmuc']?>"><?php echo $row['tendanhmuc']?></a></li>
                <?php
                }
                ?>
</ul>
</div>