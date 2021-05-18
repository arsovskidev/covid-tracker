<?php

function writeSummary($conn)
{
    // Get the summary for today from the API.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/summary');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $summary = curl_exec($cURLConnection);
    curl_close($cURLConnection);

    // Json decode them.
    $summary = json_decode($summary, true);

    $conn->beginTransaction();

    try {
        // Create pdo insert query. 
        $sql = "INSERT INTO 
     summary (id, country, country_code,
     slug, total_confirmed, total_deaths,
     total_recovered, new_confirmed, new_deaths,
     new_recovered, date) 
     
     VALUES (:id, :country, :country_code, :slug,
     :total_confirmed, :total_deaths, :total_recovered,
     :new_confirmed, :new_deaths, :new_recovered, :date) 
     
     ON DUPLICATE KEY UPDATE country=:country, country_code=:country_code, slug=:slug,
     total_confirmed=:total_confirmed, total_deaths=:total_deaths, total_recovered=:total_recovered,
     new_confirmed=:new_confirmed, new_deaths=:new_deaths, new_recovered=:new_recovered, date=:date";

        $stmt = $conn->prepare($sql);

        echo "<br>Writing summary for each country in database...";

        // Write the global summary for today.
        $global = $summary["Global"];
        $globalDateAndTime = explode("T", $global["Date"]);
        $globalDate = $globalDateAndTime[0];

        $values = [
            'id' => "global",
            'country' => "",
            'country_code' => "",
            'slug' => "",
            'total_confirmed' => $global["TotalConfirmed"],
            'total_deaths' => $global["TotalDeaths"],
            'total_recovered' => $global["TotalRecovered"],
            'new_confirmed' => $global["NewConfirmed"],
            'new_deaths' => $global["NewDeaths"],
            'new_recovered' => $global["NewRecovered"],
            'date' => $globalDate,
        ];
        $stmt->execute($values);

        // Foreach all of the countires and add them to the database.
        foreach ($summary["Countries"] as $countrySummary) {
            $dateAndTime = explode("T", $countrySummary["Date"]);
            $date = $dateAndTime[0];

            $values = [
                'id' => $countrySummary["ID"],
                'country' => $countrySummary["Country"],
                'country_code' => $countrySummary["CountryCode"],
                'slug' => $countrySummary["Slug"],
                'total_confirmed' => $countrySummary["TotalConfirmed"],
                'total_deaths' => $countrySummary["TotalDeaths"],
                'total_recovered' => $countrySummary["TotalRecovered"],
                'new_confirmed' => $countrySummary["NewConfirmed"],
                'new_deaths' => $countrySummary["NewDeaths"],
                'new_recovered' => $countrySummary["NewRecovered"],
                'date' => $date,
            ];
            $stmt->execute($values);
        }
        // Commiting all the queries.
        $conn->commit();
        echo "OK";
    } catch (Exception $e) {
        //Rollback the transaction if there is exceptions.
        $conn->rollBack();

        echo "There is an error writing summary. Please check logs!";
        file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
        die();
    }
}
