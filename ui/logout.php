<?php
session_start();

// Unset all global tracking references - clear
$_SESSION = array();

// Wipe cookie details
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}


session_destroy();

// Route user to login
header("Location: index.php");
exit();
?>