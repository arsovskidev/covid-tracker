<?php
require_once '../config.php';

try {
    if (isset($_GET["list-countries"])) {
        $getAllCountries = $conn->query("SELECT * FROM countries");
        $countries = $getAllCountries->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($countries);
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

            $getMonthData = $conn->prepare("
            SELECT `date`, SUM(`confirmed`) AS 'confirmed',
            SUM(`deaths`) AS 'deaths',
            SUM(`recovered`) AS 'recovered',
            SUM(`active`) AS 'active'
            FROM `statistics`
            WHERE `date` LIKE :date
            GROUP BY `date`");

            $getTotalToday->execute(['date' => $yesterday]);
            $getTotalYesterday->execute(['date' => $ereyesterday]);

            $getMonthData->execute(['date' => $currentMonth . "%"]);
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

            $getMonthData = $conn->prepare("
            SELECT `date`, `confirmed`, `deaths`, `recovered`, `active`
            FROM `statistics`
            WHERE slug = :slug
            AND date LIKE :date
            ORDER BY `statistics`.`date` ASC");

            $getTotalToday->execute(['date' => $yesterday, 'slug' => $slug]);
            $getTotalYesterday->execute(['date' => $ereyesterday, 'slug' => $slug]);

            $getMonthData->execute(['date' => $currentMonth . "%", 'slug' => $slug]);
        }

        // Fetch the prepared queries.
        $getTotalToday = $getTotalToday->fetch(PDO::FETCH_ASSOC);
        $getTotalYesterday = $getTotalYesterday->fetch(PDO::FETCH_ASSOC);
        $getMonthData = $getMonthData->fetchAll(PDO::FETCH_ASSOC);

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
            $data["monthly-chart"] = $getMonthData;
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
