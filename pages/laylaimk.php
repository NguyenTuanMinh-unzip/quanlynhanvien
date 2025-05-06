<?php
session_start();
require_once '../config.php';

// Kiểm tra nếu có submit form
if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    
    // Kiểm tra email có tồn tại trong bảng nhanvien không
    $sql = "SELECT * FROM nhanvien WHERE Email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Lấy MaNV từ kết quả truy vấn
        $maNV = $user['MaNV'];
        
        // Sinh OTP 6 chữ số
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Kiểm tra xem đã có bản ghi OTP cho nhân viên này chưa
        $check_sql = "SELECT * FROM otp_requests WHERE MaNV = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $maNV);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Nếu đã có, cập nhật bản ghi hiện có
            $update_sql = "UPDATE otp_requests SET otp_code = ?, otp_expiry = ? WHERE MaNV = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sss", $otp, $expiry, $maNV);
            $result = mysqli_stmt_execute($update_stmt);
        } else {
            // Nếu chưa có, tạo bản ghi mới
            $insert_sql = "INSERT INTO otp_requests (MaNV, otp_code, otp_expiry) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "sss", $maNV, $otp, $expiry);
            $result = mysqli_stmt_execute($insert_stmt);
        }
        
        if ($result) {
            // Gửi OTP ra màn hình (trong thực tế nên gửi qua email)
            echo "<div class='alert alert-success'>OTP của bạn là: $otp</div>";
            echo "<p>Nhập OTP tại form đổi mật khẩu!</p>";
            
            // Lưu email và MaNV vào session để xác thực OTP sau
            $_SESSION['email'] = $email;
            $_SESSION['MaNV'] = $maNV;
            
            // Chuyển hướng đến trang reset_password.php sau 3 giây
            echo "<p>Đang chuyển hướng đến trang đặt lại mật khẩu...</p>";
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'reset_password.php';
                }, 10000);
            </script>";
        } else {
            echo "<div class='alert alert-danger'>Có lỗi xảy ra khi tạo OTP. Vui lòng thử lại!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email không tồn tại trong hệ thống!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Quên mật khẩu</h3>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="email">Email đăng ký:</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Nhập email của bạn" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Gửi mã OTP</button>
                </form>
                <div class="mt-3">
                    <a href="login.php" class="btn btn-link">Quay lại đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>