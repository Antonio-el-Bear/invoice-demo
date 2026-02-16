<?php

include('header.php');
include('functions.php');
include('enhanced-functions.php');

?>

<div class="container-fluid">
    <h2>Advanced Invoice Dashboard</h2>
    
    <?php
    // Get all metrics
    $metrics = getDashboardMetrics();
    $aged_receivables = getAgedReceivables();
    $payment_trends = getPaymentTrends(6);
    $revenue_forecast = getRevenueForecast(3);
    $cash_flow = getCashFlowForecast(6);
    $risk_report = getCustomerRiskReport();
    ?>

    <!-- KEY METRICS ROW -->
    <div class="row" style="margin-top: 20px;">
        
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h4 class="panel-title">Total Revenue</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo CURRENCY . number_format($metrics['total_revenue'], 2); ?></h3>
                    <p><small>All time</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4 class="panel-title">Outstanding Balance</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo CURRENCY . number_format($metrics['outstanding_revenue'], 2); ?></h3>
                    <p><small><?php echo $metrics['open_invoices']; ?> open invoices</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h4 class="panel-title">Overdue Amount</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo CURRENCY . number_format($metrics['overdue_revenue'], 2); ?></h3>
                    <p><small><?php echo $metrics['overdue_invoices']; ?> overdue invoices</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h4 class="panel-title">Collection Rate</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo $metrics['collection_rate']; ?>%</h3>
                    <p><small><?php echo $metrics['paid_invoices']; ?> paid invoices</small></p>
                </div>
            </div>
        </div>

    </div>

    <!-- MONTHLY PERFORMANCE ROW -->
    <div class="row" style="margin-top: 20px;">
        
        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">This Month Revenue</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo CURRENCY . number_format($metrics['this_month_revenue'], 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">This Year Revenue</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo CURRENCY . number_format($metrics['this_year_revenue'], 2); ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Customer Base</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo $metrics['total_customers']; ?></h3>
                    <p><small><?php echo $metrics['active_customers']; ?> active</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Total Invoices</h4>
                </div>
                <div class="panel-body">
                    <h3><?php echo $metrics['total_invoices']; ?></h3>
                    <p><small><?php echo $metrics['paid_invoices']; ?> paid</small></p>
                </div>
            </div>
        </div>

    </div>

    <!-- AGED RECEIVABLES SECTION -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Aged Receivables Analysis
                        <a href="export.php?type=aged-receivables&format=csv" class="btn btn-xs btn-default pull-right" title="Export CSV">
                            <i class="glyphicon glyphicon-download"></i> Export
                        </a>
                    </h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Aging Bucket</th>
                                <th>Invoices</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aged_receivables as $aging): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($aging['aging_bucket']); ?></td>
                                <td><?php echo $aging['invoice_count']; ?></td>
                                <td><?php echo CURRENCY . number_format($aging['total_amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- CASH FLOW FORECAST -->
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Cash Flow Forecast (6 Months)</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Expected Invoices</th>
                                <th>Expected Collections</th>
                                <th>Net Cash Flow</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cash_flow as $month => $flow): ?>
                            <tr>
                                <td><?php echo $month; ?></td>
                                <td><?php echo CURRENCY . number_format($flow['expected_invoices'], 2); ?></td>
                                <td><?php echo CURRENCY . number_format($flow['expected_collections'], 2); ?></td>
                                <td style="color: <?php echo $flow['net_cash_flow'] >= 0 ? 'green' : 'red'; ?>;">
                                    <?php echo CURRENCY . number_format($flow['net_cash_flow'], 2); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- PAYMENT TRENDS & REVENUE FORECAST -->
    <div class="row" style="margin-top: 20px;">
        
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Payment Trends (Last 12 Months)</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Count</th>
                                <th>Total</th>
                                <th>Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payment_trends as $month => $trend): ?>
                            <tr>
                                <td><?php echo $month; ?></td>
                                <td><?php echo $trend['payment_count']; ?></td>
                                <td><?php echo CURRENCY . number_format($trend['total_paid'], 2); ?></td>
                                <td><?php echo CURRENCY . number_format($trend['avg_payment'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Revenue Forecast (Next 3 Months)</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Forecasted Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_forecast as $month => $forecast): ?>
                            <tr>
                                <td><?php echo $month; ?></td>
                                <td><?php echo CURRENCY . number_format($forecast, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- CUSTOMER RISK REPORT -->
    <div class="row" style="margin-top: 20px;">
        <div class="col-md-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h4 class="panel-title">High-Risk Customers Requiring Attention
                        <a href="export.php?type=risk-report&format=json" class="btn btn-xs btn-default pull-right" title="Export JSON">
                            <i class="glyphicon glyphicon-download"></i> Export
                        </a>
                    </h4>
                </div>
                <div class="panel-body">
                    <?php if (count($risk_report) > 0): ?>
                    <table class="table table-striped table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Total Invoices</th>
                                <th>Overdue Count</th>
                                <th>Outstanding</th>
                                <th>Avg Days Overdue</th>
                                <th>Payment Rate</th>
                                <th>Risk Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($risk_report as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo $customer['total_invoices']; ?></td>
                                <td><?php echo $customer['overdue_count']; ?></td>
                                <td><?php echo CURRENCY . number_format($customer['outstanding_amount'], 2); ?></td>
                                <td><?php echo round($customer['avg_days_overdue'], 1); ?></td>
                                <td><?php echo $customer['payment_completion_rate']; ?>%</td>
                                <td>
                                    <span class="label label-<?php echo $customer['risk_level'] === 'HIGH RISK' ? 'danger' : ($customer['risk_level'] === 'MEDIUM RISK' ? 'warning' : 'success'); ?>">
                                        <?php echo $customer['risk_level']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="alert alert-success">No high-risk customers at this time!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPORT SECTION -->
    <div class="row" style="margin-top: 20px; margin-bottom: 20px;">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h4 class="panel-title">Export & Reports</h4>
                </div>
                <div class="panel-body">
                    <p>
                        <a href="export.php?type=dashboard-metrics&format=json" class="btn btn-primary">
                            <i class="glyphicon glyphicon-download"></i> Dashboard Metrics (JSON)
                        </a>
                        <a href="export.php?type=invoices&format=excel" class="btn btn-primary">
                            <i class="glyphicon glyphicon-download"></i> All Invoices (Excel)
                        </a>
                        <a href="export.php?type=payments&format=excel" class="btn btn-primary">
                            <i class="glyphicon glyphicon-download"></i> All Payments (Excel)
                        </a>
                        <a href="export.php?type=customers&format=json" class="btn btn-primary">
                            <i class="glyphicon glyphicon-download"></i> Customer List (JSON)
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include('footer.php');
?>
