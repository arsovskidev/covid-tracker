<?php
define("HOSTNAME", "localhost");
define("DATABASE", "covid-tracker");
define("USERNAME", "arshetamine");
define("PASSWORD", "081200");

try {
    $conn = new PDO("mysql:host=" . HOSTNAME . ";dbname=" . DATABASE, USERNAME, PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "There is an error in database connection. Please check logs!";
    file_put_contents(dirname(__FILE__) . '/logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
