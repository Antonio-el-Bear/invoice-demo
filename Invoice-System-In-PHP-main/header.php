<?php
	//check login
	include("session.php");
?>


<!DOCTYPE html>

<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>CloudUko - Invoice Management System</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
 
<!-- ============================================
	     STYLESHEETS (All Local for Offline Functionality)
	     ============================================ -->
	
	<!-- Font Awesome: Icon font library for UI icons -->
  <link rel="stylesheet" href="css/font-awesome.min.css">
  
  <!-- Ionicons: Additional icon font set -->
  <link rel="stylesheet" href="css/ionicons.min.css">
  
  <!-- AdminLTE Theme: Main admin dashboard theme styles -->
  <link rel="stylesheet" href="css/AdminLTE.css">
 
  <!-- Skin: Green color scheme for the admin panel -->
  <link rel="stylesheet" href="css/skin-green.css">
  
  <!-- Bootstrap: Core UI framework CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  
  <!-- DateTime Picker: Styles for calendar/time selector -->
  <link rel="stylesheet" href="css/bootstrap.datetimepicker.css">
  
  <!-- DataTables CSS: Interactive table styles (LOCAL COPY) -->
  <link rel="stylesheet" href="css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
  
  <!-- Custom Application Styles -->
  <link rel="stylesheet" href="css/styles.css">
  
  	<!-- ============================================
	     JAVASCRIPT LIBRARIES (All Local for Offline Functionality)
	     ============================================ -->
	
	<!-- jQuery: Core JavaScript library for DOM manipulation -->
	<script src="js/jquery-1.11.1.min.js"></script>
	
	<!-- Moment.js: Date/time parsing and formatting library -->
	<script src="js/moment.js"></script>
	
	<!-- Bootstrap JS: UI framework JavaScript components (modals, dropdowns, etc.) -->
	<script src="js/bootstrap.min.js"></script>
	
	<!-- DataTables: Makes tables searchable, sortable, paginated (LOCAL COPY) -->
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	
	<!-- Bootstrap DateTime Picker: Calendar/time selection widget -->
	<script src="js/bootstrap.datetime.js"></script>
	
	<!-- Bootstrap Password: Password field enhancements -->
	<script src="js/bootstrap.password.js"></script>
	
	<!-- Custom Scripts: Application-specific JavaScript functionality -->
	<script src="js/scripts.js"></script>
	
	<!-- AdminLTE App: Admin template JavaScript functionality -->
	<script src="js/app.min.js"></script>

</head>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">

     <!--Logo -->
    <a href="" class="logo">
       <!--mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>IN</b>MS</span>
       <!--logo for regular state and mobile devices -->
      <span style="text-decoration:none;" class="logo-lg"><b>Invoice</b> System</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      <!-- Navbar Right Menu -->
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
         
          <!-- User Account Menu -->
          <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <!-- The user image in the navbar-->
              <img src="images/logo-01.png" class="user-image" alt="User Image">
              <!-- hidden-xs hides the username on small devices so only the image appears. -->
              <span class="hidden-xs"><?php echo $_SESSION['login_username'];?></span>
            </a>
            <ul class="dropdown-menu">
             <!-- Drop down list-->
              <li><a href="logout.php" class="btn btn-default btn-flat">Log out</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  
  
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">


      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
        <li class="header">MENU</li>
        <!-- Menu 0.1 -->
        <li class="treeview">
          <a href="dashboard.php"><i class="fa fa-tachometer"></i> <span>Dashboard</span>
            
          </a>
          
        </li>
        <!-- Menu 1 -->
         <li class="treeview">
          <a href="#"><i class="fa fa-file-text"></i> <span>Invoices</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="invoice-create.php"><i class="fa fa-plus"></i>Create Invoice</a></li>
            <li><a href="invoice-list.php"><i class="fa fa-cog"></i>Manage Invoices</a></li>
            <li><a href="reports.php"><i class="fa fa-bar-chart"></i>Reports</a></li>
            <li><a href="#" class="download-csv"><i class="fa fa-download"></i>Download CSV</a></li>
          </ul>
        </li>
        <!-- Menu 2 -->
         <li class="treeview">
          <a href="#"><i class="fa fa-archive"></i><span>Products</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="product-add.php"><i class="fa fa-plus"></i>Add Products</a></li>
            <li><a href="product-list.php"><i class="fa fa-cog"></i>Manage Products</a></li>
          </ul>
        </li>
        <!-- Menu 3 -->
        <li class="treeview">
          <a href="#"><i class="fa fa-users"></i><span>Customers</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="customer-add.php"><i class="fa fa-user-plus"></i>Add Customer</a></li>
            <li><a href="customer-list.php"><i class="fa fa-cog"></i>Manage Customers</a></li>
          </ul>
        </li>
        
        <!-- Menu 4 - Payments & Reminders -->
        <li class="treeview">
          <a href="#"><i class="fa fa-money"></i><span>Payments</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="payments-list.php"><i class="fa fa-list"></i>Payment History</a></li>
            <li><a href="reminders-list.php"><i class="fa fa-bell"></i>Reminders</a></li>
            <li><a href="profit-comparison.php"><i class="fa fa-line-chart"></i>Profit Comparison</a></li>
          </ul>
        </li>
        
        <!-- Menu 5 - System Users -->
        <li class="treeview">
          <a href="#"><i class="fa fa-user"></i><span>System Users</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="user-add.php"><i class="fa fa-plus"></i>Add User</a></li>
            <li><a href="user-list.php"><i class="fa fa-cog"></i>Manage Users</a></li>
          </ul>
        </li>
        
        <!-- Menu 6 - Audit & System -->
        <li class="treeview">
          <a href="#"><i class="fa fa-cogs"></i><span>System</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="audit-log.php"><i class="fa fa-history"></i>Activity Log</a></li>
            <li><a href="automation.php"><i class="fa fa-clock-o"></i>Automation & Tasks</a></li>
          </ul>
        </li>
        
      </ul>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
   

    <!-- Main content -->
    <section class="content">

      <!-- Your Page Content Here -->


