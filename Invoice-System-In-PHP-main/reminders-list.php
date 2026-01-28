<?php

include('header.php');
include('functions.php');
include_once("includes/config.php");

// Get all reminders
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

$query = "SELECT r.*, r.sent_to_email, i.invoice AS invoice_number, c.name AS customer_name, c.email, i.subtotal AS invoice_amount
    FROM reminders r
    JOIN invoices i ON r.invoice_id = i.id
    JOIN customers c ON i.invoice = c.invoice
    ORDER BY r.sent_date DESC";

$results = $mysqli->query($query);

?>

<h1>Payment Reminders</h1>
<hr>

<div class="row">
    <div class="col-xs-12">
        <div id="response" class="alert alert-success" style="display:none;">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <div class="message"></div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>Reminder History</h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <?php
                if($results && $results->num_rows > 0) {
                    echo '<table class="table table-striped table-hover table-bordered" id="data-table" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Reminder ID</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Amount Due</th>
                                <th>Sent Date</th>
                                <th>Reminder Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';

                    while($row = $results->fetch_assoc()) {
                        $status_badge = $row['status'] == 'Sent' ? '<span class="label label-success">Sent</span>' : '<span class="label label-danger">Failed</span>';
                        $reminder_type = isset($row['reminder_type']) ? $row['reminder_type'] : 'Manual';
                        echo '<tr>
                            <td>'.$row['id'].'</td>
                            <td><strong>'.$row['invoice'].'</strong></td>
                            <td>'.$row['customer_name'].'</td>
                            <td>'.$row['email'].'</td>
                            <td>'.CURRENCY . number_format($row['invoice_amount'], 2).'</td>
                            <td>'.$row['sent_at'].'</td>
                            <td>'.$reminder_type.'</td>
                            <td>'.$status_badge.'</td>
                        </tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert alert-info">No reminders sent yet.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
    include('footer.php');
?>
