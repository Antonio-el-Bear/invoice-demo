<?php
/**
 * ============================================
 * CLOUDUKO INVOICE SYSTEM - LOGIN PAGE
 * ============================================
 * 
 * This is the main entry point and authentication page.
 * Users must log in here before accessing the invoice system.
 * 
 * Features:
 * - User authentication form
 * - Remember me functionality
 * - AJAX-powered login (no page reload)
 * - Company logo display
 * - Security against unauthorized access
 */

// Include login page header (no authentication check, allows access to login form)
include('header-login.php');

// Include system functions (database queries, helper functions)
include('functions.php');

?>

<!-- ============================================
     RESPONSE MESSAGE AREA
     ============================================ 
     Displays success/error messages from login attempts -->
<div class="row vertical-offset-100">
	<div id="response" class="alert alert-success" style="display:none;">
		<!-- Close button for dismissing alerts -->
		<a href="#" class="close" data-dismiss="alert">&times;</a>
		<!-- Message content is dynamically inserted here by JavaScript -->
		<div class="message"></div>
	</div>

	<!-- ============================================
	     LOGIN FORM PANEL
	     ============================================ -->
	<div class="col-md-4 col-md-offset-4">
		<div class="panel panel-default login-panel">
		  	<!-- ============================================
		  	     PANEL HEADER WITH LOGO
		  	     ============================================ -->
		  	<div class="panel-heading panel-login">
		  		<h1 class="text-center">
		  			<!-- Company logo from config.php (COMPANY_LOGO constant) -->
					<img src="<?php echo COMPANY_LOGO ?>" class="img-responsive" alt="CloudUko Logo">
				</h1>
		    	
		 	</div>
		 	
		 	<!-- ============================================
		  	     PANEL BODY WITH LOGIN FORM
		  	     ============================================ -->
		  	<div class="panel-body">
		  		<!-- Form submits via AJAX in scripts.js -->
		    	<form accept-charset="UTF-8" role="form" method="post" id="login_form">
		    		<!-- Hidden field tells response.php this is a login action -->
		    		<input type="hidden" name="action" value="login">
		    		
	                <fieldset>
	                	<!-- ============================================
	                	     USERNAME INPUT FIELD
	                	     ============================================ -->
			    	  	<div class="input-group form-group">
			    	  		<!-- Icon for username field -->
			    	  		<div class="input-group-addon"><i class="glyphicon glyphicon-user"></i></div>
			    	  		<!-- Username input (required by 'required' class, validated in JS) -->
			    		    <input class="form-control required" name="username" id="username" type="text" placeholder="Enter Username" autocomplete="username">
			    		</div>
			    		
			    		<!-- ============================================
	                	     PASSWORD INPUT FIELD
	                	     ============================================ -->
			    		<div class="input-group form-group">
			    		 	<!-- Icon for password field -->
			    		 	<div class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></div>
			    		 	<!-- Password input (type="password" hides characters) -->
			    			<input class="form-control required" placeholder="Password" name="password" type="password" placeholder="Enter Password" autocomplete="current-password">
			    		</div>
			    		
			    		<!-- ============================================
	                	     REMEMBER ME CHECKBOX
	                	     ============================================ -->
			    		<div class="checkbox">
			    	    	<label>
			    	    		<!-- Checkbox to remember login (sets cookie for persistence) -->
			    	    		<input name="remember" type="checkbox" value="Remember Me"> Remember Me
			    	    	</label>
			    	    	<!-- Optional: Add forgot password link -->
			    	    	<!--<a href="forgot.php" class="float-right">Forgot password?</a>-->
			    	    </div>
			    	    
			    	    <!-- ============================================
	                	     LOGIN BUTTON
	                	     ============================================ -->
			    	    <!-- Button triggers AJAX login in scripts.js (#btn-login click event) -->
			    		<button type="button" id="btn-login" class="btn btn-danger btn-block">Login</button><br>
			    	</fieldset>
		      	</form>
		    </div>
		</div>
	</div>
</div>

<?php
	// Include page footer (closes HTML tags, includes JavaScript)
	include('footer.php');
?>