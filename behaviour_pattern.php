<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
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

    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = clone $maxDate;
        $date->modify("-$i days");
        $dates[] = $date->format('d-m-Y');
    }

    $sql = $conn->prepare("SELECT 
        SUM(CASE WHEN Behaviour_ID = 1 THEN 1 ELSE 0 END) AS Count1,
        SUM(CASE WHEN Behaviour_ID = 2 THEN 1 ELSE 0 END) AS Count2,
        SUM(CASE WHEN Behaviour_ID = 3 THEN 1 ELSE 0 END) AS Count3,
        SUM(CASE WHEN Behaviour_ID = 4 THEN 1 ELSE 0 END) AS Count4,
        SUM(CASE WHEN Behaviour_ID = 5 THEN 1 ELSE 0 END) AS Count5
        FROM Pet_Activity
        WHERE Date IN (" . implode(',', array_fill(0, count($dates), '?')) . ")
        AND PetID = ?");

    foreach ($dates as $key => $date) {
        $sql->bindValue($key + 1, $date);
    }
    $sql->bindValue(count($dates) + 1, $petID);
    $sql->execute();

    $behaviourCounts = $sql->fetch(PDO::FETCH_ASSOC);

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
    <title>Behaviour Pattern</title>
    <link rel="stylesheet" href="style.css">
    <style>
        #myChart {
            max-width: 75vw;
            max-height: 75vh;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'header.php'; ?>


    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Behaviour Pattern Analysis</h2>
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');

                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['Normal', 'Sleeping', 'Eating', 'Walking', 'Playing'],
                        datasets: [{
                            label: 'Behaviour Pattern',
                            data: [
                                <?php echo $behaviourCounts['Count1'] ?? 0; ?>,
                                <?php echo $behaviourCounts['Count2'] ?? 0; ?>,
                                <?php echo $behaviourCounts['Count3'] ?? 0; ?>,
                                <?php echo $behaviourCounts['Count4'] ?? 0; ?>,
                                <?php echo $behaviourCounts['Count5'] ?? 0; ?>
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
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            r: {
                                beginAtZero: true,
                                grid: { color: '#e0f0ff' },
                                angleLines: { color: '#e0f0ff' },
                                pointLabels: { color: '#333' }
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
    
<?php include 'footer.php'; ?>

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

