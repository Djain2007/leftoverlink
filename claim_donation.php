<?php
require_once 'db_connect.php';

// Ensure user is logged in and is an NGO
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'ngo') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['donation_id']) && isset($_POST['claim_option'])) {
    $donation_id = intval($_POST['donation_id']);
    $claim_option = $_POST['claim_option'];
    $ngo_id = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        if ($claim_option == 'request_volunteer') {
            // If requesting a volunteer, set delivery flag and generate codes
            $pickup_code = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $dropoff_code = str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT);
            $sql_update_donation = "UPDATE donations SET status = 'claimed', claimed_by_ngo_id = ?, delivery_requested = TRUE, pickup_verification_code = ?, dropoff_verification_code = ? WHERE id = ? AND status = 'available'";
            $stmt_update = $conn->prepare($sql_update_donation);
            
            // FIX: The number of variables and types now correctly matches the query (4 placeholders -> "issi" and 4 variables)
            $stmt_update->bind_param("issi", $ngo_id, $pickup_code, $dropoff_code, $donation_id);

        } else { // self_pickup
            $sql_update_donation = "UPDATE donations SET status = 'claimed', claimed_by_ngo_id = ? WHERE id = ? AND status = 'available'";
            $stmt_update = $conn->prepare($sql_update_donation);
            $stmt_update->bind_param("ii", $ngo_id, $donation_id);
        }

        $stmt_update->execute();

        if ($stmt_update->affected_rows > 0) {
            // If a volunteer was requested, we also create the delivery record
            if ($claim_option == 'request_volunteer') {
                $sql_create_delivery = "INSERT INTO deliveries (donation_id, ngo_id) VALUES (?, ?)";
                $stmt_delivery = $conn->prepare($sql_create_delivery);
                $stmt_delivery->bind_param("ii", $donation_id, $ngo_id);
                $stmt_delivery->execute();
            }
            $conn->commit();
            header("Location: dashboard.php?status=claimsuccess");
        } else {
            $conn->rollback();
            header("Location: dashboard.php?status=already_claimed");
        }
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        // Redirect with a generic error to avoid exposing technical details
        header("Location: dashboard.php?status=error");
    }
    exit();
}
?>