<?php
// Start the session on every page
session_start();

// Database credentials
$db_host = 'sql104.infinityfree.com';
$db_user = 'if0_39563886';
$db_pass = 'Sailg08531';
$db_name = 'if0_39563886_leftoverlink';

// Create a new MySQLi object for database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check for connection errors and terminate if there is one
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>