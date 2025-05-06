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

  // Lấy năm và tháng hiện tại
  $namHienTai = date('Y');
  $thangHienTai = date('m');
  
  // Xử lý lọc theo năm, tháng và phòng ban
  $filterNam = isset($_GET['nam']) ? $_GET['nam'] : $namHienTai;
  $filterThang = isset($_GET['thang']) ? $_GET['thang'] : $thangHienTai;
  $filterPhongBan = isset($_GET['phongban']) ? $_GET['phongban'] : '';
  $filterMaNV = isset($_GET['manv']) ? $_GET['manv'] : '';
  
  // Lấy danh sách phòng ban
  $queryPhongBan = "SELECT * FROM PhongBan ORDER BY TenPhongBan ASC";
  $resultPhongBan = mysqli_query($conn, $queryPhongBan);
  $arrPhongBan = array();
  while ($rowPhongBan = mysqli_fetch_array($resultPhongBan)) {
    $arrPhongBan[] = $rowPhongBan;
  }
  
  // Lấy danh sách năm từ bảng bangluongthang
  $queryNam = "SELECT DISTINCT Nam FROM bangluongthang ORDER BY Nam DESC";
  $resultNam = mysqli_query($conn, $queryNam);
  $arrNam = array();
  while ($rowNam = mysqli_fetch_array($resultNam)) {
    $arrNam[] = $rowNam;
  }
  
  // Lấy danh sách tháng từ bảng bangluongthang
  $queryThang = "SELECT DISTINCT Thang FROM bangluongthang ORDER BY Thang ASC";
  $resultThang = mysqli_query($conn, $queryThang);
  $arrThang = array();
  while ($rowThang = mysqli_fetch_array($resultThang)) {
    $arrThang[] = $rowThang;
  }
  
  // Lấy danh sách nhân viên
  $queryNhanVien = "SELECT nv.MaNV, nv.Hoten FROM NhanVien nv ORDER BY nv.Hoten ASC";
  $resultNhanVien = mysqli_query($conn, $queryNhanVien);
  $arrNhanVien = array();
  while ($rowNhanVien = mysqli_fetch_array($resultNhanVien)) {
    $arrNhanVien[] = $rowNhanVien;
  }
  
  // Xây dựng câu truy vấn với điều kiện lọc
  $query = "SELECT bl.MaBangLuong, bl.MaNV, nv.Hoten, pb.TenPhongBan, bl.Thang, bl.Nam, 
                 bl.LuongCoBan, bl.NgayCongThucTe, bl.LuongTheoNgayCong, bl.GioTangCa, bl.LuongTangCa, 
                 bl.PhuCap, bl.Thuong, bl.Phat, bl.BHXH, bl.BHYT, bl.ThueTNCN, bl.ThucLanh 
            FROM bangluongthang bl
            JOIN NhanVien nv ON bl.MaNV = nv.MaNV
            JOIN PhongBan pb ON nv.MaPb = pb.MaPb
            WHERE bl.Nam = '$filterNam'";
  
  // Thêm điều kiện lọc theo tháng nếu có
  if(!empty($filterThang)) {
    $query .= " AND bl.Thang = '$filterThang'";
  }
  
  // Thêm điều kiện lọc theo phòng ban nếu có
  if(!empty($filterPhongBan)) {
    $query .= " AND pb.MaPb = '$filterPhongBan'";
  }
  
  // Thêm điều kiện lọc theo nhân viên nếu có
  if(!empty($filterMaNV)) {
    $query .= " AND bl.MaNV = '$filterMaNV'";
  }
  
  // Sắp xếp dữ liệu
  $query .= " ORDER BY bl.Nam DESC, bl.Thang DESC, nv.Hoten ASC";
  
  $result = mysqli_query($conn, $query);
  $arrBangLuong = array();
  while ($row = mysqli_fetch_array($result)) {
    $arrBangLuong[] = $row;
  }
  
  // Thống kê tổng quát
  $queryThongKe = "SELECT 
                      COUNT(DISTINCT MaNV) as TongNhanVien,
                      SUM(ThucLanh) as TongLuongThucLanh,
                      AVG(ThucLanh) as LuongTrungBinh,
                      MAX(ThucLanh) as LuongCaoNhat,
                      MIN(ThucLanh) as LuongThapNhat,
                      SUM(LuongCoBan) as TongLuongCoBan,
                      SUM(PhuCap) as TongPhuCap,
                      SUM(Thuong) as TongThuong,
                      SUM(Phat) as TongPhat,
                      SUM(BHXH) as TongBHXH,
                      SUM(BHYT) as TongBHYT,
                      SUM(ThueTNCN) as TongThueTNCN
                  FROM bangluongthang
                  WHERE Nam = '$filterNam'";
  
  if(!empty($filterThang)) {
    $queryThongKe .= " AND Thang = '$filterThang'";
  }
  
  if(!empty($filterMaNV)) {
    $queryThongKe .= " AND MaNV = '$filterMaNV'";
  }
  
  if(!empty($filterPhongBan)) {
    $queryThongKe .= " AND MaNV IN (SELECT MaNV FROM NhanVien WHERE MaPb = '$filterPhongBan')";
  }
  
  $resultThongKe = mysqli_query($conn, $queryThongKe);
  $thongKe = mysqli_fetch_assoc($resultThongKe);
  
  // Thống kê theo tháng trong năm
  $queryThongKeThang = "SELECT Thang, 
                           COUNT(DISTINCT MaNV) as SoNhanVien,
                           SUM(ThucLanh) as TongThucLanh,
                           AVG(ThucLanh) as TrungBinhThucLanh
                        FROM bangluongthang
                        WHERE Nam = '$filterNam'";
                        
  if(!empty($filterPhongBan)) {
    $queryThongKeThang .= " AND MaNV IN (SELECT MaNV FROM NhanVien WHERE MaPb = '$filterPhongBan')";
  }
  
  if(!empty($filterMaNV)) {
    $queryThongKeThang .= " AND MaNV = '$filterMaNV'";
  }
  
  $queryThongKeThang .= " GROUP BY Thang ORDER BY Thang ASC";
  
  $resultThongKeThang = mysqli_query($conn, $queryThongKeThang);
  $arrThongKeThang = array();
  while ($row = mysqli_fetch_array($resultThongKeThang)) {
    $arrThongKeThang[] = $row;
  }
  
  // Định dạng tiền tệ VND
  function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
  }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>Thống kê lương nhân viên</h1>
    <ol class="breadcrumb">
      <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
      <li class="active">Thống kê lương</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
          <div class="box-header">
            <h3 class="box-title">Thống kê lương nhân viên</h3>
            <div class="box-tools pull-right">
              <a href="danhsachbangluong.php?p=salary&a=list" class="btn btn-primary btn-sm">
                <i class="fa fa-list"></i> Danh sách bảng lương
              </a>
            </div>
          </div>
          <!-- /.box-header -->
          <div class="box-body">
            <!-- Form lọc -->
            <div class="row">
              <div class="col-md-12">
                <form method="GET" action="" class="form-inline" style="margin-bottom: 20px;">
                  <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
                  <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
                  
                  <div class="form-group">
                    <label for="nam">Năm:</label>
                    <select class="form-control" id="nam" name="nam">
                      <?php foreach($arrNam as $nam): ?>
                        <option value="<?php echo $nam['Nam']; ?>" <?php echo ($filterNam == $nam['Nam']) ? 'selected' : ''; ?>>
                          <?php echo $nam['Nam']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="form-group" style="margin-left: 15px;">
                    <label for="thang">Tháng:</label>
                    <select class="form-control" id="thang" name="thang">
                      <option value="">-- Tất cả các tháng --</option>
                      <?php foreach($arrThang as $thang): ?>
                        <option value="<?php echo $thang['Thang']; ?>" <?php echo ($filterThang == $thang['Thang']) ? 'selected' : ''; ?>>
                          Tháng <?php echo $thang['Thang']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="form-group" style="margin-left: 15px;">
                    <label for="phongban">Phòng ban:</label>
                    <select name="phongban" id="phongban" class="form-control">
                      <option value="">-- Tất cả phòng ban --</option>
                      <?php foreach($arrPhongBan as $pb): ?>
                        <option value="<?php echo $pb['MaPb']; ?>" <?php echo ($filterPhongBan == $pb['MaPb']) ? 'selected' : ''; ?>>
                          <?php echo $pb['TenPhongBan']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <div class="form-group" style="margin-left: 15px;">
                    <label for="manv">Nhân viên:</label>
                    <select name="manv" id="manv" class="form-control">
                      <option value="">-- Tất cả nhân viên --</option>
                      <?php foreach($arrNhanVien as $nv): ?>
                        <option value="<?php echo $nv['MaNV']; ?>" <?php echo ($filterMaNV == $nv['MaNV']) ? 'selected' : ''; ?>>
                          <?php echo $nv['MaNV'] . ' - ' . $nv['Hoten']; ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  
                  <button type="submit" class="btn btn-primary" style="margin-left: 15px;">
                    <i class="fa fa-filter"></i> Lọc
                  </button>
                  
                  <?php if($filterNam != $namHienTai || $filterThang != $thangHienTai || !empty($filterPhongBan) || !empty($filterMaNV)): ?>
                    <a href="?p=<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>&a=<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>" class="btn btn-default" style="margin-left: 10px;">
                      <i class="fa fa-refresh"></i> Đặt lại
                    </a>
                  <?php endif; ?>
                </form>
              </div>
            </div>
            
            <!-- Thống kê tổng quan -->
            <div class="row">
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua">
                  <span class="info-box-icon"><i class="fa fa-users"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng nhân viên</span>
                    <span class="info-box-number"><?php echo $thongKe['TongNhanVien']; ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green">
                  <span class="info-box-icon"><i class="fa fa-money"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng lương thực lãnh</span>
                    <span class="info-box-number"><?php echo formatCurrency($thongKe['TongLuongThucLanh']); ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow">
                  <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Lương trung bình</span>
                    <span class="info-box-number"><?php echo formatCurrency($thongKe['LuongTrungBinh']); ?></span>
                  </div>
                </div>
              </div>
              
              <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red">
                  <span class="info-box-icon"><i class="fa fa-trophy"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Lương cao nhất</span>
                    <span class="info-box-number"><?php echo formatCurrency($thongKe['LuongCaoNhat']); ?></span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Thông tin chi tiết về lương -->
            <div class="row">
              <div class="col-md-6">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Chi tiết thống kê lương</h3>
                  </div>
                  <div class="box-body">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 50%">Tổng lương cơ bản</th>
                        <td><?php echo formatCurrency($thongKe['TongLuongCoBan']); ?></td>
                      </tr>
                      <tr>
                        <th>Tổng phụ cấp</th>
                        <td><?php echo formatCurrency($thongKe['TongPhuCap']); ?></td>
                      </tr>
                      <tr>
                        <th>Tổng thưởng</th>
                        <td><?php echo formatCurrency($thongKe['TongThuong']); ?></td>
                      </tr>
                      <tr>
                        <th>Tổng phạt</th>
                        <td><?php echo formatCurrency($thongKe['TongPhat']); ?></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="box box-danger">
                  <div class="box-header with-border">
                    <h3 class="box-title">Chi tiết các khoản khấu trừ</h3>
                  </div>
                  <div class="box-body">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 50%">Tổng BHXH</th>
                        <td><?php echo formatCurrency($thongKe['TongBHXH']); ?></td>
                      </tr>
                      <tr>
                        <th>Tổng BHYT</th>
                        <td><?php echo formatCurrency($thongKe['TongBHYT']); ?></td>
                      </tr>
                      <tr>
                        <th>Tổng thuế TNCN</th>
                        <td><?php echo formatCurrency($thongKe['TongThueTNCN']); ?></td>
                      </tr>
                      <tr>
                        <th>Lương thấp nhất</th>
                        <td><?php echo formatCurrency($thongKe['LuongThapNhat']); ?></td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Biểu đồ thống kê theo tháng -->
            <?php if(count($arrThongKeThang) > 0 && empty($filterThang)): ?>
            <div class="row">
              <div class="col-md-12">
                <div class="box box-success">
                  <div class="box-header with-border">
                    <h3 class="box-title">Biểu đồ thống kê lương theo tháng năm <?php echo $filterNam; ?></h3>
                  </div>
                  <div class="box-body">
                    <div id="chartLuong" style="height: 300px;"></div>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
            
            <!-- Bảng hiển thị dữ liệu -->
            <div class="box box-info">
              <div class="box-header with-border">
                <h3 class="box-title">Chi tiết lương nhân viên</h3>
                <div class="box-tools pull-right">
                  <!-- <button type="button" class="btn btn-success btn-sm" id="btn-print">
                    <i class="fa fa-print"></i> In báo cáo
                  </button> -->
                  <button type="button" class="btn btn-primary btn-sm" id="btn-excel">
                    <i class="fa fa-file-excel-o"></i> Xuất Excel
                  </button>
                </div>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th class="text-center">STT</th>
                        <th class="text-center">Mã NV</th>
                        <th>Họ tên</th>
                        <th>Phòng ban</th>
                        <th class="text-center">Tháng/Năm</th>
                        <th class="text-right">Lương cơ bản</th>
                        <th class="text-center">Ngày công</th>
                        <th class="text-right">Lương ngày công</th>
                        <th class="text-right">Phụ cấp</th>
                        <th class="text-right">Thưởng</th>
                        <th class="text-right">Phạt</th>
                        <th class="text-right">Thực lãnh</th>
                        <th class="text-center">Chi tiết</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                        if(count($arrBangLuong) > 0) {
                          $count = 1;
                          foreach ($arrBangLuong as $bangLuong) {
                            echo "<tr>";
                            echo "<td class='text-center'>".$count++."</td>";
                            echo "<td class='text-center'>".$bangLuong['MaNV']."</td>";
                            echo "<td>".$bangLuong['Hoten']."</td>";
                            echo "<td>".$bangLuong['TenPhongBan']."</td>";
                            echo "<td class='text-center'>".$bangLuong['Thang']."/".$bangLuong['Nam']."</td>";
                            echo "<td class='text-right'>".formatCurrency($bangLuong['LuongCoBan'])."</td>";
                            echo "<td class='text-center'>".$bangLuong['NgayCongThucTe']."</td>";
                            echo "<td class='text-right'>".formatCurrency($bangLuong['LuongTheoNgayCong'])."</td>";
                            echo "<td class='text-right'>".formatCurrency($bangLuong['PhuCap'])."</td>";
                            echo "<td class='text-right'>".formatCurrency($bangLuong['Thuong'])."</td>";
                            echo "<td class='text-right'>".formatCurrency($bangLuong['Phat'])."</td>";
                            echo "<td class='text-right'><strong class='text-success'>".formatCurrency($bangLuong['ThucLanh'])."</strong></td>";
                            
                            // Nút xem chi tiết
                            echo "<td class='text-center'>";
                            echo "<a href='chitietluong.php?p=salary&a=detail&id=".$bangLuong['MaNV']."&thang=".$bangLuong['Thang']."&nam=".$bangLuong['Nam']."' class='btn btn-info btn-sm'>";
                            echo "<i class='fa fa-info-circle'></i> Chi tiết";
                            echo "</a>";
                            echo "</td>";
                            
                            echo "</tr>";
                          }
                        } else {
                          echo "<tr><td colspan='13' class='text-center'>Không có dữ liệu lương";
                          if(!empty($filterThang)) {
                            echo " tháng ".$filterThang;
                          }
                          echo " năm ".$filterNam."</td></tr>";
                        }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
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

