<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng
  if(isset($row_acc) && $row_acc['Chucvu'] != 'admin' && $row_acc['Chucvu'] != 'manager') {
    $hasPermission = false;
  } else {
    $hasPermission = true;
  }

  // Lấy thông tin phòng ban từ ID được truyền vào
  if(isset($_GET['id'])) {
    $maPhongBan = $_GET['id'];
    
    // Lấy thông tin phòng ban
    $queryPhongBan = "SELECT * FROM PhongBan WHERE MaPb = '$maPhongBan'";
    $resultPhongBan = mysqli_query($conn, $queryPhongBan);
    $phongBan = mysqli_fetch_assoc($resultPhongBan);
    
    // Kiểm tra phòng ban có tồn tại không
    if(!$phongBan) {
      echo "<script>
        alert('Phòng ban không tồn tại!');
        window.location.href='danhsachphongban.php?p=department&a=list-department';
      </script>";
      exit;
    }

    // Lấy danh sách nhân viên trong phòng ban
    $queryNhanVien = "SELECT * FROM NhanVien WHERE MaPb = '$maPhongBan' ORDER BY Hoten ASC";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    $arrNhanVien = array();
    while ($row = mysqli_fetch_array($resultNhanVien)) {
      $arrNhanVien[] = $row;
    }
  } else {
    echo "<script>
      alert('Không có ID phòng ban!');
      window.location.href='danhsachphongban.php?p=department&a=list-department';
    </script>";
    exit;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Danh sách nhân viên - <?php echo $phongBan['TenPhongBan']; ?>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachphongban.php?p=department&a=list-department">Danh sách phòng ban</a></li>
      <li class="active">Danh sách nhân viên phòng ban</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Danh sách nhân viên phòng <?php echo $phongBan['TenPhongBan']; ?> (<?php echo count($arrNhanVien); ?> nhân viên)</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php 
              // Hiển thị thông báo nếu không có quyền
              if(!$hasPermission) {
                echo "<div class='alert alert-warning alert-dismissible'>";
                echo "<h4><i class='icon fa fa-ban'></i> Thông báo!</h4>";
                echo "Bạn <b>không có quyền</b> để xem trang này.";
                echo "</div>";
              }
            ?>

            <?php if($hasPermission): ?>
              <div class="table-responsive">
                <table class="table table-hover table-bordered">
                  <thead>
                    <tr>
                      <th class="text-center">STT</th>
                      <th class="text-center">Mã NV</th>
                      <th>Họ tên</th>
                      <th class="text-center">Giới tính</th>
                      <th>Số điện thoại</th>
                      <th>Chức vụ</th>
                      <th class="text-center">Ngày vào làm</th>
                      <th class="text-center">Chi tiết</th>
                      <th class="text-center">Sửa</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $count = 1;
                      foreach ($arrNhanVien as $nhanVien) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td class='text-center'>".$nhanVien['MaNV']."</td>";
                        echo "<td>".$nhanVien['Hoten']."</td>";
                        echo "<td class='text-center'>".$nhanVien['GioiTinh']."</td>";
                        echo "<td>".$nhanVien['SDT']."</td>";
                        echo "<td>".$nhanVien['ChucVu']."</td>";
                        echo "<td class='text-center'>".date('d-m-Y', strtotime($nhanVien['NgayVaoLam']))."</td>";
                        echo "<td class='text-center'>";
                        echo "<a href='chitietnhanvien.php?p=staff&a=detail-staff&id=".$nhanVien['MaNV']."' class='btn btn-info btn-sm'>";
                        echo "<i class='fa fa-info-circle'></i> Chi tiết";
                        echo "</a>";
                        echo "</td>";
                        echo "<td class='text-center'>";
                        echo "<a href='suanhanvien.php?p=staff&a=edit-staff&id=".$nhanVien['MaNV']."' class='btn btn-warning btn-sm'>";
                        echo "<i class='fa fa-edit'></i> Sửa";
                        echo "</a>";
                        echo "</td>";
                        echo "</tr>";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
              <?php if(count($arrNhanVien) == 0): ?>
                <div class="alert alert-info alert-dismissible">
                  <h4><i class="icon fa fa-info-circle"></i> Thông báo!</h4>
                  Hiện không có nhân viên nào trong phòng ban này.
                </div>
              <?php endif; ?>
              
              <div class="box-footer">
                <a href="danhsachphongban.php?p=department&a=list-department" class="btn btn-default">
                  <i class="fa fa-arrow-left"></i> Quay lại danh sách phòng ban
                </a>
              </div>
            <?php endif; ?>
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