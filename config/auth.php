<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce authentication guard on protected pages/endpoints.
 */
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        // Check if API / JSON request
        $isApi = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            || (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false);
        
        if ($isApi) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required. Please log in.',
                'redirect' => 'login.php'
            ]);
            exit;
        } else {
            header("Location: login.php");
            exit;
        }
    }
}

/**
 * Get current logged in user array.
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? 'guest',
        'full_name' => $_SESSION['full_name'] ?? 'Guest User',
        'role' => $_SESSION['role'] ?? 'partner'
    ];
}

/**
 * Get client IP address (supporting multi-pc local networks & proxies)
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Log activity in activity_logs table
 */
function logActivity($pdo, $actionType, $module, $details, $userId = null, $username = null, $fullName = null) {
    try {
        if ($userId === null && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $username = $_SESSION['username'] ?? 'unknown';
            $fullName = $_SESSION['full_name'] ?? 'Unknown User';
        }
        
        $ipAddress = getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, username, full_name, action_type, module, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $username,
            $fullName,
            $actionType,
            $module,
            $details,
            $ipAddress,
            $userAgent
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}
?>
