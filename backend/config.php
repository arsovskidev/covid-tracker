<?php
define("HOSTNAME", "localhost");
define("DATABASE", "covid-tracker");
define("USERNAME", "arshetamine");
define("PASSWORD", "081200");

date_default_timezone_set('UTC');

// Setting yesterday -2 days (not -1) and ereyesterday -3 days (not -2), because the data is wrong (or there isn't any) for yesterday.
$today = date("Y-m-d");
$yesterday = date('Y-m-d', strtotime($today . ' -2 day'));
$ereyesterday = date('Y-m-d', strtotime($today . ' -3 day'));

$lastWeek = date('Y-m-d', strtotime($today . ' -7 day'));
$lastMonth = date('Y-m-d', strtotime($today . ' -1 month'));
$lastThreeMonths = date('Y-m-d', strtotime($today . ' -3 month'));

try {
    $conn = new PDO("mysql:host=" . HOSTNAME . ";dbname=" . DATABASE, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 500;
    file_put_contents(dirname(__FILE__) . '/logs/' . $today . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
