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
    $petID = trim($petData['PetID']);

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

    include 'day_selection.php';

    $selectedDay = $_GET['day'] ?? null;
    $daySelection = getSelectedDay($maxDate, $minDate, $selectedDay);

    $date = $daySelection['date']; 
    $selectedDayValue = $daySelection['selectedDayValue']; 

    $sql = $conn->prepare("SELECT Hour, [Temperature (C)] as Temp
                          FROM Pet_Activity 
                          WHERE Date = :selectedDate
                          AND PetID = :petID COLLATE NOCASE
                          ORDER BY Hour");

    $sql->bindParam(':selectedDate', $date);
    $sql->bindParam(':petID', $petID);
    $sql->execute();

    $tempData = array_fill(0, 24, 0);
    while ($data = $sql->fetch(PDO::FETCH_ASSOC)) {
        $hour = (int)$data['Hour'];
        $tempData[$hour] = (float)$data['Temp'];
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
    <title>Temperature</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'header.php' ?>

    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Hourly Temperature </h2>

            <form method="GET" style="margin-bottom: 20px;">
                <label for="day">Select Day:</label>
                <input type="date" id="day" name="day" 
                       value="<?php echo htmlspecialchars(DateTime::createFromFormat('d-m-Y', $selectedDayValue)->format('Y-m-d')); ?>" 
                       min="<?php echo $minDate->format('Y-m-d'); ?>" 
                       max="<?php echo $maxDate->format('Y-m-d'); ?>">
                <button type="submit">Update</button>
            </form>

            <div class="graph-video-container">
                <div class="graph-container">
                    <canvas id="myChart"></canvas>
                </div>
            </div>

            <script>
                const ctx = document.getElementById('myChart').getContext('2d');
                const temperatures = <?php echo json_encode(array_values($tempData)); ?>;

                const minTemp = Math.min(...temperatures);
                const maxTemp = Math.max(...temperatures);

                const gradient = ctx.createLinearGradient(0, 0, 0, 500);
                gradient.addColorStop(0, 'red');
                gradient.addColorStop(1, 'blue');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: temperatures,
                            borderColor: gradient,
                            backgroundColor: 'rgba(0,0,0,0)',
                            tension: 0.4,
                            pointBackgroundColor: temperatures.map(temp => {
                                return temp > 39.4 ? 'yellow' : 'rgba(0, 0, 0, 0)'; 
                            }),
                            pointBorderColor: '#000000',
                            pointRadius: 5,
                            pointHoverRadius: 7, 
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            x: {
                                title: { display: true, text: 'Hour' },
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: false,
                                title: { display: true, text: 'Temperature (°C)' },
                                grid: { color: '#f0f0f0' }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const temp = context.raw;
                                        const warning = temp > 39.4 ? ' Warning: Your dog may have a fever' : '';
                                        return `Temperature: ${temp}°C\n${warning}`;
                                    }
                                }
                            }
                        }
                    }
                });
            </script>
        </main>
    </div>

    <?php include 'footer.php' ?>
</body>
</html>
