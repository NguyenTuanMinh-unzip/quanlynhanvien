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

  // Biến cho thông tin nhân viên và chấm công
  $nhanVien = array();
  $chamCong = array();
  $maChiTiet = "";
  $ngayChiTiet = "";
  $isEdit = false;

  // Kiểm tra nếu có tham số chỉnh sửa
  if(isset($_GET['id']) && isset($_GET['date'])) {
    $maChiTiet = $_GET['id'];
    $ngayChiTiet = $_GET['date'];
    $isEdit = true;
    
    // Lấy thông tin nhân viên
    $queryNhanVien = "SELECT nv.*, pb.TenPhongBan FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                      WHERE nv.MaNV = '$maChiTiet'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    
    if(mysqli_num_rows($resultNhanVien) > 0) {
      $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin nhân viên!');
        window.location.href='dulieuchamcong.php?p=attendance&a=data';
      </script>";
      exit;
    }
    
    // Kiểm tra xem đã có bản ghi chấm công chưa
    $queryChamCong = "SELECT * FROM ChamCong WHERE MaNV = '$maChiTiet' AND Ngay = '$ngayChiTiet'";
    $resultChamCong = mysqli_query($conn, $queryChamCong);
    if(mysqli_num_rows($resultChamCong) > 0) {
      $chamCong = mysqli_fetch_assoc($resultChamCong);
    }
  } else {
    // Đang ở chế độ thêm mới, lấy danh sách nhân viên
    $queryDanhSachNV = "SELECT nv.MaNV, nv.Hoten, pb.TenPhongBan 
                      FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb 
                      ORDER BY nv.Hoten ASC";
    $resultDanhSachNV = mysqli_query($conn, $queryDanhSachNV);
    $arrNhanVien = array();
    while ($row = mysqli_fetch_array($resultDanhSachNV)) {
      $arrNhanVien[] = $row;
    }
  }

  // Xử lý thêm/cập nhật chấm công
  if(isset($_POST['save'])) {
    $error = array();
    $success = array();
    $showMess = false;
    
    // Lấy dữ liệu từ form
    $maNhanVien = isset($_POST['maNhanVien']) ? $_POST['maNhanVien'] : $maChiTiet;
    $ngay = isset($_POST['ngay']) ? $_POST['ngay'] : $ngayChiTiet;
    $gioVao = isset($_POST['gioVao']) ? $_POST['gioVao'] : '';
    $gioRa = isset($_POST['gioRa']) ? $_POST['gioRa'] : '';
    $soGioLam = isset($_POST['soGioLam']) ? $_POST['soGioLam'] : 0;
    $gioTangCa = isset($_POST['gioTangCa']) ? $_POST['gioTangCa'] : 0;
    $trangThai = isset($_POST['trangThai']) ? $_POST['trangThai'] : 'Chưa chấm công';
    $ghiChu = isset($_POST['ghiChu']) ? $_POST['ghiChu'] : '';
    
    // Validate dữ liệu
    if(empty($maNhanVien)) {
      $error['maNhanVien'] = 'Vui lòng chọn nhân viên';
    }
    
    if(empty($ngay)) {
      $error['ngay'] = 'Vui lòng chọn ngày';
    }
    
    if(!empty($gioVao) && !empty($gioRa)) {
      // Tính toán số giờ làm và giờ tăng ca
      $gioVaoObj = new DateTime($gioVao);
      $gioRaObj = new DateTime($gioRa);
      
      if($gioRaObj < $gioVaoObj) {
        $error['gioRa'] = 'Giờ ra phải sau giờ vào';
      } else {
        $interval = $gioVaoObj->diff($gioRaObj);
        $totalHours = $interval->h + ($interval->i / 60);
        
        // Cập nhật số giờ làm và giờ tăng ca
        if($totalHours > 8) {
          $soGioLam = 8;
          $gioTangCa = $totalHours - 8;
        } else {
          $soGioLam = $totalHours;
          $gioTangCa = 0;
        }
      }
    }
    
    if(empty($error)) {
      // Kiểm tra xem đã có bản ghi chấm công của nhân viên này trong ngày này chưa
      $queryCheck = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' AND Ngay = '$ngay'";
      $resultCheck = mysqli_query($conn, $queryCheck);
      
      if(mysqli_num_rows($resultCheck) > 0 || $isEdit) {
        // Đã có bản ghi hoặc đang ở chế độ chỉnh sửa, thực hiện cập nhật
        $update = "UPDATE ChamCong SET 
                  GioVao = " . (!empty($gioVao) ? "'$gioVao'" : "NULL") . ", 
                  GioRa = " . (!empty($gioRa) ? "'$gioRa'" : "NULL") . ", 
                  SoGioLam = '$soGioLam', 
                  GioTangCa = '$gioTangCa', 
                  TrangThai = '$trangThai', 
                  GhiChu = '$ghiChu'
                  WHERE MaNV = '$maNhanVien' AND Ngay = '$ngay'";
        $result = mysqli_query($conn, $update);
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Cập nhật dữ liệu chấm công thành công!';
          
          // Nếu đang ở chế độ chỉnh sửa, refresh lại thông tin chấm công
          if($isEdit) {
            $resultChamCong = mysqli_query($conn, "SELECT * FROM ChamCong WHERE MaNV = '$maChiTiet' AND Ngay = '$ngayChiTiet'");
            if(mysqli_num_rows($resultChamCong) > 0) {
              $chamCong = mysqli_fetch_assoc($resultChamCong);
            }
          } else {
            // Nếu đang ở chế độ thêm mới, chuyển về trang dữ liệu chấm công
            echo "<script>
              setTimeout(function() {
                window.location.href = 'dulieuchamcong.php?p=attendance&a=data&date=$ngay';
              }, 2000);
            </script>";
          }
        } else {
          $error['db'] = 'Lỗi khi cập nhật: ' . mysqli_error($conn);
        }
      } else {
        // Chưa có bản ghi, thực hiện thêm mới
        $maCC = "CC" . time();
        $insert = "INSERT INTO ChamCong(MaCC, MaNV, Ngay, GioVao, GioRa, SoGioLam, GioTangCa, TrangThai, GhiChu) 
                  VALUES('$maCC', '$maNhanVien', '$ngay', " . 
                  (!empty($gioVao) ? "'$gioVao'" : "NULL") . ", " . 
                  (!empty($gioRa) ? "'$gioRa'" : "NULL") . ", 
                  '$soGioLam', '$gioTangCa', '$trangThai', '$ghiChu')";
        $result = mysqli_query($conn, $insert);
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Thêm dữ liệu chấm công thành công!';
          
          // Chuyển về trang dữ liệu chấm công
          echo "<script>
            setTimeout(function() {
              window.location.href = 'dulieuchamcong.php?p=attendance&a=data&date=$ngay';
            }, 2000);
          </script>";
        } else {
          $error['db'] = 'Lỗi khi thêm: ' . mysqli_error($conn);
        }
      }
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?php echo ($isEdit ? "Chỉnh sửa" : "Thêm mới"); ?> dữ liệu chấm công</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="dulieuchamcong.php?p=attendance&a=data">Dữ liệu chấm công</a></li>
      <li class="active"><?php echo ($isEdit ? "Chỉnh sửa" : "Thêm mới"); ?> chấm công</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><?php echo ($isEdit ? "Chỉnh sửa chấm công: " . $nhanVien['Hoten'] . " - Ngày: " . date('d/m/Y', strtotime($ngayChiTiet)) : "Thêm mới dữ liệu chấm công"); ?></h3>
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
              <?php if(!$isEdit): // Hiển thị dropdown chọn nhân viên nếu đang thêm mới ?>
              <div class="form-group">
                <label>Chọn nhân viên: <span style="color: red;">*</span></label>
                <select name="maNhanVien" class="form-control" required>
                  <option value="">-- Chọn nhân viên --</option>
                  <?php 
                    foreach($arrNhanVien as $nv) {
                      echo "<option value='".$nv['MaNV']."'>".$nv['Hoten']." - ".$nv['TenPhongBan']."</option>";
                    }
                  ?>
                </select>
                <small style="color: red;"><?php if(isset($error['maNhanVien'])) echo $error['maNhanVien']; ?></small>
              </div>
              <?php else: // Hiển thị thông tin nhân viên nếu đang chỉnh sửa ?>
              <div class="form-group">
                <label>Nhân viên:</label>
                <input type="text" class="form-control" value="<?php echo $nhanVien['Hoten'].' - '.$nhanVien['TenPhongBan']; ?>" readonly>
                <input type="hidden" name="maNhanVien" value="<?php echo $maChiTiet; ?>">
              </div>
              <?php endif; ?>
              
              <div class="form-group">
                <label>Ngày: <span style="color: red;">*</span></label>
                <input type="date" name="ngay" class="form-control" value="<?php echo $isEdit ? $ngayChiTiet : date('Y-m-d'); ?>" <?php echo $isEdit ? 'readonly' : ''; ?> required>
                <small style="color: red;"><?php if(isset($error['ngay'])) echo $error['ngay']; ?></small>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Giờ vào:</label>
                    <input type="time" name="gioVao" class="form-control" value="<?php echo isset($chamCong['GioVao']) ? date('H:i', strtotime($chamCong['GioVao'])) : ''; ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Giờ ra:</label>
                    <input type="time" name="gioRa" class="form-control" value="<?php echo isset($chamCong['GioRa']) ? date('H:i', strtotime($chamCong['GioRa'])) : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['gioRa'])) echo $error['gioRa']; ?></small>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số giờ làm:</label>
                    <input type="number" name="soGioLam" class="form-control" step="0.01" min="0" max="24" value="<?php echo isset($chamCong['SoGioLam']) ? $chamCong['SoGioLam'] : '0'; ?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Giờ tăng ca:</label>
                    <input type="number" name="gioTangCa" class="form-control" step="0.01" min="0" value="<?php echo isset($chamCong['GioTangCa']) ? $chamCong['GioTangCa'] : '0'; ?>">
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label>Trạng thái:</label>
                <select name="trangThai" class="form-control">
                  <option value="Chưa chấm công" <?php echo (isset($chamCong['TrangThai']) && $chamCong['TrangThai'] == 'Chưa chấm công') ? 'selected' : ''; ?>>Chưa chấm công</option>
                  <option value="Đang làm việc" <?php echo (isset($chamCong['TrangThai']) && $chamCong['TrangThai'] == 'Đang làm việc') ? 'selected' : ''; ?>>Đang làm việc</option>
                  <option value="Đã hoàn thành" <?php echo (isset($chamCong['TrangThai']) && $chamCong['TrangThai'] == 'Đã hoàn thành') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                  <option value="Vắng mặt" <?php echo (isset($chamCong['TrangThai']) && $chamCong['TrangThai'] == 'Vắng mặt') ? 'selected' : ''; ?>>Vắng mặt</option>
                </select>
              </div>
              
              <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="ghiChu" class="form-control" rows="3"><?php echo isset($chamCong['GhiChu']) ? $chamCong['GhiChu'] : ''; ?></textarea>
              </div>
              
              <div class="form-group">
                <button type="submit" name="save" class="btn btn-primary">
                  <i class="fa fa-save"></i> <?php echo $isEdit ? 'Cập nhật' : 'Thêm mới'; ?>
                </button>
                <a href="dulieuchamcong.php?p=attendance&a=data<?php echo $isEdit ? '&date='.$ngayChiTiet : ''; ?>" class="btn btn-default">
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
  // Tính toán số giờ làm và giờ tăng ca khi giờ vào/ra thay đổi
  document.addEventListener('DOMContentLoaded', function() {
    const gioVaoInput = document.querySelector('input[name="gioVao"]');
    const gioRaInput = document.querySelector('input[name="gioRa"]');
    const soGioLamInput = document.querySelector('input[name="soGioLam"]');
    const gioTangCaInput = document.querySelector('input[name="gioTangCa"]');
    
    function calculateHours() {
      if(gioVaoInput.value && gioRaInput.value) {
        const gioVao = new Date(`2000-01-01T${gioVaoInput.value}`);
        const gioRa = new Date(`2000-01-01T${gioRaInput.value}`);
        
        if(gioRa >= gioVao) {
          // Tính số giờ làm (milliseconds -> giờ)
          const diffMs = gioRa - gioVao;
          const diffHrs = diffMs / (1000 * 60 * 60);
          
          // Xử lý tăng ca nếu làm quá 8 tiếng
          if(diffHrs > 8) {
            soGioLamInput.value = 8;
            gioTangCaInput.value = (diffHrs - 8).toFixed(2);
          } else {
            soGioLamInput.value = diffHrs.toFixed(2);
            gioTangCaInput.value = 0;
          }
        }
      }
    }
    
    if(gioVaoInput && gioRaInput) {
      gioVaoInput.addEventListener('change', calculateHours);
      gioRaInput.addEventListener('change', calculateHours);
    }
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