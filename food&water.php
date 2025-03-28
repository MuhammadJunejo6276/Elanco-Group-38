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

    $datePlaceholders = [];
    foreach ($dates as $key => $date) {
        $datePlaceholders[] = ":date$key";
    }
    $placeholders = implode(',', $datePlaceholders);

    $sql = $conn->prepare("SELECT Date, SUM(`Food Intake (calories)`) AS FoodIntake, SUM(`Water Intake (ml)`) AS WaterIntake 
                         FROM Pet_Activity 
                         WHERE Date IN ($placeholders)
                         AND PetID = :petID 
                         GROUP BY Date");

    foreach ($dates as $key => $date) {
        $sql->bindValue(":date$key", $date);
    }
    $sql->bindParam(':petID', $petID);
    $sql->execute();

    $foodIntake = array_fill(0, count($dates), 0);
    $waterIntake = array_fill(0, count($dates), 0);

    while ($data = $sql->fetch(PDO::FETCH_ASSOC)) {
        $index = array_search($data['Date'], $dates);
        if ($index !== false) {
            $foodIntake[$index] = (int)$data['FoodIntake'];
            $waterIntake[$index] = (int)$data['WaterIntake'];
        }
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
    <title>Food & Water Intake</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'header.php'?>


    <div class="graphcontainer">
        <main role="main" class="pb-5">
            <h2>Food and Water Intake Per Day</h2>
            <div class="col-md-12">
                <canvas id="myChart"></canvas>
            </div>

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
<?php include 'footer.php'?>
</body>
</html>
