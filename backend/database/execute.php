<?php
require_once '../config.php';
require_once 'write-countries.php';
require_once 'write-summary.php';
require_once 'write-dayone.php';

ini_set('max_execution_time', 0);

$createCountriesTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`countries`(
    slug VARCHAR( 128 ) PRIMARY KEY,
    country VARCHAR( 256 ) NOT NULL,
    iso2 VARCHAR( 32 ) NOT NULL);";

$createSummaryTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`summary` (
    `id` VARCHAR(128) PRIMARY KEY,
    `country` VARCHAR(256) NOT NULL,
    `country_code` VARCHAR(8) NOT NULL,
    `slug` VARCHAR(128) NOT NULL,
    `total_confirmed` INT(64) NOT NULL,
    `total_deaths` INT(64) NOT NULL,
    `total_recovered` INT(64) NOT NULL,
    `new_confirmed` INT(64) NOT NULL ,
    `new_deaths` INT(64) NOT NULL,
    `new_recovered` INT(64) NOT NULL,
    `date` VARCHAR(64) NOT NULL);";

$createDayOneTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`day_one` (
    `id` VARCHAR(128) PRIMARY KEY,
    `slug` VARCHAR(128) NOT NULL,
    `confirmed` INT(64) NOT NULL,
    `deaths` INT(64) NOT NULL,
    `recovered` INT(64) NOT NULL,
    `active` INT(64) NOT NULL ,
    `date` VARCHAR(64) NOT NULL);";

$conn->exec($createCountriesTable);
$conn->exec($createSummaryTable);
$conn->exec($createDayOneTable);

writeCountries($conn);
writeSummary($conn);
