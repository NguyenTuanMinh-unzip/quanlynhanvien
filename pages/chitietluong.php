  <?php 
  // create session
  session_start();

  if(isset($_SESSION['username'])) {
    // include file
    include('../layouts/header.php');
    include('../layouts/topbar.php');
    include('../layouts/sidebar.php');

    // Mặc định hiển thị thông tin lương của người đang đăng nhập
    $maNhanVien = isset($row_acc['MaNV']) ? $row_acc['MaNV'] : '';
    
    // Nếu là admin và có tham số ID, hiển thị thông tin của nhân viên được chọn
    if(isset($row_acc) && $row_acc['Chucvu'] == 'admin' && isset($_GET['id'])) {
      $maNhanVien = $_GET['id'];
    }
    
    // Nếu không phải admin mà cố tình truy cập thông tin của người khác, chuyển về trang thông tin của mình
    if(isset($row_acc) && $row_acc['Chucvu'] != 'admin' && isset($_GET['id']) && $_GET['id'] != $row_acc['MaNV']) {
      echo "<script>
        alert('Bạn chỉ có thể xem thông tin lương của mình!');
        window.location.href='chitietluong.php';
      </script>";
      exit;
    }
    
    // Kiểm tra nếu không có mã nhân viên
    if(empty($maNhanVien)) {
      echo "<script>
        alert('Không tìm thấy thông tin nhân viên!');
        window.location.href='index.php?p=index&a=statistic';
      </script>";
      exit;
    }
    
    // Lấy thông tin nhân viên
    $queryNhanVien = "SELECT nv.*, pb.TenPhongBan FROM NhanVien nv 
                      JOIN PhongBan pb ON nv.MaPb = pb.MaPb
                      WHERE nv.MaNV = '$maNhanVien'";
    $resultNhanVien = mysqli_query($conn, $queryNhanVien);
    
    if(mysqli_num_rows($resultNhanVien) > 0) {
      $nhanVien = mysqli_fetch_assoc($resultNhanVien);
    } else {
      echo "<script>
        alert('Không tìm thấy thông tin nhân viên!');
        window.location.href='index.php?p=index&a=statistic';
      </script>";
      exit;
    }
    
    // Lấy thông tin bảng lương của nhân viên
    $queryBangLuong = "SELECT * FROM Luong WHERE MaNV = '$maNhanVien'";
    $resultBangLuong = mysqli_query($conn, $queryBangLuong);
    
    if(mysqli_num_rows($resultBangLuong) > 0) {
      $bangLuong = mysqli_fetch_assoc($resultBangLuong);
    } else {
      $bangLuong = array(
        'LuongCoBan' => 0,
        'PhuCapAnTrua' => 0,
        'PhuCapDiLai' => 0,
        'BHXH' => 0,
        'BHYT' => 0,
        'ThueTNCN' => 0
      );
    }
    
    // Lấy tháng năm hiện tại
    $thangHienTai = date('m');
    $namHienTai = date('Y');
    
    // Xử lý tham số tháng/năm từ URL nếu có
    $thang = isset($_GET['thang']) ? $_GET['thang'] : $thangHienTai;
    $nam = isset($_GET['nam']) ? $_GET['nam'] : $namHienTai;
    
    // Lấy thông tin bảng lương tháng của nhân viên
    $queryBangLuongThang = "SELECT * FROM BangLuongThang 
                            WHERE MaNV = '$maNhanVien' 
                            AND Thang = '$thang' 
                            AND Nam = '$nam'";
    $resultBangLuongThang = mysqli_query($conn, $queryBangLuongThang);
    
    if(mysqli_num_rows($resultBangLuongThang) > 0) {
      $bangLuongThang = mysqli_fetch_assoc($resultBangLuongThang);
    } else {
      $bangLuongThang = null;
    }
    
    // Lấy dữ liệu chấm công theo tháng
    $ngayDauThang = date("$nam-$thang-01");
    $ngayCuoiThang = date("Y-m-t", strtotime($ngayDauThang));
    
    $queryChamCong = "SELECT * FROM ChamCong 
                      WHERE MaNV = '$maNhanVien' 
                      AND Ngay BETWEEN '$ngayDauThang' AND '$ngayCuoiThang'
                      ORDER BY Ngay ASC";
    $resultChamCong = mysqli_query($conn, $queryChamCong);
    $arrChamCong = array();
    $tongNgayCong = 0;
    $tongGioLam = 0;
    $tongGioTangCa = 0;
    $soNgayDiMuon = 0;
    $soNgayVangMat = 0;
    
    while ($row = mysqli_fetch_array($resultChamCong)) {
      $arrChamCong[] = $row;
      
      // Đếm các loại trạng thái
      if($row['TrangThai'] == 'Đã hoàn thành') {
        $tongNgayCong++;
        $tongGioLam += $row['SoGioLam'];
        $tongGioTangCa += $row['GioTangCa'];
      } else if($row['TrangThai'] == 'Đi muộn - Đã hoàn thành') {
        $tongNgayCong++;
        $tongGioLam += $row['SoGioLam'];
        $tongGioTangCa += $row['GioTangCa'];
        $soNgayDiMuon++;
      } else if($row['TrangThai'] == 'Vắng mặt') {
        $soNgayVangMat++;
      }
    }
    
    // Tính phạt đi muộn (100,000 VND cho mỗi ngày đi muộn)
    $tienPhatDiMuon = $soNgayDiMuon * 100000;
    
    // Lấy thông tin thưởng/phạt trong tháng
    $queryKTKL = "SELECT * FROM KTKL 
                WHERE MaNV = '$maNhanVien' 
                AND MONTH(NgayApDung) = '$thang' 
                AND YEAR(NgayApDung) = '$nam'
                ORDER BY NgayApDung ASC";
    $resultKTKL = mysqli_query($conn, $queryKTKL);
    $arrKTKL = array();
    $tongThuong = 0;
    $tongPhat = 0;
    
    while ($row = mysqli_fetch_array($resultKTKL)) {
      $arrKTKL[] = $row;
      if($row['LoaiSuKien'] == 'Khen thưởng') {
        $tongThuong += $row['SoTien'];
      } else if($row['LoaiSuKien'] == 'Kỷ luật') {
        $tongPhat += $row['SoTien'];
      }
    }
    
    // Cộng tiền phạt đi muộn vào tổng tiền phạt
    $tongPhat += $tienPhatDiMuon;
    
    // Lấy lịch sử bảng lương các tháng gần đây
    $queryLichSuLuong = "SELECT * FROM BangLuongThang 
                        WHERE MaNV = '$maNhanVien' 
                        ORDER BY Nam DESC, Thang DESC 
                        LIMIT 6";
    $resultLichSuLuong = mysqli_query($conn, $queryLichSuLuong);
    $arrLichSuLuong = array();
    
    while ($row = mysqli_fetch_array($resultLichSuLuong)) {
      $arrLichSuLuong[] = $row;
    }
  ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Chi tiết lương nhân viên
        <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
          <small><a href="danhsachbangluong.php?p=salary&a=list" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Quay lại danh sách</a></small>
        <?php endif; ?>
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
        <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
          <li><a href="danhsachbangluong.php?p=salary&a=list">Bảng lương</a></li>
        <?php endif; ?>
        <li class="active">Chi tiết lương</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-3">
          <!-- Profile Image -->
          <div class="box box-primary">
            <div class="box-body box-profile">
              <img class="profile-user-img img-responsive img-circle" src="../uploads/avatars/default-avatar.png" alt="User profile picture">
              <h3 class="profile-username text-center"><?php echo $nhanVien['Hoten']; ?></h3>
              <p class="text-muted text-center"><?php echo $nhanVien['ChucVu']; ?></p>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>Mã nhân viên</b> <a class="pull-right"><?php echo $nhanVien['MaNV']; ?></a>
                </li>
                <li class="list-group-item">
                  <b>Phòng ban</b> <a class="pull-right"><?php echo $nhanVien['TenPhongBan']; ?></a>
                </li>
                <li class="list-group-item">
                  <b>Ngày vào làm</b> <a class="pull-right"><?php echo date('d/m/Y', strtotime($nhanVien['NgayVaoLam'])); ?></a>
                </li>
                <li class="list-group-item">
                  <b>Lương cơ bản</b> <a class="pull-right"><?php echo number_format($bangLuong['LuongCoBan']); ?> VNĐ</a>
                </li>
              </ul>
              
              <form method="GET" action="" class="form-inline text-center" style="margin-top: 15px;">
                <input type="hidden" name="p" value="<?php echo isset($_GET['p']) ? $_GET['p'] : ''; ?>">
                <input type="hidden" name="a" value="<?php echo isset($_GET['a']) ? $_GET['a'] : ''; ?>">
                <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin' && isset($_GET['id'])): ?>
                  <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                  <select name="thang" class="form-control">
                    <?php for($i = 1; $i <= 12; $i++): ?>
                      <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo ($thang == str_pad($i, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                        Tháng <?php echo $i; ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </div>
                
                <div class="form-group" style="margin-left: 5px;">
                  <select name="nam" class="form-control">
                    <?php for($i = $namHienTai - 2; $i <= $namHienTai; $i++): ?>
                      <option value="<?php echo $i; ?>" <?php echo ($nam == $i) ? 'selected' : ''; ?>>
                        Năm <?php echo $i; ?>
                      </option>
                    <?php endfor; ?>
                  </select>
                </div>
                
                <button type="submit" class="btn btn-default" style="margin-left: 5px;">
                  <i class="fa fa-search"></i> Xem
                </button>
              </form>
            </div>
          </div>
          
          <!-- Lịch sử lương -->
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Lịch sử lương</h3>
            </div>
            <div class="box-body no-padding">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Tháng/Năm</th>
                    <th>Thực lãnh</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(count($arrLichSuLuong) > 0): ?>
                    <?php foreach($arrLichSuLuong as $luong): ?>
                      <tr <?php echo ($luong['Thang'] == $thang && $luong['Nam'] == $nam) ? 'class="success"' : ''; ?>>
                        <td><?php echo $luong['Thang'] . '/' . $luong['Nam']; ?></td>
                        <td><?php echo number_format($luong['ThucLanh']); ?> VNĐ</td>
                        <td>
                          <a href="chitietluong.php?<?php echo isset($_GET['id']) ? 'id=' . $_GET['id'] . '&' : ''; ?>thang=<?php echo $luong['Thang']; ?>&nam=<?php echo $luong['Nam']; ?>" class="btn btn-xs btn-info">
                            <i class="fa fa-eye"></i> Xem
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="3" class="text-center">Không có dữ liệu lương</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="col-md-9">
          <!-- Tổng quan chấm công -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Tổng quan chấm công tháng <?php echo $thang; ?>/<?php echo $nam; ?></h3>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-calendar-check-o"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Ngày công</span>
                      <span class="info-box-number"><?php echo $tongNgayCong; ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Tổng giờ làm</span>
                      <span class="info-box-number"><?php echo number_format($tongGioLam, 1); ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Giờ tăng ca</span>
                      <span class="info-box-number"><?php echo number_format($tongGioTangCa, 1); ?></span>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-3 col-sm-6 col-xs-12">
                  <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-warning"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">Số ngày đi muộn</span>
                      <span class="info-box-number"><?php echo $soNgayDiMuon; ?></span>
                    </div>
                  </div>
                </div>
              </div>
              
              <?php if($soNgayDiMuon > 0): ?>
                <div class="alert alert-warning">
                  <h4><i class="icon fa fa-warning"></i> Thông báo!</h4>
                  <p>Số ngày đi muộn: <strong><?php echo $soNgayDiMuon; ?> ngày</strong> - Tiền phạt: <strong><?php echo number_format($tienPhatDiMuon); ?> VNĐ</strong></p>
                </div>
              <?php endif; ?>
              
              <div class="row">
                <div class="col-md-12">
                  <h4><i class="fa fa-calendar"></i> Biểu đồ chấm công tháng <?php echo $thang; ?>/<?php echo $nam; ?></h4>
                  <div class="chart">
                    <canvas id="chamCongChart" style="height:250px"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Chi tiết lương -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Chi tiết lương tháng <?php echo $thang; ?>/<?php echo $nam; ?></h3>
              
              <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
                <div class="box-tools pull-right">
                  <a href="tinhluong.php?p=salary&a=calculate&id=<?php echo $maNhanVien; ?>&thang=<?php echo $thang; ?>&nam=<?php echo $nam; ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-calculator"></i> Tính lương
                  </a>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="box-body">
              <?php if($bangLuongThang): ?>
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <tbody>
                      <tr>
                        <th colspan="2" class="bg-primary text-center">THÔNG TIN LƯƠNG THÁNG <?php echo $thang; ?>/<?php echo $nam; ?></th>
                      </tr>
                      <tr>
                        <th width="30%">Lương cơ bản</th>
                        <td><?php echo number_format($bangLuongThang['LuongCoBan']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Ngày công thực tế</th>
                        <td><?php echo $bangLuongThang['NgayCongThucTe']; ?> ngày</td>
                      </tr>
                      <tr>
                        <th>Lương theo ngày công</th>
                        <td><?php echo number_format($bangLuongThang['LuongTheoNgayCong']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Giờ tăng ca</th>
                        <td><?php echo number_format($bangLuongThang['GioTangCa'], 1); ?> giờ</td>
                      </tr>
                      <tr>
                        <th>Lương tăng ca</th>
                        <td><?php echo number_format($bangLuongThang['LuongTangCa']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Phụ cấp</th>
                        <td><?php echo number_format($bangLuongThang['PhuCap']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Thưởng</th>
                        <td class="text-success"><?php echo number_format($bangLuongThang['Thuong']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Phạt</th>
                        <td class="text-danger">
                          <?php echo number_format($bangLuongThang['Phat']); ?> VNĐ
                          <?php if($soNgayDiMuon > 0): ?>
                            <small>(bao gồm <?php echo number_format($tienPhatDiMuon); ?> VNĐ phạt đi muộn)</small>
                          <?php endif; ?>
                        </td>
                      </tr>
                      <tr>
                        <th>BHXH (<?php echo number_format(($bangLuongThang['BHXH'] / $bangLuongThang['LuongCoBan']) * 100, 1); ?>%)</th>
                        <td><?php echo number_format($bangLuongThang['BHXH']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>BHYT (<?php echo number_format(($bangLuongThang['BHYT'] / $bangLuongThang['LuongCoBan']) * 100, 1); ?>%)</th>
                        <td><?php echo number_format($bangLuongThang['BHYT']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Thuế TNCN</th>
                        <td><?php echo number_format($bangLuongThang['ThueTNCN']); ?> VNĐ</td>
                      </tr>
                      <tr class="success">
                        <th>THỰC LÃNH</th>
                        <td class="text-bold" style="font-size: 16px;"><?php echo number_format($bangLuongThang['ThucLanh']); ?> VNĐ</td>
                      </tr>
                      <tr>
                        <th>Ngày cập nhật</th>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($bangLuongThang['NgayCapNhat'])); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <div class="callout callout-info">
                  <h4><i class="fa fa-info-circle"></i> Ghi chú về cách tính lương:</h4>
                  <ol>
                    <li><strong>Lương theo ngày công</strong> = (Lương cơ bản / 22) × Số ngày công thực tế</li>
                    <li><strong>Lương tăng ca</strong> = (Lương cơ bản / (22 × 8)) × 1.5 × Số giờ tăng ca</li>
                    <li><strong>Nhân viên đi muộn (trạng thái "Đi muộn - Đã hoàn thành")</strong> bị phạt 100.000 VNĐ/ngày</li>
                    <li><strong>Thực lãnh</strong> = Lương theo ngày công + Lương tăng ca + Phụ cấp + Thưởng - Phạt - BHXH - BHYT - Thuế TNCN</li>
                  </ol>
                </div>
              <?php else: ?>
                <div class="alert alert-warning">
                  <h4><i class="icon fa fa-warning"></i> Thông báo!</h4>
                  <p>Chưa có dữ liệu lương cho tháng <?php echo $thang; ?>/<?php echo $nam; ?>.</p>
                  <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
                    <p>Vui lòng nhấn nút "Tính lương" để tạo bảng lương cho nhân viên này.</p>
                  <?php else: ?>
                    <p>Vui lòng liên hệ với phòng nhân sự để biết thêm chi tiết.</p>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          
          <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
            <!-- Chi tiết khen thưởng / kỷ luật -->
            <div class="box box-danger">
              <div class="box-header with-border">
                <h3 class="box-title">Khen thưởng / Kỷ luật tháng <?php echo $thang; ?>/<?php echo $nam; ?></h3>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                </div>
              </div>
              <div class="box-body">
                <?php if(count($arrKTKL) > 0): ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>Ngày áp dụng</th>
                          <th>Loại sự kiện</th>
                          <th>Số tiền</th>
                          <th>Mô tả</th>
                          <th>Thao tác</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($arrKTKL as $ktkl): ?>
                          <?php
                            // Màu sắc loại sự kiện
                            $typeClass = '';
                            if($ktkl['LoaiSuKien'] == 'Khen thưởng') {
                              $typeClass = 'label-success';
                            } else if($ktkl['LoaiSuKien'] == 'Kỷ luật') {
                              $typeClass = 'label-danger';
                            }
                          ?>
                          <tr>
                            <td><?php echo date('d/m/Y', strtotime($ktkl['NgayApDung'])); ?></td>
                            <td><span class="label <?php echo $typeClass; ?>"><?php echo $ktkl['LoaiSuKien']; ?></span></td>
                            <td><?php echo number_format($ktkl['SoTien']); ?> VNĐ</td>
                            <td><?php echo $ktkl['MoTa']; ?></td>
                            <td>
                              <a href="chitietktkl.php?p=salary&a=ktkl_detail&id=<?php echo $ktkl['MaKTKL']; ?>" class="btn btn-xs btn-info">
                                <i class="fa fa-edit"></i> Sửa
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                  
                  <div class="row" style="margin-top: 15px;">
                    <div class="col-md-6">
                      <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-thumbs-up"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Tổng thưởng</span>
                          <span class="info-box-number"><?php echo number_format($tongThuong); ?> VNĐ</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-thumbs-down"></i></span>
                        <div class="info-box-content">
                          <span class="info-box-text">Tổng phạt</span>
                          <span class="info-box-number"><?php echo number_format($tongPhat); ?> VNĐ</span>
                          <?php if($soNgayDiMuon > 0): ?>
                            <span class="info-box-text">
                              <small>(Bao gồm <?php echo number_format($tienPhatDiMuon); ?> VNĐ phạt đi muộn)</small>
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php else: ?>
                  <?php if($soNgayDiMuon > 0): ?>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="info-box bg-red">
                          <span class="info-box-icon"><i class="fa fa-thumbs-down"></i></span>
                          <div class="info-box-content">
                            <span class="info-box-text">Tổng phạt (từ đi muộn)</span>
                            <span class="info-box-number"><?php echo number_format($tienPhatDiMuon); ?> VNĐ</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <p class="text-center">Không có dữ liệu khen thưởng/kỷ luật trong tháng này</p>
                  <?php endif; ?>
                <?php endif; ?>
                
                <div class="text-center" style="margin-top: 15px;">
                  <a href="themktkl.php?p=salary&a=ktkl_add&id=<?php echo $maNhanVien; ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Thêm khen thưởng/kỷ luật mới
                  </a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
        </div>
      </div>
    </section>
  </div>

  <!-- ChartJS 1.0.1 -->
  <script src="../plugins/chartjs/Chart.min.js"></script>

  <script>
    $(function () {
      // Lấy dữ liệu chấm công
      var dataChamCong = {
        labels: [
          <?php
            // Tạo một mảng các ngày trong tháng
            $totalDays = date('t', strtotime($ngayDauThang));
            $labelDays = [];
            $dataNgayCong = [];
            $dataDiMuon = [];
            $dataVangMat = [];
            
            for($i = 1; $i <= $totalDays; $i++) {
              $currentDate = date('Y-m-d', strtotime($nam . '-' . $thang . '-' . $i));
              $labelDays[] = '"' . $i . '"';
              
              // Mặc định không có dữ liệu
              $isNgayCong = 0;
              $isDiMuon = 0;
              $isVangMat = 0;
              
              // Kiểm tra trạng thái cho ngày này
              foreach($arrChamCong as $chamCong) {
                if($chamCong['Ngay'] == $currentDate) {
                  if($chamCong['TrangThai'] == 'Đã hoàn thành') {
                    $isNgayCong = 1;
                  } else if($chamCong['TrangThai'] == 'Đi muộn - Đã hoàn thành') {
                    $isDiMuon = 1;
                  } else if($chamCong['TrangThai'] == 'Vắng mặt') {
                    $isVangMat = 1;
                  }
                  break;
                }
              }
              
              $dataNgayCong[] = $isNgayCong;
              $dataDiMuon[] = $isDiMuon;
              $dataVangMat[] = $isVangMat;
            }
            
            echo implode(',', $labelDays);
          ?>
        ],
        datasets: [
          {
            label: "Ngày công",
            fillColor: "rgba(0,166,90,0.6)",
            strokeColor: "rgba(0,166,90,1)",
            pointColor: "rgba(0,166,90,1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(0,166,90,1)",
            data: [<?php echo implode(',', $dataNgayCong); ?>]
          },
          {
            label: "Đi muộn",
            fillColor: "rgba(243,156,18,0.6)",
            strokeColor: "rgba(243,156,18,1)",
            pointColor: "rgba(243,156,18,1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(243,156,18,1)",
            data: [<?php echo implode(',', $dataDiMuon); ?>]
          },
          {
            label: "Vắng mặt",
            fillColor: "rgba(221,75,57,0.6)",
            strokeColor: "rgba(221,75,57,1)",
            pointColor: "rgba(221,75,57,1)",
            pointStrokeColor: "#c1c7d1",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(221,75,57,1)",
            data: [<?php echo implode(',', $dataVangMat); ?>]
          }
        ]
      };

      // Vẽ biểu đồ
      var chamCongChart = new Chart(document.getElementById("chamCongChart").getContext("2d")).Bar(dataChamCong, {
        responsive: true,
        maintainAspectRatio: true,
        scaleShowGridLines: false,
        scaleGridLineColor: "rgba(0,0,0,.05)",
        scaleGridLineWidth: 1,
        scaleShowHorizontalLines: true,
        scaleShowVerticalLines: true,
        barShowStroke: true,
        barStrokeWidth: 2,
        barValueSpacing: 5,
        barDatasetSpacing: 1,
        multiTooltipTemplate: "<%= datasetLabel %> - <%= value %>"
      });
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