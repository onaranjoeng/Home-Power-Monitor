<?php
// LATEST UPDATE 4/2/24
// settings.php
// if any errors show screen
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load existing settings or set defaults
$settings = [];
$settings = [
   'home_name' => '',
   'ct1_label' => '',
   'ct2_label' => '',
   'KiloWatt_US' => 0 // default charge per kilowatt
];

$dbConnection = new PDO('sqlite:power_data.db');
$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch the current settings
$stmt = $dbConnection->query('SELECT * FROM power_data_settings');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$settings = $row; // Use the fetched settings

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'save':
        $settings['home_name'] = $_POST['home_name'] ?? 'My Home';
        $settings['ct1_label'] = $_POST['ct1_label'] ?? 'CT1';
        $settings['ct2_label'] = $_POST['ct2_label'] ?? 'CT2';
        $settings['KiloWatt_US'] = $_POST['KiloWatt_US'] ?? 0.1;

        // Update query
        $query = "UPDATE power_data_settings SET home_name = :home_name, ct1_label = :ct1_label, ct2_label = :ct2_label, KiloWatt_US = :KiloWatt_US";

        try {
            $stmt = $dbConnection->prepare($query);

            // Bind the parameters
            $stmt->bindParam(':home_name', $settings['home_name']);
            $stmt->bindParam(':ct1_label', $settings['ct1_label']);
            $stmt->bindParam(':ct2_label', $settings['ct2_label']);
            $stmt->bindParam(':KiloWatt_US', $settings['KiloWatt_US']);

            // Execute the query
            $stmt->execute();

            // Redirect back to the main settings page
            header('Location: /settings.php');
            exit;

        } catch (PDOException $e) {
            echo "Update error: " . $e->getMessage();
        }

        return;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Settings</title>
    <link rel="stylesheet" href="css/styles.css"> 

</head>
<body>
    <header>
        <h1>Settings</h1>
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

  

    <div id="settings">
        <form action="?action=save" method="post">
            <label for="home_name">Home Name:</label><br>
            <input type="text" id="home_name" name="home_name" maxlength="20" value="<?php echo $settings['home_name']; ?>"><br><br>

            <label for="ct1_label">CT1 Label:</label><br>
            <input type="text" id="ct1_label" name="ct1_label" maxlength="10" value="<?php echo $settings['ct1_label']; ?>"><br><br>

            <label for="ct2_label">CT2 Label:</label><br>
            <input type="text" id="ct2_label" name="ct2_label" maxlength="10" value="<?php echo $settings['ct2_label']; ?>"><br><br>

            <label for="KiloWatt_US">KiloWatt/Hour(US $):</label><br>
            <input type="text" id="KiloWatt_US" name="KiloWatt_US" value="<?php echo $settings['KiloWatt_US']; ?>"><br><br>

            <input type="submit" value="Save Settings">
        </form>
    </div>

   

    </body>
</html>
