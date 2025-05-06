<?php     
    // get active sidebar
    if(isset($_GET['p']) && isset($_GET['a']))
    {
        $p = $_GET['p'];
        $a = $_GET['a'];
    }
?>

<!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <!-- <div class="pull-left image">
          <img src="../uploads/images/<?php echo $row_acc['hinh_anh']; ?>" class="img-circle" alt="User Image">
        </div> -->
        <!-- <div class="pull-left info">
          <p>
            <?php echo $row_acc['Taikhoan']; ?>
          </p>
          <a href="#"><i class="fa fa-circle text-success"></i>
            <?php 
              if($row_acc['Chucvu'] == 1)
              {
                echo "Quản trị viên";
              }
              else
              {
                echo "Nhân viên";
              }
            ?>
          </a>
        </div> -->
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Tìm kiếm...">
          <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
        </div>
      </form>
      <ul class="sidebar-menu" data-widget="tree">
        <li class="<?php if($p == 'index') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-dashboard"></i> <span>Tổng quan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if($a == 'statistic') echo 'active'; ?>"><a href="index.php?p=index&a=statistic"><i class="fa fa-circle-o"></i> Thống kê</a></li>
            <li class="<?php if($a == 'nhanvien') echo 'active'; ?>"><a a href="dulieuchamcong.php"><i class="fa fa-circle-o"></i> Thống kê chấm công</a></li>
            <li class="<?php if(($p == 'index') && ($a == 'taikhoan')) echo 'active'; ?>"><a href="thongkeluong.php"><i class="fa fa-circle-o"></i> Thống kê lương</a></li>
          </ul>
        </li>
        <li class="<?php if($p == 'staff') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-users"></i>
            <span>Quản lý Nhân viên</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- <li class="<?php if(($p == 'staff') && ($a == 'room')) echo 'active'; ?>"><a href="phongban.php?p=staff&a=room"><i class="fa fa-circle-o"></i> Phòng ban</a></li> -->
            <li class="<?php if(($p == 'staff') && ($a == 'position')) echo 'active'; ?>"><a href="chucvu.php?p=staff&a=position"><i class="fa fa-circle-o"></i> Chức vụ</a></li>
            <li class="<?php if(($p == 'staff') && ($a == 'add-staff')) echo 'active'; ?>"><a href="themnhanvien.php?p=staff&a=add-staff"><i class="fa fa-circle-o"></i> Thêm mới nhân viên</a></li>
            <li class="<?php if(($p == 'staff') && ($a =='list-staff')) echo 'active'; ?>"><a href="danhsachnhanvien.php?p=staff&a=list-staff"><i class="fa fa-circle-o"></i> Danh sách nhân viên</a></li>
          </ul>
        </li>
        <li class="<?php if($p == 'collaborate') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Quản lý phòng ban</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(($p == 'collaborate') && ($a =='add-collaborate')) echo 'active'; ?>"><a href="themphongban.php?p=collaborate&a=add-collaborate"><i class="fa fa-circle-o"></i> Tạo phòng ban</a></li>
            <li class="<?php if(($p == 'collaborate') && ($a =='list-collaborate')) echo 'active'; ?>"><a href="danhsachphongban.php?p=collaborate&a=list-collaborate"><i class="fa fa-circle-o"></i> Danh sách phòng ban</a></li>
          </ul>
        </li>
        <li class="<?php if($p == 'salary') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-money"></i>
            <span>Quản lý lương</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(($p == 'salary') && ($a =='salary')) echo 'active'; ?>">
              <a href="thembangluong.php?p=salary&a=salary"><i class="fa fa-circle-o"></i> Thêm bảng lương</a>
            </li>
            <li class="<?php if(($p == 'salary') && ($a =='calculator')) echo 'active'; ?>">
              <a href="tinhluong.php?p=salary&a=calculator"><i class="fa fa-circle-o"></i> Tính lương</a>
            </li>
            <li class="<?php if(($p == 'salary') && ($a =='list-group')) echo 'active'; ?>">
              <a href="danhsachbangluong.php?p=salary&a=list-group"><i class="fa fa-circle-o"></i> Danh sách bảng lương</a>
            </li>
              <?php if(isset($row_acc) && $row_acc['Chucvu'] != 'admin'): ?>
            <li class="<?php if(($p == 'salary') && ($a =='detail')) echo 'active'; ?>">
              <a href="chitietluong.php?p=salary&a=detail"><i class="fa fa-circle-o"></i> Chi tiết lương</a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <li class="<?php if($p == 'group') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-users"></i> <span>Chấm công</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(($p == 'chamcong') && ($a =='add-group')) echo 'active'; ?>"><a href="chamcong.php?p=chamcong&a=add-group"><i class="fa fa-circle-o"></i> Chấm công</a></li>
            <li class="<?php if(($p == 'chamcong') && ($a =='list-group')) echo 'active'; ?>"><a href="dulieuchamcong.php?p=chamcong&a=list-group"><i class="fa fa-circle-o"></i> Dữ liệu chấm công</a></li>
            <!-- <li class="<?php if(($p == 'group') && ($a =='list-group')) echo 'active'; ?>"><a href="chitietchamcong.php?p=group&a=list-group"><i class="fa fa-circle-o"></i> Chi tiết chấm công</a></li> -->
          </ul>
        </li>
        <li class="<?php if($p == 'bonus-discipline') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-star"></i> <span>Khen thưởng - Kỷ luật</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?php if(($p == 'bonus-discipline') && ($a =='bonus')) echo 'active'; ?>"><a href="themkhenthuong.php?p=bonus-discipline&a=bonus"><i class="fa fa-circle-o"></i>Thêm khen thưởng</a></li>
            <li class="<?php if(($p == 'bonus-discipline') && ($a =='discipline')) echo 'active'; ?>"><a href="themkyluat.php?p=bonus-discipline&a=discipline"><i class="fa fa-circle-o"></i> Thêm kỷ luật</a></li>
            <li class="<?php if(($p == 'group') && ($a =='list-group')) echo 'active'; ?>"><a href="danhsachkhenthuong.php?p=bonus-discipline&a=discipline"><i class="fa fa-circle-o"></i> Danh sách khen thưởng</a></li>
            <li class="<?php if(($p == 'group') && ($a =='list-group')) echo 'active'; ?>"><a href="danhsachkyluat.php?p=bonus-discipline&a=discipline"><i class="fa fa-circle-o"></i> Danh sách kỷ luật</a></li>
          </ul>
        </li>
        
        <li class="<?php if($p == 'account') echo 'active'; ?> treeview">
          <a href="#">
            <i class="fa fa-user-plus"></i> <span>Tài khoản</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- <li class="<?php if($a == 'profile') echo 'active'; ?>"><a href="thong-tin-tai-khoan.php?p=account&a=profile"><i class="fa fa-circle-o"></i> Thông tin tài khoản</a></li> -->
            <li class="<?php if($a == 'add-account') echo 'active'; ?>"><a href="dangky.php?p=account&a=add-account"><i class="fa fa-circle-o"></i> Tạo tài khoản</a></li>
            <li class="<?php if(($p == 'account') && ($a == 'list-account')) echo 'active'; ?>"><a href="danhsachtaikhoan.php?p=account&a=list-account"><i class="fa fa-circle-o"></i> Danh sách tài khoản</a></li>
          </ul>
        </li>
        <?php if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): ?>
          <li class="<?php if($p == 'resign') echo 'active'; ?>">
            <a href="quanlynghiviec.php?p=resign&a=list">
              <i class="fa fa-sign-out"></i> <span>Quản lý nghỉ việc</span>
            </a>
        </li>
        <?php endif; ?>

        <?php 

        if(isset($row_acc) && $row_acc['Chucvu'] == 'admin'): 
        ?>
        <li >
          <a href="khoiphuc.php">
            <i class="fa fa-database"></i> <span>Sao lưu & Khôi phục</span>
          </a>
        </li>

        <?php endif; ?>
        <li >
          <a href="doimatkhau.php">
            <i class="fa fa-key"></i> <span>Đổi mật khẩu</span>
          </a>
        </li>
        <li >
          <a href="logout.php">
            <i class="fa fa-sign-out"></i> <span>Đăng xuất</span>
          </a>
        </li>
      </ul>
            </section>
  </aside>