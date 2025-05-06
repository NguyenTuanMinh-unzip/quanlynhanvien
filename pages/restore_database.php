<?php
/**
 * Script khôi phục cơ sở dữ liệu từ bản sao lưu
 * Được gọi từ trang admin khi cần khôi phục
 */

// Kiểm tra đăng nhập và quyền admin
session_start();
require_once __DIR__ . '/../config.php';

// Kiểm tra quyền người dùng - CHỈ CHO PHÉP ADMIN
if(!isset($_SESSION['username']) || !isset($row_acc) || $row_acc['Chucvu'] != 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

// Thư mục lưu backup
$backupDir = __DIR__ . '/../../backups/';

// Kiểm tra tham số
if(!isset($_GET['id']) || empty($_GET['id'])) {
    die("Không tìm thấy ID bản sao lưu!");
}

$backupId = $_GET['id'];

// Lấy thông tin bản sao lưu
$sql = "SELECT * FROM backups WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $backupId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if($backupInfo = mysqli_fetch_assoc($result)) {
    $backupFile = $backupInfo['backup_path'];
    
    // Kiểm tra file tồn tại
    if(file_exists($backupFile)) {
        // Tạo một bản sao lưu của dữ liệu hiện tại trước khi khôi phục
        $currentBackupFile = $backupDir . 'pre_restore_' . date('Y-m-d_His') . '.sql';
        
        // Lấy thông tin kết nối
        $dbHost = $conn->host_info;
        $dbUser = parse_url(mysqli_get_host_info($conn), PHP_URL_USER) ?: 'root';
        $dbPassword = ''; // Cần thiết lập trong thực tế
        $dbName = $conn->database ?: 'tenmysql';
        
        // Đảm bảo đã đặt giá trị đúng cho các biến kết nối
        if (!$dbHost || !$dbUser || !$dbName) {
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPassword = '';
            $dbName = 'tenmysql';
        }
        
        // Thực hiện lệnh backup hiện tại trước khi khôi phục
        $backupCommand = "mysqldump --opt -h $dbHost -u $dbUser ";
        if ($dbPassword) {
            $backupCommand .= "-p$dbPassword ";
        }
        $backupCommand .= "$dbName > $currentBackupFile";
        
        exec($backupCommand, $backupOutput, $backupReturnVar);
        
        if($backupReturnVar !== 0) {
            die("Lỗi khi tạo bản sao lưu trước khi khôi phục: " . implode("<br>", $backupOutput));
        }
        
        // Lưu thông tin bản sao lưu này vào database
        $backupName = 'Tự động sao lưu trước khi khôi phục ' . date('Y-m-d H:i:s');
        $backupSize = filesize($currentBackupFile);
        $backupDate = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO backups (backup_name, backup_path, backup_size, backup_date) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssis", $backupName, $currentBackupFile, $backupSize, $backupDate);
        mysqli_stmt_execute($stmt);
        
        // Thực hiện lệnh khôi phục
        $restoreCommand = "mysql -h $dbHost -u $dbUser ";
        if ($dbPassword) {
            $restoreCommand .= "-p$dbPassword ";
        }
        $restoreCommand .= "$dbName < $backupFile";
        
        exec($restoreCommand, $restoreOutput, $restoreReturnVar);
        
        if($restoreReturnVar === 0) {
            // Ghi log
            $logMessage = date('Y-m-d H:i:s') . " - Khôi phục thành công từ: " . $backupInfo['backup_name'] . "\n";
            file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
            
            // Chuyển hướng về trang quản lý sao lưu
            header("Location: backup_manager.php?success=1&message=" . urlencode("Khôi phục dữ liệu thành công từ bản sao lưu: " . $backupInfo['backup_name']));
            exit;
        } else {
            // Ghi log lỗi
            $logMessage = date('Y-m-d H:i:s') . " - Lỗi khi khôi phục: " . implode("\n", $restoreOutput) . "\n";
            file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
            
            die("Lỗi khi khôi phục dữ liệu: " . implode("<br>", $restoreOutput));
        }
    } else {
        die("File sao lưu không tồn tại: " . $backupFile);
    }
} else {
    die("Không tìm thấy thông tin bản sao lưu!");
}