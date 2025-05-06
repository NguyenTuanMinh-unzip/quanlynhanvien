<?php 

// create session
session_start();

if(isset($_SESSION['username']))
{
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  $maPhongBan = "";

  // chuc nang them phong ban
  if(isset($_POST['save']))
  {
    // tao bien bat loi
    $error = array();
    $success = array();
    $showMess = false;

    // lay du lieu ve
    $maPhongBan = $_POST['maPhongBan'];
    $tenPhongBan = $_POST['tenPhongBan'];

    // validate
    if(empty($maPhongBan))
      $error['maPhongBan'] = 'error';
    if(empty($tenPhongBan))
      $error['tenPhongBan'] = 'error';

    // Kiểm tra mã phòng ban đã tồn tại chưa
    if(!empty($maPhongBan)) {
      $checkMaPb = "SELECT * FROM PhongBan WHERE MaPb = '$maPhongBan'";
      $resultMaPb = mysqli_query($conn, $checkMaPb);
      if(mysqli_num_rows($resultMaPb) > 0)
        $error['maPhongBan_exists'] = 'error';
    }

    // Kiểm tra tên phòng ban đã tồn tại chưa
    if(!empty($tenPhongBan)) {
      $checkTenPb = "SELECT * FROM PhongBan WHERE TenPhongBan = '$tenPhongBan'";
      $resultTenPb = mysqli_query($conn, $checkTenPb);
      if(mysqli_num_rows($resultTenPb) > 0)
        $error['tenPhongBan_exists'] = 'error';
    }

    if(!$error)
    {
      $showMess = true;
      
      // insert data
      $insert = "INSERT INTO PhongBan(MaPb, TenPhongBan) VALUES('$maPhongBan', '$tenPhongBan')";
      $result = mysqli_query($conn, $insert);
      
      if($result)
      {
        $success['success'] = 'Thêm phòng ban thành công';
        echo '<script>setTimeout("window.location=\'themphongban.php?p=department&a=add-department\'",1000);</script>';
      }
      else
      {
        $error['db'] = 'Lỗi khi thêm phòng ban: ' . mysqli_error($conn);
      }
    }
  }
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Thêm mới phòng ban
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li><a href="dsphongban.php?p=department&a=list-department">Phòng ban</a></li>
        <li class="active">Thêm mới phòng ban</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Thêm mới phòng ban</h3> &emsp;
              <small>Những ô nhập có dấu <span style="color: red;">*</span> là bắt buộc</small>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php 
                // show error
                if($row_acc['Chucvu'] != 'admin') 
                {
                  echo "<div class='alert alert-warning alert-dismissible'>";
                  echo "<h4><i class='icon fa fa-ban'></i> Thông báo!</h4>";
                  echo "Bạn <b> không có quyền </b> thực hiện chức năng này.";
                  echo "</div>";
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
                }
              ?>
              <form action="" method="POST">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label>Mã phòng ban <span style="color: red;">*</span>: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nhập mã phòng ban" name="maPhongBan" value="<?php echo $maPhongBan; ?>">
                      <small style="color: red;"><?php if(isset($error['maPhongBan'])){ echo "Mã phòng ban không được để trống"; } ?></small>
                      <small style="color: red;"><?php if(isset($error['maPhongBan_exists'])){ echo "Mã phòng ban đã tồn tại"; } ?></small>
                    </div>
                    <div class="form-group">
                      <label>Tên phòng ban <span style="color: red;">*</span>: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Nhập tên phòng ban" name="tenPhongBan" value="<?php echo isset($_POST['tenPhongBan']) ? $_POST['tenPhongBan'] : ''; ?>">
                      <small style="color: red;"><?php if(isset($error['tenPhongBan'])){ echo "Tên phòng ban không được để trống"; } ?></small>
                      <small style="color: red;"><?php if(isset($error['tenPhongBan_exists'])){ echo "Tên phòng ban đã tồn tại"; } ?></small>
                    </div>
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
                <?php 
                  if($_SESSION['chucvu'] == 'admin')
                    echo "<button type='submit' class='btn btn-primary' name='save'><i class='fa fa-plus'></i> Thêm mới phòng ban</button>";
                ?>
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

<?php
  // include
  include('../layouts/footer.php');
}
else
{
  // go to pages login
  header('Location: login.php');
}

?>