<?php


include('header.php');
include('functions.php');

?>

<h1>Invoice List</h1>
<hr>

<div class="row" style="margin-bottom:15px;">
	<div class="col-xs-12">
		<div class="btn-toolbar" role="toolbar">
			<div class="btn-group btn-group-sm">
				<a href="invoice-list.php" class="btn btn-default"><span class="glyphicon glyphicon-list"></span> Invoice List</a>
				<a href="pending-bills.php" class="btn btn-warning"><span class="glyphicon glyphicon-time"></span> Open Invoices</a>
				<a href="due-soon.php" class="btn btn-info"><span class="glyphicon glyphicon-calendar"></span> Due Soon</a>
				<a href="paid-this-month.php" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Paid This Month</a>
				<a href="paid-invoices.php" class="btn btn-primary"><span class="glyphicon glyphicon-usd"></span> Paid Invoices</a>
			</div>
			<div class="btn-group btn-group-sm" style="margin-left:8px;">
				<a href="invoice-create.php" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Create New Invoice</a>
			</div>
		</div>
	</div>
</div>

<div class="row">

	<div class="col-xs-12">

		<div id="response" class="alert alert-success" style="display:none;">
			<a href="#" class="close" data-dismiss="alert">&times;</a>
			<div class="message"></div>
		</div>
	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h4>Manage Invoices</h4>
			</div>
			<div class="panel-body form-group form-group-sm">
				<?php getInvoices(); ?>
			</div>
		</div>
	</div>
<div>

<div id="delete_invoice" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete Invoice</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this invoice?</p>
      </div>
      <div class="modal-footer">
        <button type="button" data-dismiss="modal" class="btn btn-primary" id="delete">Delete</button>
		<button type="button" data-dismiss="modal" class="btn">Cancel</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php
	include('footer.php');
?>