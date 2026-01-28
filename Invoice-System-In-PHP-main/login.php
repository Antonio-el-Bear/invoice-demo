<?php
/*******************************************************************************
* Invoice Management System                                               *
*                                                                              *
* Version: 1.0                                                               *
* Developer:  Abhishek Raj                                       *
*******************************************************************************/

// Start session BEFORE anything else
session_start();

// If user is already logged in, redirect to dashboard
if(!empty(['id'])) {
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
    extract($_POST);

    $username = mysqli_real_escape_string($mysqli,$_POST['username']);
    $pass_encrypt = mysqli_real_escape_string($mysqli,$_POST['password']);

    $fetch = $mysqli->query("SELECT * FROM `users` WHERE username='$username' AND `password` = '$pass_encrypt'");

    $row = mysqli_fetch_array($fetch);

    if (password_verify($pass_encrypt, $row['passowrd'])) {
        $_SESSION['login_username'] = $row['username'];    
        echo 1;  
    } else {
        echo 0;
    }
    exit;
} else if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If POST was made but with empty username/password
    header("Location:index.php");
    exit;
}

// Now include header AFTER all redirects are handled
include('header.php');
?>

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
    #login-form.on('submit', function(e){
        e.preventDefault();
        var username = #username.val();
        var password = #password.val();
        
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
