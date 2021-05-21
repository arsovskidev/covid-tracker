<?php
require_once '../config.php';

if (isset($_GET["list-countries"])) {
    $getAllCountries = $conn->query("SELECT * FROM countries");
    $countries = $getAllCountries->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($countries);
} else if (isset($_GET["list-statistics"])) {

    $data = [];

    if ($_GET["list-statistics"] == "global") {
        $getTotalToday = $conn->prepare("
            SELECT SUM(confirmed) AS 'total_confirmed',
            SUM(deaths) AS 'total_deaths',
            SUM(recovered) AS 'total_recovered',
            SUM(active) AS 'total_active'
            FROM `statistics` WHERE `date` = :date");

        $getTotalYesterday = $conn->prepare("
            SELECT SUM(confirmed) AS 'total_confirmed',
            SUM(deaths) AS 'total_deaths',
            SUM(recovered) AS 'total_recovered',
            SUM(active) AS 'total_active'
            FROM `statistics` WHERE `date` = :date");

        $getTotalToday->execute(['date' => $yesterday]);
        $getTotalYesterday->execute(['date' => $ereyesterday]);
    } else {

        $getTotalToday = $conn->prepare("
            SELECT SUM(confirmed) AS 'total_confirmed',
            SUM(deaths) AS 'total_deaths',
            SUM(recovered) AS 'total_recovered',
            SUM(active) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND slug = :slug");

        $getTotalYesterday = $conn->prepare("
            SELECT SUM(confirmed) AS 'total_confirmed',
            SUM(deaths) AS 'total_deaths',
            SUM(recovered) AS 'total_recovered',
            SUM(active) AS 'total_active'
            FROM `statistics` WHERE `date` = :date AND slug = :slug");

        $getTotalToday->execute(['date' => $yesterday, 'slug' => $_GET["list-statistics"]]);
        $getTotalYesterday->execute(['date' => $ereyesterday, 'slug' => $_GET["list-statistics"]]);
    }

    $getTotalToday = $getTotalToday->fetch(PDO::FETCH_ASSOC);
    $getTotalYesterday = $getTotalYesterday->fetch(PDO::FETCH_ASSOC);

    $data["today"]["total"] = $getTotalToday;
    $data["today"]["new"] = [
        "new_confirmed" =>  $getTotalToday["total_confirmed"] - $getTotalYesterday["total_confirmed"],
        "new_deaths" => $getTotalToday["total_deaths"] - $getTotalYesterday["total_deaths"],
        "new_recovered" => $getTotalToday["total_recovered"] - $getTotalYesterday["total_recovered"],
        "new_active" => $getTotalToday["total_active"] - $getTotalYesterday["total_active"],
    ];

    $data["date"] = $yesterday;

    if ($data['today']["total"]["total_confirmed"] == null) {
        echo 404;
    } else {
        echo json_encode($data);
    }
}
