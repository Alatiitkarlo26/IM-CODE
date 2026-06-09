<?php
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "guitarinventory_db"; 

// Create the connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

?>