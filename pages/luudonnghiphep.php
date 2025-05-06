<?php 
// create session
session_start();

if(isset($_SESSION['username']))
{
  // include file
  include('../config.php');
  include('../functions.php');

  // Kiểm tra quyền trực tiếp - không dùng hàm
//   $canAccess = false;
  
//   if($_SESSION['chucvu'] == 'admin' || $_SESSION['chucvu'] == 'Trưởng phòng' || $_SESSION['chucvu'] == 'user') {
//     $canAccess = true;
//   }
  
//   if(!$canAccess) {
//     echo "<script>
//       alert('Bạn không có quyền truy cập chức năng này!');
//       window.location.href='index.php?p=index&a=statistic';
//     </script>";
//     exit();
//   }

  // Xử lý dữ liệu khi form được gửi
  if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save']))
  {
    // tao bien bat loi
    $error = array();
    $success = array();
    $showMess = false;

    // lay du lieu ve
    $maNV = $_POST['manv'];
    $ngayBatDau = $_POST['ngay_bat_dau'];
    $ngayKetThuc = $_POST['ngay_ket_thuc'];
    $lyDo = $_POST['ly_do'];
    
    // validate
    if(empty($maNV))
      $error['maNV'] = 'error';
    if(empty($ngayBatDau))
      $error['ngayBatDau'] = 'error';
    if(empty($ngayKetThuc))
      $error['ngayKetThuc'] = 'error';
    if(empty($lyDo))
      $error['lyDo'] = 'error';
    
    // Kiểm tra ngày hợp lệ
    $today = date('Y-m-d');
    if($ngayBatDau < $today)
      $error['ngayBatDau_invalid'] = 'error';
    
    if($ngayKetThuc < $ngayBatDau)
      $error['ngayKetThuc_invalid'] = 'error';
    
    if(!$error)
    {
      $showMess = true;
      
      // Tạo mã nghỉ phép tự động
      $maNP = generateLeaveID($conn);
      
      // Trạng thái mặc định khi tạo đơn
      $trangThai = "Chờ duyệt";
      
      // Thêm đơn xin nghỉ phép vào CSDL
      $query = "INSERT INTO nghiphep (MaNP, MaNV, NgayBatDau, NgayKetThuc, LyDo, TrangThai) 
                VALUES ('$maNP', '$maNV', '$ngayBatDau', '$ngayKetThuc', '$lyDo', '$trangThai')";
      $result = mysqli_query($conn, $query);
      
      if($result)
      {
        $success['success'] = 'Đơn xin nghỉ phép đã được gửi thành công!';
        echo '<script>setTimeout("window.location=\'xemdonnghiphep.php?p=leave&a=list-leave\'",1000);</script>';
      }
      else
      {
        $error['db'] = 'Lỗi khi gửi đơn: ' . mysqli_error($conn);
      }
    }
  }
  else
  {
    // Nếu không phải POST request, chuyển hướng về trang form
    header("Location: xinnghiphep.php?p=leave&a=add-leave");
    exit();
  }

  // include thêm file nếu cần hiển thị trang kết quả
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Xử lý đơn xin nghỉ phép
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li><a href="manage_leaves.php?p=leave&a=list-leave">Nghỉ phép</a></li>
        <li class="active">Xử lý đơn xin nghỉ phép</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Thông báo</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php 
                // show error
                if(isset($error)) 
                {
                  if(isset($error['db'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo $error['db'];
                    echo "</div>";
                  }
                  
                  if(isset($error['maNV'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Thông tin nhân viên không hợp lệ!";
                    echo "</div>";
                  }
                  
                  if(isset($error['ngayBatDau'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Vui lòng chọn ngày bắt đầu nghỉ!";
                    echo "</div>";
                  }
                  
                  if(isset($error['ngayKetThuc'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Vui lòng chọn ngày kết thúc nghỉ!";
                    echo "</div>";
                  }
                  
                  if(isset($error['lyDo'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Vui lòng nhập lý do nghỉ phép!";
                    echo "</div>";
                  }
                  
                  if(isset($error['ngayBatDau_invalid'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Ngày bắt đầu nghỉ phép không thể là ngày trong quá khứ!";
                    echo "</div>";
                  }
                  
                  if(isset($error['ngayKetThuc_invalid'])) 
                  {
                    echo "<div class='alert alert-danger alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                    echo "Ngày kết thúc không thể sớm hơn ngày bắt đầu!";
                    echo "</div>";
                  }
                }
              ?>

              <?php 
                // show success
                if(isset($success)) 
                {
                  if($showMess == true)
                  {
                    echo "<div class='alert alert-success alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                    foreach ($success as $suc) 
                    {
                      echo $suc . "<br/>";
                    }
                    echo "</div>";
                  }
                }
              ?>
              
              <a href="manage_leaves.php?p=leave&a=list-leave" class="btn btn-primary">
                <i class="fa fa-list"></i> Xem danh sách đơn nghỉ phép
              </a>
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