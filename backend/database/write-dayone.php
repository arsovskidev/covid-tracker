<?php

function writeDayOne($conn, $slug)
{
    // Get the dayone for all dates on country from the API.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/dayone/country/' . $slug);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $dayone = curl_exec($cURLConnection);
    curl_close($cURLConnection);

    // Json decode them.
    $dayone = json_decode($dayone, true);

    // Starting SQL Transaction.
    $conn->beginTransaction();

    try {
        // Create pdo insert query. 
        $sql = "INSERT INTO 
     day_one (id, slug, confirmed, deaths,
     recovered, active, date) 
     
     VALUES (:id, :slug,
     :confirmed, :deaths, :recovered,
     :active, :date) 
     
     ON DUPLICATE KEY UPDATE confirmed=:confirmed, deaths=:deaths,
     recovered=:recovered, active=:active, date=:date";

        $stmt = $conn->prepare($sql);

        // Foreach all of the days and add them to the database.
        foreach ($dayone as $day) {
            $dateAndTime = explode("T", $day["Date"]);
            $date = $dateAndTime[0];

            $values = [
                'id' => $day["ID"],
                'slug' => $slug,
                'confirmed' => $day["Confirmed"],
                'deaths' => $day["Deaths"],
                'recovered' => $day["Recovered"],
                'active' => $day["Active"],
                'date' => $date,
            ];
            $stmt->execute($values);
        }

        // Commiting all the queries.
        echo "<br>Writing all days for " . $slug . " in database...";
        $conn->commit();
        echo "OK";
    } catch (Exception $e) {
        //Rollback the transaction if there is exceptions.
        $conn->rollBack();

        echo "There is an error writing dayone for countries. Please check logs!";
        file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
        die();
    }
}
