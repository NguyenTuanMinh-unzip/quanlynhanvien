<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng - CHỈ CHO PHÉP ADMIN
  if(!isset($row_acc) || $row_acc['Chucvu'] != 'admin') {
    echo "<script>
      alert('Bạn không có quyền truy cập trang này!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }

  // Lấy tháng năm hiện tại
  $thangHienTai = date('m');
  $namHienTai = date('Y');
  
  // Xử lý tham số tháng/năm từ URL nếu có
  $thang = isset($_GET['thang']) ? $_GET['thang'] : $thangHienTai;
  $nam = isset($_GET['nam']) ? $_GET['nam'] : $namHienTai;
  
  // Lấy danh sách tất cả nhân viên có bảng lương
  $queryAllNhanVien = "SELECT nv.MaNV, nv.Hoten, nv.ChucVu, pb.TenPhongBan, l.LuongCoBan 
                       FROM NhanVien nv 
                       JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                       LEFT JOIN Luong l ON nv.MaNV = l.MaNV
                       WHERE l.LuongCoBan IS NOT NULL
                       ORDER BY nv.MaNV";
  $resultAllNhanVien = mysqli_query($conn, $queryAllNhanVien);
  $allNhanVien = array();
  
  while($row = mysqli_fetch_assoc($resultAllNhanVien)) {
    $allNhanVien[] = $row;
  }
  
  // Xử lý tính lương cho tất cả nhân viên
  if(isset($_POST['tinhluongtatca'])) {
    $error = array();
    $success = array();
    $showMess = false;
    $soNhanVienThanhCong = 0;
    $soNhanVienThatBai = 0;
    
    foreach($allNhanVien as $nhanVien) {
      $maNhanVien = $nhanVien['MaNV'];
      
      // Lấy thông tin bảng lương của nhân viên
      $queryBangLuong = "SELECT * FROM Luong WHERE MaNV = '$maNhanVien'";
      $resultBangLuong = mysqli_query($conn, $queryBangLuong);
      
      if(mysqli_num_rows($resultBangLuong) > 0) {
        $bangLuong = mysqli_fetch_assoc($resultBangLuong);
        
        // Lấy dữ liệu chấm công trong tháng
        $ngayDauThang = date("$nam-$thang-01");
        $ngayCuoiThang = date("Y-m-t", strtotime($ngayDauThang));
        
        $queryChamCong = "SELECT * FROM ChamCong 
                          WHERE MaNV = '$maNhanVien' 
                          AND Ngay BETWEEN '$ngayDauThang' AND '$ngayCuoiThang'
                          ORDER BY Ngay ASC";
        $resultChamCong = mysqli_query($conn, $queryChamCong);
        $tongNgayCong = 0;
        $tongGioLam = 0;
        $tongGioTangCa = 0;
        
        while ($row = mysqli_fetch_array($resultChamCong)) {
          // Đếm các loại trạng thái
          if($row['TrangThai'] == 'Đã hoàn thành' || $row['TrangThai'] == 'Đi muộn - Đã hoàn thành') {
            $tongNgayCong++;
            $tongGioLam += $row['SoGioLam'];
            $tongGioTangCa += $row['GioTangCa'];
          }
        }
        
        // Lấy thông tin thưởng/phạt trong tháng
        $queryKTKL = "SELECT * FROM KTKL 
                      WHERE MaNV = '$maNhanVien' 
                      AND MONTH(NgayApDung) = '$thang' 
                      AND YEAR(NgayApDung) = '$nam'";
        $resultKTKL = mysqli_query($conn, $queryKTKL);
        $tongThuong = 0;
        $tongPhat = 0;
        
        while ($row = mysqli_fetch_array($resultKTKL)) {
          if($row['LoaiSuKien'] == 'Khen thưởng') {
            $tongThuong += $row['SoTien'];
          } else if($row['LoaiSuKien'] == 'Kỷ luật') {
            $tongPhat += $row['SoTien'];
          }
        }
        
        // Tính lương
        $luongCoBan = $bangLuong['LuongCoBan'];
        $phuCapAnTrua = $bangLuong['PhuCapAnTrua'];
        $phuCapDiLai = $bangLuong['PhuCapDiLai'];
        $bhxhRate = $bangLuong['BHXH'] / 100;
        $bhytRate = $bangLuong['BHYT'] / 100;
        $thueTNCNRate = $bangLuong['ThueTNCN'] / 100;
        
        $soNgayLamViecChuan = 22;
        
        // Kiểm tra nếu là thực tập sinh
        if($nhanVien['ChucVu'] == 'Thực tập sinh') {
          // Thực tập sinh: 75% lương cơ bản + phụ cấp, không trừ bảo hiểm và thuế
          $luongTheoNgayCong = ($luongCoBan * 0.75 / $soNgayLamViecChuan) * $tongNgayCong;
          $luongGioBinhThuong = ($luongCoBan * 0.75) / ($soNgayLamViecChuan * 8);
          $luongTangCa = $luongGioBinhThuong * 1.5 * $tongGioTangCa;
          $tongPhuCap = $phuCapAnTrua + $phuCapDiLai;
          $tienBHXH = 0;
          $tienBHYT = 0;
          $tienThueTNCN = 0;
          $thucLanh = $luongTheoNgayCong + $luongTangCa + $tongPhuCap + $tongThuong - $tongPhat;
        } else {
          // Nhân viên chính thức: tính lương bình thường
          $luongTheoNgayCong = ($luongCoBan / $soNgayLamViecChuan) * $tongNgayCong;
          $luongGioBinhThuong = $luongCoBan / ($soNgayLamViecChuan * 8);
          $luongTangCa = $luongGioBinhThuong * 1.5 * $tongGioTangCa;
          $tongPhuCap = $phuCapAnTrua + $phuCapDiLai;
          $tienBHXH = $luongCoBan * $bhxhRate;
          $tienBHYT = $luongCoBan * $bhytRate;
          $thuNhapTruocThue = $luongTheoNgayCong + $luongTangCa + $tongPhuCap + $tongThuong - $tongPhat;
          $thuNhapTinhThue = $thuNhapTruocThue - $tienBHXH - $tienBHYT;
          $tienThueTNCN = $thuNhapTinhThue > 0 ? $thuNhapTinhThue * $thueTNCNRate : 0;
          $thucLanh = $thuNhapTruocThue - $tienBHXH - $tienBHYT - $tienThueTNCN;
        }
        
        // Kiểm tra xem đã có bảng lương tháng này chưa
        $queryCheckBangLuong = "SELECT * FROM BangLuongThang 
                                WHERE MaNV = '$maNhanVien' 
                                AND Thang = '$thang' 
                                AND Nam = '$nam'";
        $resultCheckBangLuong = mysqli_query($conn, $queryCheckBangLuong);
        
        if(mysqli_num_rows($resultCheckBangLuong) > 0) {
          // Đã có bảng lương, cập nhật
          $bangLuongThang = mysqli_fetch_assoc($resultCheckBangLuong);
          $maBangLuong = $bangLuongThang['MaBangLuong'];
          
          $updateBangLuong = "UPDATE BangLuongThang SET 
                              LuongCoBan = '$luongCoBan',
                              NgayCongThucTe = '$tongNgayCong',
                              LuongTheoNgayCong = '$luongTheoNgayCong',
                              GioTangCa = '$tongGioTangCa',
                              LuongTangCa = '$luongTangCa',
                              PhuCap = '$tongPhuCap',
                              Thuong = '$tongThuong',
                              Phat = '$tongPhat',
                              BHXH = '$tienBHXH',
                              BHYT = '$tienBHYT',
                              ThueTNCN = '$tienThueTNCN',
                              ThucLanh = '$thucLanh',
                              NgayCapNhat = NOW()
                              WHERE MaBangLuong = '$maBangLuong'";
          $resultUpdate = mysqli_query($conn, $updateBangLuong);
          
          if($resultUpdate) {
            $soNhanVienThanhCong++;
          } else {
            $soNhanVienThatBai++;
          }
        } else {
          // Chưa có bảng lương, tạo mới
          $maBangLuong = "BL" . time() . rand(1000, 9999);
          
          $insertBangLuong = "INSERT INTO BangLuongThang(MaBangLuong, MaNV, Thang, Nam, LuongCoBan, 
                              NgayCongThucTe, LuongTheoNgayCong, GioTangCa, LuongTangCa, 
                              PhuCap, Thuong, Phat, BHXH, BHYT, ThueTNCN, ThucLanh, NgayCapNhat) 
                              VALUES('$maBangLuong', '$maNhanVien', '$thang', '$nam', '$luongCoBan', 
                              '$tongNgayCong', '$luongTheoNgayCong', '$tongGioTangCa', '$luongTangCa', 
                              '$tongPhuCap', '$tongThuong', '$tongPhat', '$tienBHXH', '$tienBHYT', 
                              '$tienThueTNCN', '$thucLanh', NOW())";
          $resultInsert = mysqli_query($conn, $insertBangLuong);
          
          if($resultInsert) {
            $soNhanVienThanhCong++;
          } else {
            $soNhanVienThatBai++;
          }
        }
      } else {
        $soNhanVienThatBai++;
      }
    }
    
    $showMess = true;
    if($soNhanVienThanhCong > 0) {
      $success['success'] = "Đã tính lương thành công cho $soNhanVienThanhCong nhân viên!";
    }
    if($soNhanVienThatBai > 0) {
      $error['error'] = "Có $soNhanVienThatBai nhân viên tính lương thất bại!";
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Tính lương tất cả nhân viên</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachbangluong.php?p=salary&a=list">Bảng lương</a></li>
      <li class="active">Tính lương tất cả</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <!-- Chọn tháng năm -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Chọn tháng năm để tính lương</h3>
          </div>
          <div class="box-body">
            <form method="GET" action="" class="form-inline">
              <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
              <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
              
              <div class="form-group">
                <label for="thang">Tháng: </label>
                <select name="thang" class="form-control" style="margin-left: 10px;">
                  <?php for($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($thang == str_pad($i, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                      Tháng <?php echo $i; ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>
              
              <div class="form-group" style="margin-left: 15px;">
                <label for="nam">Năm: </label>
                <select name="nam" class="form-control" style="margin-left: 10px;">
                  <?php for($i = $namHienTai - 2; $i <= $namHienTai; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($nam == $i) ? 'selected' : ''; ?>>
                      Năm <?php echo $i; ?>
                    </option>
                  <?php endfor; ?>
                </select>
              </div>
              
              <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                <i class="fa fa-search"></i> Xem danh sách
              </button>
            </form>
          </div>
        </div>
        
        <!-- Hiển thị thông báo -->
        <?php 
          // show error
          if(isset($error) && !empty($error) && isset($showMess) && $showMess == true) {
            echo "<div class='alert alert-danger alert-dismissible'>";
            echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>";
            echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
            foreach ($error as $err) {
              echo $err . "<br/>";
            }
            echo "</div>";
          }
          
          // show success
          if(isset($success) && !empty($success) && isset($showMess) && $showMess == true) {
            echo "<div class='alert alert-success alert-dismissible'>";
            echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>";
            echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
            foreach ($success as $suc) {
              echo $suc . "<br/>";
            }
            echo "</div>";
          }
        ?>
        
        <!-- Danh sách nhân viên -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Danh sách nhân viên - Tháng <?php echo $thang; ?>/<?php echo $nam; ?></h3>
            
            <div class="box-tools pull-right">
              <form method="POST" action="" style="display: inline-block;">
                <button type="submit" name="tinhluongtatca" class="btn btn-success" onclick="return confirm('Bạn có chắc muốn tính lương cho tất cả nhân viên trong tháng <?php echo $thang; ?>/<?php echo $nam; ?>?')">
                  <i class="fa fa-calculator"></i> Tính lương tất cả
                </button>
              </form>
            </div>
          </div>
          
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>STT</th>
                    <th>Mã NV</th>
                    <th>Họ tên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <th>Lương cơ bản</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    if(count($allNhanVien) > 0) {
                      $stt = 1;
                      foreach($allNhanVien as $nv) {
                        // Kiểm tra trạng thái bảng lương
                        $queryCheckLuong = "SELECT * FROM BangLuongThang 
                                           WHERE MaNV = '" . $nv['MaNV'] . "' 
                                           AND Thang = '$thang' 
                                           AND Nam = '$nam'";
                        $resultCheckLuong = mysqli_query($conn, $queryCheckLuong);
                        $trangThai = "Chưa tính";
                        $statusClass = "label-warning";
                        
                        if(mysqli_num_rows($resultCheckLuong) > 0) {
                          $trangThai = "Đã tính";
                          $statusClass = "label-success";
                        }
                        
                        echo "<tr>";
                        echo "<td>" . $stt++ . "</td>";
                        echo "<td>" . $nv['MaNV'] . "</td>";
                        echo "<td>" . $nv['Hoten'] . "</td>";
                        echo "<td>";
                        if($nv['ChucVu'] == 'Thực tập sinh') {
                          echo "<span class='label label-info'>" . $nv['ChucVu'] . "</span>";
                        } else {
                          echo $nv['ChucVu'];
                        }
                        echo "</td>";
                        echo "<td>" . $nv['TenPhongBan'] . "</td>";
                        echo "<td>" . number_format($nv['LuongCoBan']) . " VNĐ</td>";
                        echo "<td><span class='label " . $statusClass . "'>" . $trangThai . "</span></td>";
                        echo "<td>";
                        echo "<a href='tinhluong.php?p=salary&a=calculate&id=" . $nv['MaNV'] . "&thang=" . $thang . "&nam=" . $nam . "' class='btn btn-xs btn-primary'>";
                        echo "<i class='fa fa-calculator'></i> Tính lương";
                        echo "</a> ";
                        echo "<a href='chitietluong.php?id=" . $nv['MaNV'] . "&thang=" . $thang . "&nam=" . $nam . "' class='btn btn-xs btn-info'>";
                        echo "<i class='fa fa-eye'></i> Xem chi tiết";
                        echo "</a>";
                        echo "</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='8' class='text-center'>Không có dữ liệu nhân viên</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          
          <div class="box-footer">
            <div class="row">
              <div class="col-sm-12">
                <div class="text-right">
                  <strong>Tổng số nhân viên:</strong> <?php echo count($allNhanVien); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Ghi chú -->
        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title">Ghi chú về cách tính lương</h3>
          </div>
          <div class="box-body">
            <ul>
              <li>Chức năng này cho phép tính lương hàng loạt cho tất cả nhân viên trong một tháng cụ thể</li>
              <li>Hệ thống sẽ tự động thu thập dữ liệu chấm công, khen thưởng/kỷ luật của từng nhân viên trong tháng được chọn</li>
              <li><strong>Đối với thực tập sinh:</strong> Lương được tính = 75% lương cơ bản + phụ cấp, không trừ BHXH, BHYT và thuế TNCN</li>
              <li><strong>Đối với nhân viên chính thức:</strong> Lương được tính đầy đủ với các khoản trừ bảo hiểm và thuế</li>
              <li>Tiền phạt đi muộn đã được tính trong khoản kỷ luật (100,000 VNĐ/ngày) nên không tính thêm</li>
              <li>Nếu nhân viên đã có bảng lương trong tháng, hệ thống sẽ cập nhật lại thông tin mới nhất</li>
              <li>Bạn cũng có thể tính lương riêng cho từng nhân viên bằng cách nhấn vào nút "Tính lương" ở cột thao tác</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php
  // include footer
  include('../layouts/footer.php');
} else {
  // nếu chưa đăng nhập
  header('Location: login.php');
  exit;
}
?>