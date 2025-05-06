<?php 
// create session
session_start();

if(isset($_SESSION['username'])) {
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // Kiểm tra quyền của người dùng
  if(isset($row_acc) && $row_acc['Chucvu'] == 'admin') {
    $isAdmin = true;
  } else {
    $isAdmin = false;
  }

  // Xử lý xóa khen thưởng nếu có - CHỈ ADMIN MỚI ĐƯỢC XÓA
  if(isset($_GET['del']) && $isAdmin) {
    $maKTKL = $_GET['del'];
    
    // Lấy thông tin khen thưởng để xóa file hình ảnh nếu có
    $queryKT = "SELECT * FROM KTKL WHERE MaKTKL = '$maKTKL' AND LoaiSuKien = 'Khen thưởng'";
    $resultKT = mysqli_query($conn, $queryKT);
    if(mysqli_num_rows($resultKT) > 0) {
      $khenThuong = mysqli_fetch_assoc($resultKT);
      
      // Xóa file hình ảnh nếu có
      if(!empty($khenThuong['HinhAnh'])) {
        $filePath = '../uploads/khenthuong/' . $khenThuong['HinhAnh'];
        if(file_exists($filePath)) {
          unlink($filePath);
        }
      }
      
      // Thực hiện xóa khen thưởng
      $query = "DELETE FROM KTKL WHERE MaKTKL = '$maKTKL'";
      $result = mysqli_query($conn, $query);
      
      if($result) {
        echo "<script>
          alert('Xóa khen thưởng thành công!');
          window.location.href='danhsachkhenthuong.php?p=bonus-discipline&a=bonus';
        </script>";
        exit;
      } else {
        echo "<script>
          alert('Xóa khen thưởng thất bại! Lỗi: " . mysqli_error($conn) . "');
          window.location.href='danhsachkhenthuong.php?p=bonus-discipline&a=bonus';
        </script>";
        exit;
      }
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin khen thưởng!');
        window.location.href='danhsachkhenthuong.php?p=bonus-discipline&a=bonus';
      </script>";
      exit;
    }
  }

  // Lấy danh sách khen thưởng
  $queryKhenThuong = "SELECT kt.*, nv.Hoten, nv.ChucVu, pb.TenPhongBan 
                      FROM KTKL kt 
                      JOIN NhanVien nv ON kt.MaNV = nv.MaNV
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                      WHERE kt.LoaiSuKien = 'Khen thưởng'
                      ORDER BY kt.NgayApDung DESC";
  $resultKhenThuong = mysqli_query($conn, $queryKhenThuong);
  $arrKhenThuong = array();
  while ($row = mysqli_fetch_array($resultKhenThuong)) {
    $arrKhenThuong[] = $row;
  }
  
  // Thống kê
  $queryTongTien = "SELECT SUM(SoTien) as TongTien FROM KTKL WHERE LoaiSuKien = 'Khen thưởng'";
  $resultTongTien = mysqli_query($conn, $queryTongTien);
  $rowTongTien = mysqli_fetch_assoc($resultTongTien);
  $tongTien = $rowTongTien['TongTien'] ?: 0;
  
  $queryTongNhanVien = "SELECT COUNT(DISTINCT MaNV) as TongNhanVien FROM KTKL WHERE LoaiSuKien = 'Khen thưởng'";
  $resultTongNhanVien = mysqli_query($conn, $queryTongNhanVien);
  $rowTongNhanVien = mysqli_fetch_assoc($resultTongNhanVien);
  $tongNhanVien = $rowTongNhanVien['TongNhanVien'];
  
  $queryThangNay = "SELECT SUM(SoTien) as TongTienThangNay FROM KTKL 
                    WHERE LoaiSuKien = 'Khen thưởng' 
                    AND MONTH(NgayApDung) = MONTH(CURRENT_DATE()) 
                    AND YEAR(NgayApDung) = YEAR(CURRENT_DATE())";
  $resultThangNay = mysqli_query($conn, $queryThangNay);
  $rowThangNay = mysqli_fetch_assoc($resultThangNay);
  $tongTienThangNay = $rowThangNay['TongTienThangNay'] ?: 0;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Danh sách khen thưởng
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Danh sách khen thưởng</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <?php if(!$isAdmin): ?>
      <div class="alert alert-info alert-dismissible">
        <h4><i class="icon fa fa-info"></i> Thông báo!</h4>
        Bạn chỉ có quyền xem danh sách khen thưởng.
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo count($arrKhenThuong); ?></h3>
            <p>Tổng khen thưởng</p>
          </div>
          <div class="icon">
            <i class="fa fa-trophy"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?php echo number_format($tongTien); ?></h3>
            <p>Tổng tiền (VNĐ)</p>
          </div>
          <div class="icon">
            <i class="fa fa-money"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><?php echo $tongNhanVien; ?></h3>
            <p>Nhân viên được khen</p>
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
            <h3><?php echo number_format($tongTienThangNay); ?></h3>
            <p>Tiền thưởng tháng này</p>
          </div>
          <div class="icon">
            <i class="fa fa-calendar"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
    </div>
    <!-- /.row -->

    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Danh sách khen thưởng</h3>
            <?php if($isAdmin): ?>
              <div class="box-tools pull-right">
                <a href="themkhenthuong.php?p=bonus-discipline&a=add-bonus" class="btn btn-success btn-sm">
                  <i class="fa fa-plus"></i> Thêm khen thưởng mới
                </a>
              </div>
            <?php endif; ?>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <div class="table-responsive">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th class="text-center">STT</th>
                    <th>Mã KT</th>
                    <th>Họ tên nhân viên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <th>Mô tả khen thưởng</th>
                    <th class="text-right">Số tiền</th>
                    <th class="text-center">Ngày áp dụng</th>
                    <th class="text-center">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    if(count($arrKhenThuong) > 0) {
                      $count = 1;
                      foreach ($arrKhenThuong as $kt) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td>".$kt['MaKTKL']."</td>";
                        echo "<td>".$kt['Hoten']."</td>";
                        echo "<td>".$kt['ChucVu']."</td>";
                        echo "<td>".$kt['TenPhongBan']."</td>";
                        echo "<td>".$kt['MoTa']."</td>";
                        echo "<td class='text-right'>".number_format($kt['SoTien'])." VNĐ</td>";
                        echo "<td class='text-center'>".date('d/m/Y', strtotime($kt['NgayApDung']))."</td>";
                        
                        // Hiển thị nút thao tác
                        echo "<td class='text-center'>";
                        
                        if($isAdmin) {
                          // Nếu là admin - hiển thị tất cả nút có thể click
                          // echo "<a href='chitietktkl.php?p=bonus-discipline&a=detail-bonus&id=".$kt['MaKTKL']."' class='btn btn-primary btn-sm' title='Chi tiết'>";
                          // echo "<i class='fa fa-info-circle'></i>";
                          // echo "</a> &nbsp;";
                          
                          // echo "<a href='suakhenthuong.php?p=bonus-discipline&a=edit-bonus&id=".$kt['MaKTKL']."' class='btn btn-warning btn-sm' title='Sửa'>";
                          // echo "<i class='fa fa-edit'></i>";
                          // echo "</a> &nbsp;";
                          
                          echo "<a href='danhsachkhenthuong.php?p=bonus-discipline&a=bonus&del=".$kt['MaKTKL']."' class='btn btn-danger btn-sm' title='Xóa' onclick='return confirm(\"Bạn có chắc chắn muốn xóa khen thưởng này không?\")'>";
                          echo "<i class='fa fa-trash'></i>";
                          echo "</a>";
                        } else {
                          // Nếu không phải admin - hiển thị nút nhưng disable
                          // echo "<button class='btn btn-primary btn-sm' title='Chi tiết' disabled>";
                          // echo "<i class='fa fa-info-circle'></i>";
                          // echo "</button> &nbsp;";
                          
                          // echo "<button class='btn btn-warning btn-sm' title='Sửa' disabled>";
                          // echo "<i class='fa fa-edit'></i>";
                          // echo "</button> &nbsp;";
                          
                          echo "<button class='btn btn-danger btn-sm' title='Xóa' disabled>";
                          echo "<i class='fa fa-trash'></i>";
                          echo "</button>";
                        }
                        
                        echo "</td>";
                        echo "</tr>";
                      }
                    } else {
                      echo "<tr><td colspan='9' class='text-center'>Không có dữ liệu khen thưởng</td></tr>";
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

<script>
  $(function () {
    $('#example1').DataTable({
      'paging'      : true,
      'lengthChange': true,
      'searching'   : true,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })
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