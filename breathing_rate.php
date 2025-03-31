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

    $dateTimes = array_map(function($dateStr) {
        return DateTime::createFromFormat('d-m-Y', $dateStr);
    }, $dateRows);
    
    $dateTimes = array_filter($dateTimes);
    if (empty($dateTimes)) {
        throw new Exception("No valid dates found");
    }

    $maxDate = max($dateTimes);
    $minDate = min($dateTimes);

    // Handle week selection
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
        // Default to latest week
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = clone $maxDate;
            $date->modify("-$i days");
            $dates[] = $date->format('d-m-Y');
        }
        // Set default week value
        $isoYear = $maxDate->format('o');
        $isoWeek = $maxDate->format('W');
        $selectedWeekValue = sprintf("%s-W%02d", $isoYear, $isoWeek);
    }

    // Calculate week range
    $minWeekValue = '2020-W01';
    $maxWeekValue = (new DateTime())->format('o-\WW');

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
<?php include 'header.php'?>

<div class="graphcontainer">
    <main role="main" class="pb-5">
        <h2>Average Breaths Per Minute</h2>
        
        <!-- Week selection form -->
        <form method="GET" class="week-form">
            <label for="week">Select Week:</label>
            <input type="week" id="week" name="week" 
                   value="<?php echo htmlspecialchars($selectedWeekValue) ?>"
                   min="2020-W01" 
                   max="<?php echo $maxWeekValue ?>">
            <button type="submit">Show</button>
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
                        label: 'Average Breaths Per Minute',
                        data: <?php echo json_encode($Avgbreaths); ?>,
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
                            beginAtZero: true,
                            title: { display: true, text: 'Breaths Per Minute' },
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
<?php include 'footer.php'?>
<!-- Existing JavaScript remains unchanged -->
</body>
</html>