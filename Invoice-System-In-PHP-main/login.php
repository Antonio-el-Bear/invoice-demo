<?php
// Debug: Show all errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

/*******************************************************************************
* Invoice Management System                                               *
*                                                                              *
* Version: 1.0                                                               *
* Developer:  Abhishek Raj                                       *
*******************************************************************************/

// Start session BEFORE anything else
session_start();

// If user is already logged in, redirect to dashboard
if(!empty($_SESSION['login_username'])) {
    header('Location: dashboard.php');
    exit;
}

// Connect to the database BEFORE including header
require_once('includes/config.php');
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

// output any connection error
if ($mysqli->connect_error) {
die('Error : ('.$mysqli->connect_errno .') '. $mysqli->connect_error);
}

// Process login form submission BEFORE including header
if(!empty($_POST['username']) && !empty($_POST['password'])) {
    $username = mysqli_real_escape_string($mysqli,$_POST['username']);
    $password = mysqli_real_escape_string($mysqli,$_POST['password']);
    $pass_encrypt = md5($password);
    $fetch = $mysqli->query("SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'");
    $row = mysqli_fetch_array($fetch);
    if (!empty($row) && $row['password'] === $pass_encrypt) {
        $_SESSION['login_username'] = $row['username'];    
        $_SESSION['login_id'] = $row['id'];
        // Detect AJAX request
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo 1;
        } else {
            header('Location: dashboard.php');
        }
    } else {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo 0;
        } else {
            header('Location: login.php?error=1');
        }
    }
    exit;
} else if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If POST was made but with empty username/password
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo 0;
    } else {
        header("Location: login.php?error=1");
    }
    exit;
}

// Now include header AFTER all redirects are handled
include('header.php');
?>

<!-- Add jQuery CDN before any other scripts -->
<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Login</h1>
    </section>
    
    <section class="content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Please Login</h3>
                    </div>
                    <form class="form-horizontal" id="login-form">
                        <div class="box-body">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Username</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Password</label>
                                <div class="col-sm-10">
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function(){
    $('#login-form').on('submit', function(e){
        e.preventDefault();
        var username = $('#username').val();
        var password = $('#password').val();
        
        $.ajax({
            type: 'POST',
            url: 'login.php',
            data: {
                username: username,
                password: password
            },
            success: function(response) {
                if(response == 1) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Invalid username or password');
                }
            }
        });
    });
});
</script>

<?php include('footer.php'); ?>
