<?php
require_once 'db_connect.php';

// ** NEW: Location Data for Dropdowns **
// This data can be expanded or moved to a separate file later
$locations = [
    "Rajasthan" => ["Udaipur", "Jaipur", "Jodhpur", "Kota", "Ajmer"],
    "Maharashtra" => ["Mumbai", "Pune", "Nagpur", "Nashik"],
    "Gujarat" => ["Ahmedabad", "Surat", "Vadodara", "Rajkot"],
    "Delhi" => ["New Delhi", "Delhi"],
    "Karnataka" => ["Bengaluru", "Mysuru", "Mangaluru", "Hubballi"],
];
$states = array_keys($locations);


$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Standard Fields ---
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user_type = trim($_POST['user_type']);
    $phone_number = trim($_POST['phone_number']) ?? null;
    $address = trim($_POST['address']) ?? null;
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    // --- Dynamic Fields ---
    $business_name = trim($_POST['business_name']) ?? null;
    $ngo_name = trim($_POST['ngo_name']) ?? null;
    $ngo_reg_id = trim($_POST['ngo_registration_id']) ?? null;

    if (empty($full_name) || empty($email) || empty($password) || empty($user_type) || empty($city) || empty($state)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Please fill in all required fields, including City and State.</p></div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Invalid email format.</p></div>';
    } else {
        $sql_check = "SELECT id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>This email is already registered.</p></div>';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (full_name, email, password_hash, user_type, phone_number, address, city, state, business_name, ngo_name, ngo_registration_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssssssssss", $full_name, $email, $password_hash, $user_type, $phone_number, $address, $city, $state, $business_name, $ngo_name, $ngo_reg_id);
            if ($stmt_insert->execute()) {
                $message = '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p class="font-bold">Registration successful!</p><p>You can now <a href="login.php" class="font-bold underline">log in</a>.</p></div>';
            } else {
                $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Error: Could not register. Please try again.</p></div>';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Register - LeftOverLink</title><script src="https://cdn.tailwindcss.com"></script><link rel="preconnect" href="https://rsms.me/"><link rel="stylesheet" href="https://rsms.me/inter/inter.css"><style> :root { font-family: 'Inter', sans-serif; } @supports (font-variation-settings: normal) { :root { font-family: 'Inter var', sans-serif; } } .dynamic-field { transition: all 0.3s ease-in-out; overflow: hidden; max-height: 0; opacity: 0; transform: translateY(-10px); } .dynamic-field.visible { max-height: 500px; opacity: 1; transform: translateY(0); margin-top: 1rem; } </style>
</head>
<body class="bg-slate-100">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl p-8 space-y-6 border-t-4 border-indigo-500">
            <div class="text-center"><h1 class="text-3xl font-bold text-slate-800">Create Your Account</h1><p class="text-slate-500 mt-2">Join our community to fight food waste 🤝</p></div>
            <?php if (!empty($message)) echo $message; ?>
            <form action="register.php" method="POST" class="space-y-4">
                <input type="text" name="full_name" placeholder="Full Name" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <select id="user_type" name="user_type" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><option value="" disabled selected>-- Select your role --</option><option value="donor">I'm a Donor (Restaurant, Bakery, etc.)</option><option value="ngo">I'm an NGO / Food Bank</option><option value="volunteer">I'm a Volunteer</option></select>
                <div id="dynamic-fields-container">
                    <div id="donor_fields" class="dynamic-field space-y-4"><input type="text" name="business_name" placeholder="Restaurant / Bakery Name" class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                    <div id="ngo_fields" class="dynamic-field space-y-4"><input type="text" name="ngo_name" placeholder="NGO Name" class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"><input type="text" name="ngo_registration_id" placeholder="NGO Registration ID (Optional)" class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></div>
                </div>
                <textarea name="address" placeholder="Full Street Address" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" rows="2"></textarea>
                
                <!-- ** NEW: State and City Dropdowns ** -->
                <div class="grid grid-cols-2 gap-4">
                    <select id="state" name="state" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="" disabled selected>-- Select State --</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo htmlspecialchars($state); ?>"><?php echo htmlspecialchars($state); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="city" name="city" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" disabled>
                        <option value="" disabled selected>-- Select City --</option>
                    </select>
                </div>

                <input type="tel" name="phone_number" placeholder="Phone Number (Optional)" class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white font-bold text-lg shadow-md hover:shadow-lg transition-all">Register</button>
            </form>
            <p class="text-center text-sm text-slate-500">Already have an account? <a href="login.php" class="font-medium text-indigo-600 hover:underline">Log in here</a>.</p>
        </div>
    </div>
    
    <!-- ** NEW: JavaScript for Dynamic City Dropdown ** -->
    <script>
        const userTypeSelect = document.getElementById('user_type'); const donorFields = document.getElementById('donor_fields'); const ngoFields = document.getElementById('ngo_fields');
        userTypeSelect.addEventListener('change', function() { const selectedType = this.value; donorFields.classList.remove('visible'); ngoFields.classList.remove('visible'); if (selectedType === 'donor') { donorFields.classList.add('visible'); } else if (selectedType === 'ngo') { ngoFields.classList.add('visible'); } });

        // Location data from PHP
        const locations = <?php echo json_encode($locations); ?>;
        
        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('city');

        stateSelect.addEventListener('change', function() {
            const selectedState = this.value;
            
            // Clear previous city options
            citySelect.innerHTML = '<option value="" disabled selected>-- Select City --</option>';
            
            if (selectedState && locations[selectedState]) {
                // Enable city dropdown
                citySelect.disabled = false;
                
                // Populate with new city options
                locations[selectedState].forEach(function(city) {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            } else {
                // Disable city dropdown if no state is selected
                citySelect.disabled = true;
            }
        });
    </script>
</body>
</html>