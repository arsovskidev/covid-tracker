<?php

function writeCountries($conn, $date)
{
    // Get the countries from the API.
    // I am using the /summary endpoint instead of /countries because there are countries with 0 data.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/summary');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $countries = curl_exec($cURLConnection);

    // JSON decode them.
    $countries = json_decode($countries, true);
    $retry = 0;

    // If there is an connection error, retry to get response for 5 times with 5 seconds delay.
    while (empty($countries["Countries"]) && $retry < 5) {
        echo "\nRetrying connection in 5 seconds!";
        sleep(5);
        $countries = curl_exec($cURLConnection);
        $countries = json_decode($countries, true);
        $retry++;

        if ($retry === 5) {
            echo "\nFailed 5 times, aborting.";
            die();
        }
    }

    curl_close($cURLConnection);

    $conn->beginTransaction();

    try {
        // Create pdo insert query. 
        $sql = "INSERT IGNORE INTO 
     countries (slug, country, code)
     VALUES (:slug, :country, :code)";

        $stmt = $conn->prepare($sql);

        echo "\nWriting countires in database...";

        // Foreach all of the countires and add them to the database.
        foreach ($countries['Countries'] as $country) {
            $values = [
                'slug' => $country["Slug"],
                'country' => $country["Country"],
                'code' => $country["CountryCode"],
            ];
            $stmt->execute($values);

            // Sleep 1 sec to prevent API Hammering.
            sleep(1);
            writeStatistics($conn, $country["Slug"], $date);
        }

        // Commiting all the queries.
        $conn->commit();
        echo "OK";
    } catch (Exception $e) {
        //Rollback the transaction if there is exceptions.
        $conn->rollBack();

        echo "\nThere is an error writing countries. Please check logs!";
        file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
        die();
    }
}
