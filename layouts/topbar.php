<body class="hold-transition skin-blue sidebar-mini">
  <div class="wrapper">
    <header class="main-header">
      <a href="index.php?p=index&a=statistic" class="logo">
        <span class="logo-mini"><b>HP</b>SF</span>
        <span class="logo-lg"><b>HPSOFT</b></span>
      </a>
      <nav class="navbar navbar-static-top">
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
          <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">
          <ul class="nav navbar-nav">
            <!-- Nút gửi đơn nghỉ phép -->
            <li>
              <a href="xinnghiphep.php" title="Gửi đơn nghỉ phép">
                <i class="fa fa-paper-plane"></i> <span>Xin nghỉ phép</span>
              </a>
            </li>

            <!-- Nút xem danh sách đơn nghỉ phép -->
            <li>
              <a href="xemdonnghiphep.php" title="Xem danh sách đơn nghỉ phép">
                <i class="fa fa-list"></i> <span>Đơn đã gửi</span>
              </a>
            </li>

            <?php
            // Lấy thông tin nhân viên từ tài khoản đăng nhập
            $username = $row_acc['Taikhoan'];
            $queryNhanVien = "SELECT nv.MaNV, nv.Hoten, nv.Avatar, nv.ChucVu, pb.TenPhongBan 
                             FROM nhanvien nv
                             LEFT JOIN phongban pb ON nv.MaPb = pb.MaPb
                             JOIN taikhoan tk ON nv.MaNV = tk.MaNV
                             WHERE tk.Taikhoan = '$username'";
            $resultNhanVien = mysqli_query($conn, $queryNhanVien);
            $thongTinNV = mysqli_fetch_assoc($resultNhanVien);

            // Thiết lập giá trị mặc định nếu không tìm thấy thông tin
            $tenNhanVien = isset($thongTinNV['Hoten']) ? $thongTinNV['Hoten'] : $username;
            $chucVu = isset($thongTinNV['ChucVu']) && !empty($thongTinNV['ChucVu']) ? 
                     $thongTinNV['ChucVu'] : ($row_acc['Chucvu'] == 'admin' ? 'Quản trị viên' : 'Nhân viên');
            $phongBan = isset($thongTinNV['TenPhongBan']) ? $thongTinNV['TenPhongBan'] : '';
            $hinhAnh = isset($thongTinNV['Avatar']) && !empty($thongTinNV['Avatar']) ? 
                      $thongTinNV['Avatar'] : 'default-avatar.png';
            $maNV = isset($thongTinNV['MaNV']) ? $thongTinNV['MaNV'] : '';
            ?>

            <!-- Dropdown thông tin người dùng -->
            <li class="dropdown user user-menu">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="../uploads/avatars/<?php echo $hinhAnh; ?>" class="user-image" alt="User Image">
                <span class="hidden-xs"><?php echo $tenNhanVien; ?></span>
              </a>
              <ul class="dropdown-menu">
                <li class="user-header">
                  <img src="../uploads/avatars/<?php echo $hinhAnh; ?>" class="img-circle" alt="User Image">
                  <p>
                    <?php echo $tenNhanVien; ?>
                    <small><?php echo $chucVu; ?><?php echo !empty($phongBan) ? ' - ' . $phongBan : ''; ?></small>
                  </p>
                </li>
                <li class="user-footer">
                  <div class="pull-left">
                    <a href="chitietnhanvien.php?id=<?php echo $maNV; ?>&p=staff&a=detail-staff" class="btn btn-default btn-flat">Thông tin NV</a>
                  </div>
                  <div class="pull-right">
                    <a href="logout.php" class="btn btn-default btn-flat">Đăng xuất</a>
                  </div>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
    </header>
