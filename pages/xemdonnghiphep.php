<?php 
// create session
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if(isset($_SESSION['username']))
{
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
  include('../config.php');
  include('../functions.php');

  // Lấy thông tin người dùng - cải tiến để tránh lỗi trên line 21
  $username = $_SESSION['username'];
  
  // Khởi tạo biến trước khi sử dụng
  $maNV = '';
  $chucVu = '';
  $maPb = '';
  
  // Lấy thông tin nhân viên từ database dựa trên username
  $query_user = "SELECT nv.MaNV, nv.Chucvu, nv.MaPb, nv.Hoten 
                FROM nhanvien nv 
                INNER JOIN taikhoan tk ON nv.MaNV = tk.MaNV 
                WHERE tk.Taikhoan = ?";
                
  // Sử dụng prepared statement
  $stmt_user = mysqli_prepare($conn, $query_user);
  mysqli_stmt_bind_param($stmt_user, "s", $username);
  mysqli_stmt_execute($stmt_user);
  $result_user = mysqli_stmt_get_result($stmt_user);
  
  if(mysqli_num_rows($result_user) > 0) {
    $user_data = mysqli_fetch_assoc($result_user);
    $maNV = $user_data['MaNV'];
    $chucVu = $user_data['Chucvu'];
    $maPb = $user_data['MaPb'];
    $hoTen = $user_data['Hoten'];
    
    // Lưu vào session để sử dụng sau này
    $_SESSION['manv'] = $maNV;
    $_SESSION['chucvu'] = $chucVu;
    $_SESSION['mapb'] = $maPb;
    $_SESSION['hoten'] = $hoTen;
  } else {
    // Không tìm thấy thông tin người dùng
    echo "<script>
      alert('Không tìm thấy thông tin người dùng! Vui lòng đăng nhập lại.');
      window.location.href='logout.php';
    </script>";
    exit();
  }
  
  // Tạo biến báo lỗi
  $error = array();
  $success = array();
  $showMess = false;
  
  // Xử lý phê duyệt/từ chối đơn nghỉ phép nếu có
  if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $maNP = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Kiểm tra quyền phê duyệt
    if($chucVu != 'admin' && $chucVu != 'Trưởng phòng') {
      echo "<script>
        alert('Bạn không có quyền phê duyệt đơn nghỉ phép!');
        window.location.href='xemdonnghiphep.php?p=leave&a=list-leave';
      </script>";
      exit();
    }
    
    // Validate action
    if($action != 'approve' && $action != 'reject') {
      echo "<script>
        alert('Hành động không hợp lệ!');
        window.location.href='xemdonnghiphep.php?p=leave&a=list-leave';
      </script>";
      exit();
    }
    
    // Lấy thông tin đơn nghỉ phép và thông tin nhân viên xin nghỉ sử dụng prepared statement
    $query_leave = "SELECT np.*, nv.MaPb, nv.Hoten 
              FROM nghiphep np 
              INNER JOIN nhanvien nv ON np.MaNV = nv.MaNV 
              WHERE np.MaNP = ?";
              
    $stmt_leave = mysqli_prepare($conn, $query_leave);
    mysqli_stmt_bind_param($stmt_leave, "s", $maNP);
    mysqli_stmt_execute($stmt_leave);
    $result_leave = mysqli_stmt_get_result($stmt_leave);
    
    if(mysqli_num_rows($result_leave) == 0) {
      echo "<script>
        alert('Không tìm thấy đơn nghỉ phép!');
        window.location.href='xemdonnghiphep.php?p=leave&a=list-leave';
      </script>";
      exit();
    }
    
    $leave = mysqli_fetch_assoc($result_leave);
    
    // Kiểm tra xem người dùng có quyền phê duyệt đơn này không
    $canApprove = false;
    
    // Admin có thể phê duyệt tất cả đơn
    if($chucVu == 'admin') {
      $canApprove = true;
    }
    // Trưởng phòng chỉ phê duyệt đơn của nhân viên trong phòng ban và không phải đơn của mình
    else if($chucVu == 'Trưởng phòng' && $leave['MaPb'] == $maPb && $leave['MaNV'] != $maNV) {
      $canApprove = true;
    }
    
    if(!$canApprove) {
      echo "<script>
        alert('Bạn không có quyền phê duyệt đơn này!');
        window.location.href='xemdonnghiphep.php?p=leave&a=list-leave';
      </script>";
      exit();
    }
    
    // Kiểm tra trạng thái hiện tại của đơn
    if($leave['TrangThai'] != 'Chờ duyệt') {
      echo "<script>
        alert('Đơn này đã được xử lý!');
        window.location.href='xemdonnghiphep.php?p=leave&a=list-leave';
      </script>";
      exit();
    }
    
    // Xử lý phê duyệt hoặc từ chối đơn
    if($action == 'approve') {
      $trangThai = 'Đã duyệt';
      $ghiChu = "Đã được phê duyệt vào ngày " . date('d/m/Y');
    } else {
      $trangThai = 'Từ chối';
      $ghiChu = "Đã bị từ chối vào ngày " . date('d/m/Y');
    }
    
    $nguoiPheDuyet = $hoTen . ' (' . $chucVu . ')';
    
    // Cập nhật trạng thái đơn nghỉ phép sử dụng prepared statement
    $query_update = "UPDATE nghiphep 
                    SET TrangThai = ?, NguoiPheDuyet = ?, GhiChu = ? 
                    WHERE MaNP = ?";
    
    $stmt_update = mysqli_prepare($conn, $query_update);
    mysqli_stmt_bind_param($stmt_update, "ssss", $trangThai, $nguoiPheDuyet, $ghiChu, $maNP);
    $result_update = mysqli_stmt_execute($stmt_update);
    
    if($result_update) {
      $showMess = true;
      $success['success'] = 'Đã ' . ($action == 'approve' ? 'phê duyệt' : 'từ chối') . ' đơn nghỉ phép thành công!';
    } else {
      $error['db'] = 'Có lỗi xảy ra: ' . mysqli_error($conn);
    }
  }

  // Lấy danh sách đơn nghỉ phép dựa trên vai trò của người dùng
  $query_leaves = "SELECT np.*, nv.Hoten, nv.MaPb FROM nghiphep np
                  INNER JOIN nhanvien nv ON np.MaNV = nv.MaNV
                  WHERE 1=1";

  // Điều chỉnh truy vấn dựa trên vai trò
  $params = array();
  $types = "";
  
  // Admin xem tất cả đơn
  if($chucVu != 'admin') {
    if($chucVu == 'Trưởng phòng') {
      // Trưởng phòng xem đơn của mình và của nhân viên trong phòng
      $query_leaves .= " AND (np.MaNV = ? OR nv.MaPb = ?)";
      $params[] = $maNV;
      $params[] = $maPb;
      $types .= "ss";
    } else {
      // Nhân viên thường chỉ xem đơn của mình
      $query_leaves .= " AND np.MaNV = ?";
      $params[] = $maNV;
      $types .= "s";
    }
  }

  // Thêm sắp xếp theo ngày và trạng thái
  $query_leaves .= " ORDER BY np.TrangThai = 'Chờ duyệt' DESC, np.NgayBatDau DESC";

  // Thực thi truy vấn sử dụng prepared statement
  $stmt_leaves = mysqli_prepare($conn, $query_leaves);
  
  if(!empty($params)) {
    mysqli_stmt_bind_param($stmt_leaves, $types, ...$params);
  }
  
  mysqli_stmt_execute($stmt_leaves);
  $result_leaves = mysqli_stmt_get_result($stmt_leaves);
  
  $leaves = [];
  if($result_leaves) {
    while($row = mysqli_fetch_assoc($result_leaves)) {
      $leaves[] = $row;
    }
  } else {
    $error['db'] = 'Có lỗi xảy ra khi lấy danh sách đơn nghỉ phép: ' . mysqli_error($conn);
  }
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Quản lý đơn nghỉ phép
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li class="active">Quản lý đơn nghỉ phép</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <?php 
        // show error
        if(isset($error) && !empty($error))
        {
          if(isset($error['db'])) 
          {
            echo "<div class='alert alert-danger alert-dismissible'>";
            echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>";
            echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
            echo $error['db'];
            echo "</div>";
          }
        }
      ?>

      <?php 
        // show success
        if(isset($success) && !empty($success)) 
        {
          if($showMess == true)
          {
            echo "<div class='alert alert-success alert-dismissible'>";
            echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>";
            echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
            foreach ($success as $suc) 
            {
              echo $suc . "<br/>";
            }
            echo "</div>";
          }
        }
      ?>
      
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Danh sách đơn nghỉ phép</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-12">
                  <a href="xinnghiphep.php?p=leave&a=add-leave" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tạo đơn mới
                  </a>
                </div>
              </div>
              
              <?php if (empty($leaves)): ?>
                <div class="alert alert-info alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <h4><i class="icon fa fa-info"></i> Thông báo!</h4>
                  Chưa có đơn nghỉ phép nào.
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover" style="width: 100%;">
                    <thead>
                      <tr>
                        <th>Mã NP</th>
                        <th>Nhân viên</th>
                        <th>Ngày bắt đầu</th>
                        <th>Ngày kết thúc</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Người phê duyệt</th>
                        <th>Ghi chú</th>
                        <?php if ($chucVu == 'admin' || $chucVu == 'Trưởng phòng'): ?>
                          <th>Thao tác</th>
                        <?php endif; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($leaves as $leave): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($leave['MaNP']); ?></td>
                          <td><?php echo htmlspecialchars($leave['Hoten']); ?></td>
                          <td><?php echo date('d/m/Y', strtotime($leave['NgayBatDau'])); ?></td>
                          <td><?php echo date('d/m/Y', strtotime($leave['NgayKetThuc'])); ?></td>
                          <td><?php echo htmlspecialchars($leave['LyDo']); ?></td>
                          <td>
                            <?php if ($leave['TrangThai'] == 'Chờ duyệt'): ?>
                              <span class="label label-warning"><?php echo $leave['TrangThai']; ?></span>
                            <?php elseif ($leave['TrangThai'] == 'Đã duyệt'): ?>
                              <span class="label label-success"><?php echo $leave['TrangThai']; ?></span>
                            <?php elseif ($leave['TrangThai'] == 'Từ chối'): ?>
                              <span class="label label-danger"><?php echo $leave['TrangThai']; ?></span>
                            <?php else: ?>
                              <span class="label label-default"><?php echo $leave['TrangThai']; ?></span>
                            <?php endif; ?>
                          </td>
                          <td><?php echo htmlspecialchars($leave['NguoiPheDuyet'] ?? 'Chưa có'); ?></td>
                          <td><?php echo htmlspecialchars($leave['GhiChu'] ?? ''); ?></td>
                          
                          <?php if ($chucVu == 'admin' || $chucVu == 'Trưởng phòng'): ?>
                            <td>
                              <?php
                              // Chỉ hiển thị nút phê duyệt khi đơn đang chờ duyệt
                              if ($leave['TrangThai'] == 'Chờ duyệt'):
                                // Admin có thể phê duyệt tất cả
                                $canApprove = ($chucVu == 'admin');
                                
                                // Trưởng phòng chỉ phê duyệt nhân viên trong phòng và không phải đơn của mình
                                if ($chucVu == 'Trưởng phòng' && $leave['MaPb'] == $maPb && $leave['MaNV'] != $maNV) {
                                  $canApprove = true;
                                }
                                
                                if ($canApprove):
                              ?>
                                <div class="btn-group">
                                  <a href="xemdonnghiphep.php?id=<?php echo $leave['MaNP']; ?>&action=approve&p=leave&a=approve-leave" 
                                    class="btn btn-success btn-sm" 
                                    onclick="return confirm('Bạn có chắc muốn duyệt đơn này?')">
                                    <i class="fa fa-check"></i> Duyệt
                                  </a>
                                  <a href="xemdonnghiphep.php?id=<?php echo $leave['MaNP']; ?>&action=reject&p=leave&a=reject-leave" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Bạn có chắc muốn từ chối đơn này?')">
                                    <i class="fa fa-times"></i> Từ chối
                                  </a>
                                </div>
                              <?php endif; ?>
                              <?php endif; ?>
                            </td>
                          <?php endif; ?>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
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

<?php
  // include
  include('../layouts/footer.php');
  
  // đóng kết nối
  mysqli_close($conn);
}
else
{
  // go to pages login
  header('Location: login.php');
}
?>