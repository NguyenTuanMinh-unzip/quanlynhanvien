<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra tài khoản có quyền đổi mật khẩu hay không
  $isAdmin = false;
  if(isset($row_acc) && $row_acc['Chucvu'] == 'admin') {
    $isAdmin = true;
  }

  // Kiểm tra có ID tài khoản được truyền vào không (chỉ admin mới có thể truyền ID)
  $taiKhoanEdit = isset($_GET['id']) ? $_GET['id'] : $_SESSION['username'];
  
  // Nếu không phải admin và cố tình truyền ID khác với tài khoản của mình
  if(!$isAdmin && $taiKhoanEdit != $_SESSION['username']) {
    echo "<script>
      alert('Bạn không có quyền đổi mật khẩu tài khoản khác!');
      window.location.href='doimatkhau.php';
    </script>";
    exit;
  }
  
  // Lấy thông tin tài khoản cần đổi mật khẩu
  $queryTaiKhoan = "SELECT tk.*, nv.Hoten, nv.ChucVu 
                   FROM TaiKhoan tk 
                   LEFT JOIN NhanVien nv ON tk.MaNV = nv.MaNV 
                   WHERE tk.Taikhoan = '$taiKhoanEdit'";
  $resultTaiKhoan = mysqli_query($conn, $queryTaiKhoan);
  
  if(mysqli_num_rows($resultTaiKhoan) > 0) {
    $taiKhoan = mysqli_fetch_assoc($resultTaiKhoan);
  } else {
    echo "<script>
      alert('Không tìm thấy thông tin tài khoản!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }
  
  // Xử lý đổi mật khẩu
  if(isset($_POST['doimatkhau'])) {
    $error = array();
    $success = array();
    $showMess = false;
    
    // Lấy dữ liệu từ form
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate dữ liệu
    // Nếu là admin đổi mật khẩu tài khoản khác, không cần mật khẩu cũ
    if(!$isAdmin || $taiKhoanEdit == $_SESSION['username']) {
      // Kiểm tra mật khẩu cũ
      if(!password_verify($oldPassword, $taiKhoan['Matkhau'])) {
        $error['oldPassword'] = 'Mật khẩu cũ không chính xác';
      }
    }
    
    // Kiểm tra mật khẩu mới
    if(empty($newPassword)) {
      $error['newPassword'] = 'Vui lòng nhập mật khẩu mới';
    } else if(strlen($newPassword) < 6) {
      $error['newPassword'] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    }
    
    // Kiểm tra xác nhận mật khẩu
    if(empty($confirmPassword)) {
      $error['confirmPassword'] = 'Vui lòng xác nhận mật khẩu mới';
    } else if($newPassword != $confirmPassword) {
      $error['confirmPassword'] = 'Xác nhận mật khẩu không khớp';
    }
    
    if(empty($error)) {
      // Mã hóa mật khẩu mới
      $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      
      // Cập nhật mật khẩu mới
      $updatePassword = "UPDATE TaiKhoan SET Matkhau = ? WHERE Taikhoan = ?";
      $stmt = mysqli_prepare($conn, $updatePassword);
      mysqli_stmt_bind_param($stmt, "ss", $hashedNewPassword, $taiKhoanEdit);
      $resultUpdate = mysqli_stmt_execute($stmt);
      
      if($resultUpdate) {
        $showMess = true;
        $success['success'] = 'Đổi mật khẩu thành công!';
        
        // Nếu user đổi mật khẩu chính mình, cần đăng xuất để đăng nhập lại
        if($taiKhoanEdit == $_SESSION['username']) {
          echo "<script>
            setTimeout(function() {
              alert('Mật khẩu đã được thay đổi. Vui lòng đăng nhập lại!');
              window.location.href='logout.php';
            }, 2000);
          </script>";
        }
      } else {
        $error['db'] = 'Lỗi khi cập nhật mật khẩu: ' . mysqli_error($conn);
      }
    }
  }
  
  // Nếu là admin, lấy danh sách tài khoản để hiển thị dropdown
  if($isAdmin) {
    $queryAllAccount = "SELECT tk.Taikhoan, nv.Hoten, nv.ChucVu 
                        FROM TaiKhoan tk 
                        LEFT JOIN NhanVien nv ON tk.MaNV = nv.MaNV 
                        ORDER BY nv.Hoten ASC";
    $resultAllAccount = mysqli_query($conn, $queryAllAccount);
    $arrTaiKhoan = array();
    while ($row = mysqli_fetch_array($resultAllAccount)) {
      $arrTaiKhoan[] = $row;
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Đổi mật khẩu</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Đổi mật khẩu</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">
              <?php echo $isAdmin && $taiKhoanEdit != $_SESSION['username'] ? 'Đổi mật khẩu cho ' . $taiKhoan['Hoten'] : 'Đổi mật khẩu'; ?>
            </h3>
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
            
            <?php if($isAdmin): ?>
            <div class="form-group">
              <label>Chọn tài khoản:</label>
              <select class="form-control" id="accountSelect" onchange="changeAccount(this.value)">
                <?php 
                  foreach($arrTaiKhoan as $tk) {
                    $selected = $tk['Taikhoan'] == $taiKhoanEdit ? 'selected' : '';
                    echo "<option value='".$tk['Taikhoan']."' ".$selected.">".$tk['Hoten']." - ".$tk['Taikhoan']." (".$tk['ChucVu'].")</option>";
                  }
                ?>
              </select>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST">
              <div class="form-group">
                <label>Tài khoản:</label>
                <input type="text" class="form-control" value="<?php echo $taiKhoan['Taikhoan']; ?>" readonly>
              </div>
              
              <?php if(!$isAdmin || $taiKhoanEdit == $_SESSION['username']): ?>
              <div class="form-group">
                <label>Mật khẩu hiện tại: <span style="color: red;">*</span></label>
                <input type="password" name="oldPassword" class="form-control" required>
                <small style="color: red;"><?php if(isset($error['oldPassword'])) echo $error['oldPassword']; ?></small>
              </div>
              <?php endif; ?>
              
              <div class="form-group">
                <label>Mật khẩu mới: <span style="color: red;">*</span></label>
                <input type="password" name="newPassword" class="form-control" required minlength="6">
                <small style="color: red;"><?php if(isset($error['newPassword'])) echo $error['newPassword']; ?></small>
              </div>
              
              <div class="form-group">
                <label>Xác nhận mật khẩu mới: <span style="color: red;">*</span></label>
                <input type="password" name="confirmPassword" class="form-control" required>
                <small style="color: red;"><?php if(isset($error['confirmPassword'])) echo $error['confirmPassword']; ?></small>
              </div>
              
              <div class="form-group">
                <button type="submit" name="doimatkhau" class="btn btn-primary">
                  <i class="fa fa-key"></i> Đổi mật khẩu
                </button>
                <a href="index.php?p=index&a=statistic" class="btn btn-default">
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
  // Chuyển đến trang đổi mật khẩu của tài khoản được chọn
  function changeAccount(taikhoan) {
    window.location.href = 'doimatkhau.php?p=account&a=change-password&id=' + taikhoan;
  }
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