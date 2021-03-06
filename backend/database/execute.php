<?php
require_once '../config.php';
require_once 'getCountries.php';
require_once 'writeStatistics.php';

ini_set('max_execution_time', 0);

$createStatisticsTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`statistics` (
    `id` VARCHAR(128) PRIMARY KEY,
    `slug` VARCHAR(128) NOT NULL,
    `country` VARCHAR(256) NOT NULL,
    `confirmed` INT(64) NOT NULL,
    `deaths` INT(64) NOT NULL,
    `recovered` INT(64) NOT NULL,
    `active` INT(64) NOT NULL ,
    `date` VARCHAR(64) NOT NULL);";

$conn->exec($createStatisticsTable);

echo "Current date is " . $today . " UTC.\n";
$allCountries = getCountries($conn);
writeStatistics($conn, $allCountries, "2020-05-01", $today);
