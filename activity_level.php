<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elanco Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        .header {
            background-color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0A4A94;
        }

        .header img {
            height: 50px;
        }

        .profile-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .profile-button {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: white;
            color: #0A4A94;
            border: 2px solid #0A4A94;
            padding: 5px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            position: relative;
        }

        .profile-button:hover {
            background-color: #0A4A94;
            color: white;
        }

        .profile-container img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            padding: 5px;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 180px;
            z-index: 1000;
        }

        .dropdown a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #0A4A94;
            font-size: 14px;
            font-weight: bold;
        }

        .dropdown a:hover {
            background-color: #f0f0f0;
        }

        .navbar {
            background-color: #0A4A94;
            padding: 10px 0;
            text-align: center;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            position: relative;
        }

        .nav-links a::after {
            content: "•";
            color: red;
            position: absolute;
            top: -5px;
            right: -8px;
            font-size: 16px;
        }

        .footer {
            background-color: #002a66;
            padding: 10px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .footer img {
            width: 30px;
            margin: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="ElancoPics/elancologo.png" alt="Elanco Logo">
        <div class="profile-container" onclick="toggleDropdown()">
            <img src="ElancoPics/profileicon.png" alt="Profile">
            <button class="profile-button">Profile ▼</button>
            <div class="dropdown" id="profileDropdown">
                <a href="personalinfo.php">Personal Information</a>
                <a href="settings.php">Settings</a>
                <a href="help.php">Help</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <nav class="navbar">
        <div class="nav-links">
            <a href="current_activity.php">Current Activity</a>
            <a href="activity_level.php">Activity Level</a>
            <a href="heart_rate.php">Heart Rate</a>
            <a href="temperature.php">Temperature</a>
            <a href="weight.php">Weight</a>
            <a href="food&water.php">Food and Water Intake</a>
            <a href="calorie_burn.php">Calorie Burn</a>
            <a href="breathing_rate.php">Breathing Rate</a>
            <a href="behaviour_pattern.php">Behaviour Pattern</a>
            <a href="barking_frequency.php">Barking Frequency</a>
        </div>
    </nav>

    <div class="footer">
        <img src="ElancoPics/fblogo.png" alt="Facebook">
        <img src="ElancoPics/instalogo.png" alt="Instagram">
        <img src="ElancoPics/twitterlogo.png" alt="Twitter">
    </div>

    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("profileDropdown");
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        }

        document.addEventListener("click", function(event) {
            var profileContainer = document.querySelector(".profile-container");
            var dropdown = document.getElementById("profileDropdown");

            if (!profileContainer.contains(event.target)) {
                dropdown.style.display = "none";
            }
        });
    </script>

</body>
</html>