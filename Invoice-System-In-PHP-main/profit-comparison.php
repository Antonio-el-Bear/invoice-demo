<?php
session_start();
require_once 'includes/config.php';

if(empty($_SESSION['id'])) {
    header('location: login.php');
    exit;
}

// Get current and previous week/month profit
$comparison_type = isset($_GET['type']) ? $_GET['type'] : 'week';

$today = date('Y-m-d');
$current_year = date('Y');
$current_month = date('m');

if($comparison_type == 'week') {
    // Get week start dates
    $current_week_start = date('Y-m-d', strtotime('monday this week'));
    $last_week_start = date('Y-m-d', strtotime('monday last week'));
    
    $query_current = "SELECT SUM(p.amount_paid) as total_paid
                      FROM payments p
                      WHERE DATE(p.payment_date) >= '$current_week_start'
                      AND DATE(p.payment_date) <= '$today'";
    
    $query_previous = "SELECT SUM(p.amount_paid) as total_paid
                       FROM payments p
                       WHERE DATE(p.payment_date) >= '$last_week_start'
                       AND DATE(p.payment_date) < DATE_SUB('$current_week_start', INTERVAL 1 DAY)";
    
    $period_label = "This Week";
    $prev_period_label = "Last Week";
} else {
    // Monthly comparison
    $first_of_month = date('Y-m-01');
    $first_of_prev_month = date('Y-m-01', strtotime('first day of last month'));
    $last_of_prev_month = date('Y-m-t', strtotime('last day of last month'));
    
    $query_current = "SELECT SUM(p.amount_paid) as total_paid
                      FROM payments p
                      WHERE DATE(p.payment_date) >= '$first_of_month'
                      AND DATE(p.payment_date) <= '$today'";
    
    $query_previous = "SELECT SUM(p.amount_paid) as total_paid
                       FROM payments p
                       WHERE DATE(p.payment_date) >= '$first_of_prev_month'
                       AND DATE(p.payment_date) <= '$last_of_prev_month'";
    
    $period_label = "This Month";
    $prev_period_label = "Last Month";
}

$result_current = $db->query($query_current);
$result_previous = $db->query($query_previous);

$current_profit = $result_current->fetch_assoc()['total_paid'] ?? 0;
$previous_profit = $result_previous->fetch_assoc()['total_paid'] ?? 0;

$difference = $current_profit - $previous_profit;
$percentage_change = $previous_profit > 0 ? ($difference / $previous_profit) * 100 : 0;
$trend = $difference >= 0 ? 'up' : 'down';

// Get detailed breakdown
$query_detail = "SELECT 
                    p.payment_date,
                    COUNT(*) as transaction_count,
                    SUM(p.amount_paid) as daily_total
                 FROM payments p";

if($comparison_type == 'week') {
    $query_detail .= " WHERE DATE(p.payment_date) >= '$current_week_start'";
} else {
    $query_detail .= " WHERE DATE(p.payment_date) >= '$first_of_month'";
}

$query_detail .= " GROUP BY DATE(p.payment_date) ORDER BY p.payment_date DESC";
$result_detail = $db->query($query_detail);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Profit Comparison | Invoice System</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/AdminLTE.css">
    <link rel="stylesheet" href="css/skin-green.css">
    <link rel="stylesheet" href="fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <style>
        .trend-up { color: #00a65a; }
        .trend-down { color: #dd4b39; }
        .comparison-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-row {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            flex: 1;
            margin: 0 10px;
        }
        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body class="skin-green">
    <div class="wrapper">
        <?php include 'header.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Profit Comparison</h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li <?php echo $comparison_type == 'week' ? 'class="active"' : ''; ?>>
                                    <a href="?type=week">Week Comparison</a>
                                </li>
                                <li <?php echo $comparison_type == 'month' ? 'class="active"' : ''; ?>>
                                    <a href="?type=month">Month Comparison</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="active tab-pane">
                                    <div class="comparison-card">
                                        <div class="stat-row">
                                            <div class="stat-box">
                                                <div class="stat-label"><?php echo $period_label; ?></div>
                                                <div class="stat-value"><?php echo CURRENCY . number_format($current_profit, 2); ?></div>
                                            </div>
                                            <div class="stat-box">
                                                <div class="stat-label"><?php echo $prev_period_label; ?></div>
                                                <div class="stat-value"><?php echo CURRENCY . number_format($previous_profit, 2); ?></div>
                                            </div>
                                            <div class="stat-box">
                                                <div class="stat-label">Difference</div>
                                                <div class="stat-value <?php echo $trend == 'up' ? 'trend-up' : 'trend-down'; ?>">
                                                    <?php echo ($trend == 'up' ? '+' : '') . CURRENCY . number_format($difference, 2); ?>
                                                </div>
                                            </div>
                                            <div class="stat-box">
                                                <div class="stat-label">Change</div>
                                                <div class="stat-value <?php echo $trend == 'up' ? 'trend-up' : 'trend-down'; ?>">
                                                    <?php echo ($trend == 'up' ? '↑' : '↓') . ' ' . number_format(abs($percentage_change), 1) . '%'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4>Daily Breakdown</h4>
                                        </div>
                                        <div class="panel-body">
                                            <table class="table table-striped table-hover" id="detail-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Transactions</th>
                                                        <th>Daily Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    while($row = $result_detail->fetch_assoc()) {
                                                        echo '<tr>
                                                            <td>'.date('F j, Y', strtotime($row['payment_date'])).'</td>
                                                            <td>'.$row['transaction_count'].'</td>
                                                            <td><strong>'.CURRENCY . number_format($row['daily_total'], 2).'</strong></td>
                                                        </tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script>
        $(document).ready(function() {
            $('#detail-table').DataTable({
                "order": [[0, 'desc']],
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
