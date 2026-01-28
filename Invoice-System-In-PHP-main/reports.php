<?php
include('header.php');
include('enhanced-functions.php');
include_once("includes/config.php");

$report_type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

?>

<h1>Reports</h1>
<hr>

<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Report Filters</h3>
            </div>
            <div class="box-body">
                <form method="GET" class="form-inline">
                    <div class="form-group">
                        <label>Report Type:</label>
                        <select name="type" class="form-control" onchange="this.form.submit()">
                            <option value="monthly" <?php echo $report_type == 'monthly' ? 'selected' : ''; ?>>Monthly Income</option>
                            <option value="customer" <?php echo $report_type == 'customer' ? 'selected' : ''; ?>>Customer Summary</option>
                            <option value="overdue" <?php echo $report_type == 'overdue' ? 'selected' : ''; ?>>Overdue Analysis</option>
                        </select>
                    </div>
                    
                    <?php if ($report_type == 'monthly'): ?>
                    <div class="form-group">
                        <label>Month:</label>
                        <select name="month" class="form-control" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" <?php echo $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Year:</label>
                        <select name="year" class="form-control" onchange="this.form.submit()">
                            <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($report_type == 'monthly'): ?>
    <!-- Monthly Income Report -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Monthly Income Report - <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h3>
                </div>
                <div class="box-body">
                    <?php $monthly = getMonthlyIncomeReport($year, $month); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Paid Invoices</h4>
                            <p class="text-success">
                                <strong>Count:</strong> <?php echo $monthly['paid_count'] ?? 0; ?><br>
                                <strong>Amount:</strong> <?php echo CURRENCY . number_format($monthly['total_paid'] ?? 0, 2); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h4>Outstanding Invoices</h4>
                            <p class="text-warning">
                                <strong>Count:</strong> <?php echo $monthly['outstanding_count'] ?? 0; ?><br>
                                <strong>Amount:</strong> <?php echo CURRENCY . number_format($monthly['total_outstanding'] ?? 0, 2); ?>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Summary</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td><strong>Total Invoices:</strong></td>
                                    <td><?php echo $monthly['total_invoices'] ?? 0; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Invoiced:</strong></td>
                                    <td><?php echo CURRENCY . number_format((($monthly['total_paid'] ?? 0) + ($monthly['total_outstanding'] ?? 0)), 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Collection Rate:</strong></td>
                                    <td>
                                        <?php 
                                        $total = ($monthly['total_paid'] ?? 0) + ($monthly['total_outstanding'] ?? 0);
                                        $rate = $total > 0 ? (($monthly['total_paid'] ?? 0) / $total) * 100 : 0;
                                        echo number_format($rate, 1) . '%';
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($report_type == 'customer'): ?>
    <!-- Customer Summary Report -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Customer Summary Report</h3>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-hover table-bordered" id="customer-report">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Total Invoices</th>
                                <th>Total Paid</th>
                                <th>Outstanding</th>
                                <th>Last Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $customers = getCustomerSummary();
                            foreach ($customers as $cust): 
                            ?>
                            <tr>
                                <td><?php echo $cust['name']; ?></td>
                                <td><?php echo $cust['email']; ?></td>
                                <td><?php echo $cust['total_invoices']; ?></td>
                                <td><?php echo CURRENCY . number_format($cust['total_paid'] ?? 0, 2); ?></td>
                                <td><?php echo CURRENCY . number_format($cust['total_outstanding'] ?? 0, 2); ?></td>
                                <td><?php echo $cust['last_invoice_date'] ?? 'Never'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#customer-report').DataTable();
    });
    </script>

<?php elseif ($report_type == 'overdue'): ?>
    <!-- Overdue Analysis Report -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Overdue Invoices Analysis</h3>
                </div>
                <div class="box-body">
                    <?php $overdue = getOverdueInvoices(); ?>
                    
                    <?php if (count($overdue) > 0): ?>
                    <div class="alert alert-danger">
                        <strong>Total Overdue:</strong> <?php echo CURRENCY . number_format(array_sum(array_column($overdue, 'balance_due')), 2); ?>
                        <strong>| Count:</strong> <?php echo count($overdue); ?>
                    </div>
                    
                    <table class="table table-striped table-hover table-bordered" id="overdue-report">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount Due</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Last Reminder</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdue as $inv): ?>
                            <tr>
                                <td><strong><?php echo $inv['invoice']; ?></strong></td>
                                <td><?php echo $inv['name']; ?></td>
                                <td><?php echo CURRENCY . number_format($inv['balance_due'], 2); ?></td>
                                <td><?php echo $inv['invoice_due_date']; ?></td>
                                <td><span class="label label-danger"><?php echo $inv['days_overdue']; ?></span></td>
                                <td><?php echo $inv['last_reminder_sent'] ?? 'Never'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-success">
                        ✓ No overdue invoices! All invoices are current.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        $('#overdue-report').DataTable();
    });
    </script>
<?php endif; ?>

<?php
include('footer.php');
?>
