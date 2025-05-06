<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Biến cho thông tin nhân viên và chấm công
  $nhanVien = array();
  $chamCong = array();
  $arrLichSu = array();
  $daCheckinVao = false;
  $daCheckinRa = false;

  // Kiểm tra quyền người dùng - CHỈ CHO PHÉP NHÂN VIÊN (KHÔNG PHẢI ADMIN)
  if(isset($row_acc) && $row_acc['Chucvu'] == 'admin') {
    echo "<script>
      alert('Trang này chỉ dành cho nhân viên thực hiện chấm công!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }

  // Lấy thông tin tài khoản đăng nhập
  if(isset($row_acc) && isset($row_acc['MaNV'])) {
    $maNhanVien = $row_acc['MaNV'];
    
    // Lấy thông tin nhân viên
    $queryNhanVien = "SELECT nv.*, pb.TenPhongBan FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                      WHERE nv.MaNV = '$maNhanVien'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    
    if(mysqli_num_rows($resultNhanVien) > 0) {
      $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin nhân viên!');
        window.location.href='index.php?p=index&a=statistic';
      </script>";
      exit;
    }
    
    // Lấy ngày hiện tại
    $ngayHienTai = date('Y-m-d');
    
    // Kiểm tra xem đã chấm công vào chưa trong ngày hôm nay
    $queryCheckVao = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' AND Ngay = '$ngayHienTai' AND GioVao IS NOT NULL";
    $resultCheckVao = mysqli_query($conn, $queryCheckVao);
    $daCheckinVao = mysqli_num_rows($resultCheckVao) > 0;
    
    // Kiểm tra xem đã chấm công ra chưa trong ngày hôm nay
    $queryCheckRa = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' AND Ngay = '$ngayHienTai' AND GioRa IS NOT NULL";
    $resultCheckRa = mysqli_query($conn, $queryCheckRa);
    $daCheckinRa = mysqli_num_rows($resultCheckRa) > 0;
    
    // Lấy thông tin chấm công của ngày hôm nay nếu có
    $queryChamCong = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' AND Ngay = '$ngayHienTai'";
    $resultChamCong = mysqli_query($conn, $queryChamCong);
    if(mysqli_num_rows($resultChamCong) > 0) {
      $chamCong = mysqli_fetch_assoc($resultChamCong);
    }
    
    // Lấy 5 lịch sử chấm công gần đây
    $queryLichSu = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' ORDER BY Ngay DESC, GioVao DESC LIMIT 5";
    $resultLichSu = mysqli_query($conn, $queryLichSu);
    $arrLichSu = array();
    while ($row = mysqli_fetch_array($resultLichSu)) {
      $arrLichSu[] = $row;
    }
    
    // Xử lý chấm công vào
    if(isset($_POST['chamcong_vao'])) {
      $error = array();
      $success = array();
      $showMess = false;
      
      // Nếu chưa chấm công vào trong ngày hôm nay
      if(!$daCheckinVao) {
        $gioVao = date('H:i:s');
        $maCC = "CC" . time();
        $ghiChu = isset($_POST['ghichu']) ? $_POST['ghichu'] : '';
        
        // Kiểm tra xem có đi muộn hay không (sau 9:10 sáng)
        $gioMuon = "09:10:00";
        $trangThai = "Đang làm việc";
        
        if(strtotime($gioVao) > strtotime($gioMuon)) {
            $trangThai = "Đi muộn";
        }
        
        if(!isset($chamCong['MaCC'])) {
          // Nếu chưa có bản ghi nào cho ngày hôm nay, tạo mới
          $insert = "INSERT INTO ChamCong(MaCC, MaNV, Ngay, GioVao, TrangThai, GhiChu) 
                     VALUES('$maCC', '$maNhanVien', '$ngayHienTai', '$gioVao', '$trangThai', '$ghiChu')";
          $result = mysqli_query($conn, $insert);
        } else {
          // Nếu đã có bản ghi cho ngày hôm nay nhưng chưa có giờ vào
          $update = "UPDATE ChamCong SET GioVao = '$gioVao', TrangThai = '$trangThai', GhiChu = '$ghiChu' 
                     WHERE MaNV = '$maNhanVien' AND Ngay = '$ngayHienTai'";
          $result = mysqli_query($conn, $update);
        }
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Chấm công vào thành công lúc ' . $gioVao . ($trangThai == "Đi muộn" ? ' (Đi muộn)' : '');
          $daCheckinVao = true;
          
          // Refresh lại thông tin chấm công
          $resultChamCong = mysqli_query($conn, $queryChamCong);
          if(mysqli_num_rows($resultChamCong) > 0) {
            $chamCong = mysqli_fetch_assoc($resultChamCong);
          }
        } else {
          $error['db'] = 'Lỗi khi chấm công: ' . mysqli_error($conn);
        }
      } else {
        $error['dacheckin'] = 'Bạn đã chấm công vào cho ngày hôm nay!';
      }
    }
    
    // Xử lý chấm công ra
      // Xử lý chấm công ra
  if(isset($_POST['chamcong_ra'])) {
    $error = array();
    $success = array();
    $showMess = false;
    
    // Chỉ cho phép chấm công ra nếu đã chấm công vào
    if($daCheckinVao) {
      if(!$daCheckinRa) {
        $gioRa = date('H:i:s');
        $ghiChu = isset($_POST['ghichu']) ? $_POST['ghichu'] : '';
        
        // Tính số giờ làm việc
        $gioVao = $chamCong['GioVao'];
        $gioTinhLam = $gioVao;
        
        // Nếu đi làm trước 9:00 sáng, tính giờ từ 9:00
        if(strtotime($gioVao) <= strtotime('09:00:00')) {
          $gioTinhLam = '09:00:00';
        }
        
        // Tính số giờ làm việc
        $gioTinhObj = new DateTime($gioTinhLam);
        $gioRaObj = new DateTime($gioRa);
        $interval = $gioTinhObj->diff($gioRaObj);
        $soGioLam = $interval->h + ($interval->i / 60);
        
        // Xử lý tăng ca
        $gioTangCa = 0;
        $soGioLamTieuChuan = 8;
        
        if($soGioLam > $soGioLamTieuChuan) {
          $gioTangCa = $soGioLam - $soGioLamTieuChuan;
          if($gioTangCa < 0.75) {
            $gioTangCa = 0;
          }
          $soGioLam = $soGioLamTieuChuan;
        }
        
        // Giữ nguyên trạng thái
        $trangThaiCu = $chamCong['TrangThai'];
        $trangThaiMoi = $trangThaiCu == "Đi muộn" ? "Đi muộn - Đã hoàn thành" : "Đã hoàn thành";
        
        $update = "UPDATE ChamCong SET GioRa = '$gioRa', SoGioLam = '$soGioLam', 
                   GioTangCa = '$gioTangCa', TrangThai = '$trangThaiMoi', GhiChu = CONCAT(GhiChu, ' | $ghiChu')
                   WHERE MaNV = '$maNhanVien' AND Ngay = '$ngayHienTai'";
        $result = mysqli_query($conn, $update);
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Chấm công ra thành công lúc ' . $gioRa;
          $daCheckinRa = true;
          
          // Nếu trạng thái là "Đi muộn - Đã hoàn thành", tự động tạo kỷ luật
          if($trangThaiMoi == "Đi muộn - Đã hoàn thành") {
            // Tạo mã kỷ luật tự động
            $maKTKL = "KL" . time();
            $loaiSuKien = "Kỷ luật";
            $moTa = "Đi muộn ngày " . date('d/m/Y', strtotime($ngayHienTai)) . " - Chấm công vào lúc " . date('H:i:s', strtotime($gioVao));
            $soTien = 100000; // 100.000 VNĐ
            $ngayApDung = $ngayHienTai;
            $ghiChuKL = "Tự động tạo từ hệ thống chấm công";
            
            // Chèn kỷ luật vào bảng KTKL
            $insertKL = "INSERT INTO KTKL(MaKTKL, MaNV, LoaiSuKien, MoTa, SoTien, NgayApDung, GhiChu) 
                         VALUES('$maKTKL', '$maNhanVien', '$loaiSuKien', '$moTa', '$soTien', '$ngayApDung', '$ghiChuKL')";
            $resultKL = mysqli_query($conn, $insertKL);
            
            if($resultKL) {
              $success['kyluat'] = 'Đã tự động tạo kỷ luật đi muộn với tiền phạt 100.000 VNĐ';
            } else {
              $error['kyluat'] = 'Lỗi khi tạo kỷ luật tự động: ' . mysqli_error($conn);
            }
          }
          
          // Refresh lại thông tin chấm công
          $resultChamCong = mysqli_query($conn, $queryChamCong);
          if(mysqli_num_rows($resultChamCong) > 0) {
            $chamCong = mysqli_fetch_assoc($resultChamCong);
          }
        } else {
          $error['db'] = 'Lỗi khi chấm công: ' . mysqli_error($conn);
        }
      } else {
        $error['dacheckin'] = 'Bạn đã chấm công ra cho ngày hôm nay!';
      }
    } else {
      $error['chuacheckin'] = 'Bạn cần chấm công vào trước khi chấm công ra!';
    }
  }
    // (Chạy hàm này qua cronjob hoặc gọi khi tải trang vào cuối ngày)
    $gioCuoiNgay = "22:30:00";
    $gioHienTai = date('H:i:s');
    
    if(strtotime($gioHienTai) >= strtotime($gioCuoiNgay)) {
        // Cập nhật trạng thái "Vắng mặt" cho những nhân viên chưa chấm công trong ngày
        $queryVangMat = "INSERT IGNORE INTO ChamCong(MaCC, MaNV, Ngay, TrangThai, GhiChu)
                          SELECT CONCAT('CC', UNIX_TIMESTAMP()), nv.MaNV, '$ngayHienTai', 'Vắng mặt', 'Tự động cập nhật'
                          FROM NhanVien nv
                          LEFT JOIN ChamCong cc ON nv.MaNV = cc.MaNV AND cc.Ngay = '$ngayHienTai'
                          WHERE cc.MaCC IS NULL ";
        mysqli_query($conn, $queryVangMat);
    }
    
  } else {
    echo "<script>
      alert('Không tìm thấy thông tin tài khoản!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Chấm công hàng ngày</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Chấm công</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <!-- Thông tin nhân viên và chấm công -->
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Thông tin nhân viên</h3>
          </div>
          <div class="box-body">
            <?php if(isset($nhanVien['MaNV'])): ?>
            <div class="row">
              <div class="col-md-6">
                <strong>Mã nhân viên:</strong> <?php echo $nhanVien['MaNV']; ?><br>
                <strong>Họ tên:</strong> <?php echo $nhanVien['Hoten']; ?><br>
                <strong>Chức vụ:</strong> <?php echo $nhanVien['ChucVu']; ?><br>
              </div>
              <div class="col-md-6">
                <strong>Phòng ban:</strong> <?php echo $nhanVien['TenPhongBan']; ?><br>
                <strong>Ngày hiện tại:</strong> <?php echo date('d/m/Y'); ?><br>
                <strong>Giờ hiện tại:</strong> <span id="current-time"></span>
              </div>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
              <p>Không tìm thấy thông tin nhân viên.</p>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Chấm công ngày <?php echo date('d/m/Y'); ?></h3>
          </div>
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
            
            <div class="table-responsive">
              <table class="table table-bordered">
                <tr>
                  <th>Trạng thái</th>
                  <th>Giờ vào</th>
                  <th>Giờ ra</th>
                  <th>Số giờ làm</th>
                  <th>Tăng ca</th>
                </tr>
                <tr>
                  <td>
                    <?php 
                      if(isset($chamCong['TrangThai'])) {
                        // Hiển thị trạng thái với màu sắc tương ứng
                        $trangThaiClass = '';
                        switch($chamCong['TrangThai']) {
                          case 'Đi muộn':
                          case 'Đi muộn - Đã hoàn thành':
                            $trangThaiClass = 'text-warning';
                            break;
                          case 'Vắng mặt':
                            $trangThaiClass = 'text-danger';
                            break;
                          case 'Đang làm việc':
                            $trangThaiClass = 'text-info';
                            break;
                          case 'Đã hoàn thành':
                            $trangThaiClass = 'text-success';
                            break;
                          default:
                            $trangThaiClass = '';
                        }
                        echo '<span class="'.$trangThaiClass.'">'.$chamCong['TrangThai'].'</span>';
                      } else {
                        echo "Chưa chấm công";
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      if(isset($chamCong['GioVao'])) {
                        // Hiển thị màu đỏ nếu vào sau 9h10
                        $gioVaoClass = strtotime($chamCong['GioVao']) > strtotime('09:10:00') ? 'text-danger' : 'text-success';
                        echo '<span class="'.$gioVaoClass.'">'.date('H:i:s', strtotime($chamCong['GioVao'])).'</span>';
                      } else {
                        echo "--:--:--";
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      if(isset($chamCong['GioRa'])) {
                        echo date('H:i:s', strtotime($chamCong['GioRa']));
                      } else {
                        echo "--:--:--";
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      if(isset($chamCong['SoGioLam'])) {
                        echo number_format($chamCong['SoGioLam'], 2) . " giờ";
                      } else {
                        echo "0.00 giờ";
                      }
                    ?>
                  </td>
                  <td>
                    <?php 
                      if(isset($chamCong['GioTangCa']) && $chamCong['GioTangCa'] > 0) {
                        echo number_format($chamCong['GioTangCa'], 2) . " giờ";
                      } else {
                        echo "0.00 giờ";
                      }
                    ?>
                  </td>
                </tr>
              </table>
            </div>
            
            <div class="row" style="margin-top: 15px;">
              <div class="col-md-12">
                <form method="POST" action="">
                  <div class="form-group">
                    <label for="ghichu">Ghi chú:</label>
                    <textarea class="form-control" id="ghichu" name="ghichu" rows="2" placeholder="Nhập ghi chú (nếu có)"></textarea>
                  </div>
                  
                  <div class="text-center">
                    <button type="submit" name="chamcong_vao" class="btn btn-primary" <?php echo $daCheckinVao ? 'disabled' : ''; ?>>
                      <i class="fa fa-sign-in"></i> Chấm công vào
                    </button>
                    <button type="submit" name="chamcong_ra" class="btn btn-danger" <?php echo $daCheckinRa || !$daCheckinVao ? 'disabled' : ''; ?>>
                      <i class="fa fa-sign-out"></i> Chấm công ra
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Lịch sử chấm công -->
      <div class="col-md-6">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Lịch sử chấm công của bạn</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th>Ngày</th>
                    <th>Giờ vào</th>
                    <th>Giờ ra</th>
                    <th>Số giờ làm</th>
                    <th>Tăng ca</th>
                    <th>Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    if(count($arrLichSu) > 0) {
                      foreach ($arrLichSu as $lichSu) {
                        // Xác định class CSS cho trạng thái
                        $statusClass = '';
                        if(strpos($lichSu['TrangThai'], 'Đi muộn') !== false) {
                          $statusClass = 'text-warning';
                        } elseif($lichSu['TrangThai'] == 'Vắng mặt') {
                          $statusClass = 'text-danger';
                        } elseif($lichSu['TrangThai'] == 'Đã hoàn thành') {
                          $statusClass = 'text-success';
                        } elseif($lichSu['TrangThai'] == 'Đang làm việc') {
                          $statusClass = 'text-info';
                        }
                        
                        // Xác định class CSS cho giờ vào
                        $gioVaoClass = '';
                        if(isset($lichSu['GioVao']) && strtotime($lichSu['GioVao']) > strtotime('09:10:00')) {
                          $gioVaoClass = 'text-danger';
                        }
                        
                        echo "<tr>";
                        echo "<td>" . date('d/m/Y', strtotime($lichSu['Ngay'])) . "</td>";
                        echo "<td class='$gioVaoClass'>" . (isset($lichSu['GioVao']) ? date('H:i:s', strtotime($lichSu['GioVao'])) : "--:--:--") . "</td>";
                        echo "<td>" . (isset($lichSu['GioRa']) ? date('H:i:s', strtotime($lichSu['GioRa'])) : "--:--:--") . "</td>";
                        echo "<td>" . number_format($lichSu['SoGioLam'], 2) . " giờ</td>";
                        echo "<td>" . number_format($lichSu['GioTangCa'], 2) . " giờ</td>";
                        echo "<td class='$statusClass'>" . $lichSu['TrangThai'] . "</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='6' class='text-center'>Không có dữ liệu</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">Hướng dẫn và Quy định chấm công</h3>
          </div>
          <div class="box-body">
            <ol>
              <li>Khi bắt đầu làm việc, nhấn nút <strong>"Chấm công vào"</strong> để ghi nhận thời gian vào làm.</li>
              <li>Khi kết thúc ngày làm việc, nhấn nút <strong>"Chấm công ra"</strong> để ghi nhận thời gian ra về.</li>
              <li>Nếu có ghi chú đặc biệt (đi muộn, về sớm, công tác...), hãy điền vào ô ghi chú.</li>
              <li>Hệ thống tính giờ làm việc như sau:
                <ul>
                  <li>Nếu check-in <strong>trước 9:00</strong>: Giờ làm sẽ tính từ 9:00 đến giờ check-out</li>
                  <li>Nếu check-in <strong>sau 9:10</strong>: Giờ làm sẽ tính từ giờ check-in đến giờ check-out</li>
                </ul>
              </li>
              <li>Tăng ca được ghi nhận nếu làm việc quá 8 giờ tiêu chuẩn <strong>và thời gian tăng ca lớn hơn 45 phút</strong>.</li>
              <li>Kiểm tra lịch sử chấm công của bạn ở bảng "Lịch sử chấm công của bạn".</li>
            </ol>
            <div class="alert alert-warning">
              <strong>Quy định về trạng thái chấm công:</strong>
              <ul>
                <li><span class="text-warning"><strong>Đi muộn:</strong></span> Khi chấm công vào sau 9:10 sáng.</li>
                <li><span class="text-danger"><strong>Vắng mặt:</strong></span> Khi nhân viên không chấm công trong ngày.</li>
                <li><span class="text-info"><strong>Đang làm việc:</strong></span> Khi nhân viên đã chấm công vào nhưng chưa chấm công ra.</li>
                <li><span class="text-success"><strong>Đã hoàn thành:</strong></span> Khi nhân viên đã chấm công vào và ra trong ngày.</li>
              </ul>
            </div>
            <p><strong>Lưu ý:</strong> Bạn chỉ có thể chấm công vào và ra một lần trong ngày.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
  // Hiển thị giờ hiện tại
  function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('current-time').textContent = `${hours}:${minutes}:${seconds}`;
    
    // Thêm cảnh báo nếu đang trong giờ làm việc mà chưa chấm công vào
    <?php if(!$daCheckinVao && date('H') >= 9 && date('H') < 18 && date('N') <= 5): ?>
    const warningElement = document.getElementById('checkin-warning');
    if(!warningElement) {
      const warningDiv = document.createElement('div');
      warningDiv.id = 'checkin-warning';
      warningDiv.className = 'alert alert-warning mt-3';
      warningDiv.innerHTML = '<strong>Lưu ý:</strong> Bạn chưa chấm công vào cho ngày hôm nay!';
      document.querySelector('.box-success .box-body').appendChild(warningDiv);
    }
    <?php endif; ?>
  }
  
  // Cập nhật giờ mỗi giây
  setInterval(updateClock, 1000);
  
  // Khởi tạo giờ khi trang tải xong
  document.addEventListener('DOMContentLoaded', updateClock);
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