<?php 
// create session
session_start();

if(isset($_SESSION['username']))
{
  // include config file để kết nối cơ sở dữ liệu
  include('../config.php');
  
  // Kiểm tra xem người dùng có phải admin không
  $username = $_SESSION['username'];
  $checkAdmin = mysqli_query($conn, "SELECT Chucvu FROM TaiKhoan WHERE Taikhoan = '$username'");
  $userData = mysqli_fetch_assoc($checkAdmin);
  
  // Nếu không phải admin thì chuyển hướng về trang chủ
  if (!$userData || $userData['Chucvu'] != 'admin') {
    $_SESSION['error_message'] = "Bạn không có quyền truy cập trang này!";
    header('Location: index.php');
    exit();
  }
  
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
  
  // Khởi tạo biến thông báo
  $message = "";
  
  // Lấy danh sách nhân viên chưa có tài khoản
  $query = "SELECT nv.MaNV, nv.Hoten, nv.Chucvu 
            FROM NhanVien nv 
            LEFT JOIN TaiKhoan tk ON nv.MaNV = tk.MaNV 
            WHERE tk.MaNV IS NULL 
            ORDER BY nv.Hoten ASC";
  $result = mysqli_query($conn, $query);
  
  // Xử lý khi form được gửi
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $employeeCode = mysqli_real_escape_string($conn, $_POST['employee-code']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    
    // Kiểm tra mật khẩu khớp nhau
    if ($password !== $confirmPassword) {
        $message = "<div class='alert alert-danger'>Mật khẩu không khớp!</div>";
    } 
    // Kiểm tra độ dài mật khẩu
    else if (strlen($password) < 6) {
        $message = "<div class='alert alert-danger'>Mật khẩu phải có ít nhất 6 ký tự!</div>";
    }
    else {
        // Lấy thông tin nhân viên
        $queryNV = "SELECT Hoten, Chucvu FROM NhanVien WHERE MaNV = '$employeeCode'";
        $resultNV = mysqli_query($conn, $queryNV);
        $rowNV = mysqli_fetch_assoc($resultNV);
        
        // Lấy chức vụ từ bảng NhanVien
        $role = $rowNV['Chucvu'];
        
        // Kiểm tra tài khoản đã tồn tại chưa
        $checkAccount = mysqli_query($conn, "SELECT Taikhoan FROM TaiKhoan WHERE MaNV = '$employeeCode'");
        
        if (mysqli_num_rows($checkAccount) > 0) {
            $message = "<div class='alert alert-danger'>Nhân viên này đã có tài khoản!</div>";
        } else {
            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Sử dụng mã nhân viên làm tên đăng nhập
            $username = $employeeCode;
            
            // Tạo tài khoản trong cơ sở dữ liệu
            $insertAccount = "INSERT INTO TaiKhoan (Taikhoan, Matkhau, Chucvu, MaNV) 
                             VALUES ('$username', '$hashedPassword', '$role', '$employeeCode')";
            
            if(mysqli_query($conn, $insertAccount)) {
                $message = "<div class='alert alert-success'>Tạo tài khoản thành công! Tên đăng nhập: $username</div>";
                // Refresh lại danh sách nhân viên chưa có tài khoản
                $result = mysqli_query($conn, $query);
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dansachtaikhoan.php';
                    }, 3000);
                </script>";
            } else {
                $message = "<div class='alert alert-danger'>Lỗi: " . mysqli_error($conn) . "</div>";
            }
        }
    }
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Tạo Tài Khoản
    </h1>
    <ol class="breadcrumb">
      <li><a href="../index.php"><i class="fa fa-dashboard"></i> Trang chủ</a></li>
      <li><a href="dansachtaikhoan.php">Quản lý tài khoản</a></li>
      <li class="active">Tạo tài khoản</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Tạo tài khoản mới</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          
          <!-- Hiển thị thông báo -->
          <?php echo $message; ?>
          
          <!-- form start -->
          <form role="form" method="POST" action="">
            <div class="box-body">
              <div class="form-group">
                <label for="employee-code">Mã Nhân Viên (Sẽ dùng làm tên đăng nhập)</label>
                <select class="form-control" id="employee-code" name="employee-code" required onchange="fetchEmployeeInfo()">
                  <option value="">-- Chọn mã nhân viên --</option>
                  <?php
                  if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                      echo '<option value="' . $row['MaNV'] . '" data-name="' . $row['Hoten'] . '" data-role="' . $row['Chucvu'] . '">' . $row['MaNV'] . ' - ' . $row['Hoten'] . '</option>';
                    }
                  }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="full-name">Họ Tên Nhân Viên</label>
                <input type="text" class="form-control" id="full-name" name="full-name" readonly>
              </div>
              <div class="form-group">
                <label for="role">Chức Vụ</label>
                <input type="text" class="form-control" id="role" name="role" readonly>
              </div>
              <div class="form-group">
                <label for="password">Mật Khẩu</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required minlength="6">
              </div>
              <div class="form-group">
                <label for="confirm-password">Xác Nhận Mật Khẩu</label>
                <input type="password" class="form-control" id="confirm-password" name="confirm-password" placeholder="Nhập lại mật khẩu" required>
              </div>
            </div>
            <!-- /.box-body -->

            <div class="box-footer">
              <button type="submit" class="btn btn-primary">Tạo Tài Khoản</button>
              <a href="dansachtaikhoan.php" class="btn btn-default">Hủy</a>
            </div>
          </form>
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

<!-- Script để tự động điền tên nhân viên và chức vụ khi chọn mã nhân viên -->
<script>
function fetchEmployeeInfo() {
  var select = document.getElementById('employee-code');
  var option = select.options[select.selectedIndex];
  var fullNameInput = document.getElementById('full-name');
  var roleInput = document.getElementById('role');
  
  if (option.value != '') {
    fullNameInput.value = option.getAttribute('data-name');
    roleInput.value = option.getAttribute('data-role');
  } else {
    fullNameInput.value = '';
    roleInput.value = '';
  }
}

// Kiểm tra mật khẩu khớp nhau trước khi submit
document.querySelector('form').addEventListener('submit', function(e) {
  var password = document.getElementById('password').value;
  var confirmPassword = document.getElementById('confirm-password').value;
  
  if (password !== confirmPassword) {
    e.preventDefault();
    alert('Mật khẩu không khớp!');
  }
});
</script>

<?php
  // include
  include('../layouts/footer.php');
}
else
{
  // Nếu không có phiên đăng nhập, chuyển hướng đến trang đăng nhập
  header('Location: login.php');
  exit();
}
?>