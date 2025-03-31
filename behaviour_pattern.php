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
    $minDate = min($dateTimes);

    if (isset($_GET['week']) && !empty($_GET['week'])) {
        try {
            $selectedWeek = $_GET['week'];
            $weekStart = new DateTime($selectedWeek);
            $weekStart->modify('monday this week');
            
            $dates = [];
            for ($i = 0; $i < 7; $i++) {
                $currentDate = clone $weekStart;
                $currentDate->modify("+$i days");
                $dates[] = $currentDate->format('d-m-Y');
            }
            $selectedWeekValue = $selectedWeek;
        } catch (Exception $e) {
            die("Invalid week selected");
        }
    } else {
        $weekStart = clone $maxDate;
        $weekStart->modify('monday this week');
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = clone $weekStart;
            $currentDate->modify("+$i days");
            $dates[] = $currentDate->format('d-m-Y');
        }
        $selectedWeekValue = $maxDate->format('o-\WW');
    }

    $minWeekValue = $minDate->format('o-\WW');
    $maxWeekValue = $maxDate->format('o-\WW');

    $stmt = $conn->prepare("SELECT Behaviour_ID, Behaviour_Desc FROM Behaviour ORDER BY Behaviour_ID");
    $stmt->execute();
    $behaviours = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $labels = array_column($behaviours, 'Behaviour_Desc');

    $datePlaceholders = [];
    foreach ($dates as $key => $date) {
        $datePlaceholders[] = ":date$key";
    }
    $placeholders = implode(',', $datePlaceholders);

    $selectClauses = array_map(function($behaviour) {
        $id = $behaviour['Behaviour_ID'];
        return "SUM(CASE WHEN Behaviour_ID = $id THEN 1 ELSE 0 END) AS Count$id";
    }, $behaviours);
    $selectSql = implode(', ', $selectClauses);

    $sql = $conn->prepare("SELECT $selectSql 
                          FROM Pet_Activity 
                          WHERE Date IN ($placeholders)
                          AND PetID = :petID");

    foreach ($dates as $key => $date) {
        $sql->bindValue(":date$key", $date);
    }
    $sql->bindParam(':petID', $petID);
    $sql->execute();

    $behaviourCounts = $sql->fetch(PDO::FETCH_ASSOC);

    $counts = [];
    foreach ($behaviours as $behaviour) {
        $id = $behaviour['Behaviour_ID'];
        $counts[] = $behaviourCounts["Count$id"] ?? 0;
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
            <form method="GET" class="week-selector">
                <label for="week">Select Week:</label>
                <input type="week" id="week" name="week" 
                       value="<?php echo htmlspecialchars($selectedWeekValue) ?>"
                       min="<?php echo $minWeekValue ?>" 
                       max="<?php echo $maxWeekValue ?>">
                <button type="submit">Update</button>
            </form>
            
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: <?php echo json_encode($labels) ?>,
                        datasets: [{
                            label: 'Behaviour Frequency',
                            data: <?php echo json_encode($counts) ?>,
                            backgroundColor: 'rgba(255,99,132,0.3)',
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