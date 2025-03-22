<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food & Water</title>
    <link rel="stylesheet" href="style.css">
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
            <h2>Food and Water Intake Per Day</h2>
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>

            <?php
            try {
                $conn = new PDO('sqlite:ElancoDatabase.db');
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Fetch data for 7 days
                $sql = $conn->query("SELECT 
                Date,
                SUM(\"Food Intake (calories)\") AS FoodIntake,
                SUM(\"Water Intake (ml)\") AS WaterIntake
                FROM Pet_Activity
                WHERE Date IN ('01-01-2021', '02-01-2021', '03-01-2021', '04-01-2021', '05-01-2021', '06-01-2021', '07-01-2021')
                AND PetID = 'CANINE001'
                GROUP BY Date");

                $dates = [];
                $foodIntake = [];
                $waterIntake = [];
                foreach($sql as $data)
                {           
                    $dates[] = $data['Date'];
                    $foodIntake[] = $data['FoodIntake'];
                    $waterIntake[] = $data['WaterIntake'];
                }

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode($dates) ?> ,
                        datasets: [{
                            label: 'Food Intake (calories)',
                            data: <?php echo json_encode($foodIntake) ?> ,
                            backgroundColor: 'rgba(255, 0, 0, 0.8)',
                            borderColor: 'rgb(255, 0, 0)',
                            borderWidth: 1
                        },
                        {
                            label: 'Water Intake (ml)',
                            data: <?php echo json_encode($waterIntake) ?> ,
                            backgroundColor: 'rgba(0, 153, 255, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Intake'
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
