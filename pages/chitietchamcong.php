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

  // Kiểm tra có ID nhân viên và ngày được truyền vào không
  if(isset($_GET['id']) && isset($_GET['date'])) {
    $maNhanVien = $_GET['id'];
    $ngay = $_GET['date'];
    
    // Lấy thông tin nhân viên
    $queryNhanVien = "SELECT nv.*, pb.TenPhongBan FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                      WHERE nv.MaNV = '$maNhanVien'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    
    // Kiểm tra nhân viên có tồn tại không
    if(!$nhanVien) {
      echo "<script>
        alert('Không tìm thấy thông tin nhân viên!');
        window.location.href='dulieuchamcong.php?p=attendance&a=data';
      </script>";
      exit;
    }
    
    // Lấy thông tin chấm công của ngày được chọn
    $queryChamCong = "SELECT * FROM ChamCong WHERE MaNV = '$maNhanVien' AND Ngay = '$ngay'";
    $resultChamCong = mysqli_query($conn, $queryChamCong);
    $chamCong = mysqli_fetch_assoc($resultChamCong);
    
    // Kiểm tra dữ liệu chấm công có tồn tại không
    if(!$chamCong) {
      // Nếu không tồn tại, tạo mới một bản ghi trống
      $maCC = "CC" . time();
      $insertNew = "INSERT INTO ChamCong(MaCC, MaNV, Ngay, TrangThai, GhiChu) 
                    VALUES('$maCC', '$maNhanVien', '$ngay', 'Chưa chấm công', 'Tạo bởi Admin')";
      $resultInsert = mysqli_query($conn, $insertNew);
      
      if($resultInsert) {
        // Lấy lại thông tin chấm công vừa tạo
        $resultChamCong = mysqli_query($conn, $queryChamCong);
        $chamCong = mysqli_fetch_assoc($resultChamCong);
      } else {
        echo "<script>
          alert('Không thể tạo dữ liệu chấm công mới: " . mysqli_error($conn) . "');
          window.location.href='dulieuchamcong.php?p=attendance&a=data';
        </script>";
        exit;
      }
    }
    
    // Lấy lịch sử chấm công 10 ngày gần nhất của nhân viên (không bao gồm ngày hiện tại)
    $queryLichSu = "SELECT * FROM ChamCong 
                    WHERE MaNV = '$maNhanVien' AND Ngay != '$ngay' 
                    ORDER BY Ngay DESC LIMIT 10";
    $resultLichSu = mysqli_query($conn, $queryLichSu);
    $arrLichSu = array();
    while ($row = mysqli_fetch_array($resultLichSu)) {
      $arrLichSu[] = $row;
    }
    
    // Xử lý cập nhật dữ liệu chấm công nếu có
    if(isset($_POST['update'])) {
      $error = array();
      $success = array();
      $showMess = false;
      
      // Lấy dữ liệu từ form
      $gioVao = !empty($_POST['gioVao']) ? $_POST['gioVao'] . ':00' : null;
      $gioRa = !empty($_POST['gioRa']) ? $_POST['gioRa'] . ':00' : null;
      $trangThai = $_POST['trangThai'];
      $ghiChu = $_POST['ghiChu'];
      
      // Mặc định số giờ làm và giờ tăng ca
      $soGioLam = 0;
      $gioTangCa = 0;
      
      // Validate
      if(empty($gioVao) && $trangThai != 'Vắng mặt' && $trangThai != 'Chưa chấm công') {
        $error['gioVao'] = 'Vui lòng nhập giờ vào';
      }
      
      // Nếu có đủ giờ vào và giờ ra, tính toán số giờ làm và giờ tăng ca
      if(!empty($gioVao) && !empty($gioRa)) {
        $gioVaoObj = new DateTime($gioVao);
        $gioRaObj = new DateTime($gioRa);
        
        if($gioRaObj <= $gioVaoObj) {
          $error['gioRa'] = 'Giờ ra phải sau giờ vào';
        } else {
          // Áp dụng quy tắc tính giờ làm việc:
          // 1. Nếu vào trước 9:00, tính từ 9:00 đến giờ ra
          // 2. Nếu vào sau 9:10, tính từ giờ vào đến giờ ra
          $gioTinhLam = $gioVao;
          $gioMuon = "09:10:00";
          $diMuon = false;
          
          // Xác định trạng thái và giờ tính
          if(strtotime($gioVao) <= strtotime('09:00:00')) {
            // Đúng giờ: tính từ 9:00
            $gioTinhLam = '09:00:00';
            if($trangThai == 'Đi muộn' || $trangThai == 'Đi muộn - Đã hoàn thành') {
              $trangThai = 'Đã hoàn thành';
            }
          } else if(strtotime($gioVao) > strtotime($gioMuon)) {
            // Đi muộn: tính từ giờ vào thực tế và cập nhật trạng thái
            $diMuon = true;
            if($trangThai != 'Đi muộn' && $trangThai != 'Đi muộn - Đã hoàn thành') {
              $trangThai = 'Đi muộn';
            }
          }
          
          // Nếu đã chấm công ra, cập nhật trạng thái
          if(!empty($gioRa)) {
            if($diMuon) {
              $trangThai = 'Đi muộn - Đã hoàn thành';
            } else {
              $trangThai = 'Đã hoàn thành';
            }
          }
          
          // Tính số giờ làm
          $gioTinhObj = new DateTime($gioTinhLam);
          $interval = $gioTinhObj->diff($gioRaObj);
          $soGioLam = $interval->h + ($interval->i / 60) + ($interval->s / 3600);
          
          // Làm tròn đến 2 chữ số thập phân
          $soGioLam = round($soGioLam, 2);
          
          // Xử lý tăng ca - chỉ ghi nhận nếu tăng ca lớn hơn 45 phút (0.75 giờ)
          $soGioLamTieuChuan = 8; // Giờ làm việc tiêu chuẩn
          
          if($soGioLam > $soGioLamTieuChuan) {
            $gioTangCa = $soGioLam - $soGioLamTieuChuan;
            
            // Chỉ ghi nhận tăng ca nếu lớn hơn 45 phút (0.75 giờ)
            if($gioTangCa < 0.75) {
              $gioTangCa = 0;
            } else {
              // Làm tròn đến 2 chữ số thập phân
              $gioTangCa = round($gioTangCa, 2);
            }
            
            // Giới hạn giờ làm tiêu chuẩn
            $soGioLam = $soGioLamTieuChuan;
          }
        }
      } else if($trangThai == 'Vắng mặt') {
        // Nếu vắng mặt, đặt giờ vào và giờ ra thành NULL
        $gioVao = null;
        $gioRa = null;
        $soGioLam = 0;
        $gioTangCa = 0;
      }
      
      if(empty($error)) {
        // Cập nhật dữ liệu chấm công
        $update = "UPDATE ChamCong SET 
                   GioVao = " . ($gioVao ? "'$gioVao'" : "NULL") . ", 
                   GioRa = " . ($gioRa ? "'$gioRa'" : "NULL") . ", 
                   SoGioLam = '$soGioLam', 
                   GioTangCa = '$gioTangCa', 
                   TrangThai = '$trangThai', 
                   GhiChu = '$ghiChu'
                   WHERE MaNV = '$maNhanVien' AND Ngay = '$ngay'";
        $result = mysqli_query($conn, $update);
        
        if($result) {
          $showMess = true;
          $success['success'] = 'Cập nhật dữ liệu chấm công thành công!';
          
          // Refresh lại thông tin chấm công
          $resultChamCong = mysqli_query($conn, $queryChamCong);
          $chamCong = mysqli_fetch_assoc($resultChamCong);
        } else {
          $error['db'] = 'Lỗi khi cập nhật: ' . mysqli_error($conn);
        }
      }
    }
  } else {
    echo "<script>
      alert('Thiếu thông tin nhân viên hoặc ngày cần xem!');
      window.location.href='dulieuchamcong.php?p=attendance&a=data';
    </script>";
    exit;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Chi tiết chấm công</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="dulieuchamcong.php?p=attendance&a=data">Dữ liệu chấm công</a></li>
      <li class="active">Chi tiết chấm công</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <!-- Thông tin nhân viên -->
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Thông tin nhân viên</h3>
          </div>
          <div class="box-body box-profile">
            <h3 class="profile-username text-center"><?php echo $nhanVien['Hoten']; ?></h3>
            <p class="text-muted text-center"><?php echo $nhanVien['ChucVu']; ?></p>
            
            <ul class="list-group list-group-unbordered">
              <li class="list-group-item">
                <b>Mã nhân viên</b> <a class="pull-right"><?php echo $nhanVien['MaNV']; ?></a>
              </li>
              <li class="list-group-item">
                <b>Phòng ban</b> <a class="pull-right"><?php echo $nhanVien['TenPhongBan']; ?></a>
              </li>
              <li class="list-group-item">
                <b>Số điện thoại</b> <a class="pull-right"><?php echo $nhanVien['SDT']; ?></a>
              </li>
              <li class="list-group-item">
                <b>Ngày vào làm</b> <a class="pull-right"><?php echo date('d/m/Y', strtotime($nhanVien['NgayVaoLam'])); ?></a>
              </li>
            </ul>
            
            <a href="dulieuchamcong.php?p=attendance&a=data" class="btn btn-default btn-block">
              <i class="fa fa-arrow-left"></i> Quay lại danh sách
            </a>
          </div>
        </div>

        <!-- Thông tin quy định chấm công -->
        <div class="box box-danger">
          <div class="box-header with-border">
            <h3 class="box-title">Quy định chấm công</h3>
          </div>
          <div class="box-body">
            <div class="callout callout-info">
              <h4>Cách tính giờ làm việc:</h4>
              <ul>
                <li>Nếu check-in <strong>trước 9:00</strong>: Giờ làm sẽ tính từ 9:00 đến giờ check-out</li>
                <li>Nếu check-in <strong>sau 9:10</strong>: Giờ làm sẽ tính từ giờ check-in đến giờ check-out</li>
                <li>Tăng ca được ghi nhận nếu làm việc quá 8 giờ tiêu chuẩn <strong>và thời gian tăng ca lớn hơn 45 phút</strong></li>
              </ul>
            </div>
            <div class="callout callout-warning">
              <h4>Trạng thái chấm công:</h4>
              <ul>
                <li><strong>Đi muộn:</strong> Khi chấm công vào sau 9:10 sáng</li>
                <li><strong>Vắng mặt:</strong> Khi nhân viên không chấm công trong ngày</li>
                <li><strong>Đang làm việc:</strong> Khi nhân viên đã chấm công vào nhưng chưa chấm công ra</li>
                <li><strong>Đã hoàn thành:</strong> Khi nhân viên đã chấm công vào và ra trong ngày</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Chi tiết chấm công -->
      <div class="col-md-8">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Chi tiết chấm công ngày <?php echo date('d/m/Y', strtotime($ngay)); ?></h3>
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
                    <label>Giờ vào:</label>
                    <input type="time" name="gioVao" class="form-control" 
                           value="<?php echo isset($chamCong['GioVao']) ? date('H:i', strtotime($chamCong['GioVao'])) : ''; ?>">
                    <small class="text-muted">Nếu nhân viên vào trước 9:00, giờ làm sẽ tính từ 9:00</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Giờ ra:</label>
                    <input type="time" name="gioRa" class="form-control" 
                           value="<?php echo isset($chamCong['GioRa']) ? date('H:i', strtotime($chamCong['GioRa'])) : ''; ?>">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Số giờ làm:</label>
                    <input type="text" class="form-control" disabled 
                           value="<?php echo number_format($chamCong['SoGioLam'], 2); ?> giờ">
                    <small class="text-muted">Số giờ làm sẽ được tự động tính dựa trên quy định</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Giờ tăng ca:</label>
                    <input type="text" class="form-control" disabled 
                           value="<?php echo number_format($chamCong['GioTangCa'], 2); ?> giờ">
                    <small class="text-muted">Tăng ca được ghi nhận nếu > 45 phút</small>
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label>Trạng thái:</label>
                <select name="trangThai" class="form-control">
                  <option value="Chưa chấm công" <?php echo ($chamCong['TrangThai'] == 'Chưa chấm công') ? 'selected' : ''; ?>>Chưa chấm công</option>
                  <option value="Đang làm việc" <?php echo ($chamCong['TrangThai'] == 'Đang làm việc') ? 'selected' : ''; ?>>Đang làm việc</option>
                  <option value="Đã hoàn thành" <?php echo ($chamCong['TrangThai'] == 'Đã hoàn thành') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                  <option value="Đi muộn" <?php echo ($chamCong['TrangThai'] == 'Đi muộn') ? 'selected' : ''; ?>>Đi muộn</option>
                  <option value="Đi muộn - Đã hoàn thành" <?php echo ($chamCong['TrangThai'] == 'Đi muộn - Đã hoàn thành') ? 'selected' : ''; ?>>Đi muộn - Đã hoàn thành</option>
                  <option value="Vắng mặt" <?php echo ($chamCong['TrangThai'] == 'Vắng mặt') ? 'selected' : ''; ?>>Vắng mặt</option>
                </select>
                <small class="text-muted">Trạng thái sẽ tự động cập nhật dựa trên giờ vào và ra, nhưng bạn có thể điều chỉnh thủ công</small>
              </div>
              
              <div class="form-group">
                <label>Ghi chú:</label>
                <textarea name="ghiChu" class="form-control" rows="3"><?php echo $chamCong['GhiChu']; ?></textarea>
              </div>
              
              <div class="alert alert-warning">
                <p><i class="fa fa-info-circle"></i> <strong>Lưu ý:</strong> Khi bạn cập nhật giờ vào và giờ ra, hệ thống sẽ tự động tính lại số giờ làm và giờ tăng ca theo quy định.</p>
              </div>
              
              <button type="submit" name="update" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> Cập nhật
              </button>
            </form>
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
        
        <!-- Lịch sử chấm công -->
        <div class="box box-success">
          <div class="box-header with-border">
            <h3 class="box-title">Lịch sử chấm công</h3>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead>
                  <tr>
                    <th>Ngày</th>
                    <th>Giờ vào</th>
                    <th>Giờ ra</th>
                    <th>Số giờ làm</th>
                    <th>Tăng ca</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
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
                        echo "<td><a href='chitietchamcong.php?p=attendance&a=detail&id=" . $maNhanVien . "&date=" . $lichSu['Ngay'] . "' class='btn btn-xs btn-info'><i class='fa fa-eye'></i> Xem</a></td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='7' class='text-center'>Không có dữ liệu lịch sử</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- JavaScript để tự động tính số giờ làm và tăng ca -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Đặt sự kiện cho form submit để xác nhận
  document.querySelector('form').addEventListener('submit', function(e) {
    if (confirm('Bạn có chắc chắn muốn cập nhật dữ liệu chấm công này?')) {
      return true;
    } else {
      e.preventDefault();
      return false;
    }
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