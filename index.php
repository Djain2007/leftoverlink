<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeftOverLink - Connecting Food, Reducing Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style> 
        :root { font-family: 'Inter', sans-serif; } 
        @supports (font-variation-settings: normal) { :root { font-family: 'Inter var', sans-serif; } } 
        .modal { transition: opacity 0.25s ease; }
    </style>
    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden'); // Prevent background scrolling
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-40">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <svg class="h-8 w-auto text-indigo-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12.0001 1.99219C15.939 1.99219 19.3444 4.54013 20.3725 8.24354C21.4005 11.9469 19.8636 15.9599 16.5818 17.8517C16.1439 18.0921 15.6547 18.2323 15.1557 18.2581C15.1118 18.2607 15.068 18.262 15.0242 18.262C14.8804 18.262 14.7366 18.2486 14.5981 18.2219C12.352 17.7949 10.3341 16.6358 8.82031 14.9375L8.81844 14.9394C8.68656 14.8102 8.54531 14.6739 8.39687 14.5327C8.39687 14.5327 8.39501 14.5308 8.39501 14.5308C8.39313 14.5289 8.39125 14.5271 8.38938 14.5252C7.08773 13.2503 6.22101 11.5312 6.00289 9.68203C5.52044 5.39531 8.37469 1.99219 12.0001 1.99219ZM12.0001 0C7.29469 0 3.10969 3.51562 2.05312 8.03906C0.996562 12.5625 3.12328 17.291 7.41094 19.1828C9.57656 20.1484 11.9672 20.575 14.3461 20.5219C19.7828 20.3922 23.9062 15.6422 23.9962 10.2305C24.0881 4.7125 18.6751 0 12.0001 0Z M10.4251 10.0312C9.48953 10.0312 8.72509 9.2668 8.72509 8.33125C8.72509 7.3957 9.48953 6.63125 10.4251 6.63125C11.3607 6.63125 12.1251 7.3957 12.1251 8.33125C12.1251 9.2668 11.3607 10.0312 10.4251 10.0312Z M16.4251 13.0312C15.4895 13.0312 14.7251 12.2668 14.7251 11.3312C14.7251 10.3957 15.4895 9.63125 16.4251 9.63125C17.3607 9.63125 18.1251 10.3957 18.1251 11.3312C18.1251 12.2668 17.3607 13.0312 16.4251 13.0312Z"/></svg>
                    <span class="font-bold text-xl text-slate-800">LeftOverLink</span>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="login.php" class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">Login</a>
                    <a href="register.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors">Register</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="py-20 sm:py-32">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl sm:text-6xl font-extrabold text-slate-900 tracking-tight">
                    Turn Surplus Food into a Shared Meal.
                </h1>
                <p class="mt-6 max-w-3xl mx-auto text-lg text-slate-600">
                    LeftOverLink is a real-time platform connecting restaurants, bakeries, and individuals with surplus food to local NGOs and shelters. Together, we can fight hunger and reduce food waste in our community.
                </p>
                <div class="mt-8 flex justify-center space-x-4">
                    <a href="register.php" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all">Join the Movement</a>
                    <a href="#how-it-works" class="px-8 py-3 bg-white hover:bg-slate-100 text-slate-800 font-bold rounded-lg shadow-lg hover:shadow-xl transition-all border border-slate-200">Learn More</a>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-20 bg-white">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-slate-900">Simple, Fast, and Impactful</h2>
                    <p class="mt-4 text-lg text-slate-600">Our process is designed for efficiency.</p>
                </div>
                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-12 text-center">
                    <div>
                        <div class="mx-auto h-16 w-16 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full text-3xl font-bold">1</div>
                        <h3 class="mt-6 text-xl font-bold">Post a Donation</h3>
                        <p class="mt-2 text-slate-600">Donors list surplus food in under 60 seconds with details like quantity and pickup time.</p>
                    </div>
                    <div>
                        <div class="mx-auto h-16 w-16 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full text-3xl font-bold">2</div>
                        <h3 class="mt-6 text-xl font-bold">Claim & Coordinate</h3>
                        <p class="mt-2 text-slate-600">Nearby NGOs are notified instantly. They can claim the food and choose to self-pickup or request a volunteer.</p>
                    </div>
                    <div>
                        <div class="mx-auto h-16 w-16 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full text-3xl font-bold">3</div>
                        <h3 class="mt-6 text-xl font-bold">Deliver & Confirm</h3>
                        <p class="mt-2 text-slate-600">Volunteers accept delivery requests and use secure codes for pickup and drop-off, ensuring a safe transfer.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Roles Section -->
        <section class="py-20">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <h3 class="text-2xl font-bold text-indigo-600">For Donors</h3>
                    <p class="mt-4 text-slate-600">Reduce waste, gain tax benefits, and build a positive community image. Posting your surplus food is quick, easy, and makes a tangible difference.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <h3 class="text-2xl font-bold text-indigo-600">For NGOs</h3>
                    <p class="mt-4 text-slate-600">Get real-time access to a variety of food donations in your city. Manage your pickups and coordinate with volunteers all in one place.</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md">
                    <h3 class="text-2xl font-bold text-indigo-600">For Volunteers</h3>
                    <p class="mt-4 text-slate-600">Be a local hero. Browse delivery opportunities in your area, accept jobs that fit your schedule, and be the crucial link that gets food to those who need it most.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-slate-300 py-8">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; <?php echo date("Y"); ?> LeftOverLink. All Rights Reserved.</p>
            <div class="mt-4 flex justify-center space-x-6">
                <button onclick="openModal('about-modal')" class="hover:text-white transition-colors">About Us</button>
                <button onclick="openModal('privacy-modal')" class="hover:text-white transition-colors">Privacy Policy</button>
                <button onclick="openModal('terms-modal')" class="hover:text-white transition-colors">Terms & Conditions</button>
            </div>
        </div>
    </footer>


    <!-- #################### MODALS #################### -->

    <!-- About Us Modal -->
    <div id="about-modal" class="modal hidden fixed inset-0 bg-slate-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">About LeftOverLink</h2>
                    <button onclick="closeModal('about-modal')" class="text-slate-400 hover:text-slate-800">&times;</button>
                </div>
                <div class="mt-4 prose max-w-none">
                    <p>LeftOverLink was born from a simple yet powerful idea: no edible food should go to waste when people in our communities are going hungry. We are a technology-driven, community-focused initiative dedicated to bridging the gap between food surplus and food scarcity.</p>
                    <p>Our mission is to provide a seamless, efficient, and reliable platform that empowers local businesses, NGOs, and volunteers to work together. By leveraging real-time technology, we aim to create a sustainable ecosystem where surplus food is a resource, not waste.</p>
                    <p>We believe that by connecting compassionate donors with dedicated organizations and volunteers, we can make a significant impact on SDG 2: Zero Hunger, one meal at a time.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacy-modal" class="modal hidden fixed inset-0 bg-slate-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Privacy Policy</h2>
                    <button onclick="closeModal('privacy-modal')" class="text-slate-400 hover:text-slate-800">&times;</button>
                </div>
                <div class="mt-4 prose max-w-none text-sm">
                    <p><strong>Last updated: July 26, 2025</strong></p>
                    <p>LeftOverLink ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how your personal information is collected, used, and disclosed by LeftOverLink.</p>
                    <h4>Information We Collect</h4>
                    <p>We collect information you provide directly to us, such as when you create an account, post a donation, or communicate with us. This information may include your name, email address, phone number, address, city, state, and any other information you choose to provide.</p>
                    <h4>How We Use Your Information</h4>
                    <p>We use the information we collect to operate, maintain, and provide you with the features and functionality of the Service. Specifically, your address, city, and state are used to connect you with local donation and delivery opportunities. Your contact information may be shared between a donor, NGO, and an accepted volunteer for the sole purpose of coordinating a pickup and delivery.</p>
                    <h4>Information Sharing</h4>
                    <p>We do not sell your personal information. We may share your information with other users of the platform as necessary to facilitate the food donation and delivery process.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    <div id="terms-modal" class="modal hidden fixed inset-0 bg-slate-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Terms & Conditions</h2>
                    <button onclick="closeModal('terms-modal')" class="text-slate-400 hover:text-slate-800">&times;</button>
                </div>
                <div class="mt-4 prose max-w-none text-sm">
                    <h4>1. Acceptance of Terms</h4>
                    <p>By accessing and using the LeftOverLink platform, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    <h4>2. User Conduct</h4>
                    <p>You agree to provide accurate and current information. You are responsible for any activity that occurs under your account. You agree not to post any food that is unsafe for consumption.</p>
                    <h4>3. Food Safety Disclaimer</h4>
                    <p>LeftOverLink is a platform to facilitate connections. We are not responsible for the quality, safety, or legality of the food donated. Donors are responsible for ensuring the food they provide is edible and handled according to safety standards. NGOs and recipients accept the food at their own risk.</p>
                    <h4>4. Limitation of Liability</h4>
                    <p>In no event shall LeftOverLink be liable for any direct, indirect, incidental, or consequential damages arising out of the use of our service.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>