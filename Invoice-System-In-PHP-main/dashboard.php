<?php
/*******************************************************************************
*  Invoice Management System                                                *
*                                                                              *
* Version: 1.0	                                                               *
* Developer:  Abhishek Raj                                   				           *
* Enhanced by: CloudUko Team                                                   *
*******************************************************************************/

include('header.php');
include('functions.php');
include_once("includes/config.php");
include_once("enhanced-functions.php");

$overdue_invoices = getOverdueInvoices();
$upcoming_invoices = getUpcomingDueInvoices();
$monthly_report = getMonthlyIncomeReport();
$outstanding_balance = getTotalOutstandingBalance();
$paid_this_month = getTotalPaidThisMonth();

?>

<section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green" data-link="paid-invoices.php" style="cursor:pointer;">
            <div class="inner">
              <h3><?php 
                
                $result = mysqli_query($mysqli, 'SELECT SUM(subtotal) AS value_sum FROM invoices WHERE status = "paid"'); 
                $row = mysqli_fetch_assoc($result); 
                $sum = $row['value_sum'];
                echo $sum;
                ?></h3>

              <p>Sales Amount</p>
            </div>
            <div class="icon">
              <i class="ion ion-social-usd"></i>
            </div>
            
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-purple" data-link="invoice-list.php" style="cursor:pointer;">
            <div class="inner">
              <h3><?php 
                
                $sql = "SELECT * FROM invoices";
                $query = $mysqli->query($sql);

                echo "$query->num_rows";
                ?></h3>

              <p>Total Invoices</p>
            </div>
            <div class="icon">
              <i class="ion ion-printer"></i>
            </div>
            
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow" data-link="pending-bills.php" style="cursor:pointer;">
            <div class="inner">
            <h3><?php 
                
                $sql = "SELECT * FROM invoices WHERE status = 'open'";
                $query = $mysqli->query($sql);

                echo "$query->num_rows";
                ?></h3>

              <p>Pending Bills</p>
            </div>
            <div class="icon">
              <i class="ion ion-load-a"></i>
            </div>
            
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red" data-link="pending-bills.php" style="cursor:pointer;">
            <div class="inner">
            <h3><?php 
                
                $result = mysqli_query($mysqli, 'SELECT SUM(subtotal) AS value_sum FROM invoices WHERE status = "open"'); 
                $row = mysqli_fetch_assoc($result); 
                $sum = $row['value_sum'];
                echo $sum;
                ?></h3>

              <p>Due Amount</p>
            </div>
            <div class="icon">
              <i class="ion ion-alert-circled"></i>
            </div>
            
          </div>
        </div>
        <!-- ./col -->
      </div>
      <!-- /.row -->


      <!-- 2nd row -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-primary" data-link="product-list.php" style="cursor:pointer;">
            <div class="inner">
              <h3><?php 
                
                $sql = "SELECT * FROM products";
                $query = $mysqli->query($sql);

                echo "$query->num_rows";
                ?></h3>

              <p>Total Products</p>
            </div>
            <div class="icon">
              <i class="ion ion-social-dropbox"></i>
            </div>
            
          </div>
        </div>

        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-maroon" data-link="customer-list.php" style="cursor:pointer;">
            <div class="inner">
              <h3><?php 
                
                $sql = "SELECT * FROM store_customers";
                $query = $mysqli->query($sql);

                echo "$query->num_rows";
                ?></h3>

              <p>Total Customers</p>
            </div>
            <div class="icon">
              <i class="ion ion-ios-people"></i>
            </div>
            
          </div>
        </div>

        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-olive" data-link="paid-invoices.php" style="cursor:pointer;">
            <div class="inner">
            <h3><?php 
                
                $sql = "SELECT * FROM invoices WHERE status = 'paid'";
                $query = $mysqli->query($sql);

                echo "$query->num_rows";
                ?></h3>

              <p>Paid Bills</p>
            </div>
            <div class="icon">
              <i class="ion ion-ios-paper"></i>
            </div>
            
          </div>
        </div>
      </div>
      
