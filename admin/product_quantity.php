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
    }

    if(empty($_POST['color_id']) || empty($_POST['size_id'])) {
        $valid = false;
        $error_message = "Vui lòng chọn cả màu sắc và kích thước";
    }

    if(!$valid) {
        if($error_message) {
            echo '<div class="callout callout-danger">';
            echo '<p>' . $error_message . '</p>';
            echo '</div>';
        }
    }
    
    if($valid) {
        $product_id = $_POST['product_id'];
        $number_import = $_POST['number_import'];
        $color_id = $_POST['color_id'];
        $size_id = $_POST['size_id'];

        $statement = $pdo->prepare("INSERT INTO tbl_product_quantity (product_id, color_id, size_id, quantity) VALUES (?, ?, ?, ?)");
        $statement->execute([$product_id, $color_id, $size_id, $number_import]);

        // Hiển thị thông báo thành công nếu thêm thành công
        if($statement) {
            $success_message = "Thêm số lượng sản phẩm thành công";
        } else {
            $error_message = "Có lỗi xảy ra khi thêm số lượng sản phẩm";
        }
    }
    
    
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Nhập số lượng sản phẩm</h1>
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
                            <label for="" class="col-sm-2 control-label">Sản phẩm <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="product_id" class="form-control">
                                    <option value="">Chọn sản phẩm</option>
                                    <?php
                                    // Lấy dữ liệu từ bảng tbl_product và hiển thị các tùy chọn sản phẩm
                                    $product_query = $pdo->query("SELECT tp.p_id, tp.p_name FROM tbl_product tp");
                                    while ($row = $product_query->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . $row['p_id'] . '">' . $row['p_name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

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
                                    <option value="">Chọn màu sắc</option>
                                    <!-- Dữ liệu màu sắc sẽ được cập nhật bằng AJAX -->
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="" class="col-sm-2 control-label">Chọn kích thước <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="size_id" class="form-control">
                                    <option value="">Chọn kích thước</option>
                                    <!-- Dữ liệu kích thước sẽ được cập nhật bằng AJAX -->
                                </select>
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
                const productSelect = document.querySelector("select[name='product_id']");
                const colorSelect = document.querySelector("select[name='color_id']");
                const sizeSelect = document.querySelector("select[name='size_id']");

                productSelect.addEventListener('change', function() {
                    const productId = this.value;

                    // Xóa dữ liệu cũ khi chọn sản phẩm mới
                    colorSelect.innerHTML = '<option value="">Chọn màu sắc</option>';
                    sizeSelect.innerHTML = '<option value="">Chọn kích thước</option>';

                    // Gửi yêu cầu AJAX để lấy kích thước và màu sắc mới
                    const xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                const data = JSON.parse(xhr.responseText);

                                // Thêm các tùy chọn màu sắc mới
                                data.colors.forEach(function(color) {
                                    colorSelect.innerHTML += `<option value='${color.color_id}'>${color.color_name}</option>`;
                                });

                                // Thêm các tùy chọn kích thước mới
                                data.sizes.forEach(function(size) {
                                    sizeSelect.innerHTML += `<option value='${size.size_id}'>${size.size_name}</option>`;
                                });
                            } else {
                                console.error("Có lỗi xảy ra");
                            }
                        }
                    };

                    xhr.open("GET", `get_sizes_and_colors_1.php?product_id=${productId}`, true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.send();
                });
            });
            </script>


        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
