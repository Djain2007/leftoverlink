<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'volunteer') {
    header("Location: dashboard.php?update=auth_error");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    $delivery_id = intval($_POST['delivery_id']);
    $volunteer_id = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        if ($action == 'accept_delivery') {
            $sql = "UPDATE deliveries SET status = 'accepted', volunteer_id = ?, accepted_at = NOW() WHERE id = ? AND status = 'pending'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $volunteer_id, $delivery_id);
        } 
        elseif ($action == 'verify_pickup') {
            $pickup_code = trim($_POST['pickup_code']);
            // First, get the correct code from the donations table
            $sql_get_code = "SELECT d.pickup_verification_code FROM deliveries del JOIN donations d ON del.donation_id = d.id WHERE del.id = ?";
            $stmt_get = $conn->prepare($sql_get_code);
            $stmt_get->bind_param("i", $delivery_id);
            $stmt_get->execute();
            $correct_code = $stmt_get->get_result()->fetch_assoc()['pickup_verification_code'];
            
            if ($correct_code && $pickup_code == $correct_code) {
                $sql = "UPDATE deliveries SET status = 'picked_up' WHERE id = ? AND volunteer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $delivery_id, $volunteer_id);
            } else {
                throw new Exception("Incorrect pickup code.");
            }
        } 
        elseif ($action == 'verify_dropoff') {
            $dropoff_code = trim($_POST['dropoff_code']);
            // Get the correct code
            $sql_get_code = "SELECT d.dropoff_verification_code, d.id as donation_id FROM deliveries del JOIN donations d ON del.donation_id = d.id WHERE del.id = ?";
            $stmt_get = $conn->prepare($sql_get_code);
            $stmt_get->bind_param("i", $delivery_id);
            $stmt_get->execute();
            $result = $stmt_get->get_result()->fetch_assoc();
            $correct_code = $result['dropoff_verification_code'];
            $donation_id_to_complete = $result['donation_id'];
            
            if ($correct_code && $dropoff_code == $correct_code) {
                // Update both deliveries and donations tables
                $sql_delivery = "UPDATE deliveries SET status = 'completed' WHERE id = ? AND volunteer_id = ?";
                $stmt = $conn->prepare($sql_delivery);
                $stmt->bind_param("ii", $delivery_id, $volunteer_id);
                $stmt->execute();

                $sql_donation = "UPDATE donations SET status = 'completed' WHERE id = ?";
                $stmt_don = $conn->prepare($sql_donation);
                $stmt_don->bind_param("i", $donation_id_to_complete);
                $stmt_don->execute();
            } else {
                throw new Exception("Incorrect dropoff code.");
            }
        }

        if (isset($stmt)) {
             $stmt->execute();
        }
       
        $conn->commit();
        header("Location: dashboard.php?update=success");

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: dashboard.php?update=error&msg=" . urlencode($e->getMessage()));
    }
    exit();
}
?>