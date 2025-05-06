<?php
// create session
session_start();

if (isset($_SESSION['username'])) {
    // include file
    include('../layouts/header.php');
    include('../layouts/topbar.php');
    include('../layouts/sidebar.php');

    // Kiểm tra quyền admin
    $isAdmin = isset($row_acc) && $row_acc['Chucvu'] == 'admin';

    // Lấy dữ liệu nhân viên
    $query = "SELECT nv.MaNV, nv.Hoten, nv.SDT, nv.CCCD, nv.GioiTinh, 
              DATE_FORMAT(nv.NgaySinh, '%d/%m/%Y') as NgaySinh, 
              DATE_FORMAT(nv.NgayVaoLam, '%d/%m/%Y') as NgayVaoLam, 
              nv.ChucVu, pb.TenPhongBan as PhongBan
              FROM NhanVien nv
              LEFT JOIN PhongBan pb ON nv.MaPb = pb.MaPb
              ORDER BY nv.MaNV";
    $result = mysqli_query($conn, $query);

    // Đếm tổng số nhân viên
    $queryCount = "SELECT COUNT(*) as tongNV FROM NhanVien";
    $resultCount = mysqli_query($conn, $queryCount);
    $rowCount = mysqli_fetch_assoc($resultCount);
    $tongNV = $rowCount['tongNV'];

    // Đếm tổng số phòng ban
    $queryPB = "SELECT COUNT(*) as tongPB FROM PhongBan";
    $resultPB = mysqli_query($conn, $queryPB);
    $rowPB = mysqli_fetch_assoc($resultPB);
    $tongPB = $rowPB['tongPB'];

    // Đếm tổng số tài khoản
    $queryTK = "SELECT COUNT(*) as tongTK FROM TaiKhoan";
    $resultTK = mysqli_query($conn, $queryTK);
    $rowTK = mysqli_fetch_assoc($resultTK);
    $tongTK = $rowTK['tongTK'];

    // Thông tin khen thưởng/kỷ luật
    $queryKhenThuong = "SELECT COUNT(*) as tongKhenThuong FROM KTKL WHERE LoaiSuKien = 'Khen thưởng'";
    $resultKhenThuong = mysqli_query($conn, $queryKhenThuong);
    $rowKhenThuong = mysqli_fetch_assoc($resultKhenThuong);
    $tongKhenThuong = $rowKhenThuong['tongKhenThuong'];

    $queryTienKhenThuong = "SELECT SUM(SoTien) as tongTienKhenThuong FROM KTKL WHERE LoaiSuKien = 'Khen thưởng'";
    $resultTienKhenThuong = mysqli_query($conn, $queryTienKhenThuong);
    $rowTienKhenThuong = mysqli_fetch_assoc($resultTienKhenThuong);
    $tongTienKhenThuong = $rowTienKhenThuong['tongTienKhenThuong'] ?: 0;

    // Nhân viên vắng/đi muộn
    $ngayHienTai = date('Y-m-d');
    $queryVang = "SELECT COUNT(*) as tongVang FROM NhanVien nv WHERE nv.MaNV NOT IN (
                    SELECT MaNV FROM ChamCong WHERE Ngay = '$ngayHienTai'
                )";
    $resultVang = mysqli_query($conn, $queryVang);
    $rowVang = mysqli_fetch_assoc($resultVang);
    $tongVang = $rowVang['tongVang'];

    // Lấy dữ liệu lương tháng hiện tại
    $thangHienTai = date('m');
    $namHienTai = date('Y');
    $thangLuongHienTai = 'Tháng ' . $thangHienTai . '/' . $namHienTai;

    $queryLuongThang = "SELECT SUM(ThucLanh) as tongLuongThang FROM BangLuongThang 
                         WHERE Thang = '$thangHienTai' AND Nam = '$namHienTai'";
    $resultLuongThang = mysqli_query($conn, $queryLuongThang);
    $rowLuongThang = mysqli_fetch_assoc($resultLuongThang);
    $tongLuongThang = $rowLuongThang['tongLuongThang'] ?: 0;

    // Nhân viên nghỉ việc
    $queryNghiViec = "SELECT COUNT(*) as tongNghiViec FROM NghiViec";
    $resultNghiViec = mysqli_query($conn, $queryNghiViec);
    $rowNghiViec = mysqli_fetch_assoc($resultNghiViec);
    $tongNghiViec = $rowNghiViec['tongNghiViec'];

    // Lấy dữ liệu chấm công hôm nay
    $queryChamCong = "SELECT cc.MaCC, cc.MaNV, nv.Hoten, cc.Ngay, cc.GioVao, cc.GioRa, 
                     cc.GioTangCa, cc.SoGioLam, cc.TrangThai, cc.GhiChu
                     FROM ChamCong cc
                     JOIN NhanVien nv ON cc.MaNV = nv.MaNV
                     WHERE cc.Ngay = '$ngayHienTai'
                     ORDER BY cc.GioVao DESC
                     LIMIT 10";
    $resultChamCong = mysqli_query($conn, $queryChamCong);
    $arrChamCong = array();
    while ($row = mysqli_fetch_array($resultChamCong)) {
        $arrChamCong[] = $row;
    }

    // Lấy dữ liệu bảng lương tháng này
    $queryBangLuong = "SELECT bl.MaBangLuong, bl.MaNV, nv.Hoten, nv.GioiTinh, bl.LuongCoBan,
                      bl.NgayCongThucTe, (bl.BHXH + bl.BHYT + bl.ThueTNCN) as KhoanNop,
                      bl.ThucLanh, 'Đang làm việc' as TrangThai
                      FROM BangLuongThang bl
                      JOIN NhanVien nv ON bl.MaNV = nv.MaNV
                      WHERE bl.Thang = '$thangHienTai' AND bl.Nam = '$namHienTai'
                      ORDER BY bl.ThucLanh DESC
                      LIMIT 10";
    $resultBangLuong = mysqli_query($conn, $queryBangLuong);
    $arrLuongThang = array();
    while ($row = mysqli_fetch_array($resultBangLuong)) {
        $arrLuongThang[] = $row;
    }

    // Thêm các query để lấy dữ liệu cho biểu đồ
    // Lấy số nhân viên theo phòng ban
    $queryNVPhongBan = "SELECT pb.TenPhongBan, COUNT(nv.MaNV) as SoNV 
                        FROM PhongBan pb 
                        LEFT JOIN NhanVien nv ON pb.MaPb = nv.MaPb 
                        GROUP BY pb.MaPb, pb.TenPhongBan";
    $resultNVPhongBan = mysqli_query($conn, $queryNVPhongBan);
    $dataNVPhongBan = array();
    while ($row = mysqli_fetch_array($resultNVPhongBan)) {
        $dataNVPhongBan[] = $row;
    }

    // Lấy số nhân viên theo giới tính
    $queryGioiTinh = "SELECT GioiTinh, COUNT(*) as SoLuong FROM NhanVien GROUP BY GioiTinh";
    $resultGioiTinh = mysqli_query($conn, $queryGioiTinh);
    $dataGioiTinh = array();
    while ($row = mysqli_fetch_array($resultGioiTinh)) {
        $dataGioiTinh[] = $row;
    }

    // Lấy thống kê chấm công trong tuần
    $queryTrangThaiCC = "SELECT TrangThai, COUNT(*) as SoLuong 
                         FROM ChamCong 
                         WHERE Ngay = '$ngayHienTai' 
                         GROUP BY TrangThai";
    $resultTrangThaiCC = mysqli_query($conn, $queryTrangThaiCC);
    $dataTrangThaiCC = array();
    while ($row = mysqli_fetch_array($resultTrangThaiCC)) {
        $dataTrangThaiCC[] = $row;
    }

    // Lấy top 5 nhân viên lương cao nhất
    $queryTop5Luong = "SELECT nv.Hoten, bl.ThucLanh 
                       FROM BangLuongThang bl 
                       JOIN NhanVien nv ON bl.MaNV = nv.MaNV 
                       WHERE bl.Thang = '$thangHienTai' AND bl.Nam = '$namHienTai' 
                       ORDER BY bl.ThucLanh DESC 
                       LIMIT 5";
    $resultTop5Luong = mysqli_query($conn, $queryTop5Luong);
    $dataTop5Luong = array();
    while ($row = mysqli_fetch_array($resultTop5Luong)) {
        $dataTop5Luong[] = $row;
    }
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Tổng quan
            <small>Phần mềm quản lý nhân viên công ty HPSOFT</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li class="active">Thống kê</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $tongNV; ?></h3>
                        <p>Nhân viên</p>
                    </div>
                    <div class="icon"> <i class="fa fa-user"></i></div>
                    <a href="danhsachnhanvien.php?p=staff&a=list-staff" class="small-box-footer">
                        Danh sách nhân viên <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
            
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $tongKhenThuong; ?></h3>
                        <p>Số nhân viên khen thưởng</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-trophy"></i>
                    </div>
                    <?php if ($isAdmin): ?>
                    <a href="khenthuong.php?p=bonus-discipline&a=bonus" class="small-box-footer">
                        Danh sách khen thưởng <i class="fa fa-arrow-circle-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="small-box-footer" style="display: block; padding: 5px;">
                        Chỉ admin mới có quyền truy cập
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ./col -->
            
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $tongTK; ?></h3>
                        <p>Tài khoản người dùng</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <?php if ($isAdmin): ?>
                    <a href="danhsachtaikhoan.php?p=account&a=list-account" class="small-box-footer">
                        Danh sách tài khoản <i class="fa fa-arrow-circle-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="small-box-footer" style="display: block; padding: 5px;">
                        Chỉ admin mới có quyền truy cập
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ./col -->
            
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo $tongVang; ?></h3>
                        <p>Đi muộn/vắng hôm nay</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-calendar-times-o"></i>
                    </div>
                    <?php if ($isAdmin): ?>
                    <a href="dulieuchamcong.php?p=attendance&a=data" class="small-box-footer">
                        Danh sách chấm công <i class="fa fa-arrow-circle-right"></i>
                    </a>
                    <?php else: ?>
                    <a href="chamcong.php?p=attendance&a=checkin" class="small-box-footer">
                        Chấm công <i class="fa fa-arrow-circle-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-building"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Phòng ban</span>
                        <span class="info-box-number"><?php echo $tongPB; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tiền chi khen thưởng</span>
                        <span class="info-box-number"><?php echo number_format($tongTienKhenThuong); ?> VNĐ</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-credit-card"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Lương trả trong tháng</span>
                        <span class="info-box-number"><?php echo number_format($tongLuongThang); ?> VNĐ</span>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-user-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Nhân viên nghỉ việc</span>
                        <span class="info-box-number"><?php echo $tongNghiViec; ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main row with charts -->
        <div class="row">
            <!-- BIỂU ĐỒ NHÂN VIÊN THEO PHÒNG BAN -->
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nhân viên theo phòng ban</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartNVPhongBan" style="height:300px"></canvas>
                    </div>
                </div>
            </div>

            <!-- BIỂU ĐỒ NHÂN VIÊN THEO GIỚI TÍNH -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tỷ lệ giới tính nhân viên</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartGioiTinh" style="height:300px"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- BIỂU ĐỒ TRẠNG THÁI CHẤM CÔNG -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Trạng thái chấm công hôm nay</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartTrangThaiCC" style="height:300px"></canvas>
                    </div>
                </div>
            </div>

            <!-- BIỂU ĐỒ TOP 5 LƯƠNG CAO NHẤT -->
            <div class="col-md-6">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Top 5 nhân viên lương cao nhất tháng này</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <canvas id="chartTop5Luong" style="height:300px"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng dữ liệu gọn hơn với DataTables -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Danh sách nhân viên gần đây</h3>
                        <div class="box-tools pull-right">
                            <a href="danhsachnhanvien.php?p=staff&a=list-staff" class="btn btn-sm btn-primary">
                                <i class="fa fa-list"></i> Xem tất cả
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <table id="tableNhanVien" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Mã NV</th>
                                    <th>Họ tên</th>
                                    <th>SĐT</th>
                                    <th>Giới tính</th>
                                    <th>Chức vụ</th>
                                    <th>Phòng ban</th>
                                    <?php if ($isAdmin): ?>
                                    <th>Thao tác</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Reset lại con trỏ kết quả
                                mysqli_data_seek($result, 0);
                                $count = 0;
                                while ($nv = mysqli_fetch_assoc($result)) {
                                    if ($count >= 10) break;
                                    $count++;
                                ?>
                                    <tr>
                                        <td><?php echo $nv['MaNV']; ?></td>
                                        <td><?php echo $nv['Hoten']; ?></td>
                                        <td><?php echo $nv['SDT']; ?></td>
                                        <td><?php echo $nv['GioiTinh']; ?></td>
                                        <td><?php echo $nv['ChucVu']; ?></td>
                                        <td><?php echo $nv['PhongBan']; ?></td>
                                        <?php if ($isAdmin): ?>
                                        <td>
                                            <a href="chitietnhanvien.php?p=staff&a=detail-staff&id=<?php echo $nv['MaNV']; ?>" 
                                               class="btn btn-primary btn-xs" title="Chi tiết">
                                                <i class="fa fa-info-circle"></i>
                                            </a>
                                            <a href="suanhanvien.php?p=staff&a=edit-staff&id=<?php echo $nv['MaNV']; ?>" 
                                               class="btn btn-warning btn-xs" title="Sửa">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Thêm Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap.min.css">
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#tableNhanVien').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "pageLength": 5,
        "language": {
            "sEmptyTable": "Không có dữ liệu",
            "sInfo": "Hiển thị _START_ đến _END_ của _TOTAL_ nhân viên",
            "sInfoEmpty": "Hiển thị 0 đến 0 của 0 nhân viên",
            "sInfoFiltered": "(lọc từ _MAX_ nhân viên)",
            "sSearch": "Tìm kiếm:",
            "oPaginate": {
                "sFirst": "Đầu",
                "sPrevious": "Trước",
                "sNext": "Tiếp",
                "sLast": "Cuối"
            }
        }
    });

    // Biểu đồ nhân viên theo phòng ban
    var ctxNVPhongBan = document.getElementById('chartNVPhongBan').getContext('2d');
    var chartNVPhongBan = new Chart(ctxNVPhongBan, {
        type: 'bar',
        data: {
            labels: [<?php 
                foreach($dataNVPhongBan as $pb) {
                    echo "'" . $pb['TenPhongBan'] . "',";
                }
            ?>],
            datasets: [{
                label: 'Số nhân viên',
                data: [<?php 
                    foreach($dataNVPhongBan as $pb) {
                        echo $pb['SoNV'] . ",";
                    }
                ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' nhân viên';
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ tỷ lệ giới tính
    var ctxGioiTinh = document.getElementById('chartGioiTinh').getContext('2d');
    var chartGioiTinh = new Chart(ctxGioiTinh, {
        type: 'doughnut',
        data: {
            labels: [<?php 
                foreach($dataGioiTinh as $gt) {
                    echo "'" . $gt['GioiTinh'] . "',";
                }
            ?>],
            datasets: [{
                data: [<?php 
                    foreach($dataGioiTinh as $gt) {
                        echo $gt['SoLuong'] . ",";
                    }
                ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.parsed || 0;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ trạng thái chấm công
    var ctxTrangThaiCC = document.getElementById('chartTrangThaiCC').getContext('2d');
    var chartTrangThaiCC = new Chart(ctxTrangThaiCC, {
        type: 'pie',
        data: {
            labels: [<?php 
                foreach($dataTrangThaiCC as $tt) {
                    echo "'" . $tt['TrangThai'] . "',";
                }
            ?>],
            datasets: [{
                data: [<?php 
                    foreach($dataTrangThaiCC as $tt) {
                        echo $tt['SoLuong'] . ",";
                    }
                ?>],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var label = context.label || '';
                            var value = context.parsed || 0;
                            return label + ': ' + value + ' nhân viên';
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ top 5 lương cao nhất
    var ctxTop5Luong = document.getElementById('chartTop5Luong').getContext('2d');
    var chartTop5Luong = new Chart(ctxTop5Luong, {
        type: 'bar',
        data: {
            labels: [<?php 
                foreach($dataTop5Luong as $nv) {
                    echo "'" . $nv['Hoten'] . "',";
                }
            ?>],
            datasets: [{
                label: 'Lương (VNĐ)',
                data: [<?php 
                    foreach($dataTop5Luong as $nv) {
                        echo $nv['ThucLanh'] . ",";
                    }
                ?>],
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('vi-VN').format(context.parsed.x) + ' VNĐ';
                        }
                    }
                }
            }
        }
    });
});
</script>

<!-- Thêm CSS tùy chỉnh -->
<style>
/* Card effect cho boxes */
.box {
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.box:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
}

/* Style cho small-box */
.small-box {
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
}

/* Style cho info-box */
.info-box {
    border-radius: 10px;
    transition: all 0.3s ease;
}

.info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
}

/* Style cho DataTables */
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #3c8dbc !important;
    border-color: #3c8dbc !important;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #3c8dbc !important;
    border-color: #3c8dbc !important;
    color: white !important;
}

/* Style cho tables */
.table>tbody>tr:hover {
    background-color: #f5f5f5;
}

/* Style cho box-header */
.box-header {
    background: linear-gradient(45deg, #f7f7f7, #ffffff);
    border-bottom: 2px solid #f4f4f4;
}

/* Animation cho số liệu */
.inner h3 {
    animation: countUp 1s ease-out;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .box-header h3.box-title {
        font-size: 16px;
    }
    
    canvas {
        height: 250px !important;
    }
}
</style>

<?php
    // include
    include('../layouts/footer.php');
} else {
    // go to pages login
    header('Location: login.php');
}
?>