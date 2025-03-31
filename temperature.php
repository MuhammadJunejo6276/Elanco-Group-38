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
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = clone $maxDate;
            $date->modify("-$i days");
            $dates[] = $date->format('d-m-Y');
        }
        $isoYear = $maxDate->format('o');
        $isoWeek = $maxDate->format('W');
        $selectedWeekValue = sprintf("%s-W%02d", $isoYear, $isoWeek);
    }

    $minWeekValue = '2020-W01';
    $maxWeekValue = (new DateTime())->format('o-\WW');

    $datePlaceholders = [];
    foreach ($dates as $key => $date) {
        $datePlaceholders[] = ":date$key";
    }
    $placeholders = implode(',', $datePlaceholders);

    $sql = $conn->prepare("SELECT Date, 
                          MIN(`Temperature (C)`) as MinTemp,
                          MAX(`Temperature (C)`) as MaxTemp,
                          AVG(`Temperature (C)`) as AvgTemp 
                          FROM Pet_Activity 
                          WHERE Date IN ($placeholders)
                          AND PetID = :petID 
                          GROUP BY Date");

    foreach ($dates as $key => $date) {
        $sql->bindValue(":date$key", $date);
    }
    $sql->bindParam(':petID', $petID);
    $sql->execute();

    $tempData = array_fill(0, count($dates), ['Min' => 0, 'Max' => 0, 'Avg' => 0]);
    $averages = [];
    
    while ($data = $sql->fetch(PDO::FETCH_ASSOC)) {
        $index = array_search($data['Date'], $dates);
        if ($index !== false) {
            $tempData[$index] = [
                'Min' => (float)$data['MinTemp'],
                'Max' => (float)$data['MaxTemp'],
                'Avg' => (float)$data['AvgTemp']
            ];
            $averages[] = (float)$data['AvgTemp'];
        }
    }

    $minAvg = min($averages);
    $maxAvg = max($averages);
    $range = $maxAvg - $minAvg ?: 1;

    $backgroundColors = [];
    foreach ($averages as $avg) {
        $ratio = ($avg - $minAvg) / $range;
        $red = (int)(30 + (225 * $ratio));
        $green = (int)(144 - (44 * $ratio));
        $blue = (int)(255 - (255 * $ratio));
        $backgroundColors[] = "rgba($red, $green, $blue, 0.7)";
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
    <title>Temperature Range</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'header.php'?>

<div class="graphcontainer">
    <main role="main" class="pb-5">
        <h2>Daily Temperature Range</h2>

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
                <canvas id="tempChart"></canvas>
            </div>
        </div>

        <script>
            const ctx = document.getElementById('tempChart').getContext('2d');
            const averages = <?php echo json_encode($averages); ?>;
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dates); ?>,
                    datasets: [{
                        label: 'Temperature Range (°C)',
                        data: <?php echo json_encode(array_map(fn($t) => [$t['Min'], $t['Max']], $tempData)); ?>,
                        backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                        borderColor: '#333',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            title: { 
                                display: true, 
                                text: 'Temperature (°C)'
                            },
                            grid: { color: '#f0f0f0' }
                        },
                        y: {
                            title: { 
                                display: true, 
                                text: 'Date'
                            },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (context) => context[0].label,
                                label: (context) => {
                                    const [min, max] = context.raw;
                                    const avg = averages[context.dataIndex].toFixed(1);
                                    return [
                                        `Minimum: ${min}°C`,
                                        `Maximum: ${max}°C`,
                                        `Average: ${avg}°C`
                                    ];
                                }
                            }
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
        const dropdown = document.getElementById("profileDropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }
    
    document.addEventListener("click", function(event) {
        const profileContainer = document.querySelector(".profile-container");
        const dropdown = document.getElementById("profileDropdown");
        if (!profileContainer.contains(event.target)) {
            dropdown.style.display = "none";
        }
    });
</script>
</body>
</html>