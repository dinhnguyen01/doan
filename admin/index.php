<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Bảng thống kê</h1>
</section>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['view_revenue']) && !empty($_POST['selected_date'])) {
    $_SESSION['selected_date'] = $_POST['selected_date'];
}

// Xử lý khi người dùng gửi form chọn tháng
if (isset($_POST['view_monthly_revenue']) && !empty($_POST['selected_month'])) {
    $_SESSION['selected_month'] = $_POST['selected_month'];
}

if (isset($_POST['view_custom_revenue']) && !empty($_POST['start_date']) && !empty($_POST['end_date'])) {
    $_SESSION['start_date'] = $_POST['start_date'];
    $_SESSION['end_date'] = $_POST['end_date'];
}

if (isset($_SESSION['selected_date'])) {
    $selected_date = $_SESSION['selected_date'];

    $statement = $pdo->prepare("
        SELECT DATE(p.payment_date) AS order_date, 
            SUM(o.quantity) AS total_quantity, 
            SUM(o.quantity * o.unit_price) AS total_revenue
        FROM tbl_order o
        INNER JOIN tbl_payment p ON o.payment_id = p.payment_id
        WHERE DATE(p.payment_date) = :selected_date
        GROUP BY DATE(p.payment_date)
    ");
    $statement->bindParam(':selected_date', $selected_date);
    $statement->execute();
    $order_stats_for_selected_date = $statement->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['selected_month'])) {
    $selected_month = $_SESSION['selected_month'];

    // Tách năm và tháng từ giá trị selected_month
    $year = date('Y', strtotime($selected_month));
    $month = date('m', strtotime($selected_month));

    $statement_monthly = $pdo->prepare("
        SELECT YEAR(p.payment_date) AS order_year, 
        MONTH(p.payment_date) AS order_month, 
        SUM(o.quantity) AS total_quantity, 
        SUM(o.quantity * o.unit_price) AS total_revenue
        FROM tbl_order o
        INNER JOIN tbl_payment p ON o.payment_id = p.payment_id
        WHERE MONTH(p.payment_date) = :selected_month AND YEAR(p.payment_date) = :selected_year
        GROUP BY YEAR(p.payment_date), MONTH(p.payment_date)
    ");
    $statement_monthly->bindParam(':selected_month', $month);
    $statement_monthly->bindParam(':selected_year', $year);
    $statement_monthly->execute();
    $order_stats_by_month = $statement_monthly->fetchAll(PDO::FETCH_ASSOC);
}


if (isset($_SESSION['start_date']) && isset($_SESSION['end_date'])) {
    $start_date = $_SESSION['start_date'];
    $end_date = $_SESSION['end_date'];

    $statement_custom = $pdo->prepare("
        SELECT DATE(p.payment_date) AS order_date, 
            SUM(o.quantity) AS total_quantity, 
            SUM(o.quantity * o.unit_price) AS total_revenue
        FROM tbl_order o
        INNER JOIN tbl_payment p ON o.payment_id = p.payment_id
        WHERE DATE(p.payment_date) BETWEEN :start_date AND :end_date
        GROUP BY DATE(p.payment_date)
    ");
    $statement_custom->bindParam(':start_date', $start_date);
    $statement_custom->bindParam(':end_date', $end_date);
    $statement_custom->execute();
    $order_stats_for_custom_date = $statement_custom->fetchAll(PDO::FETCH_ASSOC);
}
?>

<section class="content">
    <div class="row">
    <div class="col-md-12">
        <!-- Phần doanh thu ngày -->
        <div class="col-md-6">
            <form action="" method="post">
                <div class="form-group">
                    <label for="selected_date">Chọn ngày để xem doanh thu:</label>
                    <input type="date" class="form-control" id="selected_date" name="selected_date" required>
                </div>
                <button type="submit" class="btn btn-primary" name="view_revenue">Xem doanh thu</button>
            </form>

            <?php if (isset($order_stats_for_selected_date)): ?>
                <?php if (count($order_stats_for_selected_date) > 0): ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h3>Thống kê doanh số cho ngày <?php echo date("d-m-Y", strtotime($selected_date)); ?></h3>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Tổng số sản phẩm bán ra</th>
                                        <th>Doanh số</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_stats_for_selected_date as $order): ?>
                                        <tr>
                                            <td><?php echo date("d-m-Y", strtotime($order['order_date'])); ?></td>
                                            <td><?php echo $order['total_quantity']; ?></td>
                                            <td><?php echo number_format($order['total_revenue'], 0, '.', ',');?>₫</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Không có doanh thu cho ngày <?php echo date("d-m-Y", strtotime($selected_date)); ?></h4>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
       
        <div class="col-md-6">
            <form action="" method="post">
                <label for="selected_month">Chọn ngày để xem doanh thu:</label>
                <div class="form-group">
                    <label for="start_date">Từ ngày:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Đến ngày:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" name="view_custom_revenue">Xem doanh thu</button>
            </form>
     

        <!-- Hiển thị kết quả thống kê doanh thu -->
        <?php if (isset($order_stats_for_custom_date)): ?>
            <?php if (count($order_stats_for_custom_date) > 0): ?>
                <div class="col-md-12">
                    <h3>Thống kê doanh số từ <?php echo date("d-m-Y", strtotime($start_date)); ?> đến <?php echo date("d-m-Y", strtotime($end_date)); ?></h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Tổng số sản phẩm bán ra</th>
                                <th>Doanh số</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_stats_for_custom_date as $order): ?>
                                <tr>
                                    <td><?php echo date("d-m-Y", strtotime($order['order_date'])); ?></td>
                                    <td><?php echo $order['total_quantity']; ?></td>
                                    <td><?php echo number_format($order['total_revenue'], 0, '.', ',');?>₫</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="col-md-12">
                    <h4 style="font-size:17px">Không có doanh thu trong khoảng thời gian từ <?php echo date("d-m-Y", strtotime($start_date)); ?> đến <?php echo date("d-m-Y", strtotime($end_date)); ?></h4>
                </div>
            <?php endif; ?>
        <?php endif; ?>
