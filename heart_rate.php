<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $conn = new PDO('sqlite:ElancoDatabase.db');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch the PetID for the logged-in user
    $stmt = $conn->prepare("SELECT PetID FROM Pet WHERE Owner_ID = :user_id LIMIT 1");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $petData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$petData) {
        throw new Exception("No pets found for this user");
    }
    $petID = $petData['PetID'];

    // Fetch the distinct dates of activity for the pet
    $stmt = $conn->prepare("SELECT DISTINCT Date FROM Pet_Activity WHERE PetID = :petID");
    $stmt->bindParam(':petID', $petID);
    $stmt->execute();
    $dateRows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    if (empty($dateRows)) {
        throw new Exception("No activity data found for this pet");
    }

    $dateTimes = [];
    foreach ($dateRows as $dateStr) {
        $date = DateTime::createFromFormat('d-m-Y', $dateStr);
        if ($date) {
            $dateTimes[] = $date;
        }
    }

    if (empty($dateTimes)) {
        throw new Exception("No valid dates found");
    }

    $maxDate = max($dateTimes);

    // Get the past 7 dates
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = clone $maxDate;
        $date->modify("-$i days");
        $dates[] = $date->format('d-m-Y');
    }

    $datePlaceholders = [];
    foreach ($dates as $key => $date) {
        $datePlaceholders[] = ":date$key";
    }
    $placeholders = implode(',', $datePlaceholders);

    // Query the heart rate data
    $sql = $conn->prepare("SELECT Date, AVG(`Heart Rate (bpm)`) AS HeartRate
                           FROM Pet_Activity 
                           WHERE Date IN ($placeholders)
                           AND PetID = :petID 
                           GROUP BY Date");

    foreach ($dates as $key => $date) {
        $sql->bindValue(":date$key", $date);
    }
    $sql->bindParam(':petID', $petID);
    $sql->execute();

    $heartRates = array_fill(0, count($dates), 0);

    while ($data = $sql->fetch(PDO::FETCH_ASSOC)) {
        $index = array_search($data['Date'], $dates);
        if ($index !== false) {
            $heartRates[$index] = round($data['HeartRate'], 1);
        }
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Heart Rate</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .graph-video-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }
        .graph-container {
            flex: 2;
            height: 600px;
            padding: 10px;
            background-color: #fff5f8;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .video-container {
            flex: 1;
            height: 600px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Arial', sans-serif;
            color: #333;
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
            <h2>Average Heart Rate</h2>
            <div class="graph-video-container">
                <div class="graph-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($dates) ?>,
                        datasets: [{
                            label: 'Average Beats Per Minute',
                            data: <?php echo json_encode($heartRates) ?>,
                            borderColor: '#ff6384',
                            backgroundColor: 'rgba(255,99,132,0.2)',
                            tension: 0.4,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#ff6384',
                            pointRadius: 5,
                            pointHoverRadius: 7,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: { display: true, text: 'Date' },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: false,
                                title: { display: true, text: 'Heart Rate (bpm)' },
                                grid: { color: '#f0f0f0' }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: { enabled: true }
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
