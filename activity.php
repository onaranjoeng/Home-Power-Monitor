<?php
// LATEST UPDATE 4/2/24
// Activity.php page
// Database connection
$dbConnection = new PDO('sqlite:power_data.db');

// Fetch the settings
$stmt = $dbConnection->query('SELECT * FROM power_data_settings');
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Function to get daily power consumption for the last 7 days
function getWeeklyPowerData() {
    global $dbConnection;
    $stmt = $dbConnection->query("WITH EnergyData AS (
        SELECT
            datetime,
            (julianday(datetime) - julianday(LAG(datetime) OVER (ORDER BY datetime))) AS seconds_CT1,
            (julianday(datetime) - julianday(LAG(datetime) OVER (ORDER BY datetime))) AS seconds_CT2, 
            CASE 
                WHEN real_power_CT1 < 0 or real_power_CT1 like 'NaN' THEN 0
                ELSE real_power_CT1
            END AS energy_watts_CT1,
            CASE 
                WHEN real_power_CT2 < 0 or real_power_CT2 like 'NaN' THEN 0
                ELSE real_power_CT2
            END AS energy_watts_CT2
        FROM
            power_data
        WHERE 
            energy_watts_CT1 > 0
    )
    SELECT
        CASE strftime('%w', datetime)
            WHEN '0' THEN 'Sun'
            WHEN '1' THEN 'Mon'
            WHEN '2' THEN 'Tues'
            WHEN '3' THEN 'Wed'
            WHEN '4' THEN 'Thurs'
            WHEN '5' THEN 'Fri'
            WHEN '6' THEN 'Sat'
        END || ', ' || strftime('%m-%d', datetime) AS day,
        (SUM(CASE WHEN seconds_CT1 = 0 or seconds_CT1 is null THEN 1 ELSE seconds_CT1 END * energy_watts_CT1)/SUM(CASE WHEN energy_watts_CT1 = 0 THEN 0 ELSE CASE WHEN seconds_CT1 = 0 or seconds_CT1 is null THEN 1 ELSE seconds_CT1 END END)) *24 / 1000 AS total_power,
        (SUM(CASE WHEN seconds_CT2 = 0 or seconds_CT2 is null THEN 1 ELSE seconds_CT2 END * energy_watts_CT2)/SUM(CASE WHEN energy_watts_CT2 = 0 THEN 0 ELSE CASE WHEN seconds_CT2 = 0 or seconds_CT2 is null THEN 1 ELSE seconds_CT2 END END)) *24 / 1000 AS total_power2
    FROM
        EnergyData
    WHERE
        strftime('%W', datetime) >= strftime('%W', 'now') AND
        strftime('%Y', datetime) = strftime('%Y', 'now')
    GROUP BY
        DATE(datetime)
    ORDER BY strftime('%w', datetime) DESC LIMIT 7");

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
    </header>
    <nav>
        <a href="index.php">Live</a>
        <a href="activity.php">Activity</a>
        <a href="settings.php">Settings</a>
    </nav>

    <h2 class="home-name"><?php echo htmlspecialchars($settings['home_name']); ?></h2>

    <footer>
        <img src="footer_logo.png" alt="Footer Image">
        &copy; <?php echo date("Y"); ?> Team 306
    </footer>

    <div class="chart-container">
        <h2><?php echo ($settings['ct1_label']); ?> Daily Consumption</h2>
        <canvas id="dailyChartCT1"></canvas>
        <h2><?php echo ($settings['ct1_label']); ?> Weekly Costs</h2>
        <canvas id="costChartCT1"></canvas>
        <h2><?php echo ($settings['ct2_label']); ?> Daily Consumption</h2>
        <canvas id="dailyChartCT2"></canvas>
        <h2><?php echo ($settings['ct2_label']); ?> Weekly Costs</h2>
        <canvas id="costChartCT2"></canvas>
    </div>

    <script>
        // GENERAL WEEKLY DATA
        var weeklyPowerData = <?php echo json_encode($weeklyPowerData); ?>;
        var labels = weeklyPowerData.map(function(data) { 
            return data.day; });
        // FOR BLUE AKA CT1 
        var powerValuesCT1 = weeklyPowerData.map(function(data) { 
            return (data.total_power).toFixed(4); }); // Convert watts to kWh
        var costValuesCT1 = powerValuesCT1.map(function(power) { 
            return (power * <?php echo $settings['KiloWatt_US']; ?>).toFixed(2); }); // Calculate costs
        var totalCostCT1 = costValuesCT1.reduce(function(acc, curr){
            return acc + parseFloat(curr);}, 0); // Calculate the total cost

        // FOR ORANGE AKA CT2
        var powerValuesCT2 = weeklyPowerData.map(function(data) { 
            return (data.total_power).toFixed(4); }); // Convert watts to kWh
        var costValuesCT2 = powerValuesCT2.map(function(power) { 
            return (power * <?php echo $settings['KiloWatt_US']; ?>).toFixed(2); }); // Calculate costs
        var totalCostCT2 = costValuesCT2.reduce(function(acc, curr){
            return acc + parseFloat(curr);}, 0); // Calculate the total cost

        // Daily Power Consumption Chart CT1
        var dailyChartCT1 = new Chart(document.getElementById('dailyChartCT1'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Power (kWh)',
                    data: powerValuesCT1,
                    backgroundColor: 'rgba(54, 162, 235, .9)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
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
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 15 // Set the font size for the dataset labels
                            }
                        }
                    }
                }
            }
        });

        // Doughnut Chart for Costs CT1
        var costChartCT1 = new Chart(document.getElementById('costChartCT1'), {
            type: 'doughnut',
            data: {
                labels: labels, // Add the total cost label and day labels
                datasets: [{
                    label: 'Costs ($)',
                    data: costValuesCT1, // Add the total cost and day costs
                    backgroundColor: labels.map(() => 'rgba(54, 162, 235, .9)'), // Color for day costs
                    borderColor: labels.map(() => 'rgba(220,220,220)'), // Border color for day costs
                    borderWidth: 2
                }]
            },
            options: {
                layout: {
                    padding: {
                        bottom: 25                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': $';
                                }
                                if (context.parsed !== undefined) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        display: false // Hide the legend since we are displaying the total cost in the center
                    },
                    doughnutlabel: {
                        labels: [{
                            text: 'Total: $' + totalCostCT1.toFixed(2),
                            font: {
                                size: '20'
                            }
                        }]
                    }
                }
            },
            plugins: [{
                beforeDraw: function(chart) {
                    var width = chart.width,
                        height = chart.height,
                        ctx = chart.ctx;
                    ctx.restore();
                    var fontSize = (height / 300).toFixed(2);
                    ctx.font = fontSize + "em sans-serif";
                    ctx.textBaseline = "middle";
                    var totalCostText = "Total Cost:",
                        totalCostTextX = Math.round((width - ctx.measureText(totalCostText).width) / 2),
                        totalCostTextY = height / 2.1 - (fontSize * 10); // Position "Total Cost" above the amount
                    ctx.fillText(totalCostText, totalCostTextX, totalCostTextY);

                    var amountFontSize = (height / 300).toFixed(2); // Adjust font size for the amount
                    ctx.font = amountFontSize + "em sans-serif";
                    var amountText = "$" + totalCostCT1.toFixed(2),
                        amountTextX = Math.round((width - ctx.measureText(amountText).width) / 2),
                        amountTextY = height / 2.05 + (amountFontSize * 8); // Position the amount below "Total Cost"
                    ctx.fillText(amountText, amountTextX, amountTextY);
                    ctx.save();
                }
            }]
        });

         // Daily Power Consumption Chart CT2
         var dailyChartCT2 = new Chart(document.getElementById('dailyChartCT2'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Power (kWh)',
                    data: powerValuesCT2,
                    backgroundColor: 'rgba(255,165,1,1)',
                    borderColor: 'rgba(255,165,1,1)',
                    borderWidth: 2
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
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 15 // Set the font size for the dataset labels
                            }
                        }
                    }
                }
            }
        });

        // Doughnut Chart for Costs CT2
        var costChartCT2 = new Chart(document.getElementById('costChartCT2'), {
            type: 'doughnut',
            data: {
                labels: labels, // Add the total cost label and day labels
                datasets: [{
                    label: 'Costs ($)',
                    data: costValuesCT2, // Add the total cost and day costs
                    backgroundColor: labels.map(() => 'rgba(255,165,1,1)'), // Color for day costs
                    borderColor: labels.map(() => 'rgba(220,220,220)'), // Border color for day costs
                    borderWidth: 2
                }]
            },
            options: {
                layout: {
                    padding: {
                        bottom: 25                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                if (label) {
                                    label += ': $';
                                }
                                if (context.parsed !== undefined) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        display: false // Hide the legend since we are displaying the total cost in the center
                    },
                    doughnutlabel: {
                        labels: [{
                            text: 'Total: $' + totalCostCT2.toFixed(2),
                            font: {
                                size: '20'
                            }
                        }]
                    }
                }
            },
            plugins: [{
                beforeDraw: function(chart) {
                    var width = chart.width,
                        height = chart.height,
                        ctx = chart.ctx;
                    ctx.restore();
                    var fontSize = (height / 300).toFixed(2);
                    ctx.font = fontSize + "em sans-serif";
                    ctx.textBaseline = "middle";
                    var totalCostText = "Total Cost:",
                        totalCostTextX = Math.round((width - ctx.measureText(totalCostText).width) / 2),
                        totalCostTextY = height / 2.1 - (fontSize * 10); // Position "Total Cost" above the amount
                    ctx.fillText(totalCostText, totalCostTextX, totalCostTextY);

                    var amountFontSize = (height / 300).toFixed(2); // Adjust font size for the amount
                    ctx.font = amountFontSize + "em sans-serif";
                    var amountText = "$" + totalCostCT2.toFixed(2),
                        amountTextX = Math.round((width - ctx.measureText(amountText).width) / 2),
                        amountTextY = height / 2.05 + (amountFontSize * 8); // Position the amount below "Total Cost"
                    ctx.fillText(amountText, amountTextX, amountTextY);
                    ctx.save();
                }
            }]
        });

    </script>



</body>
</html>
