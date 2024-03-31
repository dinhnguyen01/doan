<?php
// Kết nối cơ sở dữ liệu và các cài đặt khác ở đây
include("admin/inc/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id'], $_POST['size_id'], $_POST['color_id'])) {
    // Nhận dữ liệu từ yêu cầu AJAX
    $product_id = $_POST['product_id'];
    $size_id = $_POST['size_id'];
    $color_id = $_POST['color_id'];

    // Thực hiện truy vấn để lấy số lượng sản phẩm từ bảng tbl_prodcut_quantity dựa trên product_id, size_id, và color_id
    $statement = $pdo->prepare("SELECT quantity FROM tbl_product_quantity WHERE product_id = ? AND size_id = ? AND color_id = ?");
    $statement->execute([$product_id, $size_id, $color_id]);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Trả về số lượng sản phẩm
        echo $result['quantity'];
    } else {
        echo "0";
    }
    exit; // Dừng xử lý tại đây sau khi đã xử lý yêu cầu
}

// Nếu không có yêu cầu POST hoặc dữ liệu không đầy đủ, trả về thông báo lỗi
echo "Yêu cầu không hợp lệ.";
?>
