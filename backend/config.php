<?php
define("HOSTNAME", "localhost");
define("DATABASE", "covid-tracker");
define("USERNAME", "arshetamine");
define("PASSWORD", "081200");

date_default_timezone_set('UTC');

$today = date("Y-m-d");
$yesterday = date('Y-m-d', strtotime($today . ' -1 day'));
$ereyesterday = date('Y-m-d', strtotime($today . ' -2 day'));

$lastWeek = date('Y-m-d', strtotime($today . ' -7 day'));
$lastMonth = date('Y-m-d', strtotime($today . ' -1 month'));
$lastThreeMonths = date('Y-m-d', strtotime($today . ' -3 month'));

try {
    $conn = new PDO("mysql:host=" . HOSTNAME . ";dbname=" . DATABASE, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "There is an error in database connection. Please check logs!";
    file_put_contents(dirname(__FILE__) . '/logs/' . $today . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
