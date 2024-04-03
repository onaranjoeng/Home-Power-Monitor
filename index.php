<?php
// LATEST UPDATE 4/2/24
// CONNECTS DATABASE AND CREATES/STORES DATABASE

// if any errors show  screen
//ini_set('display_errors', 1);
//error_reporting(E_ALL);


//$filename = 'settings.json';
//if (file_exists($filename)) {
//    $settings = json_decode(file_get_contents($filename), true);
//} else {
    // Default settings

    
// Load settings
// Load existing settings or set defaults
$settings = [];
$filename = 'settings.json';
if (file_exists($filename)) {
    $settings = json_decode(file_get_contents($filename), true);
} else {
    $settings = [
        'home_name' => 'My Home',
        'ct1_label' => 'CT1',
        'ct2_label' => 'CT2',
        'charge_per_kilowatt' => 0.1137 // default charge per kilowatt
    ];
}

// Database connection
$dbConnection = new PDO('sqlite:power_data.db');
$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch the settings
$stmt = $dbConnection->query('SELECT * FROM power_data_settings');
$settings = $stmt->fetch(PDO::FETCH_ASSOC);


$action = $_GET['action'] ?? '';

switch ($action) {
    case 'start':
        // Start the daemon
        //exec('nohup sudo python3 /var/www/html/serial_daemon.py > /dev/null 2>&1 &');
        //exec('sudo /bin/python3 /var/www/html/serial_daemon.py 2>&1', $output, $return_var);
        // Start the daemon
        try {
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->exec("UPDATE power_data_settings SET daemon_status = 'running'");
            echo json_encode(['CT1' => '...', 'CT2' => '...']);

        } catch (PDOException $e) {
            echo json_encode(['CT1' => $e->getMessage(), 'CT2' => '...']);

        }

        return;

    case 'stop':
        
        // Stop the daemon
        //exec('sudo pkill -f serial_daemon.py');
        // Stop the daemon
        $dbConnection->exec("UPDATE power_data_settings SET daemon_status = 'stopped'");
        break;

    case 'values':
        // Fetch the latest values from the database
        $stmt = $dbConnection->query('SELECT * FROM power_data ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['CT1' => abs($row['real_power_CT1']), 'CT2' => abs($row['real_power_CT2'])]);
        return; // Stop further execution to prevent HTML output

    case 'initial':
        // Fetch The initial settings values
        $stmt = $dbConnection->query('SELECT * FROM power_data_settings');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $settings = json_encode($row);
        echo json_encode($row);
        //echo json_encode(['home_name' => abs($row['home_name']),'ct1_label' => abs($row['ct1_label']), 'ct2_label' => abs($row['ct2_label']), 'KiloWatt_US' => abs($row['KiloWatt_US']), 'daemon_status' => abs($row['daemon_status'])]);
        return; // Stop further execution to prevent HTML output

}

// HTML and JavaScript code
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Power Monitor</title>

    <link rel="stylesheet" href="css/styles.css">  
    
</head>
<body>
    <header>
        <h1>Home Power Monitor</h1>
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
   
    <script>

        $(document).ready(function() {
            // Chart initialization
            var intervalId = 0;
            var startButton = document.getElementById("startDaemon");
            var stopButton = document.getElementById("stopDaemon");

            var ctx = document.getElementById('powerChart').getContext('2d');

            var powerChart = new Chart(ctx, {
            type: 'line',
            data: {
                            labels: [], // Empty initially
                            datasets: [{
                                label: '<?php echo ($settings['ct1_label']); ?>',
                                data: [],
                                backgroundColor: 'rgba(255, 76, 48)',
                                borderColor: 'rgba(255, 76, 48)',
                                borderWidth: 3,
                                fill: false
                            }, {
                                label: '<?php echo ($settings['ct2_label']); ?>',
                                data: [],
                                backgroundColor: 'rgba(45, 85, 255)',
                                borderColor: 'rgba(45, 85, 255)',
                                borderWidth: 3,
                                fill: false
                            }]
            },
            options: {
                scales: {
                    y: { // Configuration for the y-axis
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Watts' // Label for the y-axis
                        }
                    },
                    x: { // Configuration for the x-axis
                        title: {
                            display: true,
                            text: 'Real Time' // Label for the x-axis
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 15 // Set the font size for the dataset labels
                            }
                        },
                    }
                }
            }
        });

            $('#startDaemon').click(function() {
                $.get('?action=start', function(data) {
                    let values = JSON.parse(data);
                    $('#CT1Value').text(values.CT1);
                    $('#CT2Value').text(values.CT2);
                    startButton.style.display = "none";
                    stopButton.style.display = "block";
                     // Update values every 1 seconds
                     intervalId = setInterval(updateValues, 1000);
                });
            });

            $('#stopDaemon').click(function() {
                $.get('?action=stop', function() {
                    startButton.style.display = "block";
                    stopButton.style.display = "none";
                    clearInterval(intervalId);
                });
            });

            function formatTime(date) {
                var hours = date.getHours();
                var minutes = date.getMinutes().toString().padStart(2, '0');
                var seconds = date.getSeconds().toString().padStart(2, '0');
                var ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // The hour '0' should be '12'
                return hours + ':' + minutes + ':' + seconds + ' ' + ampm;
            }


            function updateValues() {
                $.get('?action=values', function(data) {
                    let values = JSON.parse(data);
                    $('#CT1Value').text(values.CT1);
                    $('#CT2Value').text(values.CT2);
                    updatePowerData(values.CT1,values.CT2);
                });
            }

            function inicializeValues() {
                $.get('?action=initial', function(data) {

                    let valuesSettings = JSON.parse(data);
                   
                    if (valuesSettings.daemon_status === "running") {
                        startButton.style.display = "none";
                        stopButton.style.display = "block";
                    } else {
                        startButton.style.display = "block";
                        stopButton.style.display = "none";
                    }
                    $('#ct1_label').text(valuesSettings.ct1_label);
                    $('#ct2_label').text(valuesSettings.ct2_label);
                    

                    intervalId = setInterval(updateValues, 1000);
                });
            }



            function updatePowerData(ct1Value, ct2Value) {
                // Update chart data
                powerChart.data.datasets[0].data.push(ct1Value);
                powerChart.data.datasets[1].data.push(ct2Value);
                var now = new Date();
                var time = new Date(now.getTime());
                powerChart.data.labels.push(formatTime(time));
                if (powerChart.data.datasets[0].data.length > 30) {
                    powerChart.data.labels.shift();
                    powerChart.data.datasets[0].data.shift();
                    powerChart.data.datasets[1].data.shift();
                }       
                powerChart.update();
            }

            // Initial Values
            inicializeValues();

            // Initial update
            updatePowerData(0,0);
        });


    </script>


    <div id="content">
        <div id="powerData">
            <?php
                $api_url = 'http://localhost:5000/api/power';
                $data = json_decode(file_get_contents($api_url), true);
            ?>
            <p><span id="ct1_label">CT0</span>: <span id="CT1Value">0</span> Watts</p>
            <p><span id="ct2_label">CT1</span>: <span id="CT2Value">0</span> Watts</p>
            <p> </p>
            <div class="button-container">
                <button id="startDaemon">Start Monitor</button>
                <button id="stopDaemon">Stop Monitor</button>
            </div>

        </div>

        <div id="chartContainer">
          
            <canvas id="powerChart"></canvas>
        </div>
    </div>



</body>
</html>
