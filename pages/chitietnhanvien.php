<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra có ID nhân viên được truyền vào không
  if(isset($_GET['id'])) {
    $maNhanVien = $_GET['id'];
    
    // Lấy thông tin nhân viên và phòng ban
    $queryNhanVien = "SELECT nv.*, pb.TenPhongBan 
                      FROM NhanVien nv 
                      LEFT JOIN PhongBan pb ON nv.MaPb = pb.MaPb 
                      WHERE nv.MaNV = '$maNhanVien'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    
    // Kiểm tra nhân viên có tồn tại không
    if(mysqli_num_rows($resultNhanVien) > 0) {
      $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    } else {
      echo "<script>
        alert('Nhân viên không tồn tại!');
        window.location.href='danhsachnhanvien.php?p=staff&a=list-staff';
      </script>";
      exit;
    }
  } else {
    echo "<script>
      alert('Không có ID nhân viên!');
      window.location.href='danhsachnhanvien.php?p=staff&a=list-staff';
    </script>";
    exit;
  }
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Thông tin nhân viên
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li><a href="danhsachnhanvien.php?p=staff&a=list-staff">Danh sách nhân viên</a></li>
        <li class="active">Thông tin nhân viên</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title" style="font-size: 18px;">Mã nhân viên: <?php echo $nhanVien['MaNV']; ?></h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12 text-center">
                  <?php if(!empty($nhanVien['Avatar'])): ?>
                    <img src="../uploads/avatars/<?php echo $nhanVien['Avatar']; ?>" width="200" height="250" style="object-fit: cover; border: 2px solid #3c8dbc; border-radius: 5px;">
                  <?php else: ?>
                    <img src="../uploads/avatars/default-avatar.png" width="200" height="250" style="object-fit: cover; border: 2px solid #3c8dbc; border-radius: 5px;">
                  <?php endif; ?>
                </div>
                <div class="col-lg-4 col-sm-4 col-md-4 col-xs-12">
                  <p style="font-size: 16px; margin-bottom: 10px;">Họ tên nhân viên: <b><?php echo $nhanVien['Hoten']; ?></b></p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Giới tính: 
                    <b><?php echo !empty($nhanVien['GioiTinh']) ? $nhanVien['GioiTinh'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Ngày sinh: 
                    <b><?php echo !empty($nhanVien['NgaySinh']) ? date('d/m/Y', strtotime($nhanVien['NgaySinh'])) : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Số điện thoại: 
                    <b><?php echo !empty($nhanVien['SDT']) ? $nhanVien['SDT'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Email: 
                    <b><?php echo !empty($nhanVien['Email']) ? $nhanVien['Email'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Số CCCD: 
                    <b><?php echo !empty($nhanVien['CCCD']) ? $nhanVien['CCCD'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Địa chỉ: 
                    <b><?php echo !empty($nhanVien['DiaChi']) ? $nhanVien['DiaChi'] : 'Chưa cập nhật'; ?></b>
                  </p>
                </div>
                <!-- col-4 -->
                <div class="col-lg-5 col-sm-4 col-md-4 col-xs-12">
                  <p style="font-size: 16px; margin-bottom: 10px;">Phòng ban: 
                    <b><?php echo !empty($nhanVien['TenPhongBan']) ? $nhanVien['TenPhongBan'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Mã phòng ban: 
                    <b><?php echo !empty($nhanVien['MaPb']) ? $nhanVien['MaPb'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Chức vụ: 
                    <b><?php echo !empty($nhanVien['ChucVu']) ? $nhanVien['ChucVu'] : 'Chưa cập nhật'; ?></b>
                  </p>
                  <p style="font-size: 16px; margin-bottom: 10px;">Ngày vào làm: 
                    <b><?php echo !empty($nhanVien['NgayVaoLam']) ? date('d/m/Y', strtotime($nhanVien['NgayVaoLam'])) : 'Chưa cập nhật'; ?></b>
                  </p>
                </div>
                <!-- col-5 -->
              </div>
              <!-- row -->
              
              <hr>
              
              <!-- Buttons -->
              <div class="row">
                <div class="col-md-12">
                  <a href="danhsachnhanvien.php?p=staff&a=list-staff" class="btn btn-default"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a>
                  
                  <?php if(isset($_SESSION['chucvu']) && $_SESSION['chucvu'] == 'admin'): ?>
                    <a href="suanhanvien.php?id=<?php echo $nhanVien['MaNV']; ?>&p=staff&a=edit-staff" class="btn btn-primary"><i class="fa fa-edit"></i> Sửa thông tin</a>
                    
                    <button class="btn btn-info" onclick="window.print();"><i class="fa fa-print"></i> In thông tin</button>
                  <?php endif; ?>
                </div>
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

<?php
  // include
  include('../layouts/footer.php');
} else {
  // nếu chưa đăng nhập
  header('Location: login.php');
  exit;
}
?>