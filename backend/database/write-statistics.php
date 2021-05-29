<?php

function writeStatistics($conn, $array, $dateFrom, $dateTo)
{
    // Start SQL Transactions.
    echo "Starting to write statistics in database...\n";
    $conn->beginTransaction();

    foreach ($array as $country) {
        // Sleep for 1 second to prevent API Hammering.
        sleep(1);

        // Get the total statistics for all dates on country from the API.
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/total/country/' . $country['slug'] . '?from=' . $dateFrom . '&to=' . $dateTo);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $statistics = curl_exec($cURLConnection);

        // JSON decode them.
        $statistics = json_decode($statistics, true);
        $retry = 0;

        // If there is an connection error, retry to get response for 5 times with 5 seconds delay.
        while (empty($statistics[0]) && $retry < 5) {
            echo "Retrying connection in 5 seconds!\n";
            sleep(5);
            $statistics = curl_exec($cURLConnection);
            $statistics = json_decode($statistics, true);
            $retry++;

            if ($retry === 5) {
                echo "Failed 5 times, aborting stats for all country.\n";
                $conn->rollBack();
                die();
            }
        }

        curl_close($cURLConnection);

        try {
            // Create pdo insert query. 
            $sql = "INSERT INTO 
                statistics (id, slug, country, confirmed, deaths,
                recovered, active, date)
     
                VALUES (:id, :slug, :country,
                :confirmed, :deaths, :recovered,
                :active, :date) 
     
                ON DUPLICATE KEY UPDATE confirmed=:confirmed, deaths=:deaths,
                recovered=:recovered, active=:active";

            $stmt = $conn->prepare($sql);
            echo "Writing\Updating all stats for " . $country['slug'] . " in database... ";

            // Check if there is data for the country.
            if (!empty($statistics[0])) {

                // Foreach all of the days and add them to the database.
                foreach ($statistics as $day) {
                    $dateToUnix = strtotime($day["Date"]);
                    $date = date("Y-m-d", $dateToUnix);

                    // ID For statistics for each country is made up by the unix date of the stats added the country slug.
                    // This makes up unique id for each country and it's stats date.
                    // With this unique id it's easy just to update the days statistics whenever we want.

                    // Hashing the id to create unique id hash.
                    $id = $dateToUnix . $country['slug'];
                    $hash = md5($id);

                    $values = [
                        'id' => $hash,
                        'slug' => $country['slug'],
                        'country' => $day["Country"],
                        'confirmed' => $day["Confirmed"],
                        'deaths' => $day["Deaths"],
                        'recovered' => $day["Recovered"],
                        'active' => $day["Active"],
                        'date' => $date,
                    ];
                    $stmt->execute($values);
                }
                echo "OK\n";
            } else {
                echo "No data for country " . $country['slug'] . "\n";
            }
        } catch (Exception $e) {
            //Rollback the transaction if there is exceptions.
            $conn->rollBack();

            echo "There is an error writing statistic for countries. Please check logs!\n";
            file_put_contents('../logs/' . date("Y-m-d") . '.log', $e . PHP_EOL, FILE_APPEND);
            die();
        }
    }
    $conn->commit();
    echo "Finished writing all statistics in database.\n";
}
