<?php
session_start();
require_once '../db/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: index.php?error=emptyfields");
        exit();
    }

    // Securely pull the user records from tbl_users using prepared statements
    $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM tbl_users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            
            // Populate Session Registry parameters
            $_SESSION['user_id'] = (int)$row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            
            // Standardize role detection strings (handles 'role', 'role_id', 'user_type')
            $detected_role = "";
            if (isset($row['role'])) { $detected_role = $row['role']; }
            if (isset($row['role_id'])) { $detected_role = $row['role_id']; }
            if (isset($row['user_type'])) { $detected_role = $row['user_type']; }
            
            $detected_role = trim(strtolower((string)$detected_role));

            // Strict Role-Based Endpoint Dispatch Router (Normalized to Title Case matches)
            if ($detected_role === 'admin' || $detected_role === 'super admin' || $detected_role === '1') {
                $_SESSION['role'] = 'Admin';
                header("Location: dashboard.php");
                exit();
            } else if ($detected_role === 'staff' || $detected_role === 'member' || $detected_role === '2') {
                $_SESSION['role'] = 'Staff';
                header("Location: staff-terminal.php");
                exit();
            } else {
                header("Location: index.php?error=invalid_role");
                exit();
            }

        } else {
            header("Location: index.php?error=wrongpassword");
            exit();
        }
    } else {
        header("Location: index.php?error=usernotfound");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>