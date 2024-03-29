<?php

// if any errors show  screen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$dbConnection = new PDO('sqlite:power_data.db');


// Function to get daily power consumption for each day of the week for Sensor 1
function getDailyConsumption1() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%w', datetime) AS weekday, SUM(real_power_CT1) AS total_consumption FROM power_data GROUP BY weekday");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 7, 0); // Initialize array with 0 for each day of the week
    foreach ($result as $row) {
        $data[$row['weekday']] = $row['total_consumption'];
    }
    return $data;
}

// Repeat similar functions for Sensor 2
function getDailyConsumption2() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%w', datetime) AS weekday, SUM(real_power_CT2) AS total_consumption FROM power_data GROUP BY weekday");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 7, 0);
    foreach ($result as $row) {
        $data[$row['weekday']] = (float)$row['total_consumption'];
    }
    return $data;
}

// Function to get weekly power consumption for each week of the month for Sensor 1
function getWeeklyConsumption1() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%W', datetime) AS week, SUM(real_power_CT1) AS total_consumption FROM power_data GROUP BY week");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 4, 0); // Initialize array with 0 for each week of the month
    foreach ($result as $row) {
        $weekIndex = (int)$row['week'] % 4; // Assuming 4 weeks in a month for simplicity
        $data[$weekIndex] = (float)$row['total_consumption'];
    }
    return $data;
}

// Function to get weekly power consumption for each week of the month for Sensor 2
function getWeeklyConsumption2() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%W', datetime) AS week, SUM(real_power_CT2) AS total_consumption FROM power_data GROUP BY week");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 4, 0); // Initialize array with 0 for each week of the month
    foreach ($result as $row) {
        $weekIndex = (int)$row['week'] % 4; // Assuming 4 weeks in a month for simplicity
        $data[$weekIndex] = (float)$row['total_consumption'];
    }
    return $data;
}

// Function to get monthly power consumption for each week of the year for Sensor 1
function getMonthlyConsumption1() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%W', datetime) AS month, SUM(real_power_CT1) AS total_consumption FROM power_data GROUP BY week");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 4, 0); // Initialize array with 0 for each month of the year
    foreach ($result as $row) {
        $weekIndex = (int)$row['month'] % 4; // Assuming 4 weeks in a year for simplicity
        $data[$weekIndex] = (float)$row['total_consumption'];
    }
    return $data;
}

// Function to get monthly power consumption for each week of the year for Sensor 2
function getMonthlyConsumption2() {
    global $dbConnection;
    $stmt = $dbConnection->query("SELECT strftime('%W', datetime) AS month, SUM(real_power_CT2) AS total_consumption FROM power_data GROUP BY week");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = array_fill(0, 4, 0); // Initialize array with 0 for each month of the year
    foreach ($result as $row) {
        $weekIndex = (int)$row['month'] % 4; // Assuming 4 weeks in a month for simplicity
        $data[$weekIndex] = (float)$row['total_consumption'];
    }
    return $data;
}

