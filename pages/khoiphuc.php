<?php
// Kiểm tra đăng nhập và quyền admin
session_start();
require_once '../config.php';
include('../layouts/header.php');
include('../layouts/topbar.php');
include('../layouts/sidebar.php');

// Kiểm tra quyền người dùng - CHỈ CHO PHÉP ADMIN
if(!isset($_SESSION['username']) || !isset($row_acc) || $row_acc['Chucvu'] != 'admin') {
    echo "<script>
        alert('Bạn không có quyền truy cập trang này!');
        window.location.href='index.php?p=index&a=statistic';
    </script>";
    exit;
}

// Thư mục lưu backup
$backupDir = __DIR__ . '/../../backups/';

// Tạo thư mục backups nếu chưa tồn tại
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Tạo bảng backups nếu chưa tồn tại
$createTableQuery = "CREATE TABLE IF NOT EXISTS backups (
    id INT(11) NOT NULL AUTO_INCREMENT,
    backup_name VARCHAR(255) NOT NULL,
    backup_path VARCHAR(255) NOT NULL,
    backup_size INT(11) NOT NULL,
    backup_date DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

mysqli_query($conn, $createTableQuery);

// Xử lý yêu cầu tạo sao lưu thủ công
if(isset($_POST['create_backup'])) {
    $backupName = isset($_POST['backup_name']) ? $_POST['backup_name'] : 'Sao lưu thủ công ' . date('Y-m-d H:i:s');
    
    // Thực hiện sao lưu
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPassword = '';
    $dbName = 'quanlynhanvien';
    
    // Tên file backup
    $backupFile = $backupDir . 'backup_' . date('Y-m-d_His') . '.sql';
    
    // Đường dẫn đến mysqldump trong XAMPP
    $mysqldumpPath = 'C:/xampp/mysql/bin/mysqldump';
    
    // Thực hiện lệnh backup
    $command = "\"$mysqldumpPath\" --opt -h $dbHost -u $dbUser";
    if ($dbPassword) {
        $command .= " -p$dbPassword";
    }
    $command .= " $dbName > \"$backupFile\"";
    
    // Ghi log trước khi thực hiện
    $logMessage = date('Y-m-d H:i:s') . " - Thực hiện lệnh sao lưu: $command\n";
    file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
    
    // Thực thi lệnh và bắt output
    ob_start();
    system($command, $returnVar);
    $output = ob_get_contents();
    ob_end_clean();
    
    if($returnVar === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
        // Thêm thông tin vào bảng backups
        $backupSize = filesize($backupFile);
        $backupDate = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO backups (backup_name, backup_path, backup_size, backup_date) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssis", $backupName, $backupFile, $backupSize, $backupDate);
        mysqli_stmt_execute($stmt);
        
        $success = "Tạo bản sao lưu thành công! Kích thước: " . round($backupSize/1024/1024, 2) . " MB";
        
        // Ghi log
        $logMessage = date('Y-m-d H:i:s') . " - Sao lưu thành công: $backupFile - Kích thước: " . round($backupSize/1024/1024, 2) . " MB\n";
        file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
    } else {
        $error = "Lỗi khi tạo bản sao lưu. Mã lỗi: $returnVar";
        if($output) {
            $error .= "<br>Chi tiết: " . nl2br(htmlspecialchars($output));
        }
        
        // Ghi log lỗi
        $logMessage = date('Y-m-d H:i:s') . " - Lỗi khi sao lưu: $output\n";
        file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
    }
}

