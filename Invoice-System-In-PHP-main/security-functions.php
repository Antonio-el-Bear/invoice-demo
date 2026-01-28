<?php
/**
 * CloudUko Invoice System - Security Functions
 * Updated authentication and security functions with bcrypt and prepared statements
 */

include_once('includes/config.php');

/**
 * Hash password using bcrypt (PHP 5.5+)
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against bcrypt hash
 * @param string $password - Plain text password
 * @param string $hash - Stored hash
 * @return bool - True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Authenticate user with prepared statement
 * @param mysqli $mysqli - Database connection
 * @param string $username - Username
 * @param string $password - Plain text password
 * @return array - User data if authenticated, null otherwise
 */
function authenticateUser($mysqli, $username, $password) {
    // Use prepared statement to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT id, username, password, email, name FROM users WHERE username = ?");
    
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        return null;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if password uses old MD5 format (for migration)
        if (strlen($user['password']) == 32 && !preg_match('/^\$2[aby]\$/', $user['password'])) {
            // Legacy MD5 password - verify it
            if (md5($password) === $user['password']) {
                // Password matches! Update to bcrypt for future logins
                $new_hash = hashPassword($password);
                $update_stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hash, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                return $user;
            }
        } else if (verifyPassword($password, $user['password'])) {
            // Bcrypt password verified
            return $user;
        }
    }
    
    $stmt->close();
    return null;
}

/**
 * Create new user with bcrypt password
 * @param mysqli $mysqli - Database connection
 * @param string $username - Username
 * @param string $password - Plain text password
 * @param string $email - Email address
 * @param string $name - Full name
 * @return array - Result array with success/error
 */
function createUser($mysqli, $username, $password, $email, $name) {
    // Validate input
    if (empty($username) || empty($password) || empty($email)) {
        return array('success' => false, 'message' => 'Missing required fields');
    }
    
    if (strlen($password) < 8) {
        return array('success' => false, 'message' => 'Password must be at least 8 characters');
    }
    
    // Check if username exists
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        return array('success' => false, 'message' => 'Username already exists');
    }
    $check_stmt->close();
    
    // Hash password
    $password_hash = hashPassword($password);
    
    // Insert user with prepared statement
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, email, name) VALUES (?, ?, ?, ?)");
    
    if (!$stmt) {
        return array('success' => false, 'message' => 'Database error: ' . $mysqli->error);
    }
    
    $stmt->bind_param("ssss", $username, $password_hash, $email, $name);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('success' => true, 'message' => 'User created successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        return array('success' => false, 'message' => 'Error creating user: ' . $error);
    }
}

/**
 * Update user password with bcrypt
 * @param mysqli $mysqli - Database connection
 * @param int $user_id - User ID
 * @param string $password - New plain text password
 * @return array - Result array
 */
function updateUserPassword($mysqli, $user_id, $password) {
    if (strlen($password) < 8) {
        return array('success' => false, 'message' => 'Password must be at least 8 characters');
    }
    
    $password_hash = hashPassword($password);
    
    $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $password_hash, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('success' => true, 'message' => 'Password updated successfully');
    } else {
        $error = $stmt->error;
        $stmt->close();
        return array('success' => false, 'message' => 'Error updating password: ' . $error);
    }
}

/**
 * Generate CSRF token
 * @return string - CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token - Token to verify
 * @return bool - True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>
