<?php
session_start();

$db_file = "ElancoDatabase.db";

try {
    $conn = new PDO("sqlite:" . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $query = $conn->prepare("SELECT User_ID, F_Name, L_Name, Password FROM Pet_Owners WHERE Email = ?");
        $query->execute([$email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['Password']) { 
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['full_name'] = $user['F_Name'] . " " . $user['L_Name'];

            header("Location: current_activity.php");
            exit();
        } else {
            echo "<p style='color:red;'>Invalid email or password.</p>";
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elanco Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-image: url('ElancoPics/rawImage.jpg');
            background-size: cover;
            background-position: flex;
            background-repeat: no-repeat;
        }
        .header {
            background-color: white;
            padding: 10px;
            text-align: left;
            display: flex;
            border-bottom: 60px solid #0057A3;
        }
        .header img {
            width: 200px;
        }
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            border: 3px solid #0057A3;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #0057A3;
        }
        .login-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background: #0057A3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .footer {
            background-color: #002a66;
            padding: 10px;
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
    </div>
    <div class="login-container">
        <h2>User Login</h2>
        <form method="POST">
            <input type="text" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>

    <?php include 'footer.php'?>

</body>
</html>