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

  // Lấy ngày hiện tại
  $ngayHienTai = date('Y-m-d');
  
  // Xử lý lọc theo ngày và phòng ban
  $filterDate = isset($_GET['date']) ? $_GET['date'] : $ngayHienTai;
  $filterPhongBan = isset($_GET['phongban']) ? $_GET['phongban'] : '';
  
  // Lấy danh sách phòng ban
  $queryPhongBan = "SELECT * FROM PhongBan ORDER BY TenPhongBan ASC";
  $resultPhongBan = mysqli_query($conn, $queryPhongBan);
  $arrPhongBan = array();
  while ($rowPhongBan = mysqli_fetch_array($resultPhongBan)) {
    $arrPhongBan[] = $rowPhongBan;
  }
  
  // Xây dựng câu truy vấn với điều kiện lọc
  $query = "SELECT cc.MaCC, cc.MaNV, nv.Hoten, pb.TenPhongBan, cc.Ngay, cc.GioVao, cc.GioRa, cc.TrangThai 
            FROM ChamCong cc
            JOIN NhanVien nv ON cc.MaNV = nv.MaNV
            JOIN PhongBan pb ON nv.MaPb = pb.MaPb
            WHERE cc.Ngay = '$filterDate'";
  
  // Thêm điều kiện lọc theo phòng ban nếu có
  if(!empty($filterPhongBan)) {
    $query .= " AND pb.MaPb = '$filterPhongBan'";
  }
  
  // Sắp xếp dữ liệu
  $query .= " ORDER BY nv.Hoten ASC";
  
  $result = mysqli_query($conn, $query);
  $arrChamCong = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrChamCong[] = $row;
  }
  
  // Thống kê nhanh
  $countTotal = count($arrChamCong);
  $countDangLamViec = 0;
  $countDaHoanThanh = 0;
  $countVang = 0;
  
  if($countTotal > 0) {
    foreach($arrChamCong as $cc) {
      if($cc['TrangThai'] == 'Đang làm việc') {
        $countDangLamViec++;
      } else if($cc['TrangThai'] == 'Đã hoàn thành') {
        $countDaHoanThanh++;
      }
    }
  }
  
  // Lấy tổng số nhân viên để tính số nhân viên vắng
  $queryTongNV = "SELECT COUNT(*) as total FROM NhanVien";
  if(!empty($filterPhongBan)) {
    $queryTongNV .= " WHERE MaPb = '$filterPhongBan'";
  }
  $resultTongNV = mysqli_query($conn, $queryTongNV);
  $rowTongNV = mysqli_fetch_assoc($resultTongNV);
  $tongNhanVien = $rowTongNV['total'];
  
  $countVang = $tongNhanVien - $countTotal;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Dữ liệu chấm công</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Dữ liệu chấm công</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Dữ liệu chấm công nhân viên</h3>
            <div class="box-tools pull-right">
              <a href="themchamcong.php?p=attendance&a=add" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Thêm chấm công mới
              </a>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <!-- Form lọc -->
            <div class="row">
              <div class="col-md-12">
                <form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
                  <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
                  <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
                  
                  <div class="form-group">
                    <label for="date">Ngày:</label>
                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $filterDate; ?>">
                  </div>
                  
                  <div class="form-group" style="margin-left: 15px;">
                    <label for="phongban">Phòng ban:</label>
                    <select name="phongban" id="phongban" class="form-control">
                      <option value="">-- Tất cả phòng ban --</option>
                      <?php foreach($arrPhongBan as $pb): ?>
                        <option value="<?php echo $pb['MaPb']; ?>" <?php echo ($filterPhongBan == $pb['MaPb']) ? 'selected' : ''; ?>>
                          <?php echo $pb['TenPhongBan']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                    <i class="fa fa-filter"></i> Lọc
                  </button>
                  
                  <?php if($filterDate != $ngayHienTai || !empty($filterPhongBan)): ?>
                    <a href="?p=<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>&a=<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>" class="btn btn-default" style="margin-left: 10px;">
                      <i class="fa fa-refresh"></i> Đặt lại
                    </a>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            
            <!-- Thống kê nhanh -->
            <div class="row">
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua">
                  <span class="info-box-icon"><i class="fa fa-users"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số nhân viên</span>
                    <span class="info-box-number"><?php echo $tongNhanVien; ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green">
                  <span class="info-box-icon"><i class="fa fa-check-square-o"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Đã hoàn thành</span>
                    <span class="info-box-number"><?php echo $countDaHoanThanh; ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow">
                  <span class="info-box-icon"><i class="fa fa-spinner"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Đang làm việc</span>
                    <span class="info-box-number"><?php echo $countDangLamViec; ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red">
                  <span class="info-box-icon"><i class="fa fa-user-times"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Vắng mặt</span>
                    <span class="info-box-number"><?php echo $countVang; ?></span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Bảng hiển thị dữ liệu -->
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th class="text-center">STT</th>
                    <th class="text-center">Mã NV</th>
                    <th>Họ tên</th>
                    <th>Phòng ban</th>
                    <th class="text-center">Trạng thái</th>
                    <th class="text-center">Chi tiết</th>
                    <th class="text-center">Sửa</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    if(count($arrChamCong) > 0) {
                      $count = 1;
                      foreach ($arrChamCong as $chamCong) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td class='text-center'>".$chamCong['MaNV']."</td>";
                        echo "<td>".$chamCong['Hoten']."</td>";
                        echo "<td>".$chamCong['TenPhongBan']."</td>";
                        
                        // Hiển thị trạng thái với màu sắc
                        $statusClass = '';
                        switch($chamCong['TrangThai']) {
                          case 'Đang làm việc':
                            $statusClass = 'label-warning';
                            break;
                          case 'Đã hoàn thành':
                            $statusClass = 'label-success';
                            break;
                          default:
                            $statusClass = 'label-default';
                        }
                        
                        echo "<td class='text-center'><span class='label ".$statusClass."'>".$chamCong['TrangThai']."</span></td>";
                        
                        // Nút xem chi tiết
                        echo "<td class='text-center'>";
                        echo "<a href='chitietchamcong.php?p=attendance&a=detail&id=".$chamCong['MaNV']."&date=".$chamCong['Ngay']."' class='btn btn-info btn-sm'>";
                        echo "<i class='fa fa-info-circle'></i> Chi tiết";
                        echo "</a>";
                        echo "</td>";
                        
                        // Nút sửa chấm công
                        echo "<td class='text-center'>";
                        echo "<a href='themchamcong.php?p=attendance&a=edit&id=".$chamCong['MaNV']."&date=".$chamCong['Ngay']."' class='btn btn-warning btn-sm'>";
                        echo "<i class='fa fa-edit'></i> Sửa";
                        echo "</a>";
                        echo "</td>";
                        
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='7' class='text-center'>Không có dữ liệu chấm công cho ngày ".date('d/m/Y', strtotime($filterDate))."</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
            
            <!-- Danh sách nhân viên vắng mặt -->
            <?php if($countVang > 0): ?>
              <div class="box box-danger" style="margin-top: 20px;">
                <div class="box-header with-border">
                  <h3 class="box-title">Danh sách nhân viên vắng mặt ngày <?php echo date('d/m/Y', strtotime($filterDate)); ?></h3>
                </div>
                <div class="box-body">
                  <?php
                    // Lấy danh sách nhân viên vắng
                    $queryVang = "SELECT nv.MaNV, nv.Hoten, pb.TenPhongBan 
                                FROM NhanVien nv 
                                JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                                WHERE nv.MaNV NOT IN (
                                  SELECT MaNV FROM ChamCong WHERE Ngay = '$filterDate'
                                )";
                    if(!empty($filterPhongBan)) {
                      $queryVang .= " AND nv.MaPb = '$filterPhongBan'";
                    }
                    $queryVang .= " ORDER BY nv.Hoten ASC";
                    
                    $resultVang = mysqli_query($conn, $queryVang);
                    $arrVang = array();
                    while ($row = mysqli_fetch_array($resultVang)) {
                      $arrVang[] = $row;
                    }
                    
                    if(count($arrVang) > 0) {
                      echo "<div class='table-responsive'>";
                      echo "<table class='table table-bordered'>";
                      echo "<thead>";
                      echo "<tr>";
                      echo "<th class='text-center'>STT</th>";
                      echo "<th class='text-center'>Mã NV</th>";
                      echo "<th>Họ tên</th>";
                      echo "<th>Phòng ban</th>";
                      echo "<th class='text-center'>Thao tác</th>";
                      echo "</tr>";
                      echo "</thead>";
                      echo "<tbody>";
                      
                      $count = 1;
                      foreach($arrVang as $vang) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td class='text-center'>".$vang['MaNV']."</td>";
                        echo "<td>".$vang['Hoten']."</td>";
                        echo "<td>".$vang['TenPhongBan']."</td>";
                        echo "<td class='text-center'>";
                        echo "<a href='themchamcong.php?p=attendance&a=add&id=".$vang['MaNV']."&date=".$filterDate."' class='btn btn-primary btn-sm'>";
                        echo "<i class='fa fa-plus'></i> Thêm chấm công";
                        echo "</a>";
                        echo "</td>";
                        echo "</tr>";
                      }
                      
                      echo "</tbody>";
                      echo "</table>";
                      echo "</div>";
                    } else {
                      echo "<p class='text-center'>Không có nhân viên vắng mặt.</p>";
                    }
                  ?>
                </div>
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