<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elanco Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color:rgb(44, 49, 94);
            text-align: center;
        }
        .header {
            background-color:rgb(2, 51, 103);
            padding: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header img {
            max-width: 200px;
        }
        .login-button {
            background-color: white;
            color: #004a99;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            background-color: #fff;
            padding: 20px;
        }
        .sidebar {
            width: 15%;
        }
        .main-content {
            width: 50%;
            text-align: center;
        }
        .main-content img {
            width: 100%;
            height: auto;
        }
        .footer {
             background-color: #002a66;
             padding: 20px;
             position: fixed;
             bottom: 0;
             width: 100%;
             display: flex;
             justify-content: space-between;
             align-items: center;
             color: white;
        }
        .footer img {
             width: 30px;
        }
        .footer .social-icons {
             display: flex;
             gap: 10px;
        }
        .footer .rights {
             flex-grow: 1;
             text-align: center;
        }

        .footer img {
             width: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="ElancoPics/elancologo.png" alt="Elanco Logo">
        <button class="login-button" onclick="location.href='login.php'">Log in</button>
    </div>
    <div class="container">
        <div class="sidebar">
            <img src="ElancoPics/activity.avif" alt="Dog playing" width="100%" height="32%">
            <img src="ElancoPics/at-home-pet-oxygen.webp" alt="Dog breath" width="100%" height="32%">
            <img src="ElancoPics/dog-eating-out-of-bowl.webp" alt="Dog Eating" width="100%" height="32%">
        </div>
        <div class="main-content">
            <h2>Welcome to Elanco</h2>
            <p>Elanco is a global leader in animal health dedicated to innovating and delivering products and services to prevent and treat disease in farm animals and pets, creating value for farmers, pet owner, veterinarians, stakeholders and society as a whole.</p>
            <img src="ElancoPics/rawImage.jpg" alt="Main Image">
        </div>
        <div class="sidebar">
            <img src="ElancoPics/image-61109-800.jpg" alt="Dog Temperature" width="100%">
            <img src="ElancoPics/heartbeat.jpg" alt="Dog Heart beat" width="100%">
            <img src="ElancoPics/barking.jpg" alt="Dog Barking" width="100%">
        </div>
    </div>
    <div class="footer">
        <div class="social-icons">
            <img src="ElancoPics/emailicon.webp" alt="Email">
            <img src="ElancoPics/fblogo.png" alt="Facebook">
            <img src="ElancoPics/instalogo.png" alt="Instagram">
            <img src="ElancoPics/twitterlogo.png" alt="Twitter">
        </div>
        <div class="rights">&copy; 2025 Elanco. All rights reserved.</div>
    </div>
</body>
</html>