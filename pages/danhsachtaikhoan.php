<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng - CHỈ CHO PHÉP ADMIN
  if(isset($row_acc) && $row_acc['Chucvu'] != 'admin') {
    echo "<script>
      alert('Bạn không có quyền truy cập trang này!');
      window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
  }

  // Xử lý xóa tài khoản nếu có
  if(isset($_GET['del'])) {
    $taiKhoanXoa = $_GET['del'];
    
    // Không cho phép xóa tài khoản của chính mình
    if($taiKhoanXoa == $_SESSION['username']) {
      echo "<script>
        alert('Không thể xóa tài khoản đang đăng nhập!');
        window.location.href='danhsachtaikhoan.php?p=account&a=list-account';
      </script>";
      exit;
    }
    
    // Thực hiện xóa tài khoản
    $query = "DELETE FROM TaiKhoan WHERE Taikhoan = '$taiKhoanXoa'";
    $result = mysqli_query($conn, $query);
    
    if($result) {
      echo "<script>
        alert('Xóa tài khoản thành công!');
        window.location.href='danhsachtaikhoan.php?p=account&a=list-account';
      </script>";
      exit;
    } else {
      echo "<script>
        alert('Xóa tài khoản thất bại! Lỗi: " . mysqli_error($conn) . "');
        window.location.href='danhsachtaikhoan.php?p=account&a=list-account';
      </script>";
      exit;
    }
  }

  // Lấy danh sách tài khoản
  $query = "SELECT tk.*, nv.Hoten, nv.ChucVu as ChucVuNV 
            FROM TaiKhoan tk 
            LEFT JOIN NhanVien nv ON tk.MaNV = nv.MaNV 
            ORDER BY tk.Chucvu DESC, nv.Hoten ASC";
  $result = mysqli_query($conn, $query);
  $danhSachTaiKhoan = array();
  while ($row = mysqli_fetch_array($result)) {
    $danhSachTaiKhoan[] = $row;
  }
  
  // Đếm số lượng theo phân loại
  $queryCountAdmin = "SELECT COUNT(*) as total FROM TaiKhoan WHERE Chucvu = 'admin'";
  $resultCountAdmin = mysqli_query($conn, $queryCountAdmin);
  $rowCountAdmin = mysqli_fetch_assoc($resultCountAdmin);
  $tongAdmin = $rowCountAdmin['total'];
  
  $queryCountNV = "SELECT COUNT(*) as total FROM TaiKhoan WHERE Chucvu != 'admin'";
  $resultCountNV = mysqli_query($conn, $queryCountNV);
  $rowCountNV = mysqli_fetch_assoc($resultCountNV);
  $tongNV = $rowCountNV['total'];
  
  $queryCountTotal = "SELECT COUNT(*) as total FROM TaiKhoan";
  $resultCountTotal = mysqli_query($conn, $queryCountTotal);
  $rowCountTotal = mysqli_fetch_assoc($resultCountTotal);
  $tongTaiKhoan = $rowCountTotal['total'];
  
  $queryNoAccount = "SELECT COUNT(*) as total FROM NhanVien nv WHERE nv.MaNV NOT IN (SELECT MaNV FROM TaiKhoan WHERE MaNV IS NOT NULL)";
  $resultNoAccount = mysqli_query($conn, $queryNoAccount);
  $rowNoAccount = mysqli_fetch_assoc($resultNoAccount);
  $tongNoAccount = $rowNoAccount['total'];
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Danh sách tài khoản
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Danh sách tài khoản</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo $tongTaiKhoan; ?></h3>
            <p>Tổng số tài khoản</p>
          </div>
          <div class="icon">
            <i class="fa fa-users"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
          <div class="inner">
            <h3><?php echo $tongAdmin; ?></h3>
            <p>Tài khoản admin</p>
          </div>
          <div class="icon">
            <i class="fa fa-user-secret"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?php echo $tongNV; ?></h3>
            <p>Tài khoản nhân viên</p>
          </div>
          <div class="icon">
            <i class="fa fa-user"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><?php echo $tongNoAccount; ?></h3>
            <p>Nhân viên chưa có tài khoản</p>
          </div>
          <div class="icon">
            <i class="fa fa-user-plus"></i>
          </div>
          <a href="dangky.php?p=account&a=add-account" class="small-box-footer">Thêm tài khoản <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <!-- ./col -->
    </div>
    <!-- /.row -->

    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Danh sách tài khoản người dùng</h3>
            <div class="box-tools pull-right">
              <a href="dangky.php?p=account&a=add-account" class="btn btn-primary btn-sm">
                <i class="fa fa-plus"></i> Thêm tài khoản mới
              </a>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="text-center">STT</th>
                    <th>Tài khoản</th>
                    <th>Tên nhân viên</th>
                    <th>Mã nhân viên</th>
                    <th>Chức vụ nhân viên</th>
                    <th class="text-center">Quyền tài khoản</th>
                    <th class="text-center">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    if(count($danhSachTaiKhoan) > 0) {
                      $count = 1;
                      foreach ($danhSachTaiKhoan as $tk) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td>".$tk['Taikhoan']."</td>";
                        echo "<td>".($tk['Hoten'] ?? 'Không có')."</td>";
                        echo "<td>".($tk['MaNV'] ?? 'Không có')."</td>";
                        echo "<td>".($tk['ChucVuNV'] ?? 'Không có')."</td>";
                        
                        // Hiển thị quyền tài khoản với màu sắc khác nhau
                        $quyenClass = '';
                        switch($tk['Chucvu']) {
                          case 'admin':
                            $quyenClass = 'label-danger';
                            break;
                          case 'manager':
                            $quyenClass = 'label-warning';
                            break;
                          default:
                            $quyenClass = 'label-primary';
                        }
                        echo "<td class='text-center'><span class='label ".$quyenClass."'>".$tk['Chucvu']."</span></td>";
                        
                        // Hiển thị nút thao tác
                        echo "<td class='text-center'>";
                        
                        // Không hiển thị nút xóa cho tài khoản đang đăng nhập
                        if($tk['Taikhoan'] != $_SESSION['username']) {
                          echo "<a href='doimatkhau.php?p=account&a=change-password&id=".$tk['Taikhoan']."' class='btn btn-primary btn-sm' title='Đổi mật khẩu'>";
                          echo "<i class='fa fa-key'></i>";
                          echo "</a> &nbsp;";
                          
                          // echo "<a href='suataikhoan.php?p=account&a=edit-account&id=".$tk['Taikhoan']."' class='btn btn-warning btn-sm' title='Sửa tài khoản'>";
                          // echo "<i class='fa fa-edit'></i>";
                          // echo "</a> &nbsp;";
                          
                          echo "<a href='danhsachtaikhoan.php?p=account&a=list-account&del=".$tk['Taikhoan']."' class='btn btn-danger btn-sm' title='Xóa tài khoản' onclick='return confirm(\"Bạn có chắc chắn muốn xóa tài khoản này không?\")'>";
                          echo "<i class='fa fa-trash'></i>";
                          echo "</a>";
                        } else {
                          echo "<a href='doimatkhau.php?p=account&a=change-password' class='btn btn-primary btn-sm' title='Đổi mật khẩu'>";
                          echo "<i class='fa fa-key'></i>";
                          echo "</a> &nbsp;";
                          
                          echo "<span class='label label-default'>Đang đăng nhập</span>";
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='7' class='text-center'>Không có dữ liệu tài khoản</td></tr>";
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