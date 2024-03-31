<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Xem sản phẩm</h1>
	</div>
	<div class="content-header-right">
		<a href="product-add.php" class="btn btn-primary btn-sm">Thêm mới</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
					<thead class="thead-dark">
							<tr>
								<th width="10">STT</th>
								<th>Hình ảnh</th>
								<th width="160">Tên sản phẩm</th>
								<th width="60">Giá gốc</th>
								<th width="60">Giá giảm</th>
								<th width="60">Tổng kho</th>
								<th>Nổi bật</th>
								<th>Active</th>
								<th>Danh mục</th>
								<th width="80">Hành động</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT
														
														t1.p_id,
														t1.p_name,
														t1.p_old_price,
														t1.p_current_price,
														t1.p_featured_photo,
														t1.p_is_featured,
														t1.p_is_active,
														t1.ecat_id,

														t2.ecat_id,
														t2.ecat_name,

														t3.mcat_id,
														t3.mcat_name,

														t4.tcat_id,
														t4.tcat_name

							                           	FROM tbl_product t1
							                           	JOIN tbl_end_category t2
							                           	ON t1.ecat_id = t2.ecat_id
							                           	JOIN tbl_mid_category t3
							                           	ON t2.mcat_id = t3.mcat_id
							                           	JOIN tbl_top_category t4
							                           	ON t3.tcat_id = t4.tcat_id
							                           	ORDER BY t1.p_id DESC
							                           	");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:82px;"><img src="../assets/uploads/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
									<td><?php echo $row['p_name']; ?></td>
									<td><?php echo number_format($row['p_old_price'], 0, '.', ',') ?>₫</td>
									<td><?php echo number_format($row['p_current_price'], 0, '.', ',') ?>₫</td>

									<?php
										$statement_quantity = $pdo->prepare("SELECT SUM(quantity) AS total_quantity FROM tbl_product_quantity WHERE product_id = ?");
										$statement_quantity->execute([$row['p_id']]);
										$quantity_result = $statement_quantity->fetch(PDO::FETCH_ASSOC);
										$total_quantity = ($quantity_result['total_quantity'] !== null) ? $quantity_result['total_quantity'] : 0;
									?>

									<td><?php echo $total_quantity ?></td>

									<td>
										<?php if($row['p_is_featured'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Có</span>';} else {echo '<span class="badge badge-success" style="background-color:red;">Không</span>';} ?>
									</td>
									<td>
										<?php if($row['p_is_active'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Có</span>';} else {echo '<span class="badge badge-danger" style="background-color:red;">Không</span>';} ?>
									</td>
									<td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
									<td>
										<a href="product-import-qty.php?id=<?php echo $row['p_id']; ?>" class="btn btn-info btn-xs" style="margin-bottom: 10px">Nhập Kho</a>	
										<br>									
										<a href="product-edit.php?id=<?php echo $row['p_id']; ?>" class="btn btn-primary btn-xs">Sửa</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['p_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Xóa</a>  
									</td>
								</tr>
								<?php
							}
							?>							
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Xác nhận xóa</h4>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa mục này?</p>
                <p style="color:red;">Hãy cẩn thận! Sản phẩm này cũng sẽ bị xóa khỏi bảng đặt hàng, bảng thanh toán, bảng kích thước, bảng màu.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy bỏ</button>
                <a class="btn btn-danger btn-ok">Xóa</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>