<?php
require_once '../config.php';

try {
    if (isset($_GET["list-countries"])) {
        $getAllCountries = $conn->prepare("SELECT * FROM countries");
        $getAllCountries->execute();
        $getAllCountries = $getAllCountries->fetchAll(PDO::FETCH_ASSOC);

        $getAllStatistics = $conn->prepare("
        SELECT `slug`, `country`,
        `confirmed` AS 'total_confirmed',
        `deaths` AS 'total_deaths',
        `recovered` AS 'total_recovered',
        `active` AS 'total_active',
        `date`
        FROM `statistics` WHERE `date` = :date");

        $getAllStatistics->execute(['date' => $yesterday]);
        $getAllStatistics = $getAllStatistics->fetchAll(PDO::FETCH_ASSOC);

        $data['countries'] = $getAllCountries;
        $data['statistics'] = $getAllStatistics;

        echo json_encode($data);
    } else if (isset($_GET["list-statistics"])) {
        $slug = $_GET["list-statistics"];
        $data = [];

        // If the global is needed we dont need slug.
        if ($slug === "global") {
            $getTotalToday = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date");

            $getTotalYesterday = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date");

            $getChartData = $conn->prepare("
            SELECT `date`, SUM(`confirmed`) AS 'confirmed',
            SUM(`deaths`) AS 'deaths',
            SUM(`recovered`) AS 'recovered',
            SUM(`active`) AS 'active'
            FROM `statistics`
            GROUP BY `date`");

            $getTotalToday->execute(['date' => $yesterday]);
            $getTotalYesterday->execute(['date' => $ereyesterday]);

            $getChartData->execute();
        } else {

            $getTotalToday = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND slug = :slug");

            $getTotalYesterday = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND slug = :slug");

            $getChartData = $conn->prepare("
            SELECT `date`, `confirmed`, `deaths`, `recovered`, `active`
            FROM `statistics`
            WHERE slug = :slug");

            $getTotalToday->execute(['date' => $yesterday, 'slug' => $slug]);
            $getTotalYesterday->execute(['date' => $ereyesterday, 'slug' => $slug]);

            $getChartData->execute(['slug' => $slug]);
        }

        // Fetch the prepared queries.
        $getTotalToday = $getTotalToday->fetch(PDO::FETCH_ASSOC);
        $getTotalYesterday = $getTotalYesterday->fetch(PDO::FETCH_ASSOC);
        $getChartData = $getChartData->fetchAll(PDO::FETCH_ASSOC);

        // Check if there is data or not.
        if ($getTotalToday["total_confirmed"] != null) {

            $data["synced"] = $yesterday;
            $data["today"]["total"] = $getTotalToday;
            $data["today"]["new"] = [
                "new_confirmed" =>  $getTotalToday["total_confirmed"] - $getTotalYesterday["total_confirmed"],
                "new_deaths" => $getTotalToday["total_deaths"] - $getTotalYesterday["total_deaths"],
                "new_recovered" => $getTotalToday["total_recovered"] - $getTotalYesterday["total_recovered"],
                "new_active" => $getTotalToday["total_active"] - $getTotalYesterday["total_active"],
            ];
            $data["monthly-chart"] = $getChartData;
            echo json_encode($data);
        } else {
            echo 404;
        }
    }
} catch (Exception $e) {
    echo "\nThere is an error getting response from database. Please check logs!";
    file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
