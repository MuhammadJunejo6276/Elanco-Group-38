<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Level (Steps)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px #ccc;
        }
        .header {
            background: #2d5ea6;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .content {
            display: flex;
            justify-content: space-between;
            padding: 20px;
        }
        .chart-container {
            width: 70%;
        }
        .filter-container {
            width: 25%;
            background: #e3e3e3;
            padding: 15px;
            border-radius: 5px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        .btn {
            background: #2d5ea6;
            color: white;
            padding: 10px;
            text-align: center;
            display: block;
            border: none;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        Activity Level (Steps)
    </div>

    <div class="content">
        <div class="chart-container">
            <canvas id="stepsChart"></canvas>
        </div>

        <div class="filter-container">
            <h3>Filter Data</h3>
            <label>From:</label>
            <input type="date" id="fromDate">
            <label>To:</label>
            <input type="date" id="toDate">
            <button class="btn">Apply Filter</button>
        </div>
    </div>

    <button class="btn" onclick="goBack()">Back</button>
</div>

<script>
    // Sample data for steps
    var days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    var steps = [90, 20, 80, 40, 10, 120, 50]; // Example data

    // Chart.js configuration
    var ctx = document.getElementById('stepsChart').getContext('2d');
    var stepsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: days,
            datasets: [{
                label: 'Steps',
                data: steps,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Back button function
    function goBack() {
        window.history.back();
    }
</script>

</body>
</html>
