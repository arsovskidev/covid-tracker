<?php
require_once '../config.php';
require_once 'write-countries.php';
require_once 'write-statistics.php';

ini_set('max_execution_time', 0);

$createCountriesTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`countries`(
    slug VARCHAR(128) PRIMARY KEY,
    country VARCHAR( 256) NOT NULL,
    code VARCHAR(16) NOT NULL);";

$createStatisticsTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`statistics` (
    `id` VARCHAR(128) PRIMARY KEY,
    `slug` VARCHAR(128) NOT NULL,
    `country` VARCHAR(256) NOT NULL,
    `confirmed` INT(64) NOT NULL,
    `deaths` INT(64) NOT NULL,
    `recovered` INT(64) NOT NULL,
    `active` INT(64) NOT NULL ,
    `date` VARCHAR(64) NOT NULL);";

$conn->exec($createCountriesTable);
$conn->exec($createStatisticsTable);

echo "Current date is " . $today . ". \n";
writeCountries($conn, $today);
