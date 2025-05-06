<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra có ID nhân viên được truyền vào không
  if(isset($_GET['id'])) {
    $maNhanVien = $_GET['id'];
    
    // Lấy thông tin nhân viên hiện tại
    $queryNhanVien = "SELECT * FROM NhanVien WHERE MaNV = '$maNhanVien'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    
    // Kiểm tra nhân viên có tồn tại không
    if(!$nhanVien) {
      echo "<script>
        alert('Nhân viên không tồn tại!');
        window.location.href='danhsachnhanvien.php?p=staff&a=list-staff';
      </script>";
      exit;
    }
  } else {
    echo "<script>
      alert('Không có ID nhân viên!');
      window.location.href='danhsachnhanvien.php?p=staff&a=list-staff';
    </script>";
    exit;
  }

  // chuc nang cap nhat nhan vien
  if(isset($_POST['update'])) {
    // tao bien bat loi
    $error = array();
    $success = array();
    $showMess = false;

    // lay du lieu ve
    $hoTen = $_POST['hoTen'];
    $sdt = $_POST['sdt'];
    $email = $_POST['email'];
    $cccd = $_POST['cccd'];
    $diaChi = $_POST['diaChi'];
    $gioiTinh = $_POST['gioiTinh'];
    $ngaySinh = $_POST['ngaySinh'];
    $ngayVaoLam = $_POST['ngayVaoLam'];
    $chucVu = $_POST['chucVu'];
    $MaPb = $_POST['maPhongBan'];
    
    // Xử lý avatar
    $avatar = $nhanVien['Avatar']; // Giữ nguyên avatar cũ nếu không upload mới
    
    // Kiểm tra nếu có file upload
    if(isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
      $targetDir = "../uploads/avatars/";
      
      // Tạo thư mục nếu chưa tồn tại
      if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
      }
      
      // Lấy phần mở rộng của file
      $imageFileType = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
      
      // Tạo tên file mới để tránh trùng lặp
      $newFileName = $maNhanVien . "_" . time() . "." . $imageFileType;
      
      // Chỉ lưu tên file vào database, không lưu đường dẫn đầy đủ
      $targetFilePath = $targetDir . $newFileName;
      
      // Kiểm tra loại file
      $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
      if(in_array($imageFileType, $allowTypes)) {
        // Upload file
        if(move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFilePath)) {
          // Nếu upload thành công, cập nhật tên file mới
          $avatar = $newFileName;
          
          // Xóa file cũ nếu có
          if(!empty($nhanVien['Avatar'])) {
            $oldFilePath = $targetDir . $nhanVien['Avatar'];
            if(file_exists($oldFilePath)) {
              @unlink($oldFilePath);
            }
          }
        } else {
          $error['avatar_upload'] = 'error';
        }
      } else {
        $error['avatar_type'] = 'error';
      }
    }

    // validate
    if(empty($hoTen))
      $error['hoTen'] = 'error';
    if(empty($sdt))
      $error['sdt'] = 'error';
    if(empty($email))
      $error['email'] = 'error';
    if(!filter_var($email, FILTER_VALIDATE_EMAIL))
      $error['email_invalid'] = 'error';
    if(empty($cccd))
      $error['cccd'] = 'error';
    if($gioiTinh == 'chon')
      $error['gioiTinh'] = 'error';
    if(empty($ngayVaoLam))
      $error['ngayVaoLam'] = 'error';
    if(empty($chucVu))
      $error['chucVu'] = 'error';
    if($MaPb == 'chon')
      $error['MaPb'] = 'error';

    // Kiểm tra SDT đã tồn tại chưa (trừ nhân viên hiện tại)
    if(!empty($sdt)) {
      $checkSDT = "SELECT * FROM NhanVien WHERE SDT = '$sdt' AND MaNV != '$maNhanVien'";
      $resultSDT = mysqli_query($conn, $checkSDT);
      if(mysqli_num_rows($resultSDT) > 0)
        $error['sdt_exists'] = 'error';
    }

    // Kiểm tra Email đã tồn tại chưa (trừ nhân viên hiện tại)
    if(!empty($email)) {
      $checkEmail = "SELECT * FROM NhanVien WHERE Email = '$email' AND MaNV != '$maNhanVien'";
      $resultEmail = mysqli_query($conn, $checkEmail);
      if(mysqli_num_rows($resultEmail) > 0)
        $error['email_exists'] = 'error';
    }

    // Kiểm tra CCCD đã tồn tại chưa (trừ nhân viên hiện tại)
    if(!empty($cccd)) {
      $checkCCCD = "SELECT * FROM NhanVien WHERE CCCD = '$cccd' AND MaNV != '$maNhanVien'";
      $resultCCCD = mysqli_query($conn, $checkCCCD);
      if(mysqli_num_rows($resultCCCD) > 0)
        $error['cccd_exists'] = 'error';
    }

    if(!$error) {
      $showMess = true;

      // update data
      $update = "UPDATE NhanVien SET 
                  Avatar = '$avatar',
                  Hoten = '$hoTen', 
                  SDT = '$sdt',
                  Email = '$email',
                  CCCD = '$cccd',
                  DiaChi = '$diaChi',
                  GioiTinh = '$gioiTinh', 
                  NgaySinh = '$ngaySinh', 
                  NgayVaoLam = '$ngayVaoLam', 
                  ChucVu = '$chucVu', 
                  MaPb = '$MaPb'
                WHERE MaNV = '$maNhanVien'";
      $result = mysqli_query($conn, $update);

      if($result) {
        $success['success'] = 'Cập nhật nhân viên thành công';
        // Cập nhật lại thông tin nhân viên sau khi update
        $resultNhanVien = mysqli_query($conn, $queryNhanVien);
        $nhanVien = mysqli_fetch_assoc($resultNhanVien);
        echo '<script>setTimeout("window.location=\'danhsachnhanvien.php?p=staff&a=list-staff\'",2000);</script>';
      } else {
        $error['db'] = 'Lỗi khi cập nhật nhân viên: ' . mysqli_error($conn);
      }
    }
  }

  // Get phong ban data
  $phongBan = "SELECT * FROM PhongBan";
  $resultPhongBan = mysqli_query($conn, $phongBan);
  $arrPhongBan = array();
  while ($rowPhongBan = mysqli_fetch_array($resultPhongBan)) {
    $arrPhongBan[] = $rowPhongBan;
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Sửa thông tin nhân viên</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachnhanvien.php?p=staff&a=list-staff">Nhân viên</a></li>
      <li class="active">Sửa thông tin nhân viên</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Sửa thông tin nhân viên: <?php echo $nhanVien['Hoten']; ?></h3> &emsp;
            <small>Những ô nhập có dấu <span style="color: red;">*</span> là bắt buộc</small>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <?php 
              // show quyền hạn nếu không phải admin
              if(isset($row_acc) && $row_acc['Chucvu'] != 'admin') {
                echo "<div class='alert alert-warning alert-dismissible'>";
                echo "<h4><i class='icon fa fa-ban'></i> Thông báo!</h4>";
                echo "Bạn <b> không có quyền </b> thực hiện chức năng này.";
                echo "</div>";
              }
            ?>

            <?php 
              // show success
              if(isset($success) && $showMess == true) {
                echo "<div class='alert alert-success alert-dismissible'>";
                echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                foreach ($success as $suc) {
                  echo $suc . "<br/>";
                }
                echo "</div>";
              }
            ?>

            <?php 
              // show error
              if(isset($error['db'])) {
                echo "<div class='alert alert-danger alert-dismissible'>";
                echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                echo $error['db'];
                echo "</div>";
              }
            ?>

            <form action="" method="POST" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mã nhân viên: </label>
                    <input type="text" class="form-control" name="maNhanVien" value="<?php echo $nhanVien['MaNV']; ?>" readonly>
                  </div>
                  
                  <div class="form-group">
                    <label>Ảnh đại diện: </label>
                    <div class="row">
                      <div class="col-md-4">
                        <div style="width: 150px; height: 150px; border: 1px solid #ddd; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; overflow: hidden;">
                          <?php if(!empty($nhanVien['Avatar'])): ?>
                            <img src="../uploads/avatars/<?php echo $nhanVien['Avatar']; ?>" alt="<?php echo $nhanVien['MaNV']; ?>" style="max-width: 100%; max-height: 100%;">
                          <?php else: ?>
                            <img src="../uploads/avatars/default-avatar.png" alt="Default Avatar" style="max-width: 100%; max-height: 100%;">
                          <?php endif; ?>
                        </div>
                      </div>
                      <div class="col-md-8">
                        <input type="file" name="avatar" accept="image/*">
                        <small>Chọn ảnh JPG, PNG, GIF</small>
                        <small style="color: red; display: block;"><?php if(isset($error['avatar_upload'])) echo "Lỗi khi tải ảnh lên"; ?></small>
                        <small style="color: red; display: block;"><?php if(isset($error['avatar_type'])) echo "Chỉ chấp nhận định dạng JPG, PNG, GIF"; ?></small>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label>Họ tên nhân viên <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập họ tên nhân viên" name="hoTen" value="<?php echo isset($_POST['hoTen']) ? $_POST['hoTen'] : $nhanVien['Hoten']; ?>">
                    <small style="color: red;"><?php if(isset($error['hoTen'])) echo "Họ tên nhân viên không được để trống"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Số điện thoại <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập số điện thoại" name="sdt" value="<?php echo isset($_POST['sdt']) ? $_POST['sdt'] : $nhanVien['SDT']; ?>">
                    <small style="color: red;"><?php if(isset($error['sdt'])) echo "Số điện thoại không được để trống"; ?></small>
                    <small style="color: red;"><?php if(isset($error['sdt_exists'])) echo "Số điện thoại đã tồn tại"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Email <span style="color: red;">*</span>: </label>
                    <input type="email" class="form-control" placeholder="Nhập email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : $nhanVien['Email']; ?>">
                    <small style="color: red;"><?php if(isset($error['email'])) echo "Email không được để trống"; ?></small>
                    <small style="color: red;"><?php if(isset($error['email_invalid'])) echo "Email không hợp lệ"; ?></small>
                    <small style="color: red;"><?php if(isset($error['email_exists'])) echo "Email đã tồn tại"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Số CCCD <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập số CCCD" name="cccd" value="<?php echo isset($_POST['cccd']) ? $_POST['cccd'] : $nhanVien['CCCD']; ?>">
                    <small style="color: red;"><?php if(isset($error['cccd'])) echo "Số CCCD không được để trống"; ?></small>
                    <small style="color: red;"><?php if(isset($error['cccd_exists'])) echo "Số CCCD đã tồn tại"; ?></small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Địa chỉ: </label>
                    <textarea class="form-control" name="diaChi" rows="3" placeholder="Nhập địa chỉ"><?php echo isset($_POST['diaChi']) ? $_POST['diaChi'] : $nhanVien['DiaChi']; ?></textarea>
                  </div>
                  <div class="form-group">
                    <label>Giới tính <span style="color: red;">*</span>: </label>
                    <select class="form-control" name="gioiTinh">
                      <option value="chon">--- Chọn giới tính ---</option>
                      <option value="Nam" <?php if(isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == 'Nam') { echo 'selected'; } else if($nhanVien['GioiTinh'] == 'Nam') { echo 'selected'; } ?>>Nam</option>
                      <option value="Nữ" <?php if(isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == 'Nữ') { echo 'selected'; } else if($nhanVien['GioiTinh'] == 'Nữ') { echo 'selected'; } ?>>Nữ</option>
                    </select>
                    <small style="color: red;"><?php if(isset($error['gioiTinh'])) echo "Vui lòng chọn giới tính"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Ngày sinh: </label>
                    <input type="date" class="form-control" name="ngaySinh" value="<?php echo isset($_POST['ngaySinh']) ? $_POST['ngaySinh'] : $nhanVien['NgaySinh']; ?>">
                  </div>
                  <div class="form-group">
                    <label>Ngày vào làm <span style="color: red;">*</span>: </label>
                    <input type="date" class="form-control" name="ngayVaoLam" value="<?php echo isset($_POST['ngayVaoLam']) ? $_POST['ngayVaoLam'] : $nhanVien['NgayVaoLam']; ?>">
                    <small style="color: red;"><?php if(isset($error['ngayVaoLam'])) echo "Vui lòng chọn ngày vào làm"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Chức vụ <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập chức vụ" name="chucVu" value="<?php echo isset($_POST['chucVu']) ? $_POST['chucVu'] : $nhanVien['ChucVu']; ?>">
                    <small style="color: red;"><?php if(isset($error['chucVu'])) echo "Chức vụ không được để trống"; ?></small>
                  </div>
                  <div class="form-group">
                    <label>Phòng ban <span style="color: red;">*</span>: </label>
                    <select class="form-control" name="maPhongBan">
                      <option value="chon">--- Chọn phòng ban ---</option>
                      <?php 
                        foreach ($arrPhongBan as $pb) {
                          $selected = '';
                          if(isset($_POST['maPhongBan']) && $_POST['maPhongBan'] == $pb['MaPb']) {
                            $selected = 'selected';
                          } else if($nhanVien['MaPb'] == $pb['MaPb']) {
                            $selected = 'selected';
                          }
                          echo "<option value='".$pb['MaPb']."' ".$selected.">".$pb['TenPhongBan']."</option>";
                        }
                      ?>
                    </select>
                    <small style="color: red;"><?php if(isset($error['MaPb'])) echo "Vui lòng chọn phòng ban"; ?></small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <?php 
                      if(isset($_SESSION['chucvu']) && $_SESSION['chucvu'] == 'admin') {
                        echo "<button type='submit' class='btn btn-primary' name='update'><i class='fa fa-save'></i> Lưu thay đổi</button>";
                      }
                    ?>
                    <a href="danhsachnhanvien.php?p=staff&a=list-staff" class="btn btn-default"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a>
                  </div>
                </div>
              </div>
            </form>
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