/*
// Get total daily, weekly, and monthly consumption for the activity page for both sensors
$dailyConsumption1 = getTotalConsumption1('1 day');
$weeklyConsumption1 = getTotalConsumption1('7 days');
$monthlyConsumption1 = getTotalConsumption1('1 month');

$dailyConsumption2 = getTotalConsumption2('1 day');
$weeklyConsumption2 = getTotalConsumption2('7 days');
$monthlyConsumption2 = getTotalConsumption2('1 month');
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity</title>

    <link rel="stylesheet" href="css/styles.css">   <! --  connect to styles file -->
    
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script src="chart-switcher.js"></script> <!-- Include the JavaScript file -->
<body>
    <header>
        <h1>Activity</h1>
    </header>
    
    <nav>
        <a href="index.php">Live</a>
        <a href="activity.php">Activity</a>
        <a href="settings.php">Settings</a>
    </nav>
    
    <div class="chart-container">
    <!-- Weekly Charts -->
    <div class="chart-row">
        <div class="chart-column">
            <h3>Sensor 1 - Total Consumption</h3>
            <canvas id="weeklyChart1"></canvas>
        </div>
        <div class="chart-column">
            <h3>Sensor 2 - Total Consumption</h3>
            <canvas id="weeklyChart2"></canvas>
        </div>
    </div>

    <!-- Monthly Charts -->
    <div class="chart-row">
        <div class="chart-column">
            <canvas id="monthlyChart1"></canvas>
        </div>
        <div class="chart-column">
            <canvas id="monthlyChart2"></canvas>
        </div>
    </div>

    <!-- Yearly Charts -->
    <div class="chart-row">
        <div class="chart-column">
            <canvas id="yearlyChart1"></canvas>
        </div>
        <div class="chart-column">
            <canvas id="yearlyChart2"></canvas>
        </div>
    </div>
</div>


    <div style="text-align: center;">
        <button onclick="showChart('daily')">Daily</button>
        <button onclick="showChart('weekly')">Weekly</button>
        <button onclick="showChart('monthly')">Monthly</button>
    </div>

    <script>
        
        var dailyData1 = <?php echo json_encode(getDailyConsumption1()); ?>;
        var weeklyData1 = <?php echo json_encode(getWeeklyConsumption1()); ?>;
        var monthlyData1 = <?php echo json_encode(getMonthlyConsumption1()); ?>;
        var dailyData2 = <?php echo json_encode(getDailyConsumption2()); ?>;
        var weeklyData2 = <?php echo json_encode(getWeeklyConsumption2()); ?>;
        var monthlyData2 = <?php echo json_encode(getMonthlyConsumption2()); ?>;
            
        // Initialize weekly chart for Sensor 1
        var weeklyChartData1 = {
            labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            datasets: [{
                label: 'Total Consumption',
                data: dailyData1, // Use the PHP data for weekly consumption
                backgroundColor: 'rgba(241, 90, 34)',
                borderColor: 'rgba(241, 90, 34)',
                borderWidth: 1
            }]
        };

        var weeklyCtx1 = document.getElementById('weeklyChart1').getContext('2d');
        var weeklyChart1 = new Chart(weeklyCtx1, {
        type: 'bar',
        data: weeklyChartData1,
        options: chartOptions
    });

         // Initialize weekly chart for Sensor 2
        var weeklyChartData2 = {
            labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            datasets: [{
                label: 'Total Consumption',
                data: dailyData2, // Example data
                backgroundColor: 'rgba(3, 138, 255)',
                borderColor: 'rgba(3, 138, 255)',
                borderWidth: 1
            }]
        };

        var weeklyCtx2 = document.getElementById('weeklyChart2').getContext('2d');
        var weeklyChart2 = new Chart(weeklyCtx2, {
        type: 'bar',
        data: weeklyChartData2,
        options: chartOptions
    });

        // Initialize monthly charts
        var monthlyChartData2 = {
            labels: ['Week 1, Week 2, Week 3, Week 4'],
            datasets: [{
                label: 'Total Consumption',
                data: weeklyData2, // Example data
                backgroundColor: 'rgba(3, 138, 255)',
                borderColor: 'rgba(3, 138, 255)',
                borderWidth: 1
            }]
        };

        var monthlyCtx2 = document.getElementById('weeklyChart2').getContext('2d');
        var monthlyChart2 = new Chart(monthlyCtx2, {
        type: 'bar',
        data: monthlyChartData2,
        options: chartOptions
    });

        var weeklyChartData2 = {
                labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Total Consumption',
                    data: dailyData2, // Example data
                    backgroundColor: 'rgba(3, 138, 255)',
                    borderColor: 'rgba(3, 138, 255)',
                    borderWidth: 1
                }]
            };

            var weeklyCtx2 = document.getElementById('weeklyChart2').getContext('2d');
            var weeklyChart2 = new Chart(weeklyCtx2, {
            type: 'bar',
            data: weeklyChartData2,
            options: chartOptions
        });

        // Similarly, initialize monthly and yearly charts with appropriate data and labels
        // Monthly chart data for Sensor 1 and Sensor 2
        var monthlyChartData1 = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Total Consumption',
                data: [1400, 1500, 1600, 1700], // Example data
                backgroundColor: 'rgba(241, 90, 34)',
                borderColor: 'rgba(241, 90, 34)',
                borderWidth: 1
            }]
        };

        var monthlyChartData2 = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Total Consumption',
                data: [1800, 1900, 2000, 2100], // Example data
                backgroundColor: 'rgba(3, 138, 255)',
                borderColor: 'rgba(3, 138, 255)',
                borderWidth: 1
            }]
        };

        // Yearly chart data for Sensor 1 and Sensor 2
        var yearlyChartData1 = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Total Consumption',
                data: [12000, 13000, 14000, 15000, 16000, 17000, 18000, 19000, 20000, 21000, 22000, 23000], // Example data
                backgroundColor: 'rgba(241, 90, 34)',
                borderColor: 'rgba(241, 90, 34)',
                borderWidth: 1
            }]
        };

        var yearlyChartData2 = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Total Consumption',
                data: [12500, 13500, 14500, 15500, 16500, 17500, 18500, 19500, 20500, 21500, 22500, 23500], // Example data
                backgroundColor: 'rgba(3, 138, 255)',
                borderColor: 'rgba(3, 138, 255)',
                borderWidth: 1
            }]
        };


        // Function to show only one chart at a time
        function showChart(period) {
            var periods = ['daily', 'weekly', 'monthly'];
            periods.forEach(function(p) {
                var display = p === period ? 'block' : 'none';
                document.querySelectorAll('.chart-column canvas').forEach(function(chart) {
                    if (chart.id.includes(p)) {
                        chart.parentNode.style.display = display;
                    }
                });
            });
        }

        // Show daily charts by default
        showChart('daily');
    </script>

</body>
</html>