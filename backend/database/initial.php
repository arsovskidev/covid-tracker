<?php
require_once '../config.php';
require_once 'writeCountries.php';
require_once 'writeAllTime.php';

$createCountriesTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`countries`(
    slug VARCHAR( 128 ) PRIMARY KEY,
    country VARCHAR( 256 ) NOT NULL,
    iso2 VARCHAR( 32 ) NOT NULL);";

$createAllTimeTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`all_time`(
    slug VARCHAR( 128 ) PRIMARY KEY,
    total_confirmed INT( 128 ) NOT NULL,
    total_deaths INT( 128 ) NOT NULL,
    total_recovered INT( 128 ) NOT NULL);";

$conn->exec($createCountriesTable);
$conn->exec($createAllTimeTable);

writeCountries($conn);
writeAllTimeStats($conn);
