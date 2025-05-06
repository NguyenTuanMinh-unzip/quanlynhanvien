<?php

// create session
session_start();

if (isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  if (isset($_POST['edit'])) {
    $id = $_POST['idStaff'];
    echo "<script>location.href='suanhanvien.php?p=staff&a=list-staff&id=" . $id . "'</script>";
  }

  if (isset($_POST['view'])) {
    $id = $_POST['idStaff'];
    echo "<script>location.href='chitietnhanvien.php?p=staff&a=list-staff&id=" . $id . "'</script>";
  }

  $showData = "SELECT NV.MaNV, NV.HoTen, NV.Avatar, NV.ChucVu, PB.TenPhongBan as PhongBan FROM NhanVien NV LEFT JOIN PhongBan PB ON NV.MaPb = PB.MaPb ORDER BY NV.MaNV";
  $result = mysqli_query($conn, $showData);
  $arrShow = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrShow[] = $row;
  }

  // Khởi tạo biến
  $error = array();
  $success = array();
  $showMess = false;

  // delete record
  if (isset($_POST['delete'])) {
    // Lấy mã nhân viên từ form
    if(isset($_POST['idStaff'])) {
      $maNV = $_POST['idStaff'];

      // Trước tiên, kiểm tra xem nhân viên có phải là admin không
      $checkAdmin = "SELECT ChucVu FROM NhanVien WHERE MaNV = '$maNV'";
      $resultCheck = mysqli_query($conn, $checkAdmin);

      if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
        $row = mysqli_fetch_assoc($resultCheck);
        if ($row['ChucVu'] == 'Admin' || $row['ChucVu'] == 'admin') {
          // Nếu là admin, hiển thị thông báo lỗi
          $error['admin'] = 'Không thể xóa nhân viên có chức vụ Admin.';
        } else {
          // Nếu không phải admin, tiến hành xóa
          $delete = "DELETE FROM NhanVien WHERE MaNV = '$maNV'";
          $resultDel = mysqli_query($conn, $delete);

          if ($resultDel) {
            $showMess = true;
            $success['success'] = 'Xóa nhân viên thành công.';
            echo '<script>setTimeout("window.location=\'danhsachnhanvien.php?p=staff&a=list-staff\'",1000);</script>';
          }
        }
      } else {
        // Không tìm thấy nhân viên
        $error['notfound'] = 'Không tìm thấy thông tin nhân viên.';
      }
    } else {
      // Không có mã nhân viên được truyền
      $error['missing'] = 'Không xác định được nhân viên cần xóa.';
    }
  }
  ?>
  <!-- Modal -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <span style="font-size: 18px;">Thông báo</span>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="idStaff">
            Bạn có thực sự muốn xóa nhân viên này?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy bỏ</button>
            <button type="submit" class="btn btn-primary" name="delete">Xóa</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Nhân viên
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <li><a href="danhsachnhanvien.php?p=staff&a=list-staff">Nhân viên</a></li>
        <li class="active">Danh sách nhân viên</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- row -->
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Danh sách nhân viên</h3>
            </div>

            <!-- /.box-header -->
            <div class="box-body">

              <div class="d-flex" style="margin-bottom: 15px; display: flex; justify-content: end;">
                <a href="themnhanvien.php?p=staff&a=add-staff" class="btn btn-primary">
                  <i class="fa fa-plus" aria-hidden="true" style="margin-right: 5px;"></i>Thêm nhân viên</a>
                <!-- <a href="export-nhan-vien.php" class="btn btn-success" style="margin-left: 7px;">
                  <i class="fa fa-file-excel-o" aria-hidden="true" style="margin-right: 5px;"></i>Xuất excel
                </a> -->
              </div>

              <?php
              // show error
              if ($row_acc['Chucvu'] != 'admin') {
                echo "<div class='alert alert-warning alert-dismissible'>";
                echo "<h4><i class='icon fa fa-ban'></i> Thông báo!</h4>";
                echo "Bạn <b> chỉ có thể xem </b> danh sách này.";
                echo "</div>";
              }
              ?>

              <?php
              // show error
              if (isset($error) && !empty($error)) {
                if ($showMess == false) {
                  echo "<div class='alert alert-danger alert-dismissible'>";
                  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
                  echo "<h4><i class='icon fa fa-ban'></i> Lỗi!</h4>";
                  foreach ($error as $err) {
                    echo $err . "<br/>";
                  }
                  echo "</div>";
                }
              }
              ?>
              <?php
              // show success
              if (isset($success) && !empty($success)) {
                if ($showMess == true) {
                  echo "<div class='alert alert-success alert-dismissible'>";
                  echo "<h4><i class='icon fa fa-check'></i> Thành công!</h4>";
                  foreach ($success as $suc) {
                    echo $suc . "<br/>";
                  }
                  echo "</div>";
                }
              }
              ?>
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>STT</th>
                      <th>Mã nhân viên</th>
                      <th>Ảnh</th>
                      <th>Họ tên</th>
                      <th>Chức vụ</th>
                      <th>Phòng ban</th>
                      <th>Xem</th>
                      <th>Sửa</th>
                      <th>Xóa</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $count = 1;
                    foreach ($arrShow as $arrS) {
                    ?>
                      <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo $arrS['MaNV']; ?></td>
                        <td align="center"><img src="../uploads/avatars/<?php echo $arrS['Avatar']; ?>" width="80"></td>
                        <td><?php echo $arrS['HoTen']; ?></td>
                        <td><?php echo $arrS['ChucVu']; ?></td>
                        <td><?php echo $arrS['PhongBan']; ?></td>
                        <td>
                          <?php
                          if ($row_acc['Chucvu'] == 'admin') {
                            echo "<form method='POST'>";
                            echo "<input type='hidden' value='" . $arrS['MaNV'] . "' name='idStaff'/>";
                            echo "<button type='submit' class='btn btn-primary btn-flat' name='view'><i class='fa fa-eye'></i></button>";
                            echo "</form>";
                          } else {
                            echo "<button type='button' class='btn btn-primary btn-flat' disabled><i class='fa fa-eye'></i></button>";
                          }
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($row_acc['Chucvu'] == 'admin') {
                            echo "<form method='POST'>";
                            echo "<input type='hidden' value='" . $arrS['MaNV'] . "' name='idStaff'/>";
                            echo "<button type='submit' class='btn bg-orange btn-flat' name='edit'><i class='fa fa-edit'></i></button>";
                            echo "</form>";
                          } else {
                            echo "<button type='button' class='btn bg-orange btn-flat' disabled><i class='fa fa-edit'></i></button>";
                          }
                          ?>

                        </td>
                        <td>
                          <?php
                          if ($row_acc['Chucvu'] == 'admin') {
                            echo "<button type='button' class='btn bg-maroon btn-flat' data-toggle='modal' data-target='#exampleModal' data-whatever='" . $arrS['MaNV'] . "'><i class='fa fa-trash'></i></button>";
                          } else {
                            echo "<button type='button' class='btn bg-maroon btn-flat' disabled><i class='fa fa-trash'></i></button>";
                          }
                          ?>
                        </td>
                      </tr>
                    <?php
                      $count++;
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

  <!-- Thêm script JavaScript để xử lý modal -->
  <script>
    $(document).ready(function() {
      $('#exampleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');
        var modal = $(this);
        modal.find('.modal-body input[name="idStaff"]').val(recipient);
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