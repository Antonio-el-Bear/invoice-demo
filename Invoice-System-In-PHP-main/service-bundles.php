<?php
include_once('includes/config.php');

function ensureServiceBundleTablesPage($mysqli) {
	$mysqli->query("CREATE TABLE IF NOT EXISTS service_bundles (
		id INT(11) NOT NULL AUTO_INCREMENT,
		bundle_name VARCHAR(255) NOT NULL,
		bundle_desc TEXT DEFAULT NULL,
		billing_type VARCHAR(50) NOT NULL DEFAULT 'one-time',
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY (id)
	)");

	$mysqli->query("CREATE TABLE IF NOT EXISTS service_bundle_items (
		id INT(11) NOT NULL AUTO_INCREMENT,
		bundle_id INT(11) NOT NULL,
		product_name VARCHAR(255) NOT NULL,
		qty DECIMAL(10,2) NOT NULL DEFAULT 1.00,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY bundle_id (bundle_id)
	)");
}

$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($mysqli->connect_error) {
	die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

ensureServiceBundleTablesPage($mysqli);

$productOptions = '';
$productPrices = array();
$productResult = $mysqli->query("SELECT product_name, product_price FROM products ORDER BY product_name ASC");
if ($productResult) {
	while ($product = $productResult->fetch_assoc()) {
		$productName = htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8');
		$productOptions .= '<option value="'.$productName.'">'.$productName.'</option>';
		$productPrices[$product['product_name']] = (float)$product['product_price'];
	}
	$productResult->free();
}

$bundles = array();
$bundleResult = $mysqli->query("SELECT id, bundle_name, bundle_desc, billing_type FROM service_bundles ORDER BY bundle_name ASC");
if ($bundleResult) {
	while ($bundle = $bundleResult->fetch_assoc()) {
		$itemStmt = $mysqli->prepare("SELECT product_name, qty FROM service_bundle_items WHERE bundle_id = ? ORDER BY id ASC");
		$itemStmt->bind_param('i', $bundle['id']);
		$itemStmt->execute();
		$itemResult = $itemStmt->get_result();
		$items = array();
		$total = 0;
		while ($item = $itemResult->fetch_assoc()) {
			$price = isset($productPrices[$item['product_name']]) ? $productPrices[$item['product_name']] : 0;
			$total += $price * (float)$item['qty'];
			$items[] = $item['product_name'].' x '.rtrim(rtrim(number_format((float)$item['qty'], 2, '.', ''), '0'), '.');
		}
		$itemStmt->close();
		$bundle['items'] = $items;
		$bundle['total'] = number_format($total, 2, '.', '');
		$bundles[] = $bundle;
	}
	$bundleResult->free();
}

$mysqli->close();

include('header.php');
?>

<h1>Service Bundles</h1>
<hr>

<div id="response" class="alert alert-success" style="display:none;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<div class="message"></div>
</div>

<div class="row">
	<div class="col-xs-12 col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>Create Custom Bundle</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<form method="post" id="create_service_bundle">
					<input type="hidden" name="action" value="create_service_bundle">
					<div class="row">
						<div class="col-xs-12">
							<input type="text" class="form-control required" name="bundle_name" placeholder="Bundle name">
						</div>
					</div>
					<div class="row margin-top">
						<div class="col-xs-12">
							<input type="text" class="form-control" name="bundle_desc" placeholder="Bundle description">
						</div>
					</div>
					<div class="row margin-top">
						<div class="col-xs-12">
							<select class="form-control" name="billing_type">
								<option value="one-time">One-Time</option>
								<option value="monthly">Monthly</option>
							</select>
						</div>
					</div>
					<div class="row margin-top">
						<div class="col-xs-12">
							<table class="table table-bordered" id="bundle_items_table">
								<thead>
									<tr>
										<th>Product</th>
										<th>Qty / Hours</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<select class="form-control" name="bundle_product_name[]">
												<option value="">Select a product</option>
												<?php echo $productOptions; ?>
											</select>
										</td>
										<td>
											<input type="number" step="0.25" min="0.25" class="form-control" name="bundle_product_qty[]" value="1">
										</td>
										<td>
											<a href="#" class="btn btn-danger btn-xs remove-bundle-item-row"><span class="glyphicon glyphicon-remove"></span></a>
										</td>
									</tr>
								</tbody>
							</table>
							<a href="#" id="add_bundle_item_row" class="btn btn-default btn-sm">Add Item Row</a>
						</div>
					</div>
					<div class="row margin-top">
						<div class="col-xs-12 text-right">
							<input type="submit" id="action_create_service_bundle" class="btn btn-success" value="Save Custom Bundle" data-loading-text="Saving...">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="col-xs-12 col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>Custom Bundle List</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php if (count($bundles) > 0) { ?>
				<table class="table table-striped table-hover table-bordered">
					<thead>
						<tr>
							<th>Bundle</th>
							<th>Billing</th>
							<th>Items</th>
							<th>Total</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($bundles as $bundle) { ?>
						<tr>
							<td>
								<strong><?php echo htmlspecialchars($bundle['bundle_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
								<small><?php echo htmlspecialchars($bundle['bundle_desc'], ENT_QUOTES, 'UTF-8'); ?></small>
							</td>
							<td><?php echo htmlspecialchars($bundle['billing_type'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo htmlspecialchars(implode(', ', $bundle['items']), ENT_QUOTES, 'UTF-8'); ?></td>
							<td><?php echo CURRENCY . $bundle['total']; ?></td>
							<td><a href="#" class="btn btn-danger btn-xs delete-service-bundle" data-bundle-id="<?php echo (int)$bundle['id']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php } else { ?>
				<p>No custom bundles created yet.</p>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div id="delete_service_bundle" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Delete Service Bundle</h4>
			</div>
			<div class="modal-body">
				<p>Are you sure you want to delete this custom service bundle?</p>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-primary" id="delete_service_bundle_confirm">Delete</button>
				<button type="button" data-dismiss="modal" class="btn">Cancel</button>
			</div>
		</div>
	</div>
</div>

<?php include('footer.php'); ?>