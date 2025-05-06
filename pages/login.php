<?php
session_start();
require_once '../config.php';

// Cấu hình cho chức năng khóa tài khoản
define('MAX_LOGIN_ATTEMPTS', 5); // Số lần đăng nhập sai tối đa
define('LOCKOUT_TIME', 15); // Thời gian khóa tài khoản (phút)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['taikhoan']);
    $password = trim($_POST['password']);

    // Kiểm tra xem tài khoản có bị khóa không
    $check_lockout = $conn->prepare("SELECT login_attempts, last_failed_login FROM TaiKhoan WHERE Taikhoan = ?");
    $check_lockout->bind_param("s", $username);
    $check_lockout->execute();
    $lockout_result = $check_lockout->get_result();
    
    if ($lockout_result->num_rows === 1) {
        $lockout_data = $lockout_result->fetch_assoc();
        $login_attempts = $lockout_data['login_attempts'];
        $last_failed_login = $lockout_data['last_failed_login'];
        
        // Kiểm tra xem tài khoản có bị khóa tạm thời không
        if ($login_attempts >= MAX_LOGIN_ATTEMPTS && $last_failed_login) {
            $lockout_time = strtotime($last_failed_login) + (LOCKOUT_TIME * 60);
            $current_time = time();
            
            if ($current_time < $lockout_time) {
                $remaining_time = ceil(($lockout_time - $current_time) / 60);
                $error = "Tài khoản đã bị khóa tạm thời. Vui lòng thử lại sau $remaining_time phút.";
            } else {
                // Reset số lần đăng nhập sai nếu đã hết thời gian khóa
                $reset_attempts = $conn->prepare("UPDATE TaiKhoan SET login_attempts = 0 WHERE Taikhoan = ?");
                $reset_attempts->bind_param("s", $username);
                $reset_attempts->execute();
                $login_attempts = 0;
            }
        }
    }
    
    // Tiếp tục xử lý đăng nhập nếu không bị khóa
    if (!isset($error)) {
        // Truy vấn kiểm tra tài khoản
        $stmt = $conn->prepare("SELECT * FROM TaiKhoan WHERE Taikhoan = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Kiểm tra tài khoản tồn tại
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Kiểm tra mật khẩu đã mã hóa
            if (password_verify($password, $row['Matkhau'])) {
                // Đăng nhập thành công - reset số lần đăng nhập sai
                $reset_attempts = $conn->prepare("UPDATE TaiKhoan SET login_attempts = 0, last_failed_login = NULL WHERE Taikhoan = ?");
                $reset_attempts->bind_param("s", $username);
                $reset_attempts->execute();
                
                $_SESSION['username'] = $username;
                $_SESSION['chucvu'] = $row['Chucvu'];
                $_SESSION['MaNV'] = $row['MaNV'];
                
                header("Location: index.php");
                exit();
            } else {
                // Mật khẩu sai - tăng số lần đăng nhập sai
                $update_attempts = $conn->prepare("UPDATE TaiKhoan SET login_attempts = login_attempts + 1, last_failed_login = NOW() WHERE Taikhoan = ?");
                $update_attempts->bind_param("s", $username);
                $update_attempts->execute();
                
                // Kiểm tra số lần đăng nhập sai
                $check_attempts = $conn->prepare("SELECT login_attempts FROM TaiKhoan WHERE Taikhoan = ?");
                $check_attempts->bind_param("s", $username);
                $check_attempts->execute();
                $attempts_result = $check_attempts->get_result();
                $attempts_row = $attempts_result->fetch_assoc();
                
                $remaining_attempts = MAX_LOGIN_ATTEMPTS - $attempts_row['login_attempts'];
                
                if ($remaining_attempts > 0) {
                    $error = "Mật khẩu không đúng. Bạn còn $remaining_attempts lần thử.";
                } else {
                    $error = "Tài khoản đã bị khóa tạm thời do nhập sai mật khẩu quá nhiều lần. Vui lòng thử lại sau " . LOCKOUT_TIME . " phút.";
                }
            }
        } else {
            $error = "Tài khoản không tồn tại.";
        }
        
        $stmt->close();
    }
}
?>

<!-- HTML Giao diện giữ nguyên, thêm hiển thị lỗi -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8fafc;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
  <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-xl shadow-lg">
    <div class="text-center">
      <h2 class="text-3xl font-extrabold text-gray-900">Welcome back</h2>
      <div class="mt-4 flex justify-center">
        <img src="anh/images.png" alt="Company Logo" class="h-20 w-auto">
      </div>
    </div>

    <!-- Hiển thị lỗi -->
    <?php if (!empty($error)): ?>
      <div class="bg-red-100 text-red-600 p-3 rounded text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form class="mt-8 space-y-6" method="POST">
      <div class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">User name</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-envelope text-gray-400"></i>
            </div>
            <input id="email" name="taikhoan" type="text" required
              class="py-3 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="employee code">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-lock text-gray-400"></i>
            </div>
            <input id="password" name="password" type="password" required
              class="py-3 pl-10 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="••••••••">
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <div class="text-sm">
          <a href="laylaimk.php" class="font-medium text-indigo-600 hover:text-indigo-500">Forgot password?</a>
        </div>
      </div>

      <div>
        <button type="submit"
          class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Sign in
        </button>
      </div>
    </form>
  </div>
</body>
</html>