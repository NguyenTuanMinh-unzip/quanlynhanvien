<?php
// Kiểm tra đăng nhập và quyền admin
session_start();
require_once '../config.php';

if(!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');

// Kiểm tra quyền người dùng - CHỈ CHO PHÉP ADMIN
if(!isset($row_acc) || $row_acc['Chucvu'] != 'admin') {
    echo "<script>
        alert('Bạn không có quyền truy cập trang này!');
        window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
}

// Xử lý thêm thông tin nghỉ việc
if(isset($_POST['add_termination'])) {
    $maNV = $_POST['maNV'];
    $ngayThongBao = $_POST['ngayThongBao'];
    $ngayNghiViec = $_POST['ngayNghiViec'];
    $lyDo = mysqli_real_escape_string($conn, $_POST['lyDo']);
    $tinhTrangBanGiao = mysqli_real_escape_string($conn, $_POST['tinhTrangBanGiao']);
    $ghiChu = mysqli_real_escape_string($conn, $_POST['ghiChu']);
    
    // Tạo mã nghỉ việc
    $maNghiViec = "NV" . time();
    
    // Bắt đầu transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Thêm thông tin nghỉ việc
        $sql_add = "INSERT INTO nghiviec (MaNghiViec, MaNV, NgayThongBao, NgayNghiViec, LyDo, TinhTrangBanGiao, GhiChu) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_add = mysqli_prepare($conn, $sql_add);
        mysqli_stmt_bind_param($stmt_add, "sssssss", $maNghiViec, $maNV, $ngayThongBao, $ngayNghiViec, $lyDo, $tinhTrangBanGiao, $ghiChu);
        mysqli_stmt_execute($stmt_add);
        
        // Xóa tài khoản của nhân viên để họ không thể đăng nhập
        $sql_delete_account = "DELETE FROM taikhoan WHERE MaNV = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete_account);
        mysqli_stmt_bind_param($stmt_delete, "s", $maNV);
        mysqli_stmt_execute($stmt_delete);
        
        // Cập nhật trạng thái nhân viên
        $sql_update_employee = "UPDATE nhanvien SET ChucVu = 'Đã nghỉ việc' WHERE MaNV = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update_employee);
        mysqli_stmt_bind_param($stmt_update, "s", $maNV);
        mysqli_stmt_execute($stmt_update);
        
        // Commit transaction
        mysqli_commit($conn);
        $success = "Đã thêm thông tin nghỉ việc và xóa tài khoản nhân viên thành công!";
    } catch (Exception $e) {
        // Rollback nếu có lỗi
        mysqli_rollback($conn);
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý hoàn tác nghỉ việc (phục hồi nhân viên)
if(isset($_POST['reactivate_employee'])) {
    $maNV = $_POST['employee_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    mysqli_begin_transaction($conn);
    
    try {
        // Kiểm tra xem tài khoản đã tồn tại chưa
        $check_account = "SELECT * FROM taikhoan WHERE MaNV = ?";
        $stmt_check = mysqli_prepare($conn, $check_account);
        mysqli_stmt_bind_param($stmt_check, "s", $maNV);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if(mysqli_num_rows($result_check) == 0) {
            // Tạo lại tài khoản cho nhân viên
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_recreate = "INSERT INTO taikhoan (Taikhoan, Matkhau, Chucvu, MaNV) VALUES (?, ?, 'Nhân viên', ?)";
            $stmt_recreate = mysqli_prepare($conn, $sql_recreate);
            mysqli_stmt_bind_param($stmt_recreate, "sss", $username, $hashed_password, $maNV);
            mysqli_stmt_execute($stmt_recreate);
            
            // Cập nhật lại chức vụ nhân viên
            $sql_update = "UPDATE nhanvien SET ChucVu = 'Nhân viên' WHERE MaNV = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "s", $maNV);
            mysqli_stmt_execute($stmt_update);
            
            // Xóa thông tin nghỉ việc
            $sql_delete = "DELETE FROM nghiviec WHERE MaNV = ?";
            $stmt_delete = mysqli_prepare($conn, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "s", $maNV);
            mysqli_stmt_execute($stmt_delete);
            
            mysqli_commit($conn);
            $success = "Đã phục hồi tài khoản nhân viên thành công!";
        } else {
            throw new Exception("Tài khoản nhân viên vẫn còn tồn tại!");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách nhân viên đang làm việc (có tài khoản)
$sql_employees = "SELECT nv.*, pb.TenPhongBan, tk.Taikhoan 
                  FROM nhanvien nv 
                  INNER JOIN taikhoan tk ON nv.MaNV = tk.MaNV
                  LEFT JOIN phongban pb ON nv.MaPb = pb.MaPb 
                  WHERE nv.ChucVu != 'Đã nghỉ việc' 
                  ORDER BY nv.Hoten";
$result_employees = mysqli_query($conn, $sql_employees);

// Lấy danh sách nhân viên đã nghỉ việc
$sql_terminated = "SELECT nv.*, ng.*, pb.TenPhongBan 
                   FROM nghiviec ng 
                   JOIN nhanvien nv ON ng.MaNV = nv.MaNV 
                   LEFT JOIN phongban pb ON nv.MaPb = pb.MaPb 
                   ORDER BY ng.NgayNghiViec DESC";
$result_terminated = mysqli_query($conn, $sql_terminated);

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Quản lý nghỉ việc
            <small>Thêm và quản lý nhân viên nghỉ việc</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li class="active">Quản lý nghỉ việc</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Hiển thị thông báo -->
        <?php if(isset($success)): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i> Thành công!</h4>
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-ban"></i> Lỗi!</h4>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Form thêm thông tin nghỉ việc -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Thêm thông tin nghỉ việc</h3>
                    </div>
                    <!-- form start -->
                    <form method="post" action="">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="maNV">Chọn nhân viên:</label>
                                <select class="form-control select2" name="maNV" id="maNV" required style="width: 100%;">
                                    <option value="">-- Chọn nhân viên --</option>
                                    <?php 
                                    while($employee = mysqli_fetch_assoc($result_employees)): 
                                        // Không cho phép admin tự cho mình nghỉ việc
                                        if($employee['MaNV'] != $_SESSION['MaNV']): 
                                    ?>
                                    <option value="<?php echo $employee['MaNV']; ?>">
                                        <?php echo $employee['MaNV'] . ' - ' . $employee['Hoten'] . ' (' . $employee['TenPhongBan'] . ') - TK: ' . $employee['Taikhoan']; ?>
                                    </option>
                                    <?php 
                                        endif;
                                    endwhile; 
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ngayThongBao">Ngày thông báo:</label>
                                <input type="date" class="form-control" id="ngayThongBao" name="ngayThongBao" 
                                       required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="ngayNghiViec">Ngày nghỉ việc:</label>
                                <input type="date" class="form-control" id="ngayNghiViec" name="ngayNghiViec" 
                                       required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="lyDo">Lý do:</label>
                                <textarea class="form-control" id="lyDo" name="lyDo" rows="3" required></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="tinhTrangBanGiao">Tình trạng bàn giao:</label>
                                <select class="form-control" id="tinhTrangBanGiao" name="tinhTrangBanGiao" required>
                                    <option value="Chưa bàn giao">Chưa bàn giao</option>
                                    <option value="Đang bàn giao">Đang bàn giao</option>
                                    <option value="Đã bàn giao">Đã bàn giao</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ghiChu">Ghi chú:</label>
                                <textarea class="form-control" id="ghiChu" name="ghiChu" rows="2"></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="add_termination" class="btn btn-primary">
                                <i class="fa fa-user-times"></i> Xác nhận nghỉ việc
                            </button>
                            <button type="reset" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Làm mới
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Thông tin và lưu ý -->
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Lưu ý quan trọng</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-warning">
                            <h4><i class="icon fa fa-warning"></i> Cảnh báo!</h4>
                            <p>Khi xác nhận nghỉ việc cho nhân viên:</p>
                            <ul>
                                <li><strong>Tài khoản của nhân viên sẽ bị XÓA VĨNH VIỄN.</strong></li>
                                <li>Nhân viên sẽ không thể đăng nhập vào hệ thống.</li>
                                <li>Thông tin nhân viên vẫn được lưu trữ trong cơ sở dữ liệu.</li>
                                <li>Các dữ liệu lịch sử của nhân viên sẽ được giữ nguyên.</li>
                                <li>Có thể phục hồi tài khoản nếu cần thiết (tạo tài khoản mới).</li>
                            </ul>
                        </div>
                        
                        <div class="callout callout-info">
                            <h4><i class="icon fa fa-info"></i> Quy trình nghỉ việc</h4>
                            <ol>
                                <li>Nhân viên nộp đơn xin nghỉ việc</li>
                                <li>Quản lý xác nhận và điền thông tin vào form</li>
                                <li>Kiểm tra tình trạng bàn giao công việc</li>
                                <li>Xác nhận nghỉ việc trong hệ thống</li>
                                <li>Hệ thống tự động xóa tài khoản đăng nhập</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <!-- Thống kê nhanh -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Thống kê nghỉ việc</h3>
                    </div>
                    <div class="box-body">
                        <?php
                        // Thống kê theo tháng hiện tại
                        $month = date('m');
                        $year = date('Y');
                        $sql_stats = "SELECT COUNT(*) as total FROM nghiviec 
                                     WHERE MONTH(NgayNghiViec) = $month AND YEAR(NgayNghiViec) = $year";
                        $result_stats = mysqli_query($conn, $sql_stats);
                        $stats = mysqli_fetch_assoc($result_stats);
                        ?>
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Nghỉ việc tháng <?php echo $month; ?></span>
                                <span class="info-box-number"><?php echo $stats['total']; ?> nhân viên</span>
                            </div>
                        </div>
                        
                        <?php
                        // Thống kê theo năm
                        $sql_year_stats = "SELECT COUNT(*) as total FROM nghiviec 
                                          WHERE YEAR(NgayNghiViec) = $year";
                        $result_year_stats = mysqli_query($conn, $sql_year_stats);
                        $year_stats = mysqli_fetch_assoc($result_year_stats);
                        ?>
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Nghỉ việc năm <?php echo $year; ?></span>
                                <span class="info-box-number"><?php echo $year_stats['total']; ?> nhân viên</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Danh sách nhân viên đã nghỉ việc -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Danh sách nhân viên đã nghỉ việc</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="terminated-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Mã NV</th>
                                        <th>Họ tên</th>
                                        <th>Phòng ban</th>
                                        <th>Ngày thông báo</th>
                                        <th>Ngày nghỉ việc</th>
                                        <th>Lý do</th>
                                        <th>Tình trạng bàn giao</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($terminated = mysqli_fetch_assoc($result_terminated)): ?>
                                    <tr>
                                        <td><?php echo $terminated['MaNV']; ?></td>
                                        <td><?php echo $terminated['Hoten']; ?></td>
                                        <td><?php echo $terminated['TenPhongBan']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($terminated['NgayThongBao'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($terminated['NgayNghiViec'])); ?></td>
                                        <td><?php echo $terminated['LyDo']; ?></td>
                                        <td>
                                            <?php 
                                            $status_class = '';
                                            switch($terminated['TinhTrangBanGiao']) {
                                                case 'Đã bàn giao':
                                                    $status_class = 'label-success';
                                                    break;
                                                case 'Đang bàn giao':
                                                    $status_class = 'label-warning';
                                                    break;
                                                default:
                                                    $status_class = 'label-danger';
                                            }
                                            ?>
                                            <span class="label <?php echo $status_class; ?>">
                                                <?php echo $terminated['TinhTrangBanGiao']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $terminated['GhiChu']; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" 
                                                    data-target="#reactivateModal<?php echo $terminated['MaNV']; ?>">
                                                <i class="fa fa-undo"></i> Phục hồi
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal phục hồi nhân viên -->
                                    <div class="modal fade" id="reactivateModal<?php echo $terminated['MaNV']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                    <h4 class="modal-title">Phục hồi nhân viên: <?php echo $terminated['Hoten']; ?></h4>
                                                </div>
                                                <form method="post" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="employee_id" value="<?php echo $terminated['MaNV']; ?>">
                                                        <div class="form-group">
                                                            <label>Tên đăng nhập mới:</label>
                                                            <input type="text" class="form-control" name="username" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Mật khẩu mới:</label>
                                                            <input type="password" class="form-control" name="password" required>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <strong>Lưu ý:</strong> Việc phục hồi sẽ tạo tài khoản mới cho nhân viên với thông tin đăng nhập mới.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                                                        <button type="submit" name="reactivate_employee" class="btn btn-primary">Phục hồi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- DataTables và Select2 -->
<script src="../../bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="../../bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="../../bower_components/select2/dist/js/select2.full.min.js"></script>

<script>
$(function () {
    // Initialize DataTable
    $('#terminated-table').DataTable({
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false,
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json'
        }
    });
    
    // Initialize Select2
    $('.select2').select2();
    
    // Validate dates
    $('#ngayNghiViec').change(function() {
        var ngayThongBao = new Date($('#ngayThongBao').val());
        var ngayNghiViec = new Date($(this).val());
        
        if(ngayNghiViec < ngayThongBao) {
            alert('Ngày nghỉ việc không thể trước ngày thông báo!');
            $(this).val('');
        }
    });
});
</script>

<?php
// Include footer
include('../layouts/footer.php');
?>