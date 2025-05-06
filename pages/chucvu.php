<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng - chỉ admin mới có full quyền
  if(isset($row_acc) && $row_acc['Chucvu'] == 'admin') {
    $hasFullPermission = true;
  } else {
    $hasFullPermission = false;
  }

  // Phân loại các nhóm chức vụ
  $leaderPositions = array('Giám đốc', 'Trưởng phòng', 'Trưởng ban', 'Quản lý');
  $deputyPositions = array('Phó Giám đốc', 'Phó phòng', 'Phó Trưởng phòng', 'Phó Trưởng ban', 'Phó Quản lý');
  $otherSpecialPositions = array('Thực tập sinh', 'Trợ lý', 'Cố vấn');

  // Lấy danh sách tất cả các chức vụ để làm bộ lọc
  $queryChucVu = "SELECT DISTINCT ChucVu FROM NhanVien ORDER BY ChucVu ASC";
  $resultChucVu = mysqli_query($conn, $queryChucVu);
  $arrChucVu = array();
  while ($rowChucVu = mysqli_fetch_array($resultChucVu)) {
    $arrChucVu[] = $rowChucVu['ChucVu'];
  }

  // Xử lý lọc 
  $filterChucVu = isset($_GET['chucvu']) ? $_GET['chucvu'] : '';
  
  // Xây dựng câu truy vấn với điều kiện lọc
  $query = "SELECT nv.MaNV, nv.Hoten, nv.ChucVu FROM NhanVien nv";
  
  // Thêm điều kiện lọc nếu có
  if(!empty($filterChucVu)) {
    $query .= " WHERE nv.ChucVu = '$filterChucVu'";
  }
  
  // Thêm điều kiện sắp xếp mặc định
  $query .= " ORDER BY nv.ChucVu ASC, nv.Hoten ASC";
  
  $result = mysqli_query($conn, $query);
  $arrNhanVien = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrNhanVien[] = $row;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Danh sách nhân viên theo chức vụ</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Danh sách chức vụ</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Danh sách nhân viên và chức vụ</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php 
              // Hiển thị thông báo nếu không phải admin
              if(!$hasFullPermission) {
                echo "<div class='alert alert-info alert-dismissible'>";
                echo "<h4><i class='icon fa fa-info'></i> Thông báo!</h4>";
                echo "Bạn chỉ có quyền xem danh sách nhân viên theo chức vụ.";
                echo "</div>";
              }
            ?>

            <!-- Form lọc -->
            <div class="row">
              <div class="col-md-12">
                <form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
                  <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
                  <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
                  
                  <div class="form-group">
                    <label for="chucvu">Lọc theo chức vụ:</label>
                    <select name="chucvu" id="chucvu" class="form-control">
                      <option value="">-- Tất cả chức vụ --</option>
                      <?php foreach($arrChucVu as $chucVu): ?>
                        <option value="<?php echo $chucVu; ?>" <?php echo ($filterChucVu == $chucVu) ? 'selected' : ''; ?>>
                          <?php echo $chucVu; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                    <i class="fa fa-filter"></i> Lọc
                  </button>
                  
                  <?php if(!empty($filterChucVu)): ?>
                    <a href="?p=<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>&a=<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>" class="btn btn-default" style="margin-left: 10px;">
                      <i class="fa fa-refresh"></i> Đặt lại
                    </a>
                  <?php endif; ?>
                </form>
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
                    <th>Chức vụ</th>
                    <th class="text-center">Chi tiết</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $count = 1;
                    foreach ($arrNhanVien as $nhanVien) {
                      // Xác định loại chức vụ
                      $positionType = '';
                      $rowStyle = '';
                      
                      // Kiểm tra chức vụ lãnh đạo
                      foreach ($leaderPositions as $position) {
                        if (strpos($nhanVien['ChucVu'], $position) !== false) {
                          $positionType = 'leader';
                          break;
                        }
                      }
                      
                      // Kiểm tra chức vụ phó
                      if (empty($positionType)) {
                        foreach ($deputyPositions as $position) {
                          if (strpos($nhanVien['ChucVu'], $position) !== false || $nhanVien['ChucVu'] == 'Phó phòng') {
                            $positionType = 'deputy';
                            break;
                          }
                        }
                      }
                      
                      // Kiểm tra các chức vụ đặc biệt khác
                      if (empty($positionType)) {
                        foreach ($otherSpecialPositions as $position) {
                          if (strpos($nhanVien['ChucVu'], $position) !== false) {
                            $positionType = 'special';
                            break;
                          }
                        }
                      }
                      
                      // Thiết lập style dựa vào loại chức vụ
                      switch ($positionType) {
                        case 'leader':
                          $rowStyle = 'background-color: #d9edf7; font-weight: bold;'; // Màu xanh nhạt cho lãnh đạo
                          break;
                        case 'deputy':
                          $rowStyle = 'background-color: #fcf8e3; font-style: italic;'; // Màu vàng nhạt cho phó
                          break;
                        case 'special':
                          $rowStyle = 'background-color: #f2dede;'; // Màu đỏ nhạt cho vị trí đặc biệt
                          break;
                        default:
                          $rowStyle = '';
                      }
                      
                      echo "<tr style='".$rowStyle."'>";
                      echo "<td class='text-center'>".$count++."</td>";
                      echo "<td class='text-center'>".$nhanVien['MaNV']."</td>";
                      echo "<td>".$nhanVien['Hoten']."</td>";
                      
                      // Hiển thị chức vụ với định dạng dựa trên loại
                      if ($positionType == 'leader') {
                        echo "<td><strong>".$nhanVien['ChucVu']."</strong></td>";
                      } elseif ($positionType == 'deputy') {
                        echo "<td><em>".$nhanVien['ChucVu']."</em></td>";
                      } else {
                        echo "<td>".$nhanVien['ChucVu']."</td>";
                      }
                      
                      echo "<td class='text-center'>";
                      
                      if($hasFullPermission) {
                        // Nếu là admin, nút có thể click được
                        echo "<a href='chitietnhanvien.php?p=staff&a=detail-staff&id=".$nhanVien['MaNV']."' class='btn btn-info btn-sm'>";
                        echo "<i class='fa fa-info-circle'></i> Chi tiết";
                        echo "</a>";
                      } else {
                        // Nếu không phải admin, nút bị disable
                        echo "<button class='btn btn-info btn-sm' disabled>";
                        echo "<i class='fa fa-info-circle'></i> Chi tiết";
                        echo "</button>";
                      }
                      
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
                <?php if(!empty($filterChucVu)): ?>
                  Không tìm thấy nhân viên nào với chức vụ "<?php echo $filterChucVu; ?>".
                <?php else: ?>
                  Hiện không có nhân viên nào trong hệ thống.
                <?php endif; ?>
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