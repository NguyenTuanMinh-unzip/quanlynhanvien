<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng - chỉ admin mới có full quyền
  if(isset($row_acc) && $row_acc['Chucvu'] == 'admin') {
    $hasFullPermission = true;
  } else {
    $hasFullPermission = false;
  }

  // Lấy danh sách phòng ban và số nhân viên trong từng phòng ban
  $query = "SELECT pb.MaPb, pb.TenPhongBan, COUNT(nv.MaNV) as SoNhanVien 
            FROM PhongBan pb 
            LEFT JOIN NhanVien nv ON pb.MaPb = nv.MaPb 
            GROUP BY pb.MaPb, pb.TenPhongBan
            ORDER BY pb.TenPhongBan ASC";
  $result = mysqli_query($conn, $query);
  $arrPhongBan = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrPhongBan[] = $row;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Danh sách phòng ban</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Danh sách phòng ban</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Danh sách phòng ban</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php 
              // Hiển thị thông báo nếu không phải admin
              if(!$hasFullPermission) {
                echo "<div class='alert alert-info alert-dismissible'>";
                echo "<h4><i class='icon fa fa-info'></i> Thông báo!</h4>";
                echo "Bạn chỉ có quyền xem danh sách phòng ban.";
                echo "</div>";
              }
            ?>

            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Mã phòng ban</th>
                    <th>Tên phòng ban</th>
                    <th class="text-center">Số nhân viên</th>
                    <th class="text-center">Xem</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $count = 1;
                    foreach ($arrPhongBan as $phongBan) {
                      echo "<tr>";
                      echo "<td class='text-center'>".$count++."</td>";
                      echo "<td class='text-center'>".$phongBan['MaPb']."</td>";
                      echo "<td>".$phongBan['TenPhongBan']."</td>";
                      echo "<td class='text-center'>".$phongBan['SoNhanVien']."</td>";
                      echo "<td class='text-center'>";
                      
                      if($hasFullPermission) {
                        // Nếu là admin, nút có thể click được
                        echo "<a href='nhanvienphongban.php?p=department&a=staff-department&id=".$phongBan['MaPb']."' class='btn btn-primary btn-sm'>";
                        echo "<i class='fa fa-eye'></i> Xem";
                        echo "</a>";
                      } else {
                        // Nếu không phải admin, nút bị disable
                        echo "<button class='btn btn-primary btn-sm' disabled>";
                        echo "<i class='fa fa-eye'></i> Xem";
                        echo "</button>";
                      }
                      
                      echo "</td>";
                      echo "</tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php
  // include footer
  include('../layouts/footer.php');
} else {
  // nếu chưa đăng nhập
  header('Location: login.php');
  exit;
}
?>