<?php
require_once 'db_connect.php';
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    if (empty($email) || empty($password)) {
        $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Email and password are required.</p></div>';
    } else {
        // ** NEW: Fetch city and state in the SELECT query **
        $sql = "SELECT id, full_name, email, password_hash, user_type, city, state FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                // ** NEW: Store city and state in the session **
                $_SESSION['user_city'] = $user['city'];
                $_SESSION['user_state'] = $user['state'];
                header("Location: dashboard.php");
                exit();
            } else {
                $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Invalid email or password.</p></div>';
            }
        } else {
            $message = '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>Invalid email or password.</p></div>';
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login - LeftOverLink</title><script src="https://cdn.tailwindcss.com"></script><link rel="preconnect" href="https://rsms.me/"><link rel="stylesheet" href="https://rsms.me/inter/inter.css"><style> :root { font-family: 'Inter', sans-serif; } @supports (font-variation-settings: normal) { :root { font-family: 'Inter var', sans-serif; } } </style>
</head>
<body class="bg-slate-100">
    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-8 space-y-6 border-t-4 border-indigo-500">
            <div class="text-center"><h1 class="text-3xl font-bold text-slate-800">Welcome Back!</h1><p class="text-slate-500 mt-2">Log in to continue your journey 💚</p></div>
            <?php if (!empty($message)) echo $message; ?>
            <form action="login.php" method="POST" class="space-y-4">
                <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-3 bg-slate-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 rounded-lg text-white font-bold text-lg shadow-md hover:shadow-lg transition-all">Log In</button>
            </form>
            <p class="text-center text-sm text-slate-500">Don't have an account? <a href="register.php" class="font-medium text-indigo-600 hover:underline">Sign up here</a>.</p>
        </div>
    </div>
</body>
</html>