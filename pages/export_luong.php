<?php
session_start();

if (isset($_SESSION['username'])) {
    require_once('../config.php');

    // Lấy thông tin tài khoản từ username đã lưu trong session
    $username = $_SESSION['username'];
    $sql_acc = "SELECT * FROM taikhoan WHERE Taikhoan = '$username'";
    $result_acc = mysqli_query($conn, $sql_acc);
    $row_acc = mysqli_fetch_assoc($result_acc);

    // Kiểm tra quyền truy cập
    if (!$row_acc || $row_acc['Chucvu'] != 'admin') {
        echo "<script>
            alert('Bạn không có quyền truy cập trang này!');
            window.location.href='index.php?p=index&a=statistic';
        </script>";
        exit;
    }

    // Lấy các tham số lọc
    $filterNam = isset($_GET['nam']) ? $_GET['nam'] : date('Y');
    $filterThang = isset($_GET['thang']) ? $_GET['thang'] : '';
    $filterPhongBan = isset($_GET['phongban']) ? $_GET['phongban'] : '';
    $filterMaNV = isset($_GET['manv']) ? $_GET['manv'] : '';

    // Tạo truy vấn lấy bảng lương
    $query = "SELECT bl.MaBangLuong, bl.MaNV, nv.Hoten, pb.TenPhongBan, bl.Thang, bl.Nam, 
                    bl.LuongCoBan, bl.NgayCongThucTe, bl.LuongTheoNgayCong, bl.GioTangCa, bl.LuongTangCa, 
                    bl.PhuCap, bl.Thuong, bl.Phat, bl.BHXH, bl.BHYT, bl.ThueTNCN, bl.ThucLanh 
              FROM bangluongthang bl
              JOIN NhanVien nv ON bl.MaNV = nv.MaNV
              JOIN PhongBan pb ON nv.MaPb = pb.MaPb
              WHERE bl.Nam = '$filterNam'";

    if (!empty($filterThang)) $query .= " AND bl.Thang = '$filterThang'";
    if (!empty($filterPhongBan)) $query .= " AND pb.MaPb = '$filterPhongBan'";
    if (!empty($filterMaNV)) $query .= " AND bl.MaNV = '$filterMaNV'";

    $query .= " ORDER BY bl.Nam DESC, bl.Thang DESC, nv.Hoten ASC";

    $result = mysqli_query($conn, $query);

    $title = "Bảng Lương";
    if (!empty($filterThang)) $title .= " Tháng " . $filterThang;
    $title .= " Năm " . $filterNam;

    $filename = "Bang_Luong";
    if (!empty($filterThang)) $filename .= "_Thang" . $filterThang;
    $filename .= "_Nam" . $filterNam . ".xls";

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html><head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<style>
        table {border-collapse: collapse; width: 100%;}
        th, td {border: 1px solid #000000; padding: 5px;}
        th {background-color: #f2f2f2; font-weight: bold; text-align: center;}
        .text-right {text-align: right;}
        .text-center {text-align: center;}
    </style>';
    echo '</head><body>';

    echo '<h3>' . $title . '</h3>';
    echo '<table><thead><tr>
        <th>STT</th><th>Mã NV</th><th>Họ và tên</th><th>Phòng ban</th><th>Tháng/Năm</th>
        <th>Lương cơ bản</th><th>Ngày công</th><th>Lương ngày công</th><th>Giờ tăng ca</th>
        <th>Lương tăng ca</th><th>Phụ cấp</th><th>Thưởng</th><th>Phạt</th>
        <th>BHXH</th><th>BHYT</th><th>Thuế TNCN</th><th>Thực lãnh</th>
    </tr></thead><tbody>';

    if (mysqli_num_rows($result) > 0) {
        $stt = 1;
        mysqli_data_seek($result, 0);
        $tong = [
            'LuongCoBan' => 0, 'LuongTheoNgayCong' => 0, 'LuongTangCa' => 0,
            'PhuCap' => 0, 'Thuong' => 0, 'Phat' => 0,
            'BHXH' => 0, 'BHYT' => 0, 'ThueTNCN' => 0, 'ThucLanh' => 0
        ];

        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td class="text-center">' . $stt++ . '</td>';
            echo '<td class="text-center">' . $row['MaNV'] . '</td>';
            echo '<td>' . $row['Hoten'] . '</td>';
            echo '<td>' . $row['TenPhongBan'] . '</td>';
            echo '<td class="text-center">' . $row['Thang'] . '/' . $row['Nam'] . '</td>';
            echo '<td class="text-right">' . number_format($row['LuongCoBan'], 0, ',', '.') . '</td>';
            echo '<td class="text-center">' . $row['NgayCongThucTe'] . '</td>';
            echo '<td class="text-right">' . number_format($row['LuongTheoNgayCong'], 0, ',', '.') . '</td>';
            echo '<td class="text-center">' . $row['GioTangCa'] . '</td>';
            echo '<td class="text-right">' . number_format($row['LuongTangCa'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['PhuCap'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['Thuong'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['Phat'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['BHXH'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['BHYT'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['ThueTNCN'], 0, ',', '.') . '</td>';
            echo '<td class="text-right">' . number_format($row['ThucLanh'], 0, ',', '.') . '</td>';
            echo '</tr>';

            // Tính tổng
            foreach ($tong as $key => $val) {
                $tong[$key] += $row[$key];
            }
        }

        echo '<tr style="font-weight: bold; background-color: #e6e6e6;">';
        echo '<td colspan="5" class="text-center">TỔNG CỘNG</td>';
        echo '<td class="text-right">' . number_format($tong['LuongCoBan'], 0, ',', '.') . '</td>';
        echo '<td></td>';
        echo '<td class="text-right">' . number_format($tong['LuongTheoNgayCong'], 0, ',', '.') . '</td>';
        echo '<td></td>';
        echo '<td class="text-right">' . number_format($tong['LuongTangCa'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['PhuCap'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['Thuong'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['Phat'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['BHXH'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['BHYT'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['ThueTNCN'], 0, ',', '.') . '</td>';
        echo '<td class="text-right">' . number_format($tong['ThucLanh'], 0, ',', '.') . '</td>';
        echo '</tr>';
    } else {
        echo '<tr><td colspan="17" align="center">Không có dữ liệu</td></tr>';
    }

    echo '</tbody></table>';

    // Thông tin người xuất báo cáo
    $maNV = $row_acc['MaNV'];
    $sql_nv = "SELECT HoTen FROM NhanVien WHERE MaNV = '$maNV'";
    $result_nv = mysqli_query($conn, $sql_nv);
    $row_nv = mysqli_fetch_assoc($result_nv);

    echo '<div style="margin-top: 20px; text-align: right;">';
    echo '<p>Ngày xuất báo cáo: ' . date('d/m/Y') . '</p>';
    echo '<p>Người xuất báo cáo: ' . ($row_nv['HoTen'] ?? 'Không xác định') . '</p>';
    echo '</div>';

    echo '</body></html>';
    mysqli_close($conn);

} else {
    header('Location: login.php');
    exit;
}
?>
