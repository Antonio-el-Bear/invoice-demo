<?php

include('header.php');
include('functions.php');
include_once("includes/config.php");

// Get open/pending invoices
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

$query = "SELECT i.*, c.name as customer_name, c.email
    FROM invoices i
    LEFT JOIN customers c ON c.invoice = i.invoice
    WHERE i.status = 'open'
    ORDER BY i.invoice_due_date ASC";

$results = $mysqli->query($query);

?>

<h1>Pending Bills</h1>
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
            <div class="btn-group btn-group-sm pull-right">
                <a href="automation.php" class="btn btn-default"><span class="glyphicon glyphicon-cog"></span> Automation Settings</a>
                <a href="cron-send-reminders.php" class="btn btn-warning" target="_blank" title="Send reminders to all overdue invoices"><span class="glyphicon glyphicon-bell"></span> Send All Reminders</a>
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
                <h4>Pending Bills - Invoices Awaiting Payment</h4>
            </div>
            <div class="panel-body form-group form-group-sm">
                <?php
                $total = 0;
                if($results && $results->num_rows > 0) {
                    echo '<div class="alert alert-warning">
                        <strong>Total Pending Invoices:</strong> '.$results->num_rows.' | 
                        <strong>Total Due Amount:</strong> ';
                    
                    // Reset for total calculation
                    $results->data_seek(0);
                    while($row = $results->fetch_assoc()) {
                        $total += ($row['subtotal'] - ($row['amount_paid'] ?? 0));
                    }
                    echo CURRENCY . number_format($total, 2) . '
                    </div>';
                    
                    // Reset again for display
                    $results->data_seek(0);
                    
                    echo '<table class="table table-striped table-hover" id="data-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Balance Due</th>
                                <th>Due Date</th>
                                <th>Days Until Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';

                    while($row = $results->fetch_assoc()) {
                        $balance_due = $row['subtotal'] - ($row['amount_paid'] ?? 0);
                        // Parse date safely - try multiple formats
                        try {
                            $due_date = DateTime::createFromFormat('d/m/Y', $row['invoice_due_date']);
                            if (!$due_date) $due_date = DateTime::createFromFormat('Y-m-d', $row['invoice_due_date']);
                            if (!$due_date) $due_date = new DateTime($row['invoice_due_date']);
                        } catch (Exception $e) {
                            $due_date = new DateTime();
                        }
                        $today = new DateTime();
                        $days_until = $today->diff($due_date)->days;
                        $is_overdue = $today > $due_date;
                        $days_label = $is_overdue ? '<span class="label label-danger">Overdue by '.$days_until.' days</span>' : '<span class="label label-warning">'.$days_until.' days</span>';
                        
                        // Determine if invoice needs reminder button (overdue only)
                        $reminder_btn = '';
                        if ($is_overdue) {
                            $reminder_btn = '<a href="#" data-invoice-id="'.$row['id'].'" data-email="'.$row['email'].'" data-invoice-number="'.$row['invoice'].'" class="btn btn-warning btn-xs send-reminder" title="Send Payment Reminder"><span class="glyphicon glyphicon-bell"></span></a> ';
                        }
                        
                        echo '<tr>
                            <td><strong>'.$row['invoice'].'</strong></td>
                            <td>'.$row['customer_name'].'</td>
                            <td>'.CURRENCY . number_format($row['subtotal'], 2).'</td>
                            <td>'.CURRENCY . number_format($row['amount_paid'] ?? 0, 2).'</td>
                            <td><strong>'.CURRENCY . number_format($balance_due, 2).'</strong></td>
                            <td>'.$row['invoice_due_date'].'</td>
                            <td>'.$days_label.'</td>
                            <td>
                                '.$reminder_btn.'
                                <a href="invoice-edit.php?id='.$row['invoice'].'" class="btn btn-primary btn-xs" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>
                                <a href="#" data-invoice-id="'.$row['invoice'].'" data-email="'.$row['email'].'" data-invoice-type="'.$row['invoice_type'].'" data-custom-email="'.$row['custom_email'].'" class="btn btn-success btn-xs email-invoice" title="Email"><span class="glyphicon glyphicon-envelope"></span></a>
                                <a href="invoices/'.$row['invoice'].'.pdf" class="btn btn-info btn-xs" target="_blank" title="Download"><span class="glyphicon glyphicon-download-alt"></span></a>
                                <a data-invoice-id="'.$row['invoice'].'" class="btn btn-danger btn-xs delete-invoice" title="Delete"><span class="glyphicon glyphicon-trash"></span></a>
                            </td>
                        </tr>';
                    }

                    echo '</tbody></table>';
                } else {
                    echo '<div class="alert alert-success">? No pending invoices! All invoices are paid.</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

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
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    #data-table.DataTable();

    // Delete invoice
    $(document).on('click', '.delete-invoice', function(e) {
        e.preventDefault();
        var invoice_id = $(this).data('invoice-id');
        $('#delete').data('invoice-id', invoice_id);
        $('#delete_invoice').modal('show');
    });

    $('#delete').on('click', function() {
        var invoice_id = $(this).data('invoice-id');
        $.ajax({
            type: 'POST',
            url: 'response.php',
            data: {
                action: 'delete_invoice',
                invoice_id: invoice_id
            },
            success: function(response) {
                $('#response').show();
                $('.message').html(response);
                setTimeout(function() {
                    location.reload();
                }, 1000);
            }
        });
    });

    // Email invoice
    $(document).on('click', '.email-invoice', function(e) {
        e.preventDefault();
        var invoice_id = $(this).data('invoice-id');
        var email = $(this).data('email');
        var invoice_type = $(this).data('invoice-type');
        var custom_email = $(this).data('custom-email');

        $.ajax({
            type: 'POST',
            url: 'response.php',
            data: {
                action: 'send_invoice_email',
                invoice_id: invoice_id,
                email: email,
                invoice_type: invoice_type,
                custom_email: custom_email
            },
            success: function(response) {
                $('#response').show();
                $('.message').html(response);
                setTimeout(function() {
                    $('#response').hide();
                }, 3000);
            }
        });
    });

    // Send payment reminder
    $(document).on('click', '.send-reminder', function(e) {
        e.preventDefault();
        var invoice_id = $(this).data('invoice-id');
        var invoice_number = $(this).data('invoice-number');
        var email = $(this).data('email');

        if (!confirm('Send payment reminder for Invoice #' + invoice_number + ' to ' + email + '?')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'response.php',
            data: {
                action: 'send_reminder',
                invoice_id: invoice_id
            },
            success: function(response) {
                var data = JSON.parse(response);
                $('#response').removeClass('alert-success alert-danger');
                $('#response').addClass(data.status === 'Success' ? 'alert-success' : 'alert-danger');
                $('#response').show();
                $('.message').html(data.message);
                setTimeout(function() {
                    $('#response').hide();
                }, 5000);
            },
            error: function() {
                $('#response').removeClass('alert-success').addClass('alert-danger');
                $('#response').show();
                $('.message').html('Failed to send reminder. Please try again.');
                setTimeout(function() {
                    $('#response').hide();
                }, 5000);
            }
        });
    });
});
</script>

<?php
    include('footer.php');
?>


