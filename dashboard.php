<?php
require_once 'db_connect.php';

// Session check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// User data from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'];
$user_type = $_SESSION['user_type'];
$user_city = $_SESSION['user_city'] ?? null;
$user_state = $_SESSION['user_state'] ?? null;

// Helper function for time
function time_ago($datetime, $full = false) { $now = new DateTime; $ago = new DateTime($datetime); $diff = $now->diff($ago); $diff->w = floor($diff->d / 7); $diff->d -= $diff->w * 7; $string = ['y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second']; foreach ($string as $k => &$v) { if ($diff->$k) $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : ''); else unset($string[$k]); } if (!$full) $string = array_slice($string, 0, 1); $is_past = $now > $ago; return $string ? ($is_past ? '' : 'in ') . implode(', ', $string) . ($is_past ? ' ago' : '') : 'just now'; }

// Message handling from GET parameters
$page_message = '';
if(isset($_GET['status'])) {
    switch($_GET['status']) {
        case 'postsuccess': $page_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p>Donation posted successfully!</p></div>'; break;
        case 'claimsuccess': $page_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p>Donation claimed successfully!</p></div>'; break;
        case 'updatesuccess': $page_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p>Action completed successfully!</p></div>'; break;
        case 'deletesuccess': $page_message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert"><p>Donation successfully deleted.</p></div>'; break;
        case 'error': $page_message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p>An error occurred. Please try again.</p></div>'; break;
    }
}


// Handle Form Submission (POST requests) and then redirect
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($user_type == 'donor' && isset($_POST['submit_donation'])) {
        $food_type = trim($_POST['food_type']); $quantity = trim($_POST['quantity']); $is_veg = isset($_POST['is_veg']) ? 1 : 0; $best_before = trim($_POST['best_before']); $photo_url = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = "uploads/"; if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
            $image_name = uniqid() . '_' . basename($_FILES["photo"]["name"]); $target_file = $target_dir . $image_name;
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) { $photo_url = $target_file; }
        }
        $sql_user_address = "SELECT address FROM users WHERE id = ?"; $stmt_addr = $conn->prepare($sql_user_address); $stmt_addr->bind_param("i", $user_id); $stmt_addr->execute(); $pickup_location = $stmt_addr->get_result()->fetch_assoc()['address'] ?? 'Address not specified'; $stmt_addr->close();
        $sql_insert = "INSERT INTO donations (donor_id, food_type, quantity_description, is_veg, photo_url, best_before, pickup_location) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert); $stmt_insert->bind_param("ississs", $user_id, $food_type, $quantity, $is_veg, $photo_url, $best_before, $pickup_location);
        if ($stmt_insert->execute()) {
            header("Location: dashboard.php?status=postsuccess");
        } else {
            header("Location: dashboard.php?status=error");
        }
        $stmt_insert->close();
        exit();
    }
}


