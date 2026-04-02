<?php
include_once('includes/config.php');

$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($mysqli->connect_error) {
	die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
}

$planNames = array(
	'Managed IT Bronze Monthly',
	'Managed IT Silver Monthly',
	'Managed IT Gold Monthly',
	'Website Care Monthly',
	'Backup Monitoring Monthly'
);

$escapedNames = array();
foreach ($planNames as $planName) {
	$escapedNames[] = "'".$mysqli->real_escape_string($planName)."'";
}

$plans = array();
$result = $mysqli->query("SELECT product_name, product_desc, product_price FROM products WHERE product_name IN (".implode(',', $escapedNames).") ORDER BY product_name ASC");
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$plans[] = $row;
	}
	$result->free();
}

$mysqli->close();

include('header.php');
?>

<h1>Recurring Monthly Plans</h1>
<hr>

<div class="panel panel-default">
	<div class="panel-heading">
		<h4>Monthly Service Offerings</h4>
	</div>
	<div class="panel-body form-group form-group-sm">
		<?php if (count($plans) > 0) { ?>
		<table class="table table-striped table-hover table-bordered">
			<thead>
				<tr>
					<th>Plan</th>
					<th>Description</th>
					<th>Monthly Price</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($plans as $plan) { ?>
				<tr>
					<td><?php echo htmlspecialchars($plan['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
					<td><?php echo htmlspecialchars($plan['product_desc'], ENT_QUOTES, 'UTF-8'); ?></td>
					<td><?php echo CURRENCY . htmlspecialchars($plan['product_price'], ENT_QUOTES, 'UTF-8'); ?></td>
					<td><a href="invoice-create.php" class="btn btn-primary btn-xs">Use On Invoice</a></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<?php } else { ?>
		<p>No recurring monthly plans are in the product catalog yet. Use the IT service seeding action on the invoice page to add them.</p>
		<?php } ?>
	</div>
</div>

<?php include('footer.php'); ?>