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

  // Xử lý xóa kỷ luật nếu có - CHỈ ADMIN MỚI ĐƯỢC XÓA
  if(isset($_GET['del']) && $isAdmin) {
    $maKTKL = $_GET['del'];
    
    // Lấy thông tin kỷ luật để xóa file hình ảnh nếu có
    $queryKL = "SELECT * FROM KTKL WHERE MaKTKL = '$maKTKL' AND LoaiSuKien = 'Kỷ luật'";
    $resultKL = mysqli_query($conn, $queryKL);
    if(mysqli_num_rows($resultKL) > 0) {
      $kyLuat = mysqli_fetch_assoc($resultKL);
      
      // Xóa file hình ảnh nếu có
      if(!empty($kyLuat['HinhAnh'])) {
        $filePath = '../uploads/kyluat/' . $kyLuat['HinhAnh'];
        if(file_exists($filePath)) {
          unlink($filePath);
        }
      }
      
      // Thực hiện xóa kỷ luật
      $query = "DELETE FROM KTKL WHERE MaKTKL = '$maKTKL'";
      $result = mysqli_query($conn, $query);
      
      if($result) {
        echo "<script>
          alert('Xóa kỷ luật thành công!');
          window.location.href='danhsachkyluat.php?p=bonus-discipline&a=discipline';
        </script>";
        exit;
      } else {
        echo "<script>
          alert('Xóa kỷ luật thất bại! Lỗi: " . mysqli_error($conn) . "');
          window.location.href='danhsachkyluat.php?p=bonus-discipline&a=discipline';
        </script>";
        exit;
      }
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin kỷ luật!');
        window.location.href='danhsachkyluat.php?p=bonus-discipline&a=discipline';
      </script>";
      exit;
    }
  }

  // Lấy danh sách kỷ luật
  $queryKyLuat = "SELECT kl.*, nv.Hoten, nv.ChucVu, pb.TenPhongBan 
                  FROM KTKL kl 
                  JOIN NhanVien nv ON kl.MaNV = nv.MaNV
                  JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                  WHERE kl.LoaiSuKien = 'Kỷ luật'
                  ORDER BY kl.NgayApDung DESC";
  $resultKyLuat = mysqli_query($conn, $queryKyLuat);
  $arrKyLuat = array();
  while ($row = mysqli_fetch_array($resultKyLuat)) {
    $arrKyLuat[] = $row;
  }
  
  // Thống kê
  $queryTongTien = "SELECT SUM(SoTien) as TongTien FROM KTKL WHERE LoaiSuKien = 'Kỷ luật'";
  $resultTongTien = mysqli_query($conn, $queryTongTien);
  $rowTongTien = mysqli_fetch_assoc($resultTongTien);
  $tongTien = $rowTongTien['TongTien'] ?: 0;
  
  $queryTongNhanVien = "SELECT COUNT(DISTINCT MaNV) as TongNhanVien FROM KTKL WHERE LoaiSuKien = 'Kỷ luật'";
  $resultTongNhanVien = mysqli_query($conn, $queryTongNhanVien);
  $rowTongNhanVien = mysqli_fetch_assoc($resultTongNhanVien);
  $tongNhanVien = $rowTongNhanVien['TongNhanVien'];
  
  $queryThangNay = "SELECT SUM(SoTien) as TongTienThangNay FROM KTKL 
                    WHERE LoaiSuKien = 'Kỷ luật' 
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
      Danh sách kỷ luật
    </h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Danh sách kỷ luật</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <?php if(!$isAdmin): ?>
      <div class="alert alert-info alert-dismissible">
        <h4><i class="icon fa fa-info"></i> Thông báo!</h4>
        Bạn chỉ có quyền xem danh sách kỷ luật.
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?php echo count($arrKyLuat); ?></h3>
            <p>Tổng kỷ luật</p>
          </div>
          <div class="icon">
            <i class="fa fa-gavel"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
          <div class="inner">
            <h3><?php echo number_format($tongTien); ?></h3>
            <p>Tổng tiền phạt (VNĐ)</p>
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
            <p>Nhân viên bị kỷ luật</p>
          </div>
          <div class="icon">
            <i class="fa fa-users"></i>
          </div>
        </div>
      </div>
      <!-- ./col -->
      <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?php echo number_format($tongTienThangNay); ?></h3>
            <p>Tiền phạt tháng này</p>
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
            <h3 class="box-title">Danh sách kỷ luật</h3>
            <?php if($isAdmin): ?>
              <div class="box-tools pull-right">
                <a href="themkyluat.php?p=bonus-discipline&a=add-discipline" class="btn btn-danger btn-sm">
                  <i class="fa fa-plus"></i> Thêm kỷ luật mới
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
                    <th>Mã KL</th>
                    <th>Họ tên nhân viên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <th>Mô tả vi phạm</th>
                    <th class="text-right">Số tiền phạt</th>
                    <th class="text-center">Ngày áp dụng</th>
                    <th class="text-center">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    if(count($arrKyLuat) > 0) {
                      $count = 1;
                      foreach ($arrKyLuat as $kl) {
                        echo "<tr>";
                        echo "<td class='text-center'>".$count++."</td>";
                        echo "<td>".$kl['MaKTKL']."</td>";
                        echo "<td>".$kl['Hoten']."</td>";
                        echo "<td>".$kl['ChucVu']."</td>";
                        echo "<td>".$kl['TenPhongBan']."</td>";
                        echo "<td>".$kl['MoTa']."</td>";
                        echo "<td class='text-right'>".number_format($kl['SoTien'])." VNĐ</td>";
                        echo "<td class='text-center'>".date('d/m/Y', strtotime($kl['NgayApDung']))."</td>";
                        
                        // Hiển thị nút thao tác
                        echo "<td class='text-center'>";
                        
                        if($isAdmin) {
                          // Nếu là admin - hiển thị tất cả nút có thể click
                          // echo "<a href='chitietktkl.php?p=bonus-discipline&a=detail-discipline&id=".$kl['MaKTKL']."' class='btn btn-primary btn-sm' title='Chi tiết'>";
                          // echo "<i class='fa fa-info-circle'></i>";
                          // echo "</a> &nbsp;";
                          
                          // echo "<a href='suakyluat.php?p=bonus-discipline&a=edit-discipline&id=".$kl['MaKTKL']."' class='btn btn-warning btn-sm' title='Sửa'>";
                          // echo "<i class='fa fa-edit'></i>";
                          // echo "</a> &nbsp;";
                          
                          echo "<a href='danhsachkyluat.php?p=bonus-discipline&a=discipline&del=".$kl['MaKTKL']."' class='btn btn-danger btn-sm' title='Xóa' onclick='return confirm(\"Bạn có chắc chắn muốn xóa kỷ luật này không?\")'>";
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
                      echo "<tr><td colspan='9' class='text-center'>Không có dữ liệu kỷ luật</td></tr>";
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