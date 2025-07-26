LeftOverLink 🤝
Turn Surplus Food into a Shared Meal.
LeftOverLink is a real-time, mobile-friendly web application designed to combat food waste and hunger by connecting those with surplus food to those in need.

Live Demo: leftoverlink.infy.uk

Hackathon Submission: This project was built for the 2025 Unity Hacks Hackathon.

🎯 Our Mission: Addressing SDG 2 - Zero Hunger
In a world where tons of edible food are wasted daily, many still go to bed hungry. LeftOverLink directly addresses the United Nations' Sustainable Development Goal 2: Zero Hunger by creating an efficient, technology-driven bridge between food surplus and food scarcity. Our platform provides a simple, localized solution to a global problem, ensuring that good food nourishes people, not landfills.

✨ How It Works
Our platform is built around a seamless, three-step process designed for speed and impact, connecting three key user groups:

1. Donors (Restaurants, Bakeries, Individuals)

2. NGOs / Receivers (Shelters, Food Banks)

3. Volunteers (Community Heroes)

Donors quickly post details about their surplus food, including type, quantity, and an optional photo. The process is designed to take less than 60 seconds.

NGOs in the same city receive instant notifications about new donations. They can view details and claim the food with a single click.

If an NGO needs help with transport, a delivery request is created. Local volunteers can see these requests and accept the ones that fit their schedule.

This creates a complete ecosystem where food is rescued and delivered to those who need it most, with minimal friction.

🚀 Key Features
Role-Based Dashboards: Separate, tailored interfaces for Donors, NGOs, and Volunteers.

Real-Time Donation Listings: NGOs see available food in their city as soon as it's posted.

Location-Based Filtering: Users only see donations and delivery requests relevant to their registered city, making the platform efficient and scalable.

Flexible Pickup Options: NGOs can choose to pick up donations themselves or request a volunteer for delivery.

Secure Verification System: A unique 2-digit code system for donors and NGOs ensures that volunteers complete pickups and drop-offs securely.

Image Uploads: Donors can upload photos of their food items to help NGOs assess the donation.

Donation Management: Donors can edit or delete their available donations.

Responsive, Mobile-First UI: Built with Tailwind CSS to ensure a seamless experience on any device.

🛠️ Tech Stack
Frontend: HTML, Tailwind CSS, Vanilla JavaScript

Backend: PHP

Database: MySQL

Server: Hosted on a standard LAMP (Linux, Apache, MySQL, PHP) stack.

⚙️ Local Setup and Installation
To run this project on your local machine, follow these steps:

1. Prerequisites
A local server environment like XAMPP or WAMP.

A web browser.

A database management tool like phpMyAdmin (included with XAMPP).

2. Installation
Clone the repository:

git clone https://github.com/your-username/leftoverlink.git

Move the project folder into your server's root directory (e.g., C:/xampp/htdocs/).

Database Setup:

Start Apache and MySQL from your XAMPP Control Panel.

Navigate to http://localhost/phpmyadmin/.

Click on the "Import" tab.

Click "Choose File" and select the database_setup.sql file included in this repository.

Click "Go" at the bottom of the page. This will create the leftoverlink_db database and all necessary tables.

Database Connection:

Open the db_connect.php file.

Ensure the database credentials match your local setup (by default, XAMPP uses root as the username and an empty password).

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'leftoverlink_db';

Create the uploads Folder:

In the root of your project folder, create a new folder named uploads. This is where images of donated food will be stored.

Run the Application:

Open your web browser and navigate to http://localhost/leftoverlink/.

You are all set! You can now register new users and test the complete application flow.
