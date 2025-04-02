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

    include 'week_selection.php';

    $selectedWeek = $_GET['week'] ?? null;

    $weekSelection = getSelectedWeekDates($maxDate, $minDate, $selectedWeek);

    $dates = $weekSelection['dates'];
    $selectedWeekValue = $weekSelection['selectedWeekValue'];

    $minWeekValue = $minDate->format('o-\WW');
    $maxWeekValue = $maxDate->format('o-\WW');
    
    $datePlaceholders = [];
    foreach ($dates as $key => $date) {
        $datePlaceholders[] = ":date$key";
    }
    $placeholders = implode(',', $datePlaceholders);

    $stmt = $conn->prepare("SELECT Date, SUM(`Weight (kg)`) / 24 AS Weight 
                          FROM Pet_Activity 
                          WHERE Date IN ($placeholders)
                          AND PetID = :petID 
                          GROUP BY Date");

    foreach ($dates as $key => $date) {
        $stmt->bindValue(":date$key", $date);
    }
    $stmt->bindParam(':petID', $petID);
    $stmt->execute();

    $weights = array_fill(0, count($dates), 0);

    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $index = array_search($data['Date'], $dates);
        if ($index !== false) {
            $weights[$index] = (float)$data['Weight'];
        }
    }

    $minWeekValue = '2020-W01';
    $maxWeekValue = '2023-W52';

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
    <title>Weight</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'header.php' ?>

    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Average Weight</h2>

            <form method="GET" class="week-form">
                <label for="week">Select Week:</label>
                <input type="week" id="week" name="week" 
                       value="<?php echo htmlspecialchars($selectedWeekValue) ?>"
                       min="2020-W01" 
                       max="<?php echo $maxWeekValue ?>">
                <button type="submit">Update</button>
            </form>

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
                        labels: <?php echo json_encode($dates); ?>,
                        datasets: [{
                            label: 'Average Weight',
                            data: <?php echo json_encode($weights); ?>,
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
                        maintainAspectRatio: true,
                        scales: {
                            x: {
                                title: { display: true, text: 'Date' },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: false,
                                title: { display: true, text: 'Weight (kg)' },
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

    <?php include 'footer.php' ?>
</body>
</html>
