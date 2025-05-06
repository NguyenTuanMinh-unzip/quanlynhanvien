<?php
// reset_password.php
session_start();
require_once '../config.php';

// Kiểm tra xem người dùng có nhập email trước không
if (!isset($_SESSION['email']) || !isset($_SESSION['MaNV'])) {
    echo "<script>
        alert('Vui lòng nhập email trước tại trang lấy lại mật khẩu');
        window.location.href = 'laylaimk.php';
    </script>";
    exit;
}

$email = $_SESSION['email'];
$maNV = $_SESSION['MaNV'];
$message = '';
$success = false;

if (isset($_POST['reset'])) {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kiểm tra mật khẩu xác nhận
    if ($new_password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>Mật khẩu xác nhận không khớp</div>";
    }
    // Kiểm tra độ dài mật khẩu
    else if (strlen($new_password) < 6) {
        $message = "<div class='alert alert-danger'>Mật khẩu phải có ít nhất 6 ký tự</div>";
    }
    else {
        // Lấy OTP và thời gian hết hạn từ database
        $sql = "SELECT otp_code, otp_expiry FROM otp_requests WHERE MaNV = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $maNV);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($otp_data = mysqli_fetch_assoc($result)) {
            // Kiểm tra OTP và thời gian hết hạn
            if ($otp_data['otp_code'] === $otp && strtotime($otp_data['otp_expiry']) > time()) {
                // Mã hóa mật khẩu mới sử dụng password_hash
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Cập nhật mật khẩu trong bảng taikhoan
                $update_sql = "UPDATE taikhoan SET Matkhau = ? WHERE MaNV = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $maNV);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    // Xóa OTP khi đã sử dụng
                    $delete_otp_sql = "DELETE FROM otp_requests WHERE MaNV = ?";
                    $delete_otp_stmt = mysqli_prepare($conn, $delete_otp_sql);
                    mysqli_stmt_bind_param($delete_otp_stmt, "s", $maNV);
                    mysqli_stmt_execute($delete_otp_stmt);
                    
                    $message = "<div class='alert alert-success'>Đổi mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.</div>";
                    $success = true;
                    
                    // Reset số lần đăng nhập sai (nếu có)
                    if (isset($login_attempts)) {
                        $reset_attempts_sql = "UPDATE taikhoan SET login_attempts = 0, last_failed_login = NULL WHERE MaNV = ?";
                        $reset_attempts_stmt = mysqli_prepare($conn, $reset_attempts_sql);
                        mysqli_stmt_bind_param($reset_attempts_stmt, "s", $maNV);
                        mysqli_stmt_execute($reset_attempts_stmt);
                    }
                    
                    // Xóa session sau 3 giây
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 3000);
                    </script>";
                } else {
                    $message = "<div class='alert alert-danger'>Lỗi khi cập nhật mật khẩu: " . mysqli_error($conn) . "</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>OTP không đúng hoặc đã hết hạn.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Không tìm thấy yêu cầu OTP cho tài khoản này.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .card {
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Đặt lại mật khẩu</h3>
            </div>
            <div class="card-body">
                <?php echo $message; ?>
                
                <?php if (!$success): ?>
                <p>Vui lòng nhập mã OTP đã được gửi đến email <strong><?php echo htmlspecialchars($email); ?></strong></p>
                
                <form method="post" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="otp">Mã OTP:</label>
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Nhập mã OTP 6 chữ số" required maxlength="6" pattern="[0-9]{6}">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required minlength="6">
                        <small class="password-requirements">Mật khẩu phải có ít nhất 6 ký tự</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                    </div>
                    <button type="submit" name="reset" class="btn btn-primary btn-block">Xác nhận</button>
                </form>
                <?php else: ?>
                <p>Đang chuyển hướng đến trang đăng nhập...</p>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="laylaimk.php" class="btn btn-link">Quay lại gửi OTP</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validateForm() {
        var newPassword = document.getElementById('new_password').value;
        var confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword.length < 6) {
            alert('Mật khẩu phải có ít nhất 6 ký tự');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            alert('Mật khẩu xác nhận không khớp');
            return false;
        }
        
        return true;
    }
    </script>
</body>
</html>