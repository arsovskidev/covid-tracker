<?php
require_once '../config.php';

try {
    // List all countries and their total statistics.
    if (isset($_GET["list-countries"])) {
        $getAllCountries = $conn->prepare("SELECT DISTINCT slug, country FROM `statistics` ORDER BY `slug` ASC");

        $getAllStatistics = $conn->prepare("
        SELECT @n := @n + 1 AS 'rank',
        `slug`, `country`,
        `confirmed` AS 'total_confirmed',
        `deaths` AS 'total_deaths',
        `recovered` AS 'total_recovered',
        `active` AS 'total_active',
        `date`
        FROM `statistics`, (SELECT @n := 0) m WHERE `date` = :date
        ORDER BY `confirmed` DESC");

        $getAllCountries->execute();
        $getAllStatistics->execute(['date' => $yesterday]);

        $getAllCountries = $getAllCountries->fetchAll(PDO::FETCH_ASSOC);
        $getAllStatistics = $getAllStatistics->fetchAll(PDO::FETCH_ASSOC);

        $data['countries'] = $getAllCountries;
        $data['statistics'] = $getAllStatistics;

        echo json_encode($data);
    }
    // List all statistics.
    else if (isset($_GET["list-statistics"])) {

        $slug = htmlspecialchars($_GET["list-statistics"]);
        $data = [];

        // If the global is needed we don't need slug.
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

            $getTotalLastMonth = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date");

            $getTotalLastThreeMonths = $conn->prepare("
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
            WHERE `date` LIKE '%0'
            GROUP BY `date`
            ORDER BY `date` ASC");

            $getTotalToday->execute(['date' => $yesterday]);
            $getTotalYesterday->execute(['date' => $ereyesterday]);
            $getTotalLastMonth->execute(['date' => $lastMonth]);
            $getTotalLastThreeMonths->execute(['date' => $lastThreeMonths]);

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

            $getTotalLastMonth = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND `slug` = :slug ");

            $getTotalLastThreeMonths = $conn->prepare("
            SELECT SUM(`confirmed`) AS 'total_confirmed',
            SUM(`deaths`) AS 'total_deaths',
            SUM(`recovered`) AS 'total_recovered',
            SUM(`active`) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND `slug` = :slug ");

            $getChartData = $conn->prepare("
            SELECT `date`, `confirmed`, `deaths`, `recovered`, `active`
            FROM `statistics`
            WHERE slug = :slug AND `date` LIKE '%0'
            ORDER BY `date` ASC");

            $getTotalToday->execute(['date' => $yesterday, 'slug' => $slug]);
            $getTotalYesterday->execute(['date' => $ereyesterday, 'slug' => $slug]);
            $getTotalLastMonth->execute(['date' => $lastMonth, 'slug' => $slug]);
            $getTotalLastThreeMonths->execute(['date' => $lastThreeMonths, 'slug' => $slug]);

            $getChartData->execute(['slug' => $slug]);
        }

        // Fetch the prepared queries.
        $getTotalToday = $getTotalToday->fetch(PDO::FETCH_ASSOC);
        $getTotalYesterday = $getTotalYesterday->fetch(PDO::FETCH_ASSOC);
        $getTotalLastMonth = $getTotalLastMonth->fetch(PDO::FETCH_ASSOC);
        $getTotalLastThreeMonths = $getTotalLastThreeMonths->fetch(PDO::FETCH_ASSOC);
        $getChartData = $getChartData->fetchAll(PDO::FETCH_ASSOC);

        // Check if the data is not null.
        if ($getTotalToday["total_confirmed"] != null) {

            $data["synced"] = $yesterday;
            $data["summary"]["total"] = $getTotalToday;
            $data["summary"]["new"]["today"] = [
                "new_confirmed" =>  $getTotalToday["total_confirmed"] - $getTotalYesterday["total_confirmed"],
                "new_deaths" => $getTotalToday["total_deaths"] - $getTotalYesterday["total_deaths"],
                "new_recovered" => $getTotalToday["total_recovered"] - $getTotalYesterday["total_recovered"],
                "new_active" => $getTotalToday["total_active"] - $getTotalYesterday["total_active"],
            ];
            $data["summary"]["new"]["monthly"] = [
                "new_confirmed" =>  $getTotalToday["total_confirmed"] - $getTotalLastMonth["total_confirmed"],
                "new_deaths" => $getTotalToday["total_deaths"] - $getTotalLastMonth["total_deaths"],
                "new_recovered" => $getTotalToday["total_recovered"] - $getTotalLastMonth["total_recovered"],
                "new_active" => $getTotalToday["total_active"] - $getTotalLastMonth["total_active"],
            ];
            $data["summary"]["new"]["three_months"] = [
                "new_confirmed" =>  $getTotalToday["total_confirmed"] - $getTotalLastThreeMonths["total_confirmed"],
                "new_deaths" => $getTotalToday["total_deaths"] - $getTotalLastThreeMonths["total_deaths"],
                "new_recovered" => $getTotalToday["total_recovered"] - $getTotalLastThreeMonths["total_recovered"],
                "new_active" => $getTotalToday["total_active"] - $getTotalLastThreeMonths["total_active"],
            ];
            $data["daily-chart"] = $getChartData;
            echo json_encode($data);
            // echo "<pre>";
            // print_r($data);
        } else {
            echo 400;
        }
    }
} catch (Exception $e) {
    echo "\nThere is an error getting response from database. Please check logs!";
    file_put_contents('../logs/' . $today . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
