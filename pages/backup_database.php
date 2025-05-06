<?php
/**
 * Script tự động sao lưu cơ sở dữ liệu
 * Được thiết kế để chạy trên môi trường XAMPP
 */

// Thư mục lưu backup (nên chỉnh sửa đường dẫn phù hợp với cấu trúc thư mục của bạn)
$backupDir = __DIR__ . '/../backups/';

// Đối với XAMPP, bạn có thể sử dụng đường dẫn tuyệt đối
// $backupDir = 'C:/xampp/htdocs/tên_dự_án_của_bạn/backups/';

// Kiểm tra và tạo thư mục nếu chưa tồn tại
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Thông tin kết nối CSDL trực tiếp
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = ''; // Thay đổi nếu bạn đã thiết lập mật khẩu cho MySQL
$dbName = 'quanlynhanvien'; // Tên database của bạn

// Kết nối đến database để thực hiện thao tác với bảng backups
require_once __DIR__ . '/../config.php';

// Tên file backup (format: backup_yyyy-mm-dd_hhmmss.sql)
$backupFile = $backupDir . 'backup_' . date('Y-m-d_His') . '.sql';

// Đường dẫn đến mysqldump.exe trong XAMPP
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

// Thực thi lệnh và hiển thị output
system($command, $returnVar);

// Kiểm tra kết quả
if ($returnVar === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
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
    
    // Thêm thông tin về bản sao lưu vào cơ sở dữ liệu
    $backupName = 'Tự động sao lưu ' . date('Y-m-d H:i:s');
    $backupPath = $backupFile;
    $backupSize = filesize($backupFile);
    $backupDate = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO backups (backup_name, backup_path, backup_size, backup_date) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssis", $backupName, $backupPath, $backupSize, $backupDate);
    mysqli_stmt_execute($stmt);
    
    // Ghi log
    $logMessage = date('Y-m-d H:i:s') . " - Sao lưu thành công: $backupFile - Kích thước: " . round($backupSize/1024/1024, 2) . " MB\n";
    file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
    
    // Xóa các bản sao lưu cũ (chỉ giữ lại 30 bản sao lưu gần nhất)
    $backupFiles = glob($backupDir . 'backup_*.sql');
    if (count($backupFiles) > 30) {
        // Sắp xếp theo thời gian tạo
        usort($backupFiles, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Xóa các file cũ nhất
        $filesToDelete = array_slice($backupFiles, 0, count($backupFiles) - 30);
        foreach ($filesToDelete as $file) {
            unlink($file);
            
            // Xóa bản ghi trong database
            $sql = "DELETE FROM backups WHERE backup_path = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $file);
            mysqli_stmt_execute($stmt);
        }
    }
    
    echo "Sao lưu thành công: $backupFile\n";
    echo "Kích thước file: " . round($backupSize/1024/1024, 2) . " MB\n";
    exit(0);
} else {
    // Ghi log lỗi
    $errorMessage = "File backup không tồn tại hoặc kích thước bằng 0.";
    if (!file_exists($backupFile)) {
        $errorMessage = "File backup không được tạo.";
    } elseif (filesize($backupFile) == 0) {
        $errorMessage = "File backup có kích thước 0 byte.";
    }
    
    $logMessage = date('Y-m-d H:i:s') . " - Lỗi khi sao lưu: $errorMessage\n";
    file_put_contents($backupDir . 'backup_log.txt', $logMessage, FILE_APPEND);
    
    echo "Lỗi khi sao lưu: $errorMessage\n";
    echo "Mã lỗi: $returnVar\n";
    exit(1);
}