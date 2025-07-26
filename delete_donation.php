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

    // Security Check: Delete only if the donation belongs to the logged-in donor AND is still available
    $sql = "DELETE FROM donations WHERE id = ? AND donor_id = ? AND status = 'available'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $donation_id, $donor_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: dashboard.php?update=deletesuccess");
        } else {
            // No rows deleted - either didn't belong to user or was already claimed
            header("Location: dashboard.php?update=deletefail");
        }
    } else {
        header("Location: dashboard.php?update=error");
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>