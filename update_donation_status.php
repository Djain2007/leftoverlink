<?php
require_once 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check for required POST data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['donation_id']) && isset($_POST['new_status'])) {
    
    $donation_id = intval($_POST['donation_id']);
    $new_status = $_POST['new_status'];
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];

    // --- Security Check & Update Logic ---
    $sql = "";
    $stmt = null;

    // Logic for an NGO marking a donation as 'completed'
    if ($user_type == 'ngo' && $new_status == 'completed') {
        // IMPORTANT: We check that the donation was claimed by THIS NGO before updating.
        // This prevents one NGO from modifying another's claim.
        $sql = "UPDATE donations SET status = ? WHERE id = ? AND claimed_by_ngo_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $new_status, $donation_id, $user_id);
    }

    // (In the future, other status updates like 'cancelled' could be added here)

    // Execute the query if one was prepared
    if ($stmt) {
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: dashboard.php?update=success");
            } else {
                // No rows updated - likely an authorization error (tried to update a donation not belonging to them)
                header("Location: dashboard.php?update=auth_error");
            }
        } else {
            // SQL error
            header("Location: dashboard.php?update=error");
        }
        $stmt->close();
    } else {
        // No valid action was determined
        header("Location: dashboard.php?update=invalid_action");
    }

    $conn->close();
    exit();

} else {
    // If accessed without proper data, redirect
    header("Location: dashboard.php");
    exit();
}
?>