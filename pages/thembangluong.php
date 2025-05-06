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

  // Biến cho thông tin nhân viên và lương
  $nhanVien = array();
  $bangLuong = array();
  $isEdit = false;
  $maLuong = "L" . time();

  // Lấy danh sách nhân viên chưa có bảng lương
  $queryDanhSachNV = "SELECT nv.MaNV, nv.Hoten, pb.TenPhongBan, nv.ChucVu 
                      FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb 
                      WHERE nv.MaNV NOT IN (
                        SELECT MaNV FROM Luong
                      )
                      ORDER BY nv.Hoten ASC";
  $resultDanhSachNV = mysqli_query($conn, $queryDanhSachNV);
  $arrNhanVien = array();
  while ($row = mysqli_fetch_array($resultDanhSachNV)) {
    $arrNhanVien[] = $row;
  }

  // Xử lý thêm bảng lương mới
  if(isset($_POST['save'])) {
    $error = array();
    $success = array();
    $showMess = false;
    
    // Lấy dữ liệu từ form
    $maNhanVien = $_POST['maNhanVien'];
    $luongCoBan = str_replace(',', '', $_POST['luongCoBan']);
    $phuCapAnTrua = str_replace(',', '', $_POST['phuCapAnTrua']);
    $phuCapDiLai = str_replace(',', '', $_POST['phuCapDiLai']);
    $bhxh = str_replace(',', '', $_POST['bhxh']);
    $bhyt = str_replace(',', '', $_POST['bhyt']);
    $thueTNCN = str_replace(',', '', $_POST['thueTNCN']);
    $ghiChu = $_POST['ghiChu'];
    $ngayCapNhat = date('Y-m-d');
    
    // Validate dữ liệu
    if(empty($maNhanVien)) {
      $error['maNhanVien'] = 'Vui lòng chọn nhân viên';
    }
    
    if(empty($luongCoBan) || !is_numeric($luongCoBan)) {
      $error['luongCoBan'] = 'Vui lòng nhập lương cơ bản hợp lệ';
    }
    
    // Kiểm tra xem nhân viên đã có bảng lương chưa
    $queryCheck = "SELECT * FROM Luong WHERE MaNV = '$maNhanVien'";
    $resultCheck = mysqli_query($conn, $queryCheck);
    if(mysqli_num_rows($resultCheck) > 0) {
      $error['exists'] = 'Nhân viên này đã có bảng lương. Vui lòng sử dụng chức năng cập nhật.';
    }
    
    if(empty($error)) {
      // Thêm mới bảng lương
      $insert = "INSERT INTO Luong(MaLuong, MaNV, LuongCoBan, PhuCapAnTrua, PhuCapDiLai, 
                 Thuong, Phat, BHXH, BHYT, ThueTNCN, NgayCapNhat, GhiChu) 
                 VALUES('$maLuong', '$maNhanVien', '$luongCoBan', '$phuCapAnTrua', '$phuCapDiLai', 
                 0, 0, '$bhxh', '$bhyt', '$thueTNCN', '$ngayCapNhat', '$ghiChu')";
      $result = mysqli_query($conn, $insert);
      
      if($result) {
        $showMess = true;
        $success['success'] = 'Thêm bảng lương thành công!';
        
        // Chuyển về trang danh sách bảng lương
        echo "<script>
          setTimeout(function() {
            window.location.href = 'danhsachbangluong.php?p=salary&a=list';
          }, 2000);
        </script>";
      } else {
        $error['db'] = 'Lỗi khi thêm bảng lương: ' . mysqli_error($conn);
      }
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Thêm mới bảng tính lương</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachbangluong.php?p=salary&a=list">Bảng lương</a></li>
      <li class="active">Thêm mới bảng lương</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Thêm mới bảng lương</h3>
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
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Mã bảng lương:</label>
                    <input type="text" class="form-control" name="maLuong" value="<?php echo $maLuong; ?>" readonly>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Chọn nhân viên: <span style="color: red;">*</span></label>
                    <select name="maNhanVien" class="form-control" required>
                      <option value="">-- Chọn nhân viên --</option>
                      <?php 
                        if(count($arrNhanVien) > 0) {
                          foreach($arrNhanVien as $nv) {
                            echo "<option value='".$nv['MaNV']."'>".$nv['Hoten']." - ".$nv['ChucVu']." (".$nv['TenPhongBan'].")</option>";
                          }
                        } else {
                          echo "<option value='' disabled>Tất cả nhân viên đã có bảng lương</option>";
                        }
                      ?>
                    </select>
                    <small style="color: red;"><?php if(isset($error['maNhanVien'])) echo $error['maNhanVien']; ?></small>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Lương cơ bản: <span style="color: red;">*</span></label>
                    <input type="text" name="luongCoBan" class="form-control currency" required>
                    <small style="color: red;"><?php if(isset($error['luongCoBan'])) echo $error['luongCoBan']; ?></small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Phụ cấp ăn trưa:</label>
                    <input type="text" name="phuCapAnTrua" class="form-control currency" value="0">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Phụ cấp đi lại:</label>
                    <input type="text" name="phuCapDiLai" class="form-control currency" value="0">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>BHXH (%):</label>
                    <input type="number" name="bhxh" class="form-control" min="0" max="100" step="0.1" value="8">
                    <small class="text-muted">Phần trăm trích từ lương cơ bản</small>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>BHYT (%):</label>
                    <input type="number" name="bhyt" class="form-control" min="0" max="100" step="0.1" value="1.5">
                    <small class="text-muted">Phần trăm trích từ lương cơ bản</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Thuế TNCN (%):</label>
                    <input type="number" name="thueTNCN" class="form-control" min="0" max="100" step="0.1" value="10">
                    <small class="text-muted">Phần trăm trích từ thu nhập tính thuế</small>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="ghiChu" class="form-control" rows="3"></textarea>
              </div>
              
              <div class="form-group">
                <button type="submit" name="save" class="btn btn-primary">
                  <i class="fa fa-save"></i> Lưu bảng lương
                </button>
                <a href="danhsachbangluong.php?p=salary&a=list" class="btn btn-default">
                  <i class="fa fa-arrow-left"></i> Quay lại
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