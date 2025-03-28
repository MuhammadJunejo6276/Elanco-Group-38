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

    $stmt = $conn->prepare("SELECT Date, SUM(`Weight (kg)`) / 24 AS Weight FROM Pet_Activity WHERE PetID = :petID GROUP BY Date ORDER BY Date DESC LIMIT 7");
    $stmt->bindParam(':petID', $petID);
    $stmt->execute();
    
    $dates = [];
    $weights = [];
    
    while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dates[] = $data['Date'];
        $weights[] = $data['Weight'];
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
    <title>Weight</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>


    <?php include 'header.php'?>


<div class="graphcontainer">
    <main role="main" class="pb-5">
        <h2>Average Weight</h2>
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
