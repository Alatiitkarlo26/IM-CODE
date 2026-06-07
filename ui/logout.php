<?php
// Initialize session tracking architecture
session_start();

// Unset all global tracking references
$_SESSION = array();

// Wipe active tracking cookie details completely from the client browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Kill server state context tracking file
session_destroy();

// Safely route the client back to the authentication portal dashboard root
header("Location: index.php");
exit();
?>