<?php


include('header.php');
include('functions.php');

$getID = $_GET['id'];

// Connect to the database
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// output any connection error
if ($mysqli->connect_error) {
	die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

// the query
$query = "SELECT p.*, i.*, c.*
			FROM invoice_items p 
			JOIN invoices i ON i.invoice = p.invoice
			JOIN customers c ON c.invoice = i.invoice
			WHERE p.invoice = '" . $mysqli->real_escape_string($getID) . "'";

$result = mysqli_query($mysqli, $query);

// Initialize variables with default values
$customer_name = $customer_email = $customer_address_1 = $customer_address_2 = '';
$customer_town = $customer_county = $customer_postcode = $customer_phone = '';
$customer_name_ship = $customer_address_1_ship = $customer_address_2_ship = '';
$customer_town_ship = $customer_county_ship = $customer_postcode_ship = '';
$invoice_number = $custom_email = $invoice_date = $invoice_due_date = '';
$invoice_subtotal = $invoice_shipping = $invoice_discount = $invoice_vat = $invoice_total = 0;
$invoice_notes = '';
$invoice_type = 'invoice';
$invoice_status = 'open';

// mysqli select query
if($result) {
	while ($row = mysqli_fetch_assoc($result)) {
		$customer_name = $row['name']; // customer name
		$customer_email = $row['email']; // customer email
		$customer_address_1 = $row['address_1']; // customer address
		$customer_address_2 = $row['address_2']; // customer address
		$customer_town = $row['town']; // customer town
		$customer_county = $row['county']; // customer county
		$customer_postcode = $row['postcode']; // customer postcode
		$customer_phone = $row['phone']; // customer phone number
		
		//shipping
		$customer_name_ship = $row['name_ship']; // customer name (shipping)
		$customer_address_1_ship = $row['address_1_ship']; // customer address (shipping)
		$customer_address_2_ship = $row['address_2_ship']; // customer address (shipping)
		$customer_town_ship = $row['town_ship']; // customer town (shipping)
		$customer_county_ship = $row['county_ship']; // customer county (shipping)
		$customer_postcode_ship = $row['postcode_ship']; // customer postcode (shipping)

		// invoice details
		$invoice_number = $row['invoice']; // invoice number
		$custom_email = $row['custom_email']; // invoice custom email body
		$invoice_date = $row['invoice_date']; // invoice date
		$invoice_due_date = $row['invoice_due_date']; // invoice due date
		$invoice_subtotal = $row['subtotal']; // invoice sub-total
		$invoice_shipping = $row['shipping']; // invoice shipping amount
		$invoice_discount = $row['discount']; // invoice discount
		$invoice_vat = $row['vat']; // invoice vat
		$invoice_total = $row['total']; // invoice total
		$invoice_notes = $row['notes']; // Invoice notes
		$invoice_type = $row['invoice_type']; // Invoice type
		$invoice_status = $row['status']; // Invoice status
	}
}

/* close connection */
$mysqli->close();

?>

<div class="row">
	<div class="col-xs-12">
		<h1>Edit Invoice #<?php echo $getID; ?></h1>
		<hr>

		<div id="response" class="alert alert-success" style="display:none;">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<div class="message"></div>
		</div>

		<form method="post" id="update_invoice">
			<input type="hidden" name="action" value="update_invoice">
			<input type="hidden" name="update_id" value="<?php echo $getID; ?>">

			<!-- Custom Email Message Section -->
			<div class="row" style="margin-bottom: 20px;">
				<div class="col-xs-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">Custom Email Message (Optional)</h4>
						</div>
						<div class="panel-body">
							<textarea name="custom_email" id="custom_email" class="form-control" rows="3" placeholder="Enter a custom email message here if you wish to override the default invoice email message."><?php echo $custom_email; ?></textarea>
							<p class="help-block">Leave blank to use the default email template.</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Invoice Display Section -->

			<div class="row">
				<div class="col-xs-5">
					<h1>
						<img src="<?php echo COMPANY_LOGO ?>" class="img-responsive">
					</h1>
				</div>
				<div class="col-xs-7 text-right">
					<div class="row">
						<div class="col-xs-6">
							<h1>INVOICE</h1>
						</div>
						<div class="col-xs-3">
							<select name="invoice_type" id="invoice_type" class="form-control">
								<option value="invoice" <?php if($invoice_type === 'invoice'){?>selected<?php } ?>>Invoice</option>
								<option value="quote" <?php if($invoice_type === 'quote'){?>selected<?php } ?>>Quote</option>
								<option value="receipt" <?php if($invoice_type === 'receipt'){?>selected<?php } ?>>Receipt</option>
							</select>
						</div>
						<div class="col-xs-3">
							<select name="invoice_status" id="invoice_status" class="form-control">
								<option value="open" <?php if($invoice_status === 'open'){?>selected<?php } ?>>Open</option>
								<option value="paid" <?php if($invoice_status === 'paid'){?>selected<?php } ?>>Paid</option>
							</select>
						</div>
					</div>
					<div class="col-xs-4 no-padding-right">
				        <div class="form-group">
				            <div class="input-group date" id="invoice_date">
				                <input type="text" class="form-control required" name="invoice_date" placeholder="Select invoice date" data-date-format="<?php echo DATE_FORMAT ?>" value="<?php echo $invoice_date; ?>" />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
				        </div>
				    </div>
				    <div class="col-xs-4">
				        <div class="form-group">
				            <div class="input-group date" id="invoice_due_date">
				                <input type="text" class="form-control required" name="invoice_due_date" placeholder="Select due date" data-date-format="<?php echo DATE_FORMAT ?>" value="<?php echo $invoice_due_date; ?>" />
				                <span class="input-group-addon">
				                    <span class="glyphicon glyphicon-calendar"></span>
				                </span>
				            </div>
				        </div>
				    </div>
					<div class="input-group col-xs-4 float-right">
						<span class="input-group-addon">#<?php echo INVOICE_PREFIX ?></span>
						<input type="text" name="invoice_id" id="invoice_id" class="form-control required" placeholder="Invoice Number" aria-describedby="sizing-addon1" value="<?php echo $getID; ?>">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4>Customer Information</h4>
							<div class="clear"></div>
						</div>
						<div class="panel-body form-group form-group-sm">
							<div class="row">
								<div class="col-xs-6">
									<div class="form-group">
										<input type="text" class="form-control margin-bottom copy-input required" name="customer_name" id="customer_name" placeholder="Enter name" tabindex="1" value="<?php echo $customer_name; ?>">
									</div>
									<div class="form-group">
										<input type="text" class="form-control margin-bottom copy-input required" name="customer_address_1" id="customer_address_1" placeholder="Address 1" tabindex="3" value="<?php echo $customer_address_1; ?>">	
									</div>
									<div class="form-group">
										<input type="text" class="form-control margin-bottom copy-input required" name="customer_town" id="customer_town" placeholder="Town" tabindex="5" value="<?php echo $customer_town; ?>">		
									</div>
									<div class="form-group no-margin-bottom">
										<input type="text" class="form-control copy-input required" name="customer_postcode" id="customer_postcode" placeholder="Postcode" tabindex="7" value="<?php echo $customer_postcode; ?>">					
									</div>
								</div>
								<div class="col-xs-6">
									<div class="input-group float-right margin-bottom">
										<span class="input-group-addon">@</span>
										<input type="email" class="form-control copy-input required" name="customer_email" id="customer_email" placeholder="E-mail address" aria-describedby="sizing-addon1" tabindex="2" value="<?php echo $customer_email; ?>">
									</div>
								    <div class="form-group">
								    	<input type="text" class="form-control margin-bottom copy-input" name="customer_address_2" id="customer_address_2" placeholder="Address 2" tabindex="4" value="<?php echo $customer_address_2; ?>">
								    </div>
								    <div class="form-group">
								    	<input type="text" class="form-control margin-bottom copy-input required" name="customer_county" id="customer_county" placeholder="County" tabindex="6" value="<?php echo $customer_county; ?>">
								    </div>
								    <div class="form-group no-margin-bottom">
								    	<input type="text" class="form-control required" name="customer_phone" id="invoice_phone" placeholder="Phone number" tabindex="8" value="<?php echo $customer_phone; ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-6 text-right">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4>Shipping Information</h4>
						</div>
						<div class="panel-body form-group form-group-sm">
							<div class="row">
								<div class="col-xs-6">
									<div class="form-group">
										<input type="text" class="form-control margin-bottom required" name="customer_name_ship" id="customer_name_ship" placeholder="Enter name" tabindex="9" value="<?php echo $customer_name_ship; ?>">
									</div>
									<div class="form-group">
										<input type="text" class="form-control margin-bottom" name="customer_address_2_ship" id="customer_address_2_ship" placeholder="Address 2" tabindex="11" value="<?php echo $customer_address_2_ship; ?>">	
									</div>
									<div class="form-group no-margin-bottom">
										<input type="text" class="form-control required" name="customer_county_ship" id="customer_county_ship" placeholder="County" tabindex="13" value="<?php echo $customer_county_ship; ?>">
									</div>
								</div>
								<div class="col-xs-6">
									<div class="form-group">
								    	<input type="text" class="form-control margin-bottom required" name="customer_address_1_ship" id="customer_address_1_ship" placeholder="Address 1" tabindex="10" value="<?php echo $customer_address_1_ship; ?>">
									</div>
									<div class="form-group">
										<input type="text" class="form-control margin-bottom required" name="customer_town_ship" id="customer_town_ship" placeholder="Town" tabindex="12" value="<?php echo $customer_town_ship; ?>">							
								    </div>
								    <div class="form-group no-margin-bottom">
								    	<input type="text" class="form-control required" name="customer_postcode_ship" id="customer_postcode_ship" placeholder="Postcode" tabindex="14" value="<?php echo $customer_postcode_ship; ?>">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- / end client details section -->
			<table class="table table-bordered" id="invoice_table">
				<thead>
					<tr>
						<th width="500">
							<h4><a href="#" class="btn btn-success btn-xs add-row"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a> Item</h4>
						</th>
						<th>
							<h4>Qty</h4>
						</th>
						<th>
							<h4>Price</h4>
						</th>
						<th width="300">
							<h4>Discount</h4>
						</th>
						<th>
							<h4>Sub Total</h4>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php 

						// Connect to the database
						$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

						// output any connection error
						if ($mysqli->connect_error) {
							die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
						}

						// the query
						$query2 = "SELECT * FROM invoice_items WHERE invoice = '" . $mysqli->real_escape_string($getID) . "'";

						$result2 = mysqli_query($mysqli, $query2);

						//var_dump($result2);

						// mysqli select query
						if($result2) {
							while ($rows = mysqli_fetch_assoc($result2)) {

								//var_dump($rows);

							    $item_product = $rows['product'];
							    $item_qty = $rows['qty'];
							    $item_price = $rows['price'];
							    $item_discount = $rows['discount'];
							    $item_subtotal = $rows['subtotal'];
					?>
					<tr>
						<td>
							<div class="form-group form-group-sm  no-margin-bottom">
								<a href="#" class="btn btn-danger btn-xs delete-row"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
								<input type="text" class="form-control form-group-sm item-input invoice_product" name="invoice_product[]" placeholder="Enter item title and / or description" value="<?php echo $item_product; ?>">
								<p class="item-select">or <a href="#">select an item</a></p>
							</div>
						</td>
						<td class="text-right">
							<div class="form-group form-group-sm no-margin-bottom">
								<input type="text" class="form-control invoice_product_qty calculate" name="invoice_product_qty[]" value="<?php echo $item_qty; ?>">
							</div>
						</td>
						<td class="text-right">
							<div class="input-group input-group-sm  no-margin-bottom">
								<span class="input-group-addon"><?php echo CURRENCY ?></span>
								<input type="text" class="form-control calculate invoice_product_price required" name="invoice_product_price[]" aria-describedby="sizing-addon1" placeholder="0.00" value="<?php echo $item_price; ?>">
							</div>
						</td>
						<td class="text-right">
							<div class="form-group form-group-sm  no-margin-bottom">
								<input type="text" class="form-control calculate" name="invoice_product_discount[]" placeholder="Enter % or value (ex: 10% or 10.50)" value="<?php echo $item_discount; ?>">
							</div>
						</td>
						<td class="text-right">
							<div class="input-group input-group-sm">
								<span class="input-group-addon"><?php echo CURRENCY ?></span>
								<input type="text" class="form-control calculate-sub" name="invoice_product_sub[]" id="invoice_product_sub" aria-describedby="sizing-addon1" value="<?php echo $item_subtotal; ?>" disabled>
							</div>
						</td>
					</tr>
					<?php } } ?>
				</tbody>
			</table>
			<div id="invoice_totals" class="padding-right row text-right">
				<div class="col-xs-6">
					<div class="input-group form-group-sm textarea no-margin-bottom">
						<textarea class-"form-control" name="invoice_notes" placeholder="Please enter any order notes here."><?php echo $invoice_notes; ?></textarea>
					</div>
				</div>
				<div class="col-xs-6 no-padding-right">
					<div class="row">
						<div class="col-xs-3 col-xs-offset-6">
							<strong>Sub Total:</strong>
						</div>
						<div class="col-xs-3">
							<?php echo CURRENCY ?><span class="invoice-sub-total"> <?php echo $invoice_subtotal; ?></span>
							<input type="hidden" name="invoice_subtotal" id="invoice_subtotal" value="<?php echo $invoice_subtotal; ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-xs-3 col-xs-offset-6">
							<strong>Discount:</strong>
						</div>
						<div class="col-xs-3">
							<?php echo CURRENCY ?><span class="invoice-discount"> <?php echo $invoice_discount; ?></span>
							<input type="hidden" name="invoice_discount" id="invoice_discount" value="<?php echo $invoice_discount; ?>">
						</div>
					</div>
					<div class="row">
						<div class="col-xs-3 col-xs-offset-6">
							<strong class="shipping">Shipping:</strong>
						</div>
						<div class="col-xs-3">
							<div class="input-group input-group-sm">
								<span class="input-group-addon"><?php echo CURRENCY ?></span>
								<input type="text" class="form-control calculate shipping" name="invoice_shipping" aria-describedby="sizing-addon1" placeholder="0.00" value="<?php echo $invoice_shipping; ?>">
							</div>
						</div>
					</div>
					<?php if (ENABLE_VAT == true) { ?>
					<div class="row">
						<div class="col-xs-3 col-xs-offset-6">
							<strong>TAX/VAT:</strong>
						</div>
						<div class="col-xs-3">
							<?php echo CURRENCY ?><span class="invoice-vat" data-enable-vat="<?php echo ENABLE_VAT ?>" data-vat-rate="<?php echo VAT_RATE ?>" data-vat-method="<?php echo VAT_INCLUDED ?>"><?php echo $invoice_vat; ?></span>
							<input type="hidden" name="invoice_vat" id="invoice_vat" value="<?php echo $invoice_vat; ?>">
						</div>
					</div>
					<?php } ?>
					<div class="row">
						<div class="col-xs-3 col-xs-offset-6">
							<strong>Total:</strong>
						</div>
						<div class="col-xs-3">
							<?php echo CURRENCY ?><span class="invoice-total"> <?php echo $invoice_total; ?></span>
							<input type="hidden" name="invoice_total" id="invoice_total" value="<?php echo $invoice_total; ?>">
						</div>
					</div>
				</div>

			</div>
			<div class="row">
				<div class="col-xs-12 margin-top btn-group">
					<button type="button" class="btn btn-info float-right margin-right" data-toggle="modal" data-target="#paymentModal">Record Payment</button>
					<input type="submit" id="action_edit_invoice" class="btn btn-success float-right" value="Update Invoice" data-loading-text="Updating...">
				</div>
			</div>
		</form>

		<div id="insert" class="modal fade">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title">Select an item</h4>
		      </div>
		      <div class="modal-body">
				<?php popProductsList(); ?>
		      </div>
		      <div class="modal-footer">
		        <button type="button" data-dismiss="modal" class="btn btn-primary" id="selected">Add</button>
				<button type="button" data-dismiss="modal" class="btn">Cancel</button>
		      </div>
		    </div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

<!-- Payment Recording Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Record Payment</h4>
      </div>
      <div class="modal-body">
        <form id="paymentForm">
          <div class="form-group">
            <label>Amount Paid (<?php echo CURRENCY; ?>):</label>
            <input type="number" class="form-control" id="paymentAmount" step="0.01" required>
          </div>
          <div class="form-group">
            <label>Payment Date:</label>
            <input type="text" class="form-control" id="paymentDate" placeholder="Select date" required>
          </div>
          <div class="form-group">
            <label>Payment Method:</label>
            <select class="form-control" id="paymentMethod">
              <option>Bank Transfer</option>
              <option>Cash</option>
              <option>Cheque</option>
              <option>Credit Card</option>
              <option>Other</option>
            </select>
          </div>
          <div class="form-group">
            <label>Notes:</label>
            <textarea class="form-control" id="paymentNotes" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="recordPaymentBtn">Record Payment</button>
      </div>
    </div>
  </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Payment History</h4>
      </div>
      <div class="modal-body">
        <div id="paymentHistoryContent"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  // Initialize date picker for payment modal
  $('#paymentDate').datetimepicker({
    format: 'YYYY-MM-DD',
    useCurrent: false,
    defaultDate: moment()
  });

  // Record Payment Button Click
  $('#recordPaymentBtn').on('click', function(){
    var invoice_id = '<?php echo $invoice_number; ?>';
    var amount = $('#paymentAmount').val();
    var payment_date = $('#paymentDate').val();
    var payment_method = $('#paymentMethod').val();
    var notes = $('#paymentNotes').val();

    if (!amount || amount <= 0) {
      alert('Please enter a valid amount');
      return;
    }

    $.ajax({
      url: 'response.php',
      type: 'POST',
      data: {
        action: 'record_payment',
        invoice_id: invoice_id,
        amount: amount,
        payment_date: payment_date,
        payment_method: payment_method,
        notes: notes
      },
      dataType: 'json',
      success: function(data){
        if (data.status === 'Success') {
          alert('Payment recorded successfully!');
          $('#paymentModal').modal('hide');
          $('#paymentForm')[0].reset();
          location.reload(); // Reload to show updated invoice
        } else {
          alert('Error: ' + data.message);
        }
      },
      error: function(){
        alert('Error recording payment. Please try again.');
      }
    });
  });

});
</script>

	</div><!-- /.col-xs-12 -->
</div><!-- /.row -->

<?php
	include('footer.php');
?>