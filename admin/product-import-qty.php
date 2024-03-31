<?php require_once('header.php'); ?>

<?php
$error_message = '';
$success_message = '';

if(isset($_POST['form1'])) {
    $valid = true;

    if(empty($_POST['number_import'])) {
        $valid = false;
        $error_message = "Số lượng nhập không được để trống";
    } elseif(!is_numeric($_POST['number_import']) || $_POST['number_import'] <= 0) {
        $valid = false;
        $error_message = "Số lượng nhập phải là số và lớn hơn 0";
    }elseif(empty($_POST['color_id']) || empty($_POST['size_id'])) {
        $valid = false;
        $error_message = "Vui lòng chọn kích thước và màu sắc";
    } elseif(empty($_POST['import_date'])) {
        $valid = false;
        $error_message = "Bạn phải nhập ngày nhập hàng";
    }

    

    if(!$valid) {
        if($error_message) {
            echo '<div class="callout callout-danger">';
            echo '<p>' . $error_message . '</p>';
            echo '</div>';
        }
    }

    if ($valid) {
        $number_import = $_POST['number_import'];
        $import_date = $_POST['import_date']; // Lấy ngày nhập từ form
        $color_id = $_POST['color_id']; // Lấy color_id từ form
        $size_id = $_POST['size_id']; // Lấy size_id từ form

        // Thêm dữ liệu vào bảng tbl_import_qty_product
        $insert_statement = $pdo->prepare("INSERT INTO tbl_import_qty_product (product_id, color_id, size_id, import_quantity, import_date) VALUES (:product_id, :color_id, :size_id, :import_quantity, :import_date)");
        $insert_statement->bindParam(':product_id', $_REQUEST['id']);
        $insert_statement->bindParam(':color_id', $color_id);
        $insert_statement->bindParam(':size_id', $size_id);
        $insert_statement->bindParam(':import_quantity', $number_import);
        $insert_statement->bindParam(':import_date', $import_date);
        $insert_statement->execute();
    
        $check_statement = $pdo->prepare("SELECT * FROM tbl_product_quantity WHERE product_id = ? AND size_id = ? AND color_id = ?");
        $check_statement->execute([$_REQUEST['id'], $size_id, $color_id]);
        $quantity_result = $check_statement->fetch(PDO::FETCH_ASSOC);

        if ($quantity_result) {
            $current_qty = $quantity_result['quantity'];
            $new_qty = $current_qty + $number_import;

            // Cập nhật số lượng mới vào CSDL
            $update_statement = $pdo->prepare("UPDATE tbl_product_quantity SET quantity = :new_qty WHERE product_id = :product_id AND size_id = :size_id AND color_id = :color_id");
            $update_statement->bindParam(':new_qty', $new_qty);
            $update_statement->bindParam(':product_id', $_REQUEST['id']);
            $update_statement->bindParam(':size_id', $size_id);
            $update_statement->bindParam(':color_id', $color_id);
            $update_statement->execute();

            $success_message = "Số lượng đã được cập nhật thành công!";
        } else {
            $error_message = "Không tìm thấy sản phẩm.";
        }      
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Nhập thêm số lượng sản phẩm vào kho</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">Xem tất cả</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <?php if($success_message): ?>
            <div class="callout callout-success">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>

            <form class="form-horizontal" action="" method="post">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Nhập số lượng <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="number_import">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Chọn màu sắc <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="color_id" class="form-control">
                                    <option value="" disabled selected>Chọn màu sắc</option> <!-- Option trống ban đầu -->
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Chọn kích thước <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="size_id" class="form-control">
                                    <option value="" disabled selected>Chọn kích thước</option> <!-- Option trống ban đầu -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Ngày nhập <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="date" class="form-control" name="import_date">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label"></label>
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-success pull-left" name="form1">Thêm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <script >
                document.addEventListener('DOMContentLoaded', function() {
                const colorSelect = document.querySelector("select[name='color_id']");
                const sizeSelect = document.querySelector("select[name='size_id']");

                const productId = <?php echo $_GET['id']; ?>; // Lấy id từ URL

                let addedColors = new Set(); // Sử dụng Set để theo dõi màu sắc
                let addedSizes = new Set(); // Sử dụng Set để theo dõi kích thước

                let dataLoaded = false; // Biến để kiểm tra xem dữ liệu đã được tải hay chưa

                // Sự kiện khi mở dropdown
                colorSelect.addEventListener('mousedown', function() {
                    if (!dataLoaded) {
                        loadDataIntoDropdown();
                        dataLoaded = true;
                    }
                });

                sizeSelect.addEventListener('mousedown', function() {
                    if (!dataLoaded) {
                        loadDataIntoDropdown();
                        dataLoaded = true;
                    }
                });

                // Hàm để tải dữ liệu và thêm vào dropdown
                function loadDataIntoDropdown() {
                    // Gửi yêu cầu AJAX để lấy kích thước và màu sắc
                    const xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                const data = JSON.parse(xhr.responseText);

                                // Thêm các tùy chọn màu sắc và kích thước từ dữ liệu tải về
                                data.quantity_info.forEach(function(info) {
                                    if (!addedColors.has(info.color_id)) {
                                        colorSelect.innerHTML += `<option value='${info.color_id}'>${info.color_name}</option>`;
                                        addedColors.add(info.color_id);
                                    }

                                    if (!addedSizes.has(info.size_id)) {
                                        sizeSelect.innerHTML += `<option value='${info.size_id}'>${info.size_name}</option>`;
                                        addedSizes.add(info.size_id);
                                    }
                                });
                            } else {
                                console.error("Có lỗi xảy ra");
                            }
                        }
                    };

                    xhr.open("GET", `get_sizes_and_colors.php?product_id=${productId}`, true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.send();
                }
            });
            </script>

        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
