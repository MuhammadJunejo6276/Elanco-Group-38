<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Behaviour Pattern</title>
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
            <h2>Behaviour Pattern Analysis</h2>
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>

            <?php
            try {
                $conn = new PDO('sqlite:ElancoDatabase.db');
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = $conn->query("SELECT 
                SUM(CASE WHEN Behaviour_ID = 1 THEN 1 ELSE 0 END) AS Count1,
                SUM(CASE WHEN Behaviour_ID = 2 THEN 1 ELSE 0 END) AS Count2,
                SUM(CASE WHEN Behaviour_ID = 3 THEN 1 ELSE 0 END) AS Count3,
                SUM(CASE WHEN Behaviour_ID = 4 THEN 1 ELSE 0 END) AS Count4,
                SUM(CASE WHEN Behaviour_ID = 5 THEN 1 ELSE 0 END) AS Count5
                FROM Pet_Activity
                WHERE Date BETWEEN '01-01-2021' AND '07-01-2021'
                AND PetID = 'CANINE001'");

                $behaviourCounts = $sql->fetch(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['Normal', 'Sleeping', 'Eating', 'Walking', 'Playing'], //Probably unhardcode these at some point
                        datasets: [{
                            label: 'Behaviour Pattern',
                            data: [
                                <?php echo $behaviourCounts['Count1'] ?>,
                                <?php echo $behaviourCounts['Count2'] ?>,
                                <?php echo $behaviourCounts['Count3'] ?>,
                                <?php echo $behaviourCounts['Count4'] ?>,
                                <?php echo $behaviourCounts['Count5'] ?>
                            ],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            r: {
                                beginAtZero: true
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
