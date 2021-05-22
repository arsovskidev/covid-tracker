<?php

function writeStatistics($conn, $slug, $date)
{
    // Get the total statistics for all dates on country from the API.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/total/country/' . $slug . '?from=2021-01-01&to=' . $date);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $statistics = curl_exec($cURLConnection);

    // JSON decode them.
    $statistics = json_decode($statistics, true);
    $retry = 0;

    // If there is an connection error, retry to get response for 5 times with 5 seconds delay.
    while (empty($statistics[0]) && $retry < 5) {
        echo "\nRetrying connection in 5 seconds!";
        sleep(5);
        $statistics = curl_exec($cURLConnection);
        $statistics = json_decode($statistics, true);
        $retry++;

        if ($retry === 5) {
            echo "\nFailed 5 times, aborting stats for all country.";
            $conn->rollBack();
            die();
        }
    }

    curl_close($cURLConnection);

    try {
        // Create pdo insert query. 
        $sql = "INSERT INTO 
     statistics (id, slug, confirmed, deaths,
     recovered, active, date)
     
     VALUES (:id, :slug,
     :confirmed, :deaths, :recovered,
     :active, :date) 
     
     ON DUPLICATE KEY UPDATE confirmed=:confirmed, deaths=:deaths,
     recovered=:recovered, active=:active";

        $stmt = $conn->prepare($sql);
        echo "\nWriting\Updating all stats for " . $slug . " in database... ";

        // Check if there is data for the country.
        if (!empty($statistics[0])) {

            // Foreach all of the days and add them to the database.
            foreach ($statistics as $day) {
                $dateToUnix = strtotime($day["Date"]);
                // ID For statistics for each country is made up by the unix date of the stats added the country slug.
                // This makes up unique id for each country and it's stats date.
                // With this unique id it's easy just to update the days statistics whenever we want.

                $id = $dateToUnix . "-" . $slug;
                $date = date("Y-m-d", $dateToUnix);

                $values = [
                    'id' => $id,
                    'slug' => $slug,
                    'confirmed' => $day["Confirmed"],
                    'deaths' => $day["Deaths"],
                    'recovered' => $day["Recovered"],
                    'active' => $day["Active"],
                    'date' => $date,
                ];
                $stmt->execute($values);
            }
            echo "OK";
        } else {
            echo "\nNo data for country " . $slug;
        }
    } catch (Exception $e) {
        //Rollback the transaction if there is exceptions.
        $conn->rollBack();

        echo "\nThere is an error writing statistic for countries. Please check logs!";
        file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
        die();
    }
}
