<?php
require_once 'config/db.php';
require_once 'config/auth.php';

if (isset($_SESSION['user_id'])) {
    // Log Logout Activity
    logActivity($pdo, 'LOGOUT', 'Authentication', "User '" . ($_SESSION['full_name'] ?? 'User') . "' logged out");
    
    // Clear session
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

header("Location: login.php");
exit;
?>
