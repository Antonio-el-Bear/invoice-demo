<?php

include('header.php');
include('functions.php');
include_once("includes/config.php");

// Get all payments
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

$query = "SELECT p.*, i.invoice, c.name as customer_name, i.subtotal as invoice_amount
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.invoice
    JOIN customers c ON i.invoice = c.invoice
    ORDER BY p.payment_date DESC";

$results = $mysqli->query($query);

?>

<h1>Payments</h1>
<hr>

<div class="row">
    <div class="col-xs-12">
        <div id="response" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Payment History
                    <a href="javascript:void(0)" id="download-all-btn" class="btn btn-xs btn-success pull-right">
                        <i class="fa fa-download"></i> Download All
                    </a>
                </h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <?php
                if($results && $results->num_rows > 0) {
                    echo '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount Paid</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    while($row = $results->fetch_assoc()) {
                        $status_badge = $row['status'] == 'Completed' ? '<span class="label label-success">Completed</span>' : '<span class="label label-warning">Pending</span>';
                        echo '<tr>
                            <td>'.$row['id'].'</td>
                            <td><strong>'.$row['invoice'].'</strong></td>
                            <td>'.$row['customer_name'].'</td>
                            <td>'.CURRENCY . number_format($row['amount_paid'], 2).'</td>
                            <td>'.$row['payment_date'].'</td>
                            <td>'.$row['payment_method'].'</td>
                            <td>'.$row['reference_number'].'</td>
                            <td>'.$status_badge.'</td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-xs btn-info download-slip" data-id="'.$row['id'].'">
                                    <i class="fa fa-download"></i> Slip
                                </a>
                            </td>
                        </tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert alert-info">No payments found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.download-slip', function() {
    var payment_id = $(this).data('id');
    window.location.href = 'download-payslip.php?id=' + payment_id;
});

$(document).on('click', '#download-all-btn', function() {
    window.location.href = 'download-payslip.php?all=1';
});
</script>

<?php
    include('footer.php');
?>