// Data Fetching for all user types
$donor_donations = []; if ($user_type == 'donor') { $sql = "SELECT * FROM donations WHERE donor_id = ? ORDER BY created_at DESC"; $stmt = $conn->prepare($sql); $stmt->bind_param("i", $user_id); $stmt->execute(); $result = $stmt->get_result(); while ($row = $result->fetch_assoc()) $donor_donations[] = $row; $stmt->close(); }
$available_donations = []; $my_claims = []; if ($user_type == 'ngo') {
    if ($user_city && $user_state) {
        $sql_avail = "SELECT d.*, u.full_name, u.business_name FROM donations d JOIN users u ON d.donor_id = u.id WHERE d.status = 'available' AND d.best_before > NOW() AND LOWER(u.city) = LOWER(?) AND LOWER(u.state) = LOWER(?) ORDER BY d.best_before ASC";
        $stmt_avail = $conn->prepare($sql_avail);
        $stmt_avail->bind_param("ss", $user_city, $user_state);
        $stmt_avail->execute();
        $result_avail = $stmt_avail->get_result();
        while ($row = $result_avail->fetch_assoc()) $available_donations[] = $row;
        $stmt_avail->close();
    }
    $sql_claimed = "SELECT d.*, u.full_name, u.business_name, del.status as delivery_status FROM donations d JOIN users u ON d.donor_id = u.id LEFT JOIN deliveries del ON d.id = del.donation_id WHERE d.claimed_by_ngo_id = ? ORDER BY d.created_at DESC"; 
    $stmt_claimed = $conn->prepare($sql_claimed); $stmt_claimed->bind_param("i", $user_id); $stmt_claimed->execute(); $result_claimed = $stmt_claimed->get_result(); while ($row = $result_claimed->fetch_assoc()) $my_claims[] = $row; $stmt_claimed->close();
}
$delivery_requests = []; $my_deliveries = []; if ($user_type == 'volunteer') {
    if ($user_city && $user_state) {
        $sql_req = "SELECT del.id as delivery_id, d.*, donor.business_name as donor_name, donor.full_name as donor_f_name, donor.address as pickup_address, ngo.ngo_name, ngo.address as destination_address FROM deliveries del JOIN donations d ON del.donation_id = d.id JOIN users donor ON d.donor_id = donor.id JOIN users ngo ON del.ngo_id = ngo.id WHERE del.status = 'pending' AND LOWER(donor.city) = LOWER(?) AND LOWER(donor.state) = LOWER(?) ORDER BY del.created_at ASC";
        $stmt_req = $conn->prepare($sql_req);
        $stmt_req->bind_param("ss", $user_city, $user_state);
        $stmt_req->execute();
        $result_req = $stmt_req->get_result();
        while ($row = $result_req->fetch_assoc()) $delivery_requests[] = $row;
        $stmt_req->close();
    }
    $sql_my = "SELECT del.id as delivery_id, del.status as delivery_status, d.*, donor.business_name as donor_name, donor.full_name as donor_f_name, donor.address as pickup_address, ngo.ngo_name, ngo.address as destination_address FROM deliveries del JOIN donations d ON del.donation_id = d.id JOIN users donor ON d.donor_id = donor.id JOIN users ngo ON del.ngo_id = ngo.id WHERE del.volunteer_id = ? AND del.status IN ('accepted', 'picked_up') ORDER BY del.accepted_at DESC";
    $stmt_my = $conn->prepare($sql_my); $stmt_my->bind_param("i", $user_id); $stmt_my->execute(); $result_my = $stmt_my->get_result(); while ($row = $result_my->fetch_assoc()) $my_deliveries[] = $row; $stmt_my->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard - LeftOverLink</title><script src="https://cdn.tailwindcss.com"></script><link rel="preconnect" href="https://rsms.me/"><link rel="stylesheet" href="https://rsms.me/inter/inter.css"><style> :root { font-family: 'Inter', sans-serif; } @supports (font-variation-settings: normal) { :root { font-family: 'Inter var', sans-serif; } } </style>
    <script>
        function changeTab(panel, tabId) { const tabContainer = document.getElementById(panel + '-tabs'); const contentContainer = document.getElementById(panel + '-content'); if (!tabContainer || !contentContainer) return; const buttons = tabContainer.querySelector('nav').children; Array.from(buttons).forEach(child => { child.classList.remove('border-indigo-500', 'text-indigo-600', 'bg-indigo-100', 'font-bold'); child.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300', 'font-semibold'); }); const activeTab = document.getElementById(tabId); activeTab.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'hover:border-slate-300', 'font-semibold'); activeTab.classList.add('border-indigo-500', 'text-indigo-600', 'bg-indigo-100', 'font-bold'); Array.from(contentContainer.children).forEach(child => child.style.display = 'none'); document.getElementById(tabId + '_content').style.display = 'block'; }
        function openModal(modalId, donationId) { if (modalId === 'claim-modal') { document.getElementById('modal-donation-id').value = donationId; } document.getElementById(modalId).classList.remove('hidden'); }
        function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }
        function openEditModal(donation) { document.getElementById('edit_donation_id').value = donation.id; document.getElementById('edit_food_type').value = donation.food_type; document.getElementById('edit_quantity').value = donation.quantity_description; const date = new Date(donation.best_before); const timezoneOffset = date.getTimezoneOffset() * 60000; const localISOTime = new Date(date - timezoneOffset).toISOString().slice(0, 16); document.getElementById('edit_best_before').value = localISOTime; document.getElementById('edit_is_veg').checked = donation.is_veg == 1; openModal('edit-modal'); }
    </script>
</head>
<body class="bg-slate-100">
    <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-40">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <svg class="h-8 w-auto text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12.0001 1.99219C15.939 1.99219 19.3444 4.54013 20.3725 8.24354C21.4005 11.9469 19.8636 15.9599 16.5818 17.8517C16.1439 18.0921 15.6547 18.2323 15.1557 18.2581C15.1118 18.2607 15.068 18.262 15.0242 18.262C14.8804 18.262 14.7366 18.2486 14.5981 18.2219C12.352 17.7949 10.3341 16.6358 8.82031 14.9375L8.81844 14.9394C8.68656 14.8102 8.54531 14.6739 8.39687 14.5327C8.39687 14.5327 8.39501 14.5308 8.39501 14.5308C8.39313 14.5289 8.39125 14.5271 8.38938 14.5252C7.08773 13.2503 6.22101 11.5312 6.00289 9.68203C5.52044 5.39531 8.37469 1.99219 12.0001 1.99219ZM12.0001 0C7.29469 0 3.10969 3.51562 2.05312 8.03906C0.996562 12.5625 3.12328 17.291 7.41094 19.1828C9.57656 20.1484 11.9672 20.575 14.3461 20.5219C19.7828 20.3922 23.9062 15.6422 23.9962 10.2305C24.0881 4.7125 18.6751 0 12.0001 0Z M10.4251 10.0312C9.48953 10.0312 8.72509 9.2668 8.72509 8.33125C8.72509 7.3957 9.48953 6.63125 10.4251 6.63125C11.3607 6.63125 12.1251 7.3957 12.1251 8.33125C12.1251 9.2668 11.3607 10.0312 10.4251 10.0312Z M16.4251 13.0312C15.4895 13.0312 14.7251 12.2668 14.7251 11.3312C14.7251 10.3957 15.4895 9.63125 16.4251 9.63125C17.3607 9.63125 18.1251 10.3957 18.1251 11.3312C18.1251 12.2668 17.3607 13.0312 16.4251 13.0312Z"/></svg>
                    <span class="ml-2 font-bold text-xl text-slate-800">LeftOverLink</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-slate-600 hidden sm:block">Welcome, <span class="font-medium text-slate-800"><?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></span>!</span>
                    <a href="logout.php" class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-screen-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <?php if (!empty($page_message)) echo $page_message; ?>
        
        <?php if ($user_type == 'donor'): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-md"><h2 class="text-2xl font-bold mb-4 text-slate-800">Post a New Donation</h2><form action="dashboard.php" method="POST" enctype="multipart/form-data" class="space-y-4"><input type="text" name="food_type" placeholder="Food Type (e.g., Bread, Rice)" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><input type="text" name="quantity" placeholder="Quantity (e.g., 10 kg, 20 packets)" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><div><label for="photo" class="block text-sm font-medium text-slate-600 mb-1">Photo (Optional)</label><input type="file" name="photo" id="photo" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"></div><div><label for="best_before" class="block text-sm font-medium text-slate-600 mb-1">Best Before Date & Time</label><input type="datetime-local" id="best_before" name="best_before" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div><div class="flex items-center"><input type="checkbox" id="is_veg" name="is_veg" value="1" checked class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"><label for="is_veg" class="ml-2 block text-sm text-slate-800">This item is vegetarian</label></div><button type="submit" name="submit_donation" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white font-bold text-lg shadow-md hover:shadow-lg transition-all">Post Donation</button></form></div>
                </div>
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold mb-4 text-slate-800">My Donation History</h2>
                    <div class="space-y-4">
                        <?php if (empty($donor_donations)): ?><p class="bg-white p-6 rounded-xl shadow-sm text-slate-500">You haven't posted any donations yet.</p><?php else: foreach ($donor_donations as $donation): ?><div class="bg-white p-4 rounded-xl shadow-sm"><div class="flex items-center justify-between"><div class="flex items-center space-x-4"><img src="<?php echo htmlspecialchars($donation['photo_url'] ?? 'https://via.placeholder.com/100'); ?>" alt="Food" class="h-16 w-16 rounded-lg object-cover"><div><h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($donation['food_type']); ?></h3><p class="text-sm text-slate-500"><?php if(!empty($donation['delivery_requested'])) { echo "Pickup Code: <span class='font-bold text-indigo-600'>".$donation['pickup_verification_code']."</span>";} else { echo "Posted: ".date('M d, Y', strtotime($donation['created_at'])); } ?></p></div></div><span class="text-sm font-semibold px-3 py-1 rounded-full <?php switch($donation['status']) { case 'available': echo 'bg-blue-100 text-blue-800'; break; case 'claimed': echo 'bg-amber-100 text-amber-800'; break; case 'completed': echo 'bg-green-100 text-green-800'; break; case 'expired': echo 'bg-red-100 text-red-800'; break; } ?>"><?php echo ucfirst($donation['status']); ?></span></div><?php if ($donation['status'] == 'available'): ?><div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-end space-x-2"><button onclick='openEditModal(<?php echo json_encode($donation); ?>)' class="text-sm font-semibold text-slate-600 hover:text-indigo-600 px-3 py-1 rounded-md hover:bg-slate-100">Edit</button><form action="delete_donation.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this donation? This cannot be undone.');"><input type="hidden" name="donation_id" value="<?php echo $donation['id']; ?>"><button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800 px-3 py-1 rounded-md hover:bg-red-50">Delete</button></form></div><?php endif; ?></div><?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($user_type == 'ngo'): ?>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-slate-800">Available Donations</h1>
                <?php if ($user_city): ?>
                    <div class="text-sm text-slate-500 font-semibold">Showing results in: <span class="text-indigo-600"><?php echo htmlspecialchars($user_city); ?></span></div>
                <?php endif; ?>
            </div>
            <div id="ngo-tabs" class="border-b border-slate-200 mb-6"><nav class="-mb-px flex space-x-6" aria-label="Tabs"><button id="btn_available_tab" onclick="changeTab('ngo', 'btn_available_tab')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm rounded-t-md border-indigo-500 text-indigo-600 bg-indigo-100">Available Donations</button><button id="btn_my_claims_tab" onclick="changeTab('ngo', 'btn_my_claims_tab')" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">My Claims</button></nav></div>
            <div id="ngo-content">
                <div id="btn_available_tab_content"><?php if (empty($available_donations)): ?><p class="bg-white p-8 rounded-xl shadow-sm text-center text-slate-500 text-lg">No available donations in your city right now. Check back soon!</p><?php else: ?><div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"><?php foreach ($available_donations as $donation): ?><div class="bg-white rounded-xl shadow-md overflow-hidden flex flex-col hover:shadow-xl transition-shadow"><img src="<?php echo htmlspecialchars($donation['photo_url'] ?? 'https://via.placeholder.com/300'); ?>" alt="Food" class="h-48 w-full object-cover"><div class="p-5 flex-grow"><h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($donation['food_type']); ?></h3><p class="text-sm text-slate-500 mb-2">by <?php echo htmlspecialchars($donation['business_name'] ?? $donation['full_name']); ?></p><p class="font-semibold text-rose-600 text-sm">Expires <?php echo time_ago($donation['best_before']); ?></p></div><div class="bg-slate-50 p-4"><button onclick="openModal('claim-modal', <?php echo $donation['id']; ?>)" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-all">Claim</button></div></div><?php endforeach; ?></div><?php endif; ?></div>
                <div id="btn_my_claims_tab_content" style="display: none;"><?php if (empty($my_claims)): ?><p class="bg-white p-8 rounded-xl shadow-sm text-center text-slate-500 text-lg">You have no active claims.</p><?php else: ?><div class="space-y-4"><?php foreach ($my_claims as $claim): ?><div class="bg-white p-4 rounded-xl shadow-sm"><div class="flex flex-col sm:flex-row sm:items-center sm:justify-between"><div><h3 class="font-bold text-lg text-slate-800"><?php echo htmlspecialchars($claim['food_type']); ?></h3><p class="text-sm text-slate-500">From <?php echo htmlspecialchars($claim['business_name'] ?? $claim['full_name']); ?></p></div><span class="mt-2 sm:mt-0 text-sm font-semibold px-3 py-1 rounded-full self-start <?php echo $claim['status'] == 'completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'; ?>"><?php echo ucfirst($claim['status']); ?></span></div><?php if($claim['status'] == 'claimed'): ?><div class="mt-4 pt-4 border-t border-slate-200 text-sm"><div class="font-semibold text-slate-700"><?php if(!empty($claim['delivery_requested'])): ?><div class="flex justify-between items-center"><span>Status: <span class="text-indigo-600"><?php echo 'Awaiting Volunteer ('.ucfirst($claim['delivery_status']).')'; ?></span></span><span class="ml-4">Drop-off Code: <span class="text-indigo-600 text-lg font-bold"><?php echo htmlspecialchars($claim['dropoff_verification_code']); ?></span></span></div><?php else: ?><div class="flex justify-between items-center"><span>Self-Pickup Arranged</span><form action="update_donation_status.php" method="POST"><input type="hidden" name="donation_id" value="<?php echo $claim['id']; ?>"><input type="hidden" name="new_status" value="completed"><button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg shadow-sm text-xs">Mark as Completed</button></form></div><?php endif; ?></div></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($user_type == 'volunteer'): ?>
            <div class="flex justify-between items-center mb-6">
                 <h1 class="text-3xl font-bold text-slate-800">Delivery Opportunities</h1>
                 <?php if ($user_city): ?>
                    <div class="text-sm text-slate-500 font-semibold">Showing requests in: <span class="text-indigo-600"><?php echo htmlspecialchars($user_city); ?></span></div>
                 <?php endif; ?>
            </div>
            <div id="volunteer-tabs" class="border-b border-slate-200 mb-6"><nav class="-mb-px flex space-x-6" aria-label="Tabs"><button id="btn_requests_tab" onclick="changeTab('volunteer', 'btn_requests_tab')" class="whitespace-nowrap py-3 px-1 border-b-2 font-bold text-sm rounded-t-md border-indigo-500 text-indigo-600 bg-indigo-100">Delivery Requests</button><button id="btn_my_deliveries_tab" onclick="changeTab('volunteer', 'btn_my_deliveries_tab')" class="whitespace-nowrap py-3 px-1 border-b-2 font-semibold text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">My Deliveries</button></nav></div>
            <div id="volunteer-content">
                <div id="btn_requests_tab_content"><?php if (empty($delivery_requests)): ?><p class="bg-white p-8 rounded-xl shadow-sm text-center text-slate-500 text-lg">No delivery requests in your city right now. You're a star for checking!</p><?php else: ?><div class="space-y-6"><?php foreach ($delivery_requests as $request): ?><div class="bg-white rounded-xl shadow-md overflow-hidden"><div class="p-6"><h3 class="font-bold text-xl text-slate-800 mb-2"><?php echo htmlspecialchars($request['food_type']); ?> (<?php echo htmlspecialchars($request['quantity_description']); ?>)</h3><div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"><div class="bg-amber-50 p-4 rounded-lg border border-amber-200"><p class="font-bold text-amber-800">1. PICKUP FROM:</p><p class="text-slate-700 font-semibold"><?php echo htmlspecialchars($request['donor_name'] ?? $request['donor_f_name']); ?></p><p class="text-slate-600"><?php echo htmlspecialchars($request['pickup_address']); ?></p></div><div class="bg-sky-50 p-4 rounded-lg border border-sky-200"><p class="font-bold text-sky-800">2. DELIVER TO:</p><p class="text-slate-700 font-semibold"><?php echo htmlspecialchars($request['ngo_name']); ?></p><p class="text-slate-600"><?php echo htmlspecialchars($request['destination_address']); ?></p></div></div></div><div class="bg-slate-50 p-4"><form action="volunteer_action.php" method="POST"><input type="hidden" name="delivery_id" value="<?php echo $request['delivery_id']; ?>"><input type="hidden" name="action" value="accept_delivery"><button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-all">Accept Delivery</button></form></div></div><?php endforeach; ?></div><?php endif; ?></div>
                <div id="btn_my_deliveries_tab_content" style="display: none;"><?php if (empty($my_deliveries)): ?><p class="bg-white p-8 rounded-xl shadow-sm text-center text-slate-500 text-lg">You have no active deliveries.</p><?php else: ?><div class="space-y-6"><?php foreach ($my_deliveries as $delivery): ?><div class="bg-white rounded-xl shadow-md"><div class="p-6"><h3 class="font-bold text-xl text-slate-800 mb-2"><?php echo htmlspecialchars($delivery['food_type']); ?></h3><p class="text-sm text-slate-500 mb-4">Pickup from <span class="font-semibold"><?php echo htmlspecialchars($delivery['donor_name'] ?? $delivery['donor_f_name']); ?></span> and deliver to <span class="font-semibold"><?php echo htmlspecialchars($delivery['ngo_name']); ?></span>.</p><?php if($delivery['delivery_status'] == 'accepted'): ?><form action="volunteer_action.php" method="POST" class="p-4 bg-amber-50 rounded-lg border border-amber-200 space-y-2"><p class="font-bold text-amber-800">Step 1: Confirm Pickup</p><p class="text-sm text-slate-600">Enter the 2-digit code provided by the donor.</p><input type="hidden" name="delivery_id" value="<?php echo $delivery['delivery_id']; ?>"><input type="hidden" name="action" value="verify_pickup"><div class="flex space-x-2"><input type="text" name="pickup_code" placeholder="##" maxlength="2" required class="w-20 px-4 py-2 bg-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg">Confirm Pickup</button></div></form><?php elseif($delivery['delivery_status'] == 'picked_up'): ?><div class="p-4 bg-green-50 rounded-lg border border-green-200 text-green-800 font-semibold">✓ Pickup Confirmed</div><form action="volunteer_action.php" method="POST" class="p-4 mt-4 bg-sky-50 rounded-lg border border-sky-200 space-y-2"><p class="font-bold text-sky-800">Step 2: Confirm Drop-off</p><p class="text-sm text-slate-600">Enter the 2-digit code provided by the NGO.</p><input type="hidden" name="delivery_id" value="<?php echo $delivery['delivery_id']; ?>"><input type="hidden" name="action" value="verify_dropoff"><div class="flex space-x-2"><input type="text" name="dropoff_code" placeholder="##" maxlength="2" required class="w-20 px-4 py-2 bg-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><button type="submit" class="flex-1 bg-sky-500 hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg">Confirm Drop-off</button></div></form><?php endif; ?></div></div><?php endforeach; ?></div><?php endif; ?></div>
            </div>
        <?php endif; ?>
    </main>

    <div id="claim-modal" class="hidden fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md"><h3 class="text-2xl font-bold text-slate-800 mb-2">Claim Donation</h3><p class="text-slate-500 mb-6">How would you like to arrange the pickup?</p><form action="claim_donation.php" method="POST" class="space-y-4"><input type="hidden" name="donation_id" id="modal-donation-id" value=""><button type="submit" name="claim_option" value="self_pickup" class="w-full text-left p-4 rounded-lg border-2 border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all"><p class="font-bold text-slate-800">I'll Pick It Up Myself</p><p class="text-sm text-slate-500">Arrange your own transportation to the donor's location.</p></button><button type="submit" name="claim_option" value="request_volunteer" class="w-full text-left p-4 rounded-lg border-2 border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all"><p class="font-bold text-slate-800">Request a Volunteer</p><p class="text-sm text-slate-500">Post a delivery request for a volunteer to handle the pickup.</p></button></form><button type="button" onclick="closeModal('claim-modal')" class="mt-6 w-full text-center text-sm text-slate-500 hover:text-slate-700">Cancel</button></div></div>
    <div id="edit-modal" class="hidden fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50 p-4"><div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md"><h3 class="text-2xl font-bold text-slate-800 mb-6">Edit Donation</h3><form action="edit_donation.php" method="POST" class="space-y-4"><input type="hidden" name="donation_id" id="edit_donation_id"><div><label for="edit_food_type" class="block text-sm font-medium text-slate-600">Food Type</label><input type="text" id="edit_food_type" name="food_type" required class="mt-1 w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div><div><label for="edit_quantity" class="block text-sm font-medium text-slate-600">Quantity</label><input type="text" id="edit_quantity" name="quantity" required class="mt-1 w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div><div><label for="edit_best_before" class="block text-sm font-medium text-slate-600">Best Before</label><input type="datetime-local" id="edit_best_before" name="best_before" required class="mt-1 w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div><div class="flex items-center"><input type="checkbox" id="edit_is_veg" name="is_veg" value="1" class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500"><label for="edit_is_veg" class="ml-2 block text-sm text-slate-800">This item is vegetarian</label></div><div class="flex items-center justify-end space-x-4 pt-4"><button type="button" onclick="closeModal('edit-modal')" class="text-sm font-semibold text-slate-600 px-4 py-2">Cancel</button><button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm">Save Changes</button></div></form></div></div>
</body>
</html>