<?php
require_once 'db_connect.php';

// Ensure user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'donor') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['donation_id'])) {
    $donation_id = intval($_POST['donation_id']);
    $donor_id = $_SESSION['user_id'];

    // Get data from form
    $food_type = trim($_POST['food_type']);
    $quantity = trim($_POST['quantity']);
    $is_veg = isset($_POST['is_veg']) ? 1 : 0;
    $best_before = trim($_POST['best_before']);

    // For now, we will not handle photo updates in the edit form to keep it simple.
    // A full implementation would require deleting the old photo and uploading a new one.
    
    // Security Check: Update only if the donation belongs to the logged-in donor
    $sql = "UPDATE donations SET food_type = ?, quantity_description = ?, is_veg = ?, best_before = ? WHERE id = ? AND donor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisii", $food_type, $quantity, $is_veg, $best_before, $donation_id, $donor_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php?update=editsuccess");
    } else {
        header("Location: dashboard.php?update=error");
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>