<!-- Vô hiệu hóa CKEditor tự động khởi tạo nếu có -->
<script>
  if(typeof CKEDITOR !== 'undefined') {
    CKEDITOR.disableAutoInline = true;
  }
</script>

<!-- Thêm script JavaScript với vanilla JS -->
<script>
  // Đảm bảo DOM đã tải trước khi thực thi JavaScript
  document.addEventListener('DOMContentLoaded', function() {
    // Xử lý nút in báo cáo
    // document.getElementById('btn-print').addEventListener('click', function() {
    //   window.print();
    // });
    
    // Xử lý nút xuất Excel
    document.getElementById('btn-excel').addEventListener('click', function() {
      // Hiển thị thông báo đang xử lý
      document.body.style.cursor = 'wait';
      
      var filterParams = '?nam=<?php echo $filterNam; ?>';
      
      <?php if(!empty($filterThang)): ?>
      filterParams += '&thang=<?php echo $filterThang; ?>';
      <?php endif; ?>
      
      <?php if(!empty($filterPhongBan)): ?>
      filterParams += '&phongban=<?php echo $filterPhongBan; ?>';
      <?php endif; ?>
      
      <?php if(!empty($filterMaNV)): ?>
      filterParams += '&manv=<?php echo $filterMaNV; ?>';
      <?php endif; ?>
      
      // Điều hướng tới file export_luong.php
      window.location.href = 'export_luong.php' + filterParams;
      
      // Khôi phục con trỏ sau 3 giây
      setTimeout(function() {
        document.body.style.cursor = 'default';
      }, 3000);
    });
    
    <?php if(count($arrThongKeThang) > 0 && empty($filterThang)): ?>
    // Khởi tạo biểu đồ nếu phần tử canvas tồn tại và Chart.js được tải
    var chartElement = document.getElementById('chartLuong');
    if (chartElement && typeof Chart !== 'undefined') {
      var ctx = chartElement.getContext('2d');
      
      var thangLabels = [<?php 
        foreach($arrThongKeThang as $item) {
          echo "'Tháng ".$item['Thang']."', ";
        }
      ?>];
      
      var tongThucLanh = [<?php 
        foreach($arrThongKeThang as $item) {
          echo $item['TongThucLanh'].", ";
        }
      ?>];
      
      var luongTrungBinh = [<?php 
        foreach($arrThongKeThang as $item) {
          echo $item['TrungBinhThucLanh'].", ";
        }
      ?>];
      
      var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: thangLabels,
          datasets: [
            {
              label: 'Tổng lương thực lãnh',
              backgroundColor: 'rgba(60,141,188,0.5)',
              borderColor: 'rgba(60,141,188,0.8)',
              borderWidth: 1,
              data: tongThucLanh
            },
            {
              label: 'Lương trung bình',
              backgroundColor: 'rgba(210, 214, 222, 0.5)',
              borderColor: 'rgba(210, 214, 222, 0.8)',
              borderWidth: 1,
              data: luongTrungBinh
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                label: function(context) {
                  var label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    label += new Intl.NumberFormat('vi-VN', { 
                      style: 'currency', 
                      currency: 'VND',
                      maximumFractionDigits: 0
                    }).format(context.parsed.y);
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    }
    <?php endif; ?>
  });
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