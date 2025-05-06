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

  // Tạo mã kỷ luật mới
  $maKTKL = "KL" . time();

  // Lấy danh sách nhân viên
  $queryNhanVien = "SELECT nv.MaNV, nv.Hoten, nv.ChucVu, pb.TenPhongBan 
                   FROM NhanVien nv
                   LEFT JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                   ORDER BY nv.Hoten ASC";
  $resultNhanVien = mysqli_query($conn, $queryNhanVien);
  $arrNhanVien = array();
  while ($row = mysqli_fetch_array($resultNhanVien)) {
    $arrNhanVien[] = $row;
  }
  
  // Tự động tạo kỷ luật cho nhân viên đi muộn
  // function taoKyLuatDiMuon($conn) {
  //   // Lấy dữ liệu chấm công có trạng thái "Đi muộn-Đã chấm công" mà chưa tạo kỷ luật
  //   $queryChamCong = "SELECT cc.MaNV, cc.Ngay, nv.Hoten
  //                     FROM ChamCong cc
  //                     JOIN NhanVien nv ON cc.MaNV = nv.MaNV
  //                     WHERE cc.TrangThai = 'Đi muộn - Đã hoàn thành'
  //                     AND NOT EXISTS (
  //                       SELECT 1 FROM KTKL kl 
  //                       WHERE kl.MaNV = cc.MaNV 
  //                       AND kl.NgayApDung = cc.Ngay
  //                       AND kl.MoTa LIKE 'Đi muộn%'
  //                     )";
  //   $resultChamCong = mysqli_query($conn, $queryChamCong);
    
  //   $count = 0;
  //   while ($row = mysqli_fetch_array($resultChamCong)) {
  //     $maNV = $row['MaNV'];
  //     $ngayChamCong = $row['NgayChamCong'];
  //     $hoTen = $row['Hoten'];
      
  //     // Tạo mã kỷ luật mới
  //     $maKTKL = "KL" . time() . "_" . $count;
      
  //     // Thêm kỷ luật tự động
  //     $moTa = "Đi muộn ngày " . date('d/m/Y', strtotime($ngayChamCong));
  //     $soTien = 100000; // 100.000 VND
  //     $ghiChu = "Tự động tạo từ hệ thống chấm công";
      
  //     $insert = "INSERT INTO KTKL(MaKTKL, MaNV, LoaiSuKien, MoTa, HinhAnh, SoTien, NgayApDung, GhiChu) 
  //               VALUES('$maKTKL', '$maNV', 'Kỷ luật', '$moTa', '', '$soTien', '$ngayChamCong', '$ghiChu')";
  //     mysqli_query($conn, $insert);
      
  //     $count++;
  //   }
    
    return $count;
  }
  
  // Gọi hàm tự động tạo kỷ luật khi trang được load
  // $countKyLuatMoi = taoKyLuatDiMuon($conn);
  // if($countKyLuatMoi > 0) {
  //   $success['auto'] = "Đã tự động tạo $countKyLuatMoi kỷ luật mới cho nhân viên đi muộn.";
  //   $showMess = true;
  // }
  
  // Xử lý thêm kỷ luật thủ công
  if(isset($_POST['save'])) {
    $error = array();
    $success = array();
    $showMess = false;
    
    // Lấy dữ liệu từ form
    $maNhanVien = $_POST['maNhanVien'];
    $moTa = $_POST['moTa'];
    $soTien = str_replace(',', '', $_POST['soTien']);
    $ngayApDung = $_POST['ngayApDung'];
    $ghiChu = $_POST['ghiChu'];
    
    // Validate dữ liệu
    if(empty($maNhanVien)) {
      $error['maNhanVien'] = 'Vui lòng chọn nhân viên';
    }
    
    if(empty($moTa)) {
      $error['moTa'] = 'Vui lòng nhập mô tả kỷ luật';
    }
    
    if(empty($soTien) || !is_numeric($soTien)) {
      $error['soTien'] = 'Vui lòng nhập số tiền hợp lệ';
    }
    
    if(empty($ngayApDung)) {
      $error['ngayApDung'] = 'Vui lòng chọn ngày áp dụng';
    }
    
    // Xử lý upload hình ảnh (nếu có)
    $hinhAnh = '';
    if(isset($_FILES['hinhAnh']) && $_FILES['hinhAnh']['error'] == 0) {
      $allowed = array('jpg', 'jpeg', 'png', 'gif');
      $filename = $_FILES['hinhAnh']['name'];
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      
      if(!in_array(strtolower($ext), $allowed)) {
        $error['hinhAnh'] = 'Chỉ chấp nhận file hình ảnh: jpg, jpeg, png, gif';
      } else {
        $newFileName = 'kyluat_' . time() . '.' . $ext;
        $upload = move_uploaded_file($_FILES['hinhAnh']['tmp_name'], '../uploads/kyluat/' . $newFileName);
        if($upload) {
          $hinhAnh = $newFileName;
        } else {
          $error['hinhAnh'] = 'Không thể upload hình ảnh';
        }
      }
    }
    
    if(empty($error)) {
      // Thêm kỷ luật
      $insert = "INSERT INTO KTKL(MaKTKL, MaNV, LoaiSuKien, MoTa, HinhAnh, SoTien, NgayApDung, GhiChu) 
                VALUES('$maKTKL', '$maNhanVien', 'Kỷ luật', '$moTa', '$hinhAnh', '$soTien', '$ngayApDung', '$ghiChu')";
      $result = mysqli_query($conn, $insert);
      
      if($result) {
        $showMess = true;
        $success['success'] = 'Thêm kỷ luật thành công!';
        
        // Chuyển về trang danh sách
        echo "<script>
          setTimeout(function() {
            window.location.href='danhsachkyluat.php?p=bonus-discipline&a=discipline';
          }, 2000);
        </script>";
      } else {
        $error['db'] = 'Lỗi khi thêm kỷ luật: ' . mysqli_error($conn);
      }
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Thêm kỷ luật
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachkyluat.php?p=bonus-discipline&a=discipline">Danh sách kỷ luật</a></li>
      <li class="active">Thêm kỷ luật</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">Thêm kỷ luật mới</h3>
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
            
            <form action="" method="POST" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mã kỷ luật:</label>
                    <input type="text" class="form-control" name="maKTKL" value="<?php echo $maKTKL; ?>" readonly>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Chọn nhân viên: <span style="color: red;">*</span></label>
                    <select name="maNhanVien" class="form-control" required>
                      <option value="">-- Chọn nhân viên --</option>
                      <?php foreach($arrNhanVien as $nv): ?>
                        <option value="<?php echo $nv['MaNV']; ?>" <?php echo (isset($_POST['maNhanVien']) && $_POST['maNhanVien'] == $nv['MaNV']) ? 'selected' : ''; ?>>
                          <?php echo $nv['Hoten'] . ' - ' . $nv['MaNV'] . ' (' . $nv['ChucVu'] . ' - ' . $nv['TenPhongBan'] . ')'; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <small style="color: red;"><?php if(isset($error['maNhanVien'])) echo $error['maNhanVien']; ?></small>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số tiền phạt: <span style="color: red;">*</span></label>
                    <input type="text" name="soTien" class="form-control currency" placeholder="Nhập số tiền" value="<?php echo isset($_POST['soTien']) ? number_format($_POST['soTien']) : ''; ?>" required>
                    <small style="color: red;"><?php if(isset($error['soTien'])) echo $error['soTien']; ?></small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Ngày áp dụng: <span style="color: red;">*</span></label>
                    <input type="date" name="ngayApDung" class="form-control" value="<?php echo isset($_POST['ngayApDung']) ? $_POST['ngayApDung'] : date('Y-m-d'); ?>" required>
                    <small style="color: red;"><?php if(isset($error['ngayApDung'])) echo $error['ngayApDung']; ?></small>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label>Mô tả vi phạm: <span style="color: red;">*</span></label>
                <textarea name="moTa" class="form-control" rows="3" placeholder="Nhập mô tả vi phạm" required><?php echo isset($_POST['moTa']) ? $_POST['moTa'] : ''; ?></textarea>
                <small style="color: red;"><?php if(isset($error['moTa'])) echo $error['moTa']; ?></small>
              </div>
              
              <div class="form-group">
                <label>Hình ảnh minh chứng (nếu có):</label>
                <input type="file" name="hinhAnh" class="form-control">
                <small style="color: red;"><?php if(isset($error['hinhAnh'])) echo $error['hinhAnh']; ?></small>
                <p class="help-block">Chấp nhận file jpg, jpeg, png, gif.</p>
              </div>
              
              <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="ghiChu" class="form-control" rows="3" placeholder="Nhập ghi chú (nếu có)"><?php echo isset($_POST['ghiChu']) ? $_POST['ghiChu'] : ''; ?></textarea>
              </div>
              
              <div class="form-group">
                <button type="submit" name="save" class="btn btn-danger">
                  <i class="fa fa-plus"></i> Thêm kỷ luật
                </button>
                <a href="danhsachkyluat.php?p=bonus-discipline&a=discipline" class="btn btn-default">
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