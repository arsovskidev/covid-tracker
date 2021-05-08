<?php
require_once '../config.php';
require_once 'writeCountries.php';

$createCountriesTable = "CREATE TABLE IF NOT EXISTS `covid-tracker`.`countries`(
    slug VARCHAR( 128 ) PRIMARY KEY,
    country VARCHAR( 256 ) NOT NULL,
    iso2 VARCHAR( 32 ) NOT NULL);";

$conn->exec($createCountriesTable);

writeCountries($conn);