// Xử lý yêu cầu khôi phục từ bản sao lưu
if(isset($_POST['restore_backup']) && isset($_POST['backup_id'])) {
    $backupId = $_POST['backup_id'];
    
    // Lấy thông tin file backup
    $sql = "SELECT * FROM backups WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $backupId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($backupInfo = mysqli_fetch_assoc($result)) {
        $backupFile = $backupInfo['backup_path'];
        
        // Kiểm tra file tồn tại
        if(file_exists($backupFile)) {
            // Sử dụng thông tin kết nối trực tiếp
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPassword = '';
            $dbName = 'quanlynhanvien';
            
            // Đường dẫn đến mysql trong XAMPP
            $mysqlPath = 'C:/xampp/mysql/bin/mysql';
            
            // Tạo bản sao lưu trước khi khôi phục
            $preRestoreBackupFile = $backupDir . 'pre_restore_' . date('Y-m-d_His') . '.sql';
            $backupCommand = "\"C:/xampp/mysql/bin/mysqldump\" --opt -h $dbHost -u $dbUser";
            if ($dbPassword) {
                $backupCommand .= " -p$dbPassword";
            }
            $backupCommand .= " $dbName > \"$preRestoreBackupFile\"";
            
            system($backupCommand, $backupReturnVar);
            
            if($backupReturnVar === 0 && file_exists($preRestoreBackupFile) && filesize($preRestoreBackupFile) > 0) {
                // Thêm bản sao lưu trước khi khôi phục vào bảng backups
                $backupName = 'Sao lưu trước khi khôi phục ' . date('Y-m-d H:i:s');
                $backupSize = filesize($preRestoreBackupFile);
                $backupDate = date('Y-m-d H:i:s');
                
                $insertSql = "INSERT INTO backups (backup_name, backup_path, backup_size, backup_date) 
                              VALUES (?, ?, ?, ?)";
                
                $insertStmt = mysqli_prepare($conn, $insertSql);
                mysqli_stmt_bind_param($insertStmt, "ssis", $backupName, $preRestoreBackupFile, $backupSize, $backupDate);
                mysqli_stmt_execute($insertStmt);
                
                // Thực hiện lệnh khôi phục
                $command = "\"$mysqlPath\" -h $dbHost -u $dbUser";
                if ($dbPassword) {
                    $command .= " -p$dbPassword";
                }
                $command .= " $dbName < \"$backupFile\"";
                
                // Ghi log trước khi thực hiện
                $logMessage = date('Y-m-d H:i:s') . " - Thực hiện lệnh khôi phục: $command\n";
                file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
                
                // Sử dụng system thay vì exec để xem output chi tiết
                ob_start();
                system($command, $returnVar);
                $output = ob_get_contents();
                ob_end_clean();
                
                if($returnVar === 0) {
                    $success = "Khôi phục dữ liệu thành công từ bản sao lưu: " . $backupInfo['backup_name'];
                    
                    // Ghi log
                    $logMessage = date('Y-m-d H:i:s') . " - Khôi phục thành công từ: " . $backupInfo['backup_name'] . "\n";
                    file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
                } else {
                    $error = "Lỗi khi khôi phục dữ liệu. Mã lỗi: $returnVar";
                    if($output) {
                        $error .= "<br>Chi tiết: " . nl2br(htmlspecialchars($output));
                    }
                    
                    // Ghi log lỗi
                    $logMessage = date('Y-m-d H:i:s') . " - Lỗi khi khôi phục: $output\n";
                    file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
                }
            } else {
                $error = "Không thể tạo bản sao lưu trước khi khôi phục. Quá trình khôi phục đã bị hủy.";
            }
        } else {
            $error = "File sao lưu không tồn tại: " . $backupFile;
        }
    } else {
        $error = "Không tìm thấy thông tin bản sao lưu!";
    }
}

// Xử lý yêu cầu xóa bản sao lưu
if(isset($_POST['delete_backup']) && isset($_POST['backup_id'])) {
    $backupId = $_POST['backup_id'];
    
    // Lấy thông tin file backup
    $sql = "SELECT * FROM backups WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $backupId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($backupInfo = mysqli_fetch_assoc($result)) {
        $backupFile = $backupInfo['backup_path'];
        
        // Xóa file nếu tồn tại
        if(file_exists($backupFile)) {
            unlink($backupFile);
        }
        
        // Xóa bản ghi trong database
        $sql = "DELETE FROM backups WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $backupId);
        
        if(mysqli_stmt_execute($stmt)) {
            $success = "Đã xóa bản sao lưu: " . $backupInfo['backup_name'];
        } else {
            $error = "Lỗi khi xóa bản ghi sao lưu: " . mysqli_error($conn);
        }
    } else {
        $error = "Không tìm thấy thông tin bản sao lưu!";
    }
}