<!-- Enhanced Dashboard: Overdue & Reports -->
<div class="row">
    <div class="col-md-3">
      <div class="small-box bg-red" data-target="#overdue-section" data-link="invoice-list.php" style="cursor:pointer;">
            <div class="inner">
                <h3><?php echo CURRENCY . number_format($outstanding_balance, 0); ?></h3>
                <p>Outstanding Balance</p>
            </div>
            <div class="icon">
                <i class="ion ion-alert-circled"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
      <div class="small-box bg-yellow" data-target="#overdue-section" data-link="invoice-list.php" style="cursor:pointer;">
            <div class="inner">
                <h3><?php echo count($overdue_invoices); ?></h3>
                <p>Overdue Invoices</p>
            </div>
            <div class="icon">
                <i class="ion ion-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
      <div class="small-box bg-blue" data-target="#upcoming-section" data-link="due-soon.php" style="cursor:pointer;">
            <div class="inner">
                <h3><?php echo count($upcoming_invoices); ?></h3>
                <p>Due Soon (7 Days)</p>
            </div>
            <div class="icon">
                <i class="ion ion-calendar"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="small-box bg-green" data-link="paid-this-month.php" style="cursor:pointer;">
            <div class="inner">
                <h3><?php echo CURRENCY . number_format($paid_this_month, 0); ?></h3>
                <p>Paid This Month</p>
            </div>
            <div class="icon">
                <i class="ion ion-checkmark-round"></i>
            </div>
        </div>
    </div>
</div>

<!-- Overdue Invoices Table -->
<?php if (count($overdue_invoices) > 0): ?>
<div class="row" id="overdue-section">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">⚠️ Overdue Invoices</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped table-hover table-bordered" id="overdue-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Amount Due</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($overdue_invoices as $inv): ?>
                        <tr>
                            <td><strong><?php echo $inv['invoice']; ?></strong></td>
                            <td><?php echo $inv['name']; ?></td>
                            <td><?php echo CURRENCY . number_format($inv['balance_due'], 2); ?></td>
                            <td><?php echo $inv['invoice_due_date']; ?></td>
                            <td><span class="label label-danger"><?php echo $inv['days_overdue']; ?> days</span></td>
                            <td>
                                <a href="invoice-edit.php?id=<?php echo $inv['invoice']; ?>" class="btn btn-xs btn-primary">View</a>
                                <button class="btn btn-xs btn-warning send-reminder" data-invoice-id="<?php echo $inv['invoice']; ?>" data-email="<?php echo $inv['email']; ?>">Send Reminder</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Due Table -->
<?php if (count($upcoming_invoices) > 0): ?>
<div class="row" id="upcoming-section">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">📅 Due Soon (Next 7 Days)</h3>
            </div>
            <div class="box-body">
                <table class="table table-striped table-hover table-bordered" id="upcoming-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Days Until Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_invoices as $inv): ?>
                        <tr>
                            <td><strong><?php echo $inv['invoice']; ?></strong></td>
                            <td><?php echo $inv['name']; ?></td>
                            <td><?php echo CURRENCY . number_format($inv['balance_due'], 2); ?></td>
                            <td><?php echo $inv['invoice_due_date']; ?></td>
                            <td><span class="label label-warning"><?php echo $inv['days_until_due']; ?> days</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.dashboard-focus {
  box-shadow: 0 0 0 3px #f39c12 inset, 0 0 12px rgba(243, 156, 18, 0.65);
  transition: box-shadow 0.2s ease;
}
</style>

<script>
$(document).on('click', '.send-reminder', function(e) {
    e.preventDefault();
    var invoice_id = $(this).attr('data-invoice-id');
    var email = $(this).attr('data-email');
    var btn = $(this);
    
    btn.prop('disabled', true).text('Sending...');
    
    $.ajax({
        url: 'response.php',
        type: 'POST',
        data: {
            action: 'send_reminder',
            invoice_id: invoice_id,
            email: email
        },
        dataType: 'json',
        success: function(data) {
            alert(data.message);
            if(data.status === 'Success') {
                btn.closest('tr').fadeOut();
            }
            btn.prop('disabled', false).text('Send Reminder');
        },
        error: function(err) {
            alert('Error sending reminder');
            btn.prop('disabled', false).text('Send Reminder');
        }
    });
});

    // Allow KPI tiles to jump to the relevant section
    $(document).on('click', '.small-box[data-target]', function () {
      var targetSelector = $(this).data('target');
      var $target = $(targetSelector);

      if ($target.length) {
        $('html, body').animate({ scrollTop: $target.offset().top - 60 }, 400);
        $target.addClass('dashboard-focus');
        setTimeout(function () { $target.removeClass('dashboard-focus'); }, 1200);
      }
    });

    // Navigate when a box has a link target
    $(document).on('click', '.small-box[data-link]', function () {
      var link = $(this).data('link');
      if (link) {
        window.location.href = link;
      }
    });
</script>
     

    </section>
    <!-- /.content -->



<?php
	include('footer.php');
?>