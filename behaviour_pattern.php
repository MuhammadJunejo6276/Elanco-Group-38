<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Behaviour Pattern</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #myChart {
            max-width: 75vw;
            max-height: 75vh;
        }
    </style>
</head>
<body>
<?php include 'header.php'?>


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

                // Fetch data for the week
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
                        labels: ['Normal', 'Sleeping', 'Eating', 'Walking', 'Playing'],
                        datasets: [{
                            label: 'Behaviour Pattern',
                            data: [
                                <?php echo $behaviourCounts['Count1'] ?>,
                                <?php echo $behaviourCounts['Count2'] ?>,
                                <?php echo $behaviourCounts['Count3'] ?>,
                                <?php echo $behaviourCounts['Count4'] ?>,
                                <?php echo $behaviourCounts['Count5'] ?>
                            ],
                            backgroundColor: 'rgba(255,99,132, 0.3)',
                            borderColor: '#ff6384',
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#ff6384',
                            pointRadius: 5,
                            pointHoverRadius: 7,
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
    
<?php include 'footer.php'?>

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

