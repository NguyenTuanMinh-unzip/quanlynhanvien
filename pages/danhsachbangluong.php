<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Phân quyền và xác định người dùng hiện tại
  $isAdmin = isset($row_acc) && $row_acc['Chucvu'] == 'admin';
  $currentUserID = $row_acc['MaNV'];

  // Xử lý lọc theo phòng ban (chỉ admin mới được lọc)
  $filterPhongBan = $isAdmin && isset($_GET['phongban']) ? $_GET['phongban'] : '';
  
  // Lấy danh sách phòng ban (chỉ admin mới cần)
  $arrPhongBan = array();
  if($isAdmin) {
    $queryPhongBan = "SELECT * FROM PhongBan ORDER BY TenPhongBan ASC";
    $resultPhongBan = mysqli_query($conn, $queryPhongBan);
    while ($rowPhongBan = mysqli_fetch_array($resultPhongBan)) {
      $arrPhongBan[] = $rowPhongBan;
    }
  }
  
  // Xây dựng câu truy vấn với điều kiện lọc và phân quyền
  $query = "SELECT l.*, nv.Hoten, nv.ChucVu, pb.TenPhongBan 
            FROM Luong l
            JOIN NhanVien nv ON l.MaNV = nv.MaNV
            JOIN PhongBan pb ON nv.MaPb = pb.MaPb";
  
  // Nếu không phải admin, chỉ hiển thị dữ liệu của người dùng hiện tại
  if(!$isAdmin) {
    $query .= " WHERE l.MaNV = '$currentUserID'";
  }
  // Thêm điều kiện lọc theo phòng ban nếu có (chỉ khi là admin)
  elseif(!empty($filterPhongBan)) {
    $query .= " WHERE pb.MaPb = '$filterPhongBan'";
  }
  
  // Sắp xếp dữ liệu
  $query .= " ORDER BY nv.Hoten ASC";
  
  $result = mysqli_query($conn, $query);
  $arrLuong = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrLuong[] = $row;
  }
  
  // Đếm số nhân viên chưa có bảng lương (chỉ admin mới quan tâm)
  $countNoSalary = 0;
  if($isAdmin) {
    $queryCountNoSalary = "SELECT COUNT(*) as total 
                           FROM NhanVien nv 
                           WHERE nv.MaNV NOT IN (
                             SELECT MaNV FROM Luong
                           )";
    $resultCountNoSalary = mysqli_query($conn, $queryCountNoSalary);
    $rowCountNoSalary = mysqli_fetch_assoc($resultCountNoSalary);
    $countNoSalary = $rowCountNoSalary['total'];
  }

  // Xử lý xóa bảng lương nếu có
  if($isAdmin && isset($_GET['delete']) && !empty($_GET['delete'])) {
    $maLuong = $_GET['delete'];
    $queryDelete = "DELETE FROM Luong WHERE MaLuong = '$maLuong'";
    $resultDelete = mysqli_query($conn, $queryDelete);
    
    if($resultDelete) {
      echo "<script>
        alert('Xóa bảng lương thành công!');
        window.location.href='danhsachbangluong.php?p=salary&a=list";
      if(!empty($filterPhongBan)) {
        echo "&phongban=$filterPhongBan";
      }
      echo "';
      </script>";
      exit;
    } else {
      echo "<script>
        alert('Lỗi khi xóa bảng lương: " . mysqli_error($conn) . "');
      </script>";
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?php echo $isAdmin ? "Danh sách bảng lương" : "Bảng lương của tôi"; ?>
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active"><?php echo $isAdmin ? "Danh sách bảng lương" : "Bảng lương của tôi"; ?></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title"><?php echo $isAdmin ? "Danh sách bảng lương nhân viên" : "Thông tin bảng lương của tôi"; ?></h3>
            <?php if($isAdmin): ?>
            <div class="box-tools pull-right">
              <a href="thembangluong.php?p=salary&a=add" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Thêm bảng lương mới
              </a>
              <?php if($countNoSalary > 0): ?>
              <span class="badge bg-yellow"><?php echo $countNoSalary; ?> nhân viên chưa có bảng lương</span>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php if($isAdmin): ?>
            <!-- Form lọc chỉ hiển thị cho admin -->
            <div class="row">
              <div class="col-md-12">
                <form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
                  <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
                  <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
                  
                  <div class="form-group">
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
                  
                  <?php if(!empty($filterPhongBan)): ?>
                    <a href="?p=<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>&a=<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>" class="btn btn-default" style="margin-left: 10px;">
                      <i class="fa fa-refresh"></i> Đặt lại
                    </a>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            <?php endif; ?>
            
            <!-- Bảng hiển thị dữ liệu -->
            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead>
                  <tr>
                    <th class="text-center">STT</th>
                    <?php if($isAdmin): ?>
                    <th>Họ tên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <?php endif; ?>
                    <th class="text-right">Lương cơ bản</th>
                    <th class="text-right">Phụ cấp ăn trưa</th>
                    <th class="text-right">Phụ cấp đi lại</th>
                    <th class="text-right">Thưởng</th>
                    <th class="text-right">Phạt</th>
                    <th class="text-right">BHXH</th>
                    <th class="text-right">BHYT</th>
                    <th class="text-right">Thuế TNCN</th>
                    <th class="text-center">Ngày cập nhật</th>
                    <th>Ghi chú</th>
                    <?php if($isAdmin): ?>
                    <th class="text-center">Thao tác</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    if(count($arrLuong) > 0) {
                      $count = 1;
                      foreach ($arrLuong as $luong) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        
                        // Các cột thông tin nhân viên chỉ hiển thị cho admin
                        if($isAdmin) {
                          echo "<td>".$luong['Hoten']."</td>";
                          echo "<td>".$luong['ChucVu']."</td>";
                          echo "<td>".$luong['TenPhongBan']."</td>";
                        }
                        
                        // Hiển thị thông tin lương
                        echo "<td class='text-right'>".number_format($luong['LuongCoBan'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['PhuCapAnTrua'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['PhuCapDiLai'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['Thuong'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['Phat'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['BHXH'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['BHYT'])."</td>";
                        echo "<td class='text-right'>".number_format($luong['ThueTNCN'])."</td>";
                        echo "<td class='text-center'>".date('d/m/Y', strtotime($luong['NgayCapNhat']))."</td>";
                        echo "<td>".($luong['GhiChu'] ? $luong['GhiChu'] : "-")."</td>";
                        
                        // Cột thao tác chỉ hiển thị cho admin
                        if($isAdmin) {
                          echo "<td class='text-center'>";
                          echo "<a href='suabangluong.php?p=salary&a=edit&id=".$luong['MaLuong']."' class='btn btn-warning btn-sm' title='Sửa'>";
                          echo "<i class='fa fa-edit'></i>";
                          echo "</a> &nbsp;";
                          echo "<a href='?p=salary&a=list&delete=".$luong['MaLuong'].($filterPhongBan ? "&phongban=".$filterPhongBan : "")."' class='btn btn-danger btn-sm' title='Xóa' onclick=\"return confirm('Bạn có chắc chắn muốn xóa bảng lương này?');\">";
                          echo "<i class='fa fa-trash'></i>";
                          echo "</a>";
                          echo "</td>";
                        }
                        
                        echo "</tr>";
                      }
                    } else {
                      $colspan = $isAdmin ? 15 : 11; // Điều chỉnh số cột tùy theo loại người dùng
                      echo "<tr><td colspan='$colspan' class='text-center'>Không có dữ liệu bảng lương</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
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