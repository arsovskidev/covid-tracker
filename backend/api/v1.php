<?php
require_once '../config.php';

try {
    // List all countries and their total statistics.
    if (isset($_GET["list-countries"])) {
        $getAllCountries = $conn->prepare("SELECT DISTINCT slug, country FROM `statistics` ORDER BY `slug` ASC");

        $getAllCountries->execute();
        $getAllCountries = $getAllCountries->fetchAll(PDO::FETCH_ASSOC);

        $data['countries'] = $getAllCountries;

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
            WHERE slug = :slug
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

            // Get every 7th day to lower the rendering time.
            for ($i = 0; $i < count($getChartData); $i += 7) {
                $data["daily-chart"][] = $getChartData[$i];
            }

            echo json_encode($data);
            // echo "<pre>";
            // print_r($data);
        } else {
            echo 400;
        }
    }
    // List all data for the grid.
    else if (isset($_GET["list-grid-data"])) {
        $getStatisticsQuery = "
        SELECT @n := @n + 1 AS 'rank',
        `slug`, `country`,
        `confirmed` AS 'total_confirmed',
        `deaths` AS 'total_deaths',
        `recovered` AS 'total_recovered',
        `active` AS 'total_active',
        `date`
        FROM `statistics`, (SELECT @n := 0) m WHERE `date` = :date
        ORDER BY `confirmed` DESC";


        $getAllStatisticsToday = $conn->prepare($getStatisticsQuery);
        $getAllStatisticsYesterday = $conn->prepare($getStatisticsQuery);
        $getAllStatisticsLastMonth = $conn->prepare($getStatisticsQuery);
        $getAllStatisticsLastThreeMonths = $conn->prepare($getStatisticsQuery);

        $getAllStatisticsToday->execute(['date' => $yesterday]);
        $getAllStatisticsYesterday->execute(['date' => $ereyesterday]);
        $getAllStatisticsLastMonth->execute(['date' => $lastMonth]);
        $getAllStatisticsLastThreeMonths->execute(['date' => $lastThreeMonths]);

        $getAllStatisticsToday = $getAllStatisticsToday->fetchAll(PDO::FETCH_ASSOC);
        $getAllStatisticsYesterday = $getAllStatisticsYesterday->fetchAll(PDO::FETCH_ASSOC);
        $getAllStatisticsLastMonth = $getAllStatisticsLastMonth->fetchAll(PDO::FETCH_ASSOC);
        $getAllStatisticsLastThreeMonths = $getAllStatisticsLastThreeMonths->fetchAll(PDO::FETCH_ASSOC);

        $data['today'] = [];
        $data['monthly'] = [];
        $data['three_months'] = [];

        // Check if the data is not null.
        if ($getAllStatisticsToday[0]["total_confirmed"] != null) {
            // Looping all today statistics from countries, calculating new cases and pushing them in the data array with key statistics.

            foreach ($getAllStatisticsToday as $key => $value) {
                $todayStatistics = [
                    'rank' => $getAllStatisticsToday[$key]["rank"],
                    'slug' => $getAllStatisticsToday[$key]["slug"],
                    'country' => $getAllStatisticsToday[$key]["country"],
                    'total_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"],
                    'total_deaths' => $getAllStatisticsToday[$key]["total_deaths"],
                    'total_recovered' => $getAllStatisticsToday[$key]["total_recovered"],
                    'total_active' => $getAllStatisticsToday[$key]["total_active"],

                    'new_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"] - $getAllStatisticsYesterday[$key]["total_confirmed"],
                    'new_deaths' => $getAllStatisticsToday[$key]["total_deaths"] - $getAllStatisticsYesterday[$key]["total_deaths"],
                    'new_recovered' => $getAllStatisticsToday[$key]["total_recovered"] - $getAllStatisticsYesterday[$key]["total_recovered"],
                    'new_active' => $getAllStatisticsToday[$key]["total_active"] - $getAllStatisticsYesterday[$key]["total_active"],
                    'date' => $getAllStatisticsToday[$key]["date"],
                ];

                $monthlyStatistics = [
                    'rank' => $getAllStatisticsToday[$key]["rank"],
                    'slug' => $getAllStatisticsToday[$key]["slug"],
                    'country' => $getAllStatisticsToday[$key]["country"],
                    'total_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"],
                    'total_deaths' => $getAllStatisticsToday[$key]["total_deaths"],
                    'total_recovered' => $getAllStatisticsToday[$key]["total_recovered"],
                    'total_active' => $getAllStatisticsToday[$key]["total_active"],

                    'new_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"] - $getAllStatisticsLastMonth[$key]["total_confirmed"],
                    'new_deaths' => $getAllStatisticsToday[$key]["total_deaths"] - $getAllStatisticsLastMonth[$key]["total_deaths"],
                    'new_recovered' => $getAllStatisticsToday[$key]["total_recovered"] - $getAllStatisticsLastMonth[$key]["total_recovered"],
                    'new_active' => $getAllStatisticsToday[$key]["total_active"] - $getAllStatisticsLastMonth[$key]["total_active"],
                    'date' => $getAllStatisticsToday[$key]["date"],
                ];

                $lastThreeMonthsStatistics = [
                    'rank' => $getAllStatisticsToday[$key]["rank"],
                    'slug' => $getAllStatisticsToday[$key]["slug"],
                    'country' => $getAllStatisticsToday[$key]["country"],
                    'total_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"],
                    'total_deaths' => $getAllStatisticsToday[$key]["total_deaths"],
                    'total_recovered' => $getAllStatisticsToday[$key]["total_recovered"],
                    'total_active' => $getAllStatisticsToday[$key]["total_active"],

                    'new_confirmed' => $getAllStatisticsToday[$key]["total_confirmed"] - $getAllStatisticsLastThreeMonths[$key]["total_confirmed"],
                    'new_deaths' => $getAllStatisticsToday[$key]["total_deaths"] - $getAllStatisticsLastThreeMonths[$key]["total_deaths"],
                    'new_recovered' => $getAllStatisticsToday[$key]["total_recovered"] - $getAllStatisticsLastThreeMonths[$key]["total_recovered"],
                    'new_active' => $getAllStatisticsToday[$key]["total_active"] - $getAllStatisticsLastThreeMonths[$key]["total_active"],
                    'date' => $getAllStatisticsToday[$key]["date"],
                ];

                array_push($data['today'], $todayStatistics);
                array_push($data['monthly'], $monthlyStatistics);
                array_push($data['three_months'], $lastThreeMonthsStatistics);
            }
            echo json_encode($data);
            // echo "<pre>";
            // print_r($data);
        } else {
            echo 400;
        }
    }
} catch (Exception $e) {
    echo 500;
    file_put_contents('../logs/' . $today . '.log', $e . PHP_EOL, FILE_APPEND);
    die();
}
