<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng - CHỈ CHO PHÉP ADMIN
  if(isset($row_acc) && $row_acc['Chucvu'] != 'admin') {
    echo "<script>
      alert('Bạn không có quyền truy cập trang này!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }

  // Kiểm tra có ID bảng lương được truyền vào không
  if(isset($_GET['id'])) {
    $maLuong = $_GET['id'];
    
    // Lấy thông tin bảng lương
    $queryLuong = "SELECT l.*, nv.MaNV, nv.Hoten, nv.ChucVu, pb.TenPhongBan 
                  FROM Luong l 
                  JOIN NhanVien nv ON l.MaNV = nv.MaNV 
                  JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                  WHERE l.MaLuong = '$maLuong'";
    $resultLuong = mysqli_query($conn, $queryLuong);
    
    if(mysqli_num_rows($resultLuong) > 0) {
      $bangLuong = mysqli_fetch_assoc($resultLuong);
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin bảng lương!');
        window.location.href='danhsachbangluong.php?p=salary&a=list';
      </script>";
      exit;
    }
    
    // Xử lý cập nhật bảng lương
    if(isset($_POST['update'])) {
      $error = array();
      $success = array();
      $showMess = false;
      
      // Lấy dữ liệu từ form
      $luongCoBan = str_replace(',', '', $_POST['luongCoBan']);
      $phuCapAnTrua = str_replace(',', '', $_POST['phuCapAnTrua']);
      $phuCapDiLai = str_replace(',', '', $_POST['phuCapDiLai']);
      $bhxh = str_replace(',', '', $_POST['bhxh']);
      $bhyt = str_replace(',', '', $_POST['bhyt']);
      $thueTNCN = str_replace(',', '', $_POST['thueTNCN']);
      $thuong = str_replace(',', '', $_POST['thuong']);
      $phat = str_replace(',', '', $_POST['phat']);
      $ghiChu = $_POST['ghiChu'];
      $ngayCapNhat = date('Y-m-d');
      
      // Validate dữ liệu
      if(empty($luongCoBan) || !is_numeric($luongCoBan)) {
        $error['luongCoBan'] = 'Vui lòng nhập lương cơ bản hợp lệ';
      }
      
      if(empty($error)) {
        // Cập nhật bảng lương
        $update = "UPDATE Luong SET 
                  LuongCoBan = '$luongCoBan', 
                  PhuCapAnTrua = '$phuCapAnTrua', 
                  PhuCapDiLai = '$phuCapDiLai', 
                  Thuong = '$thuong', 
                  Phat = '$phat', 
                  BHXH = '$bhxh', 
                  BHYT = '$bhyt', 
                  ThueTNCN = '$thueTNCN', 
                  NgayCapNhat = '$ngayCapNhat', 
                  GhiChu = '$ghiChu'
                  WHERE MaLuong = '$maLuong'";
        $result = mysqli_query($conn, $update);
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Cập nhật bảng lương thành công!';
          
          // Refresh lại thông tin bảng lương
          $resultLuong = mysqli_query($conn, $queryLuong);
          $bangLuong = mysqli_fetch_assoc($resultLuong);
        } else {
          $error['db'] = 'Lỗi khi cập nhật bảng lương: ' . mysqli_error($conn);
        }
      }
    }
  } else {
    echo "<script>
      alert('Không tìm thấy ID bảng lương!');
      window.location.href='danhsachbangluong.php?p=salary&a=list';
    </script>";
    exit;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Chỉnh sửa bảng lương</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachbangluong.php?p=salary&a=list">Bảng lương</a></li>
      <li class="active">Chỉnh sửa bảng lương</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Chỉnh sửa bảng lương nhân viên: <?php echo $bangLuong['Hoten']; ?></h3>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php 
              // show error
              if(isset($error) && !empty($error)) {
                echo "<div class='alert alert-danger alert-dismissible'>";
                echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                foreach ($error as $err) {
                  echo $err . "<br/>";
                }
                echo "</div>";
              }
              
              // show success
              if(isset($success) && !empty($success) && isset($showMess) && $showMess == true) {
                echo "<div class='alert alert-success alert-dismissible'>";
                echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                foreach ($success as $suc) {
                  echo $suc . "<br/>";
                }
                echo "</div>";
              }
            ?>
            
            <form action="" method="POST">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mã bảng lương:</label>
                    <input type="text" class="form-control" value="<?php echo $bangLuong['MaLuong']; ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label>Mã nhân viên:</label>
                    <input type="text" class="form-control" value="<?php echo $bangLuong['MaNV']; ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label>Họ tên nhân viên:</label>
                    <input type="text" class="form-control" value="<?php echo $bangLuong['Hoten']; ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label>Chức vụ:</label>
                    <input type="text" class="form-control" value="<?php echo $bangLuong['ChucVu']; ?>" readonly>
                  </div>
                  <div class="form-group">
                    <label>Phòng ban:</label>
                    <input type="text" class="form-control" value="<?php echo $bangLuong['TenPhongBan']; ?>" readonly>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Lương cơ bản: <span style="color: red;">*</span></label>
                    <input type="text" name="luongCoBan" class="form-control currency" value="<?php echo number_format($bangLuong['LuongCoBan']); ?>" required>
                    <small style="color: red;"><?php if(isset($error['luongCoBan'])) echo $error['luongCoBan']; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Phụ cấp ăn trưa:</label>
                    <input type="text" name="phuCapAnTrua" class="form-control currency" value="<?php echo number_format($bangLuong['PhuCapAnTrua']); ?>">
                  </div>
                  <div class="form-group">
                    <label>Phụ cấp đi lại:</label>
                    <input type="text" name="phuCapDiLai" class="form-control currency" value="<?php echo number_format($bangLuong['PhuCapDiLai']); ?>">
                  </div>
                  <div class="form-group">
                    <label>Thưởng:</label>
                    <input type="text" name="thuong" class="form-control currency" value="<?php echo number_format($bangLuong['Thuong']); ?>">
                  </div>
                  <div class="form-group">
                    <label>Phạt:</label>
                    <input type="text" name="phat" class="form-control currency" value="<?php echo number_format($bangLuong['Phat']); ?>">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>BHXH (%):</label>
                    <input type="number" name="bhxh" class="form-control" min="0" max="100" step="0.1" value="<?php echo $bangLuong['BHXH']; ?>">
                    <small class="text-muted">Phần trăm trích từ lương cơ bản</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>BHYT (%):</label>
                    <input type="number" name="bhyt" class="form-control" min="0" max="100" step="0.1" value="<?php echo $bangLuong['BHYT']; ?>">
                    <small class="text-muted">Phần trăm trích từ lương cơ bản</small>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Thuế TNCN (%):</label>
                    <input type="number" name="thueTNCN" class="form-control" min="0" max="100" step="0.1" value="<?php echo $bangLuong['ThueTNCN']; ?>">
                    <small class="text-muted">Phần trăm trích từ thu nhập tính thuế</small>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="ghiChu" class="form-control" rows="3"><?php echo $bangLuong['GhiChu']; ?></textarea>
              </div>
              
              <div class="form-group">
                <button type="submit" name="update" class="btn btn-primary">
                  <i class="fa fa-save"></i> Cập nhật bảng lương
                </button>
                <a href="danhsachbangluong.php?p=salary&a=list" class="btn btn-default">
                  <i class="fa fa-arrow-left"></i> Quay lại
                </a>
                <a href="tinhluong.php?p=salary&a=calculate&id=<?php echo $bangLuong['MaNV']; ?>" class="btn btn-info">
                  <i class="fa fa-calculator"></i> Tính lương tháng này
                </a>
              </div>
            </form>
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

<script>
  // Format currency input
  document.addEventListener('DOMContentLoaded', function() {
    const currencyInputs = document.querySelectorAll('.currency');
    
    currencyInputs.forEach(function(input) {
      input.addEventListener('input', function(e) {
        // Remove non-numeric characters
        let value = this.value.replace(/[^\d]/g, '');
        
        // Format with commas
        if (value.length > 0) {
          value = parseInt(value).toLocaleString('en-US');
        }
        
        this.value = value;
      });
    });
  });
</script>

<?php
  // include footer
  include('../layouts/footer.php');
} else {
  // nếu chưa đăng nhập
  header('Location: login.php');
  exit;
}
?>