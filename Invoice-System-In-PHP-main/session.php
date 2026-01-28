<?php 
/**
 * ============================================
 * SESSION AUTHENTICATION & SECURITY
 * ============================================
 * 
 * This file handles user authentication and session management.
 * It must be included at the top of every protected page.
 * 
 * Purpose:
 * - Check if user is logged in
 * - Redirect to login page if not authenticated
 * - Maintain database connection for the session
 */

	// Include system configuration (database settings, constants)
	include('includes/config.php');

	// ============================================
	// DATABASE CONNECTION
	// ============================================
	// Create a fresh database connection for this session
	// This ensures each page has access to the database
	$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

	// Check if database connection failed
	if ($mysqli->connect_error) {
		die('Database Connection Error: ' . $mysqli->connect_error);
	}

	// ============================================
	// SESSION MANAGEMENT
	// ============================================
	// Start PHP session only if one isn't already active
	// This prevents "session already started" errors
	if(session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	// ============================================
	// AUTHENTICATION CHECK
	// ============================================
	// Verify user is logged in by checking session variable
	// 'login_username' is set during successful login
	$check = isset($_SESSION['login_username']) ? $_SESSION['login_username'] : null;

	// If user is not logged in, redirect to login page
	if(!isset($check) || empty($check)) {
		// Redirect to login page (index.php)
		// User must authenticate before accessing protected content
	    header("Location:index.php");
	    exit(); // Stop script execution after redirect
	}

?>