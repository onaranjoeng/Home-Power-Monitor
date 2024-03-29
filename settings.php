<?php
// settings.php

// if any errors show screen
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
        'charge_per_kilowatt' => 0.07 // default charge per kilowatt
    ];
}


$action = $_GET['action'] ?? '';

// Function to save settings to a file
function saveSettings($settings)
{
    $filename = 'settings.json';
    file_put_contents($filename, json_encode($settings, JSON_PRETTY_PRINT));
}


// Process form submission
if ($action === 'save') {
    $settings['ct1_label'] = $_POST['ct1_label'] ?? 'CT1';
    $settings['ct2_label'] = $_POST['ct2_label'] ?? 'CT2';
    $settings['charge_per_kilowatt'] = $_POST['charge_per_kilowatt'] ?? 0.1;

    saveSettings($settings);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <title><?php echo $settings['home_name']; ?></title>
    <link rel="stylesheet" href="css/styles.css"> 

</head>
<body>
    <header>
        <h1>Settings</h1>
        <p><?php echo $settings['home_name']; ?></p>
    </header>
    <nav>
        <a href="index.php">Live</a>
        <a href="activity.php">Activity</a>
        <a href="settings.php">Settings</a>
    </nav>
    
    <div id="settings">
        <form action="?action=save" method="post">
            <label for="home_name">Change Home Name:</label><br>
            <input type="text" id="home_name" name="home_name" value="<?php echo $settings['home_name']; ?>"><br><br>

            <label for="ct1_label">Rename CT1 Label:</label><br>
            <input type="text" id="ct1_label" name="ct1_label" value="<?php echo $settings['ct1_label']; ?>"><br><br>

            <label for="ct2_label">Rename CT2 Label:</label><br>
            <input type="text" id="ct2_label" name="ct2_label" value="<?php echo $settings['ct2_label']; ?>"><br><br>

            <label for="charge_per_kilowatt">Charge per KiloWatt (US $):</label><br>
            <input type="text" id="charge_per_kilowatt" name="charge_per_kilowatt" value="<?php echo $settings['charge_per_kilowatt']; ?>"><br><br>

            <input type="submit" value="Save Settings">
        </form>
    </div>
    </body>
</html>