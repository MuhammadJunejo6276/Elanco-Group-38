<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO('sqlite:ElancoDatabase.db');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT PetID FROM Pet WHERE Owner_ID = :user_id LIMIT 1");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $petData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$petData) {
        throw new Exception("No pets found for this user");
    }
    $petID = $petData['PetID'];

    $stmt = $conn->prepare("SELECT DISTINCT Date FROM Pet_Activity WHERE PetID = :petID");
    $stmt->bindParam(':petID', $petID);
    $stmt->execute();
    $dateRows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (empty($dateRows)) {
        throw new Exception("No activity data found for this pet");
    }

    $dateTimes = array_map(function($dateStr) {
        return DateTime::createFromFormat('d-m-Y', $dateStr);
    }, $dateRows);
    
    $dateTimes = array_filter($dateTimes);
    if (empty($dateTimes)) {
        throw new Exception("No valid dates found");
    }

    $maxDate = max($dateTimes);

    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = clone $maxDate;
        $date->modify("-$i days");
        $dates[] = $date->format('d-m-Y');
    }

    $placeholders = implode(',', array_fill(0, count($dates), '?'));

    $sql = $conn->prepare("SELECT Date, SUM(`Breathing Rate (breaths/min)`) / 24 AS Avgbreaths
                         FROM Pet_Activity 
                         WHERE Date IN ($placeholders)
                         AND PetID = ? 
                         GROUP BY Date");

    foreach ($dates as $key => $date) {
        $sql->bindValue($key + 1, $date);
    }
    $sql->bindValue(count($dates) + 1, $petID);
    $sql->execute();

    $Avgbreaths = array_fill(0, count($dates), 0);
    while ($data = $sql->fetch(PDO::FETCH_ASSOC)) {
        $index = array_search($data['Date'], $dates);
        if ($index !== false) {
            $Avgbreaths[$index] = (float)$data['Avgbreaths'];
        }
    }
} catch (PDOException | Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breathing Rate</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    <?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar">
    <div class="nav-links">
        <a href="current_activity.php" class="<?= ($current_page == 'current_activity.php') ? 'active' : '' ?>">Current Activity</a>
        <a href="activity_level.php" class="<?= ($current_page == 'activity_level.php') ? 'active' : '' ?>">Activity Level</a>
        <a href="heart_rate.php" class="<?= ($current_page == 'heart_rate.php') ? 'active' : '' ?>">Heart Rate</a>
        <a href="temperature.php" class="<?= ($current_page == 'temperature.php') ? 'active' : '' ?>">Temperature</a>
        <a href="weight.php" class="<?= ($current_page == 'weight.php') ? 'active' : '' ?>">Weight</a>
        <a href="food&water.php" class="<?= ($current_page == 'food&water.php') ? 'active' : '' ?>">Food and Water Intake</a>
        <a href="calorie_burn.php" class="<?= ($current_page == 'calorie_burn.php') ? 'active' : '' ?>">Calorie Burn</a>
        <a href="breathing_rate.php" class="<?= ($current_page == 'breathing_rate.php') ? 'active' : '' ?>">Breathing Rate</a>
        <a href="behaviour_pattern.php" class="<?= ($current_page == 'behaviour_pattern.php') ? 'active' : '' ?>">Behaviour Pattern</a>
        <a href="barking_frequency.php" class="<?= ($current_page == 'barking_frequency.php') ? 'active' : '' ?>">Barking Frequency</a>
    </div>
</nav>


    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Average Breaths Per Minute</h2>
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>
            <script>
                const ctx = document.getElementById('myChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [{
                            label: 'Average Breaths Per Minute',
                            data: <?php echo json_encode($Avgbreaths); ?>,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(153, 102, 255, 0.8)',
                                'rgba(255, 159, 64, 0.8)',
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(201, 203, 207, 0.8)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(201, 203, 207, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Breaths Per Minute'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            }
                        }
                    }
                });
            </script>
        </main>
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