// Lấy danh sách bản sao lưu
$backups = [];
$sql = "SELECT * FROM backups ORDER BY backup_date DESC";
$result = mysqli_query($conn, $sql);
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $backups[] = $row;
    }
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Quản lý sao lưu dữ liệu
            <small>Tạo và khôi phục bản sao lưu</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> Tổng quan</a></li>
            <li class="active">Quản lý sao lưu</li>
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
            <div class="col-md-4">
                <!-- Tạo bản sao lưu mới -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tạo bản sao lưu mới</h3>
                    </div>
                    <!-- form start -->
                    <form method="post" action="">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="backup_name">Tên bản sao lưu:</label>
                                <input type="text" class="form-control" id="backup_name" name="backup_name" 
                                       placeholder="Nhập tên cho bản sao lưu" value="Sao lưu thủ công <?php echo date('d/m/Y H:i:s'); ?>">
                            </div>
                            <p><strong>Lưu ý:</strong> Quá trình sao lưu có thể mất vài phút tùy thuộc vào kích thước cơ sở dữ liệu.</p>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="create_backup" class="btn btn-primary">
                                <i class="fa fa-database"></i> Tạo bản sao lưu
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Thông tin về sao lưu tự động -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Sao lưu tự động</h3>
                    </div>
                    <div class="box-body">
                        <p>Hệ thống được cấu hình để tự động tạo bản sao lưu vào <strong>23:00</strong> hàng ngày.</p>
                        <p>Các bản sao lưu sẽ được lưu trữ tại: <code><?php echo $backupDir; ?></code></p>
                        <p>Hệ thống chỉ giữ lại <strong>30</strong> bản sao lưu gần nhất để tiết kiệm không gian lưu trữ.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Danh sách bản sao lưu -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Danh sách bản sao lưu</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Tên bản sao lưu</th>
                                        <th>Ngày tạo</th>
                                        <th>Kích thước</th>
                                        <th style="width: 200px">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($backups) > 0): ?>
                                        <?php foreach($backups as $index => $backup): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($backup['backup_name']); ?></td>
                                                <td><?php echo date('d/m/Y H:i:s', strtotime($backup['backup_date'])); ?></td>
                                                <td><?php echo round($backup['backup_size']/1024/1024, 2); ?> MB</td>
                                                <td>
                                                    <form method="post" action="" style="display: inline;">
                                                        <input type="hidden" name="backup_id" value="<?php echo $backup['id']; ?>">
                                                        <button type="submit" name="restore_backup" class="btn btn-info btn-sm" 
                                                                onclick="return confirm('Bạn có chắc chắn muốn khôi phục dữ liệu từ bản sao lưu này?\nLưu ý: Dữ liệu hiện tại sẽ bị ghi đè!');">
                                                            <i class="fa fa-undo"></i> Khôi phục
                                                        </button>
                                                    </form>
                                                    
                                                    <form method="post" action="" style="display: inline;">
                                                        <input type="hidden" name="backup_id" value="<?php echo $backup['id']; ?>">
                                                        <button type="submit" name="delete_backup" class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Bạn có chắc chắn muốn xóa bản sao lưu này?');">
                                                            <i class="fa fa-trash"></i> Xóa
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Chưa có bản sao lưu nào.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Hướng dẫn -->
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Hướng dẫn sử dụng</h3>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-info">
                            <h4>Tạo bản sao lưu thủ công</h4>
                            <p>Bạn có thể tạo bản sao lưu thủ công bất cứ lúc nào bằng cách nhập tên và nhấn nút "Tạo bản sao lưu".</p>
                        </div>
                        
                        <div class="callout callout-warning">
                            <h4>Khôi phục dữ liệu</h4>
                            <p><strong>Lưu ý:</strong> Khôi phục dữ liệu sẽ ghi đè toàn bộ dữ liệu hiện tại bằng dữ liệu từ bản sao lưu.</p>
                            <p>Hệ thống sẽ tự động tạo một bản sao lưu của dữ liệu hiện tại trước khi thực hiện khôi phục.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Include footer
include('../layouts/footer.php');
?>