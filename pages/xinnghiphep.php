<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
  include('../config.php');
  include('../functions.php');

  // Lấy thông tin nhân viên từ tài khoản đang đăng nhập
  $username = $_SESSION['username'];
  $query = "SELECT nv.Hoten, nv.MaNV 
            FROM taikhoan tk 
            LEFT JOIN nhanvien nv ON tk.MaNV = nv.MaNV 
            WHERE tk.Taikhoan = '$username'";
  $result = mysqli_query($conn, $query);
  $nhanvien = mysqli_fetch_assoc($result);

  $tenNhanVien = !empty($nhanvien['Hoten']) ? $nhanvien['Hoten'] : $username;
  $maNV = !empty($nhanvien['MaNV']) ? $nhanvien['MaNV'] : '';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Đơn xin nghỉ phép</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li><a href="manage_leaves.php?p=leave&a=list-leave">Nghỉ phép</a></li>
      <li class="active">Tạo đơn xin nghỉ phép</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Tạo đơn xin nghỉ phép</h3> &emsp;
            <small>Những ô nhập có dấu <span style="color: red;">*</span> là bắt buộc</small>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <form action="luudonnghiphep.php" method="POST">
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label>Họ tên nhân viên:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($tenNhanVien); ?>" readonly>
                    <input type="hidden" name="manv" value="<?php echo htmlspecialchars($maNV); ?>">
                  </div>
                  <div class="form-group">
                    <label>Ngày bắt đầu nghỉ <span style="color: red;">*</span>:</label>
                    <input type="date" class="form-control" id="ngay_bat_dau" name="ngay_bat_dau" required>
                  </div>
                  <div class="form-group">
                    <label>Ngày kết thúc nghỉ <span style="color: red;">*</span>:</label>
                    <input type="date" class="form-control" id="ngay_ket_thuc" name="ngay_ket_thuc" required>
                  </div>
                  <div class="form-group">
                    <label>Lý do <span style="color: red;">*</span>:</label>
                    <textarea class="form-control" id="ly_do" name="ly_do" rows="3" required></textarea>
                  </div>
                </div>
              </div>
              <button type="submit" class="btn btn-primary" name="save"><i class="fa fa-paper-plane"></i> Gửi đơn</button>
              <a href="manage_leaves.php?p=leave&a=list-leave" class="btn btn-default"><i class="fa fa-reply"></i> Quay lại</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script>
  // Kiểm tra ngày hợp lệ
  $(document).ready(function() {
    $('form').submit(function(e) {
      var startDate = new Date($('#ngay_bat_dau').val());
      var endDate = new Date($('#ngay_ket_thuc').val());
      var today = new Date();
      today.setHours(0, 0, 0, 0);

      if (startDate < today) {
        alert("Ngày bắt đầu nghỉ phép không thể là ngày trong quá khứ!");
        e.preventDefault();
        return false;
      }

      if (endDate < startDate) {
        alert("Ngày kết thúc không thể sớm hơn ngày bắt đầu!");
        e.preventDefault();
        return false;
      }

      return true;
    });
  });
</script>

<?php
  // include
  include('../layouts/footer.php');
} else {
  // go to pages login
  header('Location: login.php');
}
?>
