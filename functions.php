<?php
// Hàm tạo mã nghỉ phép tự động
function generateLeaveID($conn) {
    $query = "SELECT MAX(CAST(SUBSTRING(MaNP, 3) AS UNSIGNED)) as max_id FROM nghiphep";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    $nextId = 1;
    if ($row['max_id']) {
        $nextId = $row['max_id'] + 1;
    }
    
    return 'NP' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
}
?>