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

    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Average Heart Rate</h2>
            <div class="graph-video-container">
                <div class="graph-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <?php
            try {
                $conn = new PDO('sqlite:ElancoDatabase.db');
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = $conn->query("SELECT Hour, AVG(\"Heart Rate (bpm)\") AS HeartRate FROM Pet_Activity WHERE Date = '01-01-2021' AND PetID = 'CANINE001' GROUP BY Hour ORDER BY Hour");

                $hour = [];
                $HeartRate = [];
                foreach($sql as $data) {
                    $hour[] = $data['Hour'];
                    $HeartRate[] = round($data['HeartRate'], 1);
                }

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($hour) ?>,
                        datasets: [{
                            label: 'Average Beats Per Minute',
                            data: <?php echo json_encode($HeartRate) ?>,
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
                                title: { display: true, text: 'Hour of the Day' },
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
