<?php
require('../tfpdf/tfpdf.php');
include("inc/config.php");

// function truncateText($text, $maxLength) {
//     if (strlen($text) > $maxLength) {
//         $text = substr($text, 0, $maxLength - 3) . '...';
//     }
//     return $text;
// }

extract($_POST);

if (isset($_POST['customerName']) && isset($_POST['invoiceDate'])) {
    $customer_name_id = $_POST['customerName'];
    $invoice_date = $_POST['invoiceDate'];

    // Thực hiện truy vấn để lấy thông tin khách hàng từ bảng tbl_customer
    $customer_statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id = ?");
    $customer_statement->execute(array($customer_name_id));
    $customer_info = $customer_statement->fetch(PDO::FETCH_ASSOC);

    // Khởi tạo class FPDF
    $pdf = new tFPDF('P', 'mm', 'A4');
    // $pdf = new PDF_MC_Table;
    $pdf->AddPage();

    // Font settings
    // $pdf->SetFont('Arial', 'B', 16);
    
    $pdf->AddFont('dejavusans', '', 'DejaVuSans.ttf', true);
    $pdf->AddFont('dejavusans', 'B', 'DejaVuSans-Bold.ttf', true);
    $pdf->AddFont('dejavusans', 'I', 'DejaVuSans-Oblique.ttf', true);
    $pdf->AddFont('dejavusans', 'BI', 'DejaVuSans-BoldOblique.ttf', true);

    // Lấy chiều rộng của trang
    $pageWidth = $pdf->GetPageWidth();

    $text1 = 'HÓA ĐƠN BÁN HÀNG';
    $text2 = 'NỘI THẤT ĐT HOME';
    $text3 = 'Quốc lộ 27, chợ Trung Hòa, Ea Ktur, Cư Kuin, Tỉnh Đắk Lắk';

    $pdf->SetFont('dejavusans', 'B', 16);
    $text_width1 = $pdf->GetStringWidth($text1);
    $text_width2 = $pdf->GetStringWidth($text2);
    $text_width3 = $pdf->GetStringWidth($text3);

    $leftMargin = 10;
    $rightMargin = $pageWidth - $text_width2 - $leftMargin;

    $pdf->SetX($leftMargin);
    $pdf->Cell($text_width1, 5, $text1, 0, 0, 'L');

    $pdf->SetX($rightMargin);
    $pdf->Cell($text_width2, 5, $text2, 0, 1, 'R');

    $currentX = $pdf->GetX(); // Lấy vị trí hiện tại X

    $remainingSpace = $pageWidth - $currentX; // Khoảng trống còn lại từ vị trí hiện tại đến lề phải

    $pdf->SetX($currentX + $remainingSpace - $text_width3); // Đặt vị trí X mới để căn chỉnh theo lề phải
    $pdf->SetFont('dejavusans', '', 9);
    $pdf->Cell($text_width3, 10, $text3, 0, 1, 'R');



    $pdf->Cell(10, 10, '', 0, 1);
    $pdf->SetFont('dejavusans', '', 14);
    // Hiển thị tên khách hàng từ thông tin lấy được
    $pdf->Cell(0, 10, 'Tên KH: ' . $customer_info['cust_name'], 0, 1);
    $pdf->Cell(0, 10, 'Địa chỉ: ' . $customer_info['cust_address'] . ', ' . $customer_info['cust_state'] . ', ' . $customer_info['cust_city'], 0, 1);
    $pdf->Cell(5, 5, '', 0, 1);

    // Lấy thông tin chi tiết đơn hàng từ bảng tbl_order và tbl_payment
    $order_statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id IN (
        SELECT payment_id 
        FROM tbl_payment 
        WHERE customer_id = ? 
        AND DATE(payment_date) = DATE(?))");
    $order_statement->execute(array($customer_name_id, $invoice_date));
    $orders = $order_statement->fetchAll(PDO::FETCH_ASSOC);

    
    if ($orders) {
        $pdf->SetFont('dejavusans', '', 14);

        $pdf->Cell(15, 10, 'STT', 1, 0, 'C');
        $pdf->Cell(75, 10, 'Tên sản phẩm', 1, 0, 'C');
        $pdf->Cell(15, 10, 'SL', 1, 0, 'C');
        $pdf->Cell(45, 10, 'Đơn giá', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Thành tiền', 1, 1, 'C');

        $total_amount = 0;
        $i = 0;
        foreach ($orders as $order) {
            $i++;
            $pdf->Cell(15, 10, $i, 1, 0, 'C');
            // $productName = truncateText($order['product_name'], 25); 
            $pdf->Cell(75, 10, $order['product_name'], 1, 0);
            $pdf->Cell(15, 10, $order['quantity'], 1, 0, 'C');
            $pdf->Cell(45, 10, number_format($order['unit_price'], 0, '.', ',') . '₫', 1, 0, 'R');
            $subtotal = $order['quantity'] * $order['unit_price'];
            $pdf->Cell(40, 10, number_format($subtotal, 0, '.', ',') . '₫', 1, 1, 'R');

            // Tính tổng thành tiền của các sản phẩm
            $total_amount += $subtotal;
        }


        $shipping_statement = $pdo->prepare("SELECT amount FROM tbl_shipping_cost WHERE country_id = ?");
        $shipping_statement->execute(array($customer_info['cust_country']));
        $cod_amount = $shipping_statement->fetchColumn();

        if ($cod_amount !== false) {
            $pdf->Cell(150, 10, 'COD', 1, 0, 'R');
            $pdf->Cell(40, 10, number_format($cod_amount, 0, '.', ',') . '₫', 1, 1, 'R');
        } else {
            $pdf->Cell(150, 10, 'COD', 1, 0, 'R');
            $pdf->Cell(40, 10, 'N/A', 1, 1, 'R'); // Hoặc thông báo khác nếu không có thông tin COD
        }
        // Hiển thị tổng cộng của hóa đơn
        $pdf->Cell(150, 10, 'Tổng cộng', 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($total_amount + $cod_amount, 0, '.', ',') . '₫', 1, 1, 'R');
        

        $pdf->Cell(5, 5, '', 0, 1);
        $invoice_date = date_create($_POST['invoiceDate']);
        $formatted_date = 'Ngày ' . date_format($invoice_date, 'd') . ' tháng ' . date_format($invoice_date, 'm') . ' năm ' . date_format($invoice_date, 'Y');
        $pdf->Cell(0, 10, $formatted_date, 0, 1, 'R');

        // Output as PDF
        $pdf->Output('hoadon_' . date_format($invoice_date, 'd-m-Y') . '.pdf', 'D');
    } else {
        // Hiển thị thông báo nếu không có đơn hàng nào tương ứng
        echo "Không có dữ liệu hợp lệ để tạo hóa đơn.";
    }
}
?>