</div>


<div class="row">
        <!-- Phần doanh thu tháng -->
        <div class="col-md-12">
        <div class="col-md-6">
            <form action="" method="post">
                <div class="form-group">
                    <label for="selected_month">Chọn tháng để xem doanh thu:</label>
                    <input type="month" class="form-control" id="selected_month" name="selected_month" lang="vi" locale="vi" required>
                </div>
                <button type="submit" class="btn btn-primary" name="view_monthly_revenue">Xem doanh thu</button>
            </form>

            <?php if (isset($order_stats_by_month) && count($order_stats_by_month) > 0): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h3>Thống kê doanh số cho tháng <?php echo date("m-Y", strtotime($selected_month)); ?></h3>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tháng</th>
                                    <th>Năm</th>
                                    <th>Tổng số sản phẩm bán ra</th>
                                    <th>Doanh số</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_stats_by_month as $stats): ?>
                                    <tr>
                                        <td><?php echo $stats['order_month']; ?></td>
                                        <td><?php echo $stats['order_year']; ?></td>
                                        <td><?php echo $stats['total_quantity']; ?></td>
                                        <td><?php echo number_format($stats['total_revenue'], 0, '.', ',') . '₫'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php elseif (isset($selected_month) && isset($_POST['view_monthly_revenue']) && empty($_POST['selected_month'])): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Vui lòng chọn tháng để xem doanh thu</h4>
                    </div>
                </div>
            <?php elseif (isset($selected_month)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Không có doanh thu cho tháng <?php echo date("m-Y", strtotime($selected_month)); ?></h4>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div> 
    </div> 
</section>

<?php require_once('footer.php'); ?>
