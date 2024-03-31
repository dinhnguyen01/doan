<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Danh sách nhập kho</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                            <th width="10">STT</th>
                            <th>Hình ảnh</th>
                            <th width="160">Tên sản phẩm</th>
                            <th width="60">Số lượng nhập</th>
                            <th width="60">Màu sắc</th>
                            <th width="60">Kích thước</th>
                            <th width="80">Ngày nhập</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Truy vấn lấy thông tin từ các bảng
                            $statement = $pdo->prepare("
                                SELECT ip.id, p.p_featured_photo, p.p_name, ip.import_quantity, ip.import_date, c.color_name, s.size_name
                                FROM tbl_import_qty_product ip
                                INNER JOIN tbl_product p ON ip.product_id = p.p_id
                                INNER JOIN tbl_color c ON ip.color_id = c.color_id
                                INNER JOIN tbl_size s ON ip.size_id = s.size_id
                            ");
                            $statement->execute();
                            $results = $statement->fetchAll(PDO::FETCH_ASSOC);

                            // Hiển thị dữ liệu
                            // Trong vòng lặp foreach khi hiển thị dữ liệu từ CSDL
                            $count = 1;
                            foreach ($results as $row) {
                                echo "<tr>";
                                echo "<td>" . $count++ . "</td>";
                                echo "<td style='width: 82px; height: 60px'><img src='../assets/uploads/" . $row['p_featured_photo'] . "' alt='" . $row['p_name'] . "' style='width: 80px;'></td>";
                                echo "<td>" . $row['p_name'] . "</td>";
                                echo "<td>" . $row['import_quantity'] . "</td>";
                                echo "<td>" . $row['color_name'] . "</td>";
                                echo "<td>" . $row['size_name'] . "</td>";
                        
                                // Chuyển đổi định dạng ngày tháng
                                $import_date = date('d-m-Y', strtotime($row['import_date']));
                                echo "<td>" . $import_date . "</td>";
                        
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
