<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Behaviour Pattern</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://www.gstatic.com/charts/loader.js"></script>
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
                $total = array_sum($behaviourCounts);
                $percentages = array_map(fn($count) => round(($count / $total) * 100, 1), $behaviourCounts);

            } catch (PDOException $e) {
                echo "Connection failed: " . $e->getMessage();
            }
            ?>

            <div class="gauge-video-container" style="display:flex; justify-content:space-around;">
                <div class="gauge-video">
                    <div id="gaugeNormal"></div>
                    <video autoplay loop muted src="ElancoPics/normal.mp4" width="200"></video>
                </div>
                <div class="gauge-video">
                    <div id="gaugeSleeping"></div>
                    <video autoplay loop muted src="ElancoPics/sleeping.mp4" width="200"></video>
                </div>
                <div class="gauge-video">
                    <div id="gaugeEating"></div>
                    <video autoplay loop muted src="ElancoPics/eating.mp4" width="200"></video>
                </div>
                <div class="gauge-video">
                    <div id="gaugeWalking"></div>
                    <video autoplay loop muted src="ElancoPics/walking.mp4" width="200"></video>
                </div>
                <div class="gauge-video">
                    <div id="gaugePlaying"></div>
                    <video autoplay loop muted src="ElancoPics/playing.mp4" width="200"></video>
                </div>
            </div>

            <script>
                google.charts.load('current', {'packages':['gauge']});
                google.charts.setOnLoadCallback(drawGauges);

                function drawGauges() {
                    const labels = ['Normal', 'Sleeping', 'Eating', 'Walking', 'Playing'];
                    const percentages = <?= json_encode(array_values($percentages)) ?>;

                    labels.forEach((label, index) => {
                        const data = google.visualization.arrayToDataTable([
                            ['Label', 'Value'], [label, percentages[index]]
                        ]);
                        const chart = new google.visualization.Gauge(document.getElementById(`gauge${label}`));
                        chart.draw(data, {width: 200, height: 200, minorTicks: 5});
                    });
                }
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
            const dropdown = document.getElementById("profileDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        document.addEventListener("click", event => {
            if (!document.querySelector(".profile-container").contains(event.target)) {
                document.getElementById("profileDropdown").style.display = "none";
            }
        });
    </script>
</body>
</html>
