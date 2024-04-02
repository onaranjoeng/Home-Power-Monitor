<?php
// Activity.php page
// Database connection
$dbConnection = new PDO('sqlite:power_data.db');

// Function to get daily power consumption for the last 7 days
function getWeeklyPowerData() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%Y-%m-%d', datetime) AS day, SUM(real_power_CT1) + SUM(real_power_CT2) AS total_power FROM power_data GROUP BY day ORDER BY day DESC LIMIT 7");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return array_reverse($result); // Reverse the array to start from the oldest day
}

$weeklyPowerData = getWeeklyPowerData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Activity</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/styles.css"> 
</head>
<body>
<header>
        <h1>Activity</h1>
        <p><?php echo $settings['home_name']; ?></p>
    </header>
    <nav>
        <a href="index.php">Live</a>
        <a href="activity.php">Activity</a>
        <a href="settings.php">Settings</a>
    </nav>

    <footer>
        <img src="footer_logo.png" alt="Footer Image">
        &copy; <?php echo date("Y"); ?> Team 306
    </footer>

    <div class="chart-container">
        <h2>Weekly Power Consumption</h2>
        <canvas id="weeklyChart"></canvas>
        <h2>Weekly Costs</h2>
        <canvas id="costChart"></canvas>
    </div>

    <script>
        var weeklyPowerData = <?php echo json_encode($weeklyPowerData); ?>;
        var labels = weeklyPowerData.map(function(data) { return data.day; });
        var powerValues = weeklyPowerData.map(function(data) { return data.total_power; });
        var costValues = powerValues.map(function(power) { return (power * 0.07).toFixed(2); }); // Calculate costs

        // Weekly Power Consumption Chart
        var weeklyChart = new Chart(document.getElementById('weeklyChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Power (kWh)',
                    data: powerValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'kWh'
                        }
                    }
                }
            }
        });

        // Weekly Costs Chart
        var costChart = new Chart(document.getElementById('costChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Costs ($)',
                    data: costValues,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(201, 203, 207, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>



</body>
</html>
