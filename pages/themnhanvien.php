<?php 
// create session
session_start();
// echo '<pre>'; print_r($_SESSION); echo '</pre>';


if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Tạo mã nhân viên theo định dạng HPSF001, HPSF002, ...
  $prefix = "HPSF";
  
  // Lấy mã nhân viên lớn nhất trong hệ thống
  $queryMaxID = "SELECT MAX(MaNV) as maxid FROM NhanVien WHERE MaNV LIKE '$prefix%'";
  $resultMaxID = mysqli_query($conn, $queryMaxID);
  $rowMaxID = mysqli_fetch_assoc($resultMaxID);
  $maxID = $rowMaxID['maxid'];
  
  if ($maxID) {
    // Nếu đã có mã nhân viên trong hệ thống, tăng số lên 1
    $number = intval(substr($maxID, strlen($prefix)));
    $number++;
    $maNhanVien = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
  } else {
    // Nếu chưa có mã nhân viên, bắt đầu từ 001
    $maNhanVien = $prefix . "001";
  }

  // chuc nang them nhan vien
  if(isset($_POST['save'])) {
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
    
    // Xử lý upload ảnh
    $avatar = '';
    if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
      $allowed = array('jpg', 'jpeg', 'png', 'gif');
      $filename = $_FILES['avatar']['name'];
      $ext = pathinfo($filename, PATHINFO_EXTENSION);
      
      if(!in_array(strtolower($ext), $allowed)) {
        $error['avatar'] = 'error';
      } else {
        $newFileName = $maNhanVien . '_' . time() . '.' . $ext;
        $uploadPath = '../uploads/avatars/';
        
        // Tạo thư mục nếu chưa tồn tại
        if(!file_exists($uploadPath)) {
          mkdir($uploadPath, 0777, true);
        }
        
        $upload = move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath . $newFileName);
        if($upload) {
          $avatar = $newFileName;
        } else {
          $error['avatar'] = 'error';
        }
      }
    }

    // validate
    if(empty($hoTen))
      $error['hoTen'] = 'error';
    if(empty($sdt))
      $error['sdt'] = 'error';
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
    
    // Validate email
    if(!empty($email)) {
      if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email_format'] = 'error';
      } else {
        // Kiểm tra email đã tồn tại chưa
        $checkEmail = "SELECT * FROM NhanVien WHERE Email = '$email'";
        $resultEmail = mysqli_query($conn, $checkEmail);
        if(mysqli_num_rows($resultEmail) > 0)
          $error['email_exists'] = 'error';
      }
    }

    // Kiểm tra SDT đã tồn tại chưa
    if(!empty($sdt)) {
      $checkSDT = "SELECT * FROM NhanVien WHERE SDT = '$sdt'";
      $resultSDT = mysqli_query($conn, $checkSDT);
      if(mysqli_num_rows($resultSDT) > 0)
        $error['sdt_exists'] = 'error';
    }

    // Kiểm tra CCCD đã tồn tại chưa
    if(!empty($cccd)) {
      $checkCCCD = "SELECT * FROM NhanVien WHERE CCCD = '$cccd'";
      $resultCCCD = mysqli_query($conn, $checkCCCD);
      if(mysqli_num_rows($resultCCCD) > 0)
        $error['cccd_exists'] = 'error';
    }

    if(!$error) {
      $showMess = true;

      // insert data
      $insert = "INSERT INTO NhanVien(MaNV, Avatar, Hoten, SDT, Email, CCCD, DiaChi, GioiTinh, NgaySinh, NgayVaoLam, ChucVu, MaPb) 
                VALUES('$maNhanVien', '$avatar', '$hoTen', '$sdt', " . (!empty($email) ? "'$email'" : "NULL") . ", '$cccd', " . 
                (!empty($diaChi) ? "'$diaChi'" : "NULL") . ", '$gioiTinh', " . 
                (!empty($ngaySinh) ? "'$ngaySinh'" : "NULL") . ", '$ngayVaoLam', '$chucVu', '$MaPb')";
      $result = mysqli_query($conn, $insert);

      if($result) {
        $success['success'] = 'Thêm nhân viên thành công';
        
        // Sau khi thêm thành công, tạo mã nhân viên mới cho lần thêm tiếp theo
        $queryMaxID = "SELECT MAX(MaNV) as maxid FROM NhanVien WHERE MaNV LIKE '$prefix%'";
        $resultMaxID = mysqli_query($conn, $queryMaxID);
        $rowMaxID = mysqli_fetch_assoc($resultMaxID);
        $maxID = $rowMaxID['maxid'];
        $number = intval(substr($maxID, strlen($prefix)));
        $number++;
        $maNhanVien = $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
        
        echo '<script>setTimeout("window.location=\'themnhanvien.php?p=staff&a=add-staff\'",1000);</script>';
      } else {
        $error['db'] = 'Lỗi khi thêm nhân viên: ' . mysqli_error($conn);
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
    <h1>Thêm mới nhân viên</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="danhsachnhanvien.php?p=staff&a=list-staff">Nhân viên</a></li>
      <li class="active">Thêm mới nhân viên</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Thêm mới nhân viên</h3> &emsp;
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
                    <input type="text" class="form-control" name="maNhanVien" value="<?php echo $maNhanVien; ?>" readonly>
                    <small class="text-muted">Mã nhân viên được tự động tạo theo định dạng HPSF001, HPSF002, ...</small>
                  </div>
                  
                  <div class="form-group">
                    <label>Ảnh đại diện: </label>
                    <input type="file" class="form-control" name="avatar" accept="image/*">
                    <small style="color: red;"><?php if(isset($error['avatar'])) echo "Lỗi khi tải lên ảnh. Chỉ chấp nhận các file jpg, jpeg, png, gif."; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Họ tên nhân viên <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập họ tên nhân viên" name="hoTen" value="<?php echo isset($_POST['hoTen']) ? $_POST['hoTen'] : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['hoTen'])) echo "Họ tên nhân viên không được để trống"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Số điện thoại <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập số điện thoại" name="sdt" value="<?php echo isset($_POST['sdt']) ? $_POST['sdt'] : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['sdt'])) echo "Số điện thoại không được để trống"; ?></small>
                    <small style="color: red;"><?php if(isset($error['sdt_exists'])) echo "Số điện thoại đã tồn tại"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Email: </label>
                    <input type="email" class="form-control" placeholder="Nhập email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['email_format'])) echo "Email không đúng định dạng"; ?></small>
                    <small style="color: red;"><?php if(isset($error['email_exists'])) echo "Email đã tồn tại"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Số CCCD <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập số CCCD" name="cccd" value="<?php echo isset($_POST['cccd']) ? $_POST['cccd'] : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['cccd'])) echo "Số CCCD không được để trống"; ?></small>
                    <small style="color: red;"><?php if(isset($error['cccd_exists'])) echo "Số CCCD đã tồn tại"; ?></small>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label>Địa chỉ: </label>
                    <textarea class="form-control" name="diaChi" rows="3" placeholder="Nhập địa chỉ"><?php echo isset($_POST['diaChi']) ? $_POST['diaChi'] : ''; ?></textarea>
                  </div>
                  
                  <div class="form-group">
                    <label>Giới tính <span style="color: red;">*</span>: </label>
                    <select class="form-control" name="gioiTinh">
                      <option value="chon">--- Chọn giới tính ---</option>
                      <option value="Nam" <?php echo (isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                      <option value="Nữ" <?php echo (isset($_POST['gioiTinh']) && $_POST['gioiTinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                    </select>
                    <small style="color: red;"><?php if(isset($error['gioiTinh'])) echo "Vui lòng chọn giới tính"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Ngày sinh: </label>
                    <input type="date" class="form-control" name="ngaySinh" value="<?php echo isset($_POST['ngaySinh']) ? $_POST['ngaySinh'] : date("Y-m-d"); ?>">
                  </div>
                  
                  <div class="form-group">
                    <label>Ngày vào làm <span style="color: red;">*</span>: </label>
                    <input type="date" class="form-control" name="ngayVaoLam" value="<?php echo isset($_POST['ngayVaoLam']) ? $_POST['ngayVaoLam'] : date("Y-m-d"); ?>">
                    <small style="color: red;"><?php if(isset($error['ngayVaoLam'])) echo "Vui lòng chọn ngày vào làm"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Chức vụ <span style="color: red;">*</span>: </label>
                    <input type="text" class="form-control" placeholder="Nhập chức vụ" name="chucVu" value="<?php echo isset($_POST['chucVu']) ? $_POST['chucVu'] : ''; ?>">
                    <small style="color: red;"><?php if(isset($error['chucVu'])) echo "Chức vụ không được để trống"; ?></small>
                  </div>
                  
                  <div class="form-group">
                    <label>Phòng ban <span style="color: red;">*</span>: </label>
                    <select class="form-control" name="maPhongBan">
                      <option value="chon">--- Chọn phòng ban ---</option>
                      <?php 
                        foreach ($arrPhongBan as $pb) {
                          $selected = (isset($_POST['maPhongBan']) && $_POST['maPhongBan'] == $pb['MaPb']) ? 'selected' : '';
                          echo "<option value='".$pb['MaPb']."' ".$selected.">".$pb['TenPhongBan']."</option>";
                        }
                      ?>
                    </select>
                    <small style="color: red;"><?php if(isset($error['MaPb'])) echo "Vui lòng chọn phòng ban"; ?></small>
                  </div>
                </div>
              </div>

              <?php 
                if(isset($_SESSION['chucvu']) && $_SESSION['chucvu'] == 'admin') {
                  echo "<button type='submit' class='btn btn-primary' name='save'><i class='fa fa-plus'></i> Thêm mới nhân viên</button>";
                }
              ?>
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