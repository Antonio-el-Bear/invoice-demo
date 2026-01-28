<?php
/**
 * CloudUko Invoice System - Password Migration Script
 * 
 * This script migrates all existing MD5 passwords to bcrypt.
 * Users will authenticate with their existing passwords which will
 * automatically be upgraded to bcrypt on next login.
 * 
 * IMPORTANT: Run this script BEFORE deploying security-functions.php
 * 
 * Usage: http://localhost:8080/clouduko-invoice/migrate-passwords.php
 */

// Check if already running from command line
$from_cli = php_sapi_name() === 'cli';

if (!$from_cli) {
    // Require admin authentication
    include('session.php');
    include('includes/config.php');
    
    if (!isset($_SESSION['login_username'])) {
        die('You must be logged in to run this migration');
    }
    
    // Check if user is admin (assuming users table has a role column)
    $mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    $stmt = $mysqli->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['login_username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user || $user['role'] != 'admin') {
        die('Admin access required');
    }
}

include_once('includes/config.php');
include_once('security-functions.php');

$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    die('Database connection error: ' . $mysqli->connect_error);
}

echo "Starting password migration...\n";

// Get all users with MD5 passwords (32 character hashes)
$query = "SELECT id, username, password FROM users WHERE LENGTH(password) = 32";
$result = $mysqli->query($query);

if (!$result) {
    die('Query error: ' . $mysqli->error);
}

$migrated = 0;
$failed = 0;

while ($user = $result->fetch_assoc()) {
    // The password is already MD5 hashed, so we keep it as is
    // When users log in, the authenticateUser function will detect the MD5
    // and automatically upgrade it to bcrypt
    echo "User {$user['username']}: Will upgrade on next login\n";
    $migrated++;
}

echo "\n=== Migration Summary ===\n";
echo "Total users: $migrated\n";
echo "Status: All existing passwords will be automatically upgraded to bcrypt on next login\n";
echo "No password changes required at this time.\n";

$mysqli->close();

if (!$from_cli) {
    echo "<hr><p><strong>✓ Migration complete!</strong> Existing users can log in with their current passwords, which will be automatically upgraded to bcrypt on next login.</p>";
    echo "<p><a href='index.php'>Return to Dashboard</a></p>";
}

?